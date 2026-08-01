<?php

namespace App\Services;

use App\Http\Controllers\CertificateController;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\GlobalConfig;
use App\Models\Participant;
use Illuminate\Support\Facades\Log;

/**
 * Issues one certificate for one participant and delivers it.
 *
 * The admin screen generates certificates in bulk from a form where the template
 * is chosen by hand. Events with `auto_generate_certificate` enabled need the
 * same work done for a single participant the moment they register, so the
 * generate-record-deliver sequence lives here and both paths share it.
 */
class CertificateIssuer
{
    /**
     * @return array{0: bool, 1: string}  [ok, message]
     */
    public function issueFor(Event $event, Participant $participant): array
    {
        $template = $this->resolveTemplate($event);

        if (!$template) {
            return [false, 'No certificate template is set for this event, so nothing was issued.'];
        }

        $existing = Certificate::where('event_id', $event->id)
            ->where('participant_id', $participant->id)
            ->first();

        if ($existing) {
            return [true, 'A certificate already exists for this participant (' . $existing->certificate_number . ').'];
        }

        $number = app(CertificateNumberGenerator::class)->generate();

        // The record is created first because the PDF renderer looks it up to
        // stamp the certificate number and QR payload.
        $certificate = Certificate::create([
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'template_id' => $template->id,
            'certificate_number' => $number,
            'pdf_file' => '',
            'generated_at' => now(),
            // No authenticated user on the public registration route.
            'generated_by' => $event->user_id,
        ]);

        try {
            $pdfPath = app(CertificateController::class)
                ->generateCertificatePDF($event, $participant, $template, false, $number);

            $certificate->pdf_file = $pdfPath;
            $certificate->save();
        } catch (\Throwable $e) {
            // A record pointing at a PDF that was never written would show up in
            // the participant's app as a broken download, so remove it.
            $certificate->forceDelete();

            Log::error('Automatic certificate generation failed', [
                'event_id' => $event->id,
                'participant_id' => $participant->id,
                'error' => $e->getMessage(),
            ]);

            return [false, 'Certificate could not be generated: ' . $e->getMessage()];
        }

        $delivered = $this->deliver($event, $participant, $certificate);

        return [true, 'Certificate ' . $number . ' issued. ' . $delivered];
    }

    /**
     * Where the template comes from: the event's own choice, falling back to the
     * organizer's most recent active template so a missing selection does not
     * silently skip the certificate.
     */
    protected function resolveTemplate(Event $event): ?CertificateTemplate
    {
        if ($event->certificate_template_id) {
            $chosen = CertificateTemplate::find($event->certificate_template_id);

            if ($chosen) {
                return $chosen;
            }
        }

        return CertificateTemplate::where('user_id', $event->user_id)
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    /**
     * Email, SMS and Telegram, each honouring the global notification switches
     * and each isolated so one failure does not block the others.
     */
    protected function deliver(Event $event, Participant $participant, Certificate $certificate): string
    {
        $globalConfig = GlobalConfig::getConfig();
        $done = [];

        if ($globalConfig && $globalConfig->email_certificate_generated && $participant->email) {
            try {
                $mailable = $participant->registration_type === 'simplified'
                    ? new \App\Mail\CertificateGeneratedSimplified($event, $participant, $certificate)
                    : new \App\Mail\CertificateGeneratedNotification($event, $participant, $certificate);

                (new EmailService())->sendEmail($event->user_id, $mailable, $participant->email);
                $done[] = 'emailed';
            } catch (\Throwable $e) {
                Log::error('Automatic certificate email failed', ['error' => $e->getMessage()]);
                $done[] = 'email failed';
            }
        }

        if ($globalConfig && $globalConfig->sms_certificate_generated && $participant->phone) {
            try {
                $message = "Congratulations! Your certificate for {$event->name} is ready."
                    . " Certificate No: {$certificate->certificate_number}."
                    . ' Please check your email for download instructions.';

                (new InfobipService())->sendSms($participant->phone, $message, $event->user_id);
                $done[] = 'SMS sent';
            } catch (\Throwable $e) {
                Log::error('Automatic certificate SMS failed', ['error' => $e->getMessage()]);
                $done[] = 'SMS failed';
            }
        }

        if ($globalConfig && $globalConfig->telegram_certificate_generated) {
            try {
                $telegram = new TelegramService();

                if ($telegram->isEnabled()) {
                    $telegram->sendCertificateGeneratedNotification($participant, $event, $certificate);
                    $done[] = 'Telegram notified';
                }
            } catch (\Throwable $e) {
                Log::error('Automatic certificate Telegram failed', ['error' => $e->getMessage()]);
                $done[] = 'Telegram failed';
            }
        }

        return $done ? implode(', ', $done) . '.' : 'No delivery channels are enabled.';
    }
}
