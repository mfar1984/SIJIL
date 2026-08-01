@props(['name' => 'race', 'selected' => null, 'id' => null])

@php
    // Kept in one place so the admin forms and the PWA profile offer the same
    // list. Values are stored verbatim, so changing a label changes the data.
    $races = [
        'Malay',
        'Chinese',
        'Indian',
        'Iban',
        'Bidayuh',
        'Melanau',
        'Orang Ulu',
        'Kadazan-Dusun',
        'Bajau',
        'Murut',
        'Bumiputera Sabah',
        'Bumiputera Sarawak',
        'Orang Asli',
        'Other',
    ];
@endphp

<select name="{{ $name }}" id="{{ $id ?? $name }}"
        {{ $attributes->merge(['class' => 'w-full h-9 text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50']) }}>
    <option value="">-- Select Race --</option>
    @foreach($races as $race)
        <option value="{{ $race }}" {{ (string) $selected === $race ? 'selected' : '' }}>{{ $race }}</option>
    @endforeach
</select>
