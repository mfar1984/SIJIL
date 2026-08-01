<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\Campaign;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\HelpdeskTicket;
use App\Models\Participant;
use App\Models\PwaEmailTemplate;
use App\Models\PwaParticipant;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Central registry of everything that lands in the Recycle Bin.
 *
 * The order below intentionally mirrors the sidebar so the Recycle Bin,
 * the sidebar and the permission matrix all read the same way.
 */
class RecycleBin
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function types(): array
    {
        return [
            // --- Event Operations ---
            'events' => [
                'model' => Event::class,
                'label' => 'Event',
                'plural' => 'Events',
                'icon' => 'event',
                'title' => 'name',
                'subtitle' => fn(Model $m) => $m->location,
                'owner_column' => 'user_id',
            ],
            'surveys' => [
                'model' => Survey::class,
                'label' => 'Survey',
                'plural' => 'Surveys',
                'icon' => 'quiz',
                'title' => 'title',
                'subtitle' => fn(Model $m) => $m->event?->name,
                'owner_column' => 'user_id',
            ],
            'participants' => [
                'model' => Participant::class,
                'label' => 'Participant',
                'plural' => 'Participants',
                'icon' => 'people',
                'title' => 'name',
                'subtitle' => fn(Model $m) => trim(($m->email ?? '') . ' · ' . ($m->event()->withTrashed()->first()?->name ?? '—'), ' ·'),
                'owner_via_event' => true,
            ],
            'attendances' => [
                'model' => Attendance::class,
                'label' => 'Attendance',
                'plural' => 'Attendance Sessions',
                'icon' => 'how_to_reg',
                'title' => 'name',
                'subtitle' => fn(Model $m) => $m->event()->withTrashed()->first()?->name,
                'owner_column' => 'created_by',
            ],
            'certificates' => [
                'model' => Certificate::class,
                'label' => 'Certificate',
                'plural' => 'Certificates',
                'icon' => 'workspace_premium',
                'title' => 'certificate_number',
                'subtitle' => fn(Model $m) => $m->participant()->withTrashed()->first()?->name,
                'owner_column' => 'generated_by',
            ],
            'certificate_templates' => [
                'model' => CertificateTemplate::class,
                'label' => 'Certificate Template',
                'plural' => 'Certificate Templates',
                'icon' => 'design_services',
                'title' => 'name',
                'subtitle' => fn(Model $m) => $m->description,
                'owner_column' => 'user_id',
            ],

            // --- Engagement ---
            'pwa_participants' => [
                'model' => PwaParticipant::class,
                'label' => 'PWA Participant',
                'plural' => 'PWA Participants',
                'icon' => 'smartphone',
                'title' => 'name',
                'subtitle' => fn(Model $m) => $m->email,
                'owner_column' => 'created_by',
            ],
            'pwa_email_templates' => [
                'model' => PwaEmailTemplate::class,
                'label' => 'PWA Email Template',
                'plural' => 'PWA Email Templates',
                'icon' => 'email',
                'title' => 'name',
                'subtitle' => fn(Model $m) => $m->subject,
                'owner_column' => 'user_id',
            ],
            'campaigns' => [
                'model' => Campaign::class,
                'label' => 'Campaign',
                'plural' => 'Campaigns',
                'icon' => 'campaign',
                'title' => 'name',
                'subtitle' => fn(Model $m) => $m->campaign_type,
                'owner_column' => 'user_id',
            ],

            // --- System ---
            'helpdesk_tickets' => [
                'model' => HelpdeskTicket::class,
                'label' => 'Helpdesk Ticket',
                'plural' => 'Helpdesk Tickets',
                'icon' => 'help',
                'title' => 'subject',
                'subtitle' => fn(Model $m) => $m->ticket_id,
                'owner_column' => 'user_id',
            ],
            'users' => [
                'model' => User::class,
                'label' => 'User',
                'plural' => 'Users',
                'icon' => 'manage_accounts',
                'title' => 'name',
                'subtitle' => fn(Model $m) => $m->email,
                'admin_only' => true,
            ],
        ];
    }

    /**
     * Resolve a single registry entry, or null when the slug is unknown.
     *
     * @return array<string, mixed>|null
     */
    public static function type(string $slug): ?array
    {
        return static::types()[$slug] ?? null;
    }

    /**
     * Build a trashed-only query for the given type, scoped to what the
     * current user is allowed to see.
     */
    public static function query(string $slug): ?Builder
    {
        $type = static::type($slug);

        if (!$type) {
            return null;
        }

        $user = auth()->user();
        $isAdmin = $user && $user->hasRole('Administrator');

        if (!empty($type['admin_only']) && !$isAdmin) {
            return null;
        }

        /** @var Builder $query */
        $query = $type['model']::onlyTrashed();

        if (!$isAdmin) {
            if (!empty($type['owner_via_event'])) {
                $query->whereIn('event_id', Event::withTrashed()->where('user_id', $user->id)->pluck('id'));
            } elseif (!empty($type['owner_column'])) {
                $query->where($type['owner_column'], $user->id);
            }
        }

        return $query;
    }

    /**
     * Human readable label for a record inside the bin.
     */
    public static function titleFor(string $slug, Model $model): string
    {
        $type = static::type($slug);
        $value = $type ? ($model->{$type['title']} ?? null) : null;

        return $value !== null && $value !== '' ? (string) $value : ('#' . $model->getKey());
    }

    /**
     * Secondary line for a record inside the bin.
     */
    public static function subtitleFor(string $slug, Model $model): ?string
    {
        $type = static::type($slug);

        if (!$type || !isset($type['subtitle'])) {
            return null;
        }

        try {
            $value = ($type['subtitle'])($model);
        } catch (\Throwable $e) {
            return null;
        }

        return $value !== null && $value !== '' ? (string) $value : null;
    }
}
