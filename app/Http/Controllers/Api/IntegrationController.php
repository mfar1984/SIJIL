<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The read-only integration surface, authenticated by API key.
 *
 * Every response is an explicit field list. Returning whole models is how the
 * old participant search endpoint ended up handing out IC numbers and home
 * addresses to anyone who called it, so nothing here returns a model directly.
 */
class IntegrationController extends Controller
{
    private const MAX_PER_PAGE = 100;

    public function events(Request $request): JsonResponse
    {
        $events = Event::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('since'), fn ($q) => $q->where('updated_at', '>=', $request->date('since')))
            ->orderByDesc('start_date')
            ->paginate($this->perPage($request));

        return $this->paginated($events, fn (Event $event) => $this->eventFields($event));
    }

    public function event(Request $request, int $event): JsonResponse
    {
        $model = Event::find($event);

        if (! $model) {
            return response()->json(['success' => false, 'message' => 'Event not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->eventFields($model) + [
                'participant_count' => Participant::where('event_id', $model->id)->count(),
                'certificate_count' => Certificate::where('event_id', $model->id)->count(),
            ],
        ]);
    }

    public function participants(Request $request): JsonResponse
    {
        $participants = Participant::query()
            ->when($request->filled('event_id'), fn ($q) => $q->where('event_id', $request->integer('event_id')))
            ->when($request->filled('since'), fn ($q) => $q->where('updated_at', '>=', $request->date('since')))
            ->orderByDesc('created_at')
            ->paginate($this->perPage($request));

        // No identity_card, passport_no, address or date_of_birth. An integration
        // that legitimately needs those should be a separate, separately granted
        // ability rather than something every participants.read key receives.
        return $this->paginated($participants, fn (Participant $p) => [
            'id' => $p->id,
            'event_id' => $p->event_id,
            'name' => $p->name,
            'email' => $p->email,
            'organization' => $p->organization,
            'status' => $p->status,
            'registration_type' => $p->registration_type,
            'registered_at' => optional($p->registration_date)->toIso8601String(),
        ]);
    }

    public function certificates(Request $request): JsonResponse
    {
        $certificates = Certificate::query()
            ->when($request->filled('event_id'), fn ($q) => $q->where('event_id', $request->integer('event_id')))
            ->when($request->filled('since'), fn ($q) => $q->where('updated_at', '>=', $request->date('since')))
            ->orderByDesc('generated_at')
            ->paginate($this->perPage($request));

        return $this->paginated($certificates, fn (Certificate $c) => [
            'id' => $c->id,
            'certificate_number' => $c->certificate_number,
            'event_id' => $c->event_id,
            'participant_id' => $c->participant_id,
            'generated_at' => optional($c->generated_at)->toIso8601String(),
            'verify_url' => route('certificate.verify', ['number' => $c->certificate_number]),
        ]);
    }

    public function whoami(Request $request): JsonResponse
    {
        /** @var \App\Models\ApiKey $key */
        $key = $request->attributes->get('api_key');

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $key->name,
                'prefix' => $key->key_prefix,
                'abilities' => $key->abilities ?? [],
                'expires_at' => optional($key->expires_at)->toIso8601String(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventFields(Event $event): array
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'location' => $event->location,
            'status' => $event->status,
            'start_date' => optional($event->start_date)->toIso8601String(),
            'end_date' => optional($event->end_date)->toIso8601String(),
            'max_participants' => $event->max_participants,
        ];
    }

    private function perPage(Request $request): int
    {
        return max(1, min(self::MAX_PER_PAGE, $request->integer('per_page', 25)));
    }

    /**
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     */
    private function paginated($paginator, callable $transform): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => collect($paginator->items())->map($transform)->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
