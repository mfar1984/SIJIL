<?php

namespace Tests\Unit;

use App\Models\Event;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * The attendance create/edit forms receive the event list through @json, which is
 * plain json_encode over a plain array. A Carbon in that array serializes as a UTC
 * ISO-8601 timestamp, the form posts it straight back as sessions[0][date], and the
 * `date` column rejects it with SQLSTATE[22007] — a 500 on every "Scan once" save.
 *
 * No database is needed: an unsaved model casts start_date exactly the same way.
 */
class AttendanceEventDateSerializationTest extends TestCase
{
    public function test_a_bare_carbon_in_an_array_does_not_serialize_as_a_date(): void
    {
        $event = new Event(['start_date' => '2026-07-30']);

        $json = json_encode(['date' => $event->start_date]);

        $this->assertStringContainsString('T', $json);
        $this->assertStringContainsString('Z', $json);
    }

    public function test_to_date_string_gives_the_view_a_plain_y_m_d_value(): void
    {
        $event = new Event(['start_date' => '2026-07-30', 'end_date' => '2026-08-02']);

        $this->assertSame('2026-07-30', $event->start_date?->toDateString());
        $this->assertSame('2026-08-02', $event->end_date?->toDateString());
        $this->assertSame(
            '{"start_date":"2026-07-30","end_date":"2026-08-02"}',
            json_encode([
                'start_date' => $event->start_date?->toDateString(),
                'end_date' => $event->end_date?->toDateString(),
            ])
        );
    }

    public function test_the_session_date_rule_rejects_anything_that_is_not_y_m_d(): void
    {
        $rules = ['date' => 'required|date_format:Y-m-d'];

        $this->assertTrue(Validator::make(['date' => '2026-07-30'], $rules)->passes());

        foreach (['2026-07-29T16:00:00.000000Z', '2026-07-30 00:00:00', '30/07/2026'] as $bad) {
            $this->assertTrue(
                Validator::make(['date' => $bad], $rules)->fails(),
                "Expected {$bad} to be rejected by date_format:Y-m-d"
            );
        }
    }
}
