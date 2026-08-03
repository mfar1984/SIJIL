<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\GlobalConfig;
use App\Models\Participant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Sends the event reminders the Notifications tab has always offered.
 *
 * "Send event reminder" for email and SMS, and the "Reminder hours before event"
 * value, were stored and read by nothing at all: no command, no job and no
 * scheduled task existed, so no reminder was ever sent for any event.
 */
class SendEventReminders extends Command
{
    protected $signature = 'events:remind
        {--dry-run : List who would be contacted without sending anything}
        {--event= : Restrict to one event id}';

    protected $description = 'Send reminders for events starting within the configured window';

    public function handle(): int
    {
        $config = GlobalConfig::getConfig();

        $byEmail = (bool) ($config->email_event_reminder ?? false);
        $bySms = (bool) ($config->sms_event_reminder ?? false);

        if (! $byEmail && ! $bySms) {
            $this->info('Event reminders are switched off on the Notifications tab. Nothing to do.');

            return self::SUCCESS;
        }

        $hours = max(1, min(72, (int) ($config->sms_reminder_hours ?? 24)));

        /*
         * The window is one hour wide and sits exactly $hours ahead of now, so an
         * hourly run reaches each event once. A "starts within the next N hours"
         * query would instead match the same event on every run until it began.
         */
        $from = now()->addHours($hours);
        $to = $from->copy()->addHour();

        $events = Event::query()
            ->when($this->option('event'), fn ($q) => $q->whereKey($this->option('event')))
            ->whereNotNull('start_date')
            ->whereBetween('start_date', [$from, $to])
            ->where(function ($query) {
                $query->whereNull('status')->orWhereNotIn('status', ['cancelled', 'completed', 'archived']);
            })
            ->get();

        if ($events->isEmpty()) {
            $this->info(sprintf(
                'No events starting between %s and %s.',
                $from->toDateTimeString(),
                $to->toDateTimeString()
            ));

            return self::SUCCESS;
        }

        $emailed = 0;
        $texted = 0;
        $skipped = 0;

        foreach ($events as $event) {
            $participants = Participant::where('event_id', $event->id)
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 'active');
                })
                ->get();

            $this->line(sprintf(
                '%s (starts %s) - %d participant(s)',
                $event->name,
                $event->start_date->toDateTimeString(),
                $participants->count()
            ));

            foreach ($participants as $participant) {
                if ($this->alreadyReminded($event->id, $participant->id)) {
                    $skipped++;
                    continue;
                }

                $sent = false;

                if ($byEmail && filled($participant->email)) {
                    if ($this->option('dry-run')) {
                        $this->line('   email -> ' . $participant->email);
                        $sent = true;
                    } else {
                        try {
                            (new \App\Services\EmailService())->sendEmail(
                                $event->user_id,
                                new \App\Mail\EventReminder($event, $participant, $hours),
                                $participant->email
                            );
                            $emailed++;
                            $sent = true;
                        } catch (\Throwable $e) {
                            Log::error('Event reminder email failed', [
                                'event' => $event->id,
                                'participant' => $participant->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }

                if ($bySms && filled($participant->phone)) {
                    $message = sprintf(
                        'Reminder: %s starts %s at %s.',
                        $event->name,
                        $event->start_date->format('d/m/Y g:ia'),
                        $event->location ?: 'the announced venue'
                    );

                    if ($this->option('dry-run')) {
                        $this->line('   sms   -> ' . $participant->phone);
                        $sent = true;
                    } else {
                        try {
                            (new \App\Services\InfobipService())->sendSms(
                                $participant->phone,
                                $message,
                                $event->user_id
                            );
                            $texted++;
                            $sent = true;
                        } catch (\Throwable $e) {
                            Log::error('Event reminder SMS failed', [
                                'event' => $event->id,
                                'participant' => $participant->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }

                if ($sent && ! $this->option('dry-run')) {
                    $this->markReminded($event->id, $participant->id);
                }
            }
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run: nothing was sent.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%d email(s) and %d SMS sent. %d participant(s) already had a reminder.',
            $emailed,
            $texted,
            $skipped
        ));

        activity('notifications')
            ->withProperties([
                'events' => $events->pluck('id')->all(),
                'emailed' => $emailed,
                'texted' => $texted,
                'hours_ahead' => $hours,
            ])
            ->log("Event reminders sent ({$emailed} email, {$texted} SMS)");

        return self::SUCCESS;
    }

    /**
     * Guards against a repeat if the command runs twice in the same window, for
     * example after a failed run is retried by hand.
     */
    private function alreadyReminded(int $eventId, int $participantId): bool
    {
        if (! Schema::hasTable('activity_log')) {
            return false;
        }

        return DB::table('activity_log')
            ->where('log_name', 'notifications')
            ->where('description', 'Event reminder sent')
            ->where('properties->event_id', $eventId)
            ->where('properties->participant_id', $participantId)
            ->exists();
    }

    private function markReminded(int $eventId, int $participantId): void
    {
        activity('notifications')
            ->withProperties(['event_id' => $eventId, 'participant_id' => $participantId])
            ->log('Event reminder sent');
    }
}
