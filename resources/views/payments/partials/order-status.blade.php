@php
    $orderTone = [
        'pending' => 'gray',
        'awaiting_payment' => 'blue',
        'fulfilled' => 'green',
        'needs_review' => 'red',
        'failed' => 'red',
        'cancelled' => 'gray',
        'expired' => 'amber',
    ][$order->status] ?? 'gray';
@endphp
@include('payments.partials.badge', ['tone' => $orderTone, 'label' => $order->statusLabel()])
@if($order->is_test)
    @include('payments.partials.badge', ['tone' => 'purple', 'label' => 'TEST'])
@endif
