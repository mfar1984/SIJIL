@php
    $resultTone = match ($result) {
        'fulfilled', 'already_fulfilled' => 'green',
        'recorded', 'cancelled', 'expired', 'not_yet_paid' => 'blue',
        'replayed', 'ignored', 'order_not_ready' => 'gray',
        'needs_review', 'rejected', 'error' => 'red',
        default => 'gray',
    };
@endphp
@include('payments.partials.badge', ['tone' => $resultTone, 'label' => str_replace('_', ' ', (string) $result)])
