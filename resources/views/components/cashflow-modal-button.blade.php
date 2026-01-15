@props([
    'duka' => null,
    'type' => 'expense',
    'size' => 'md', // sm, md, lg
    'class' => ''
])

@php
    $sizeClasses = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg'
    ];

    $btnClass = $type === 'income'
        ? 'btn-success'
        : 'btn-danger';

    $icon = $type === 'income'
        ? 'fa-plus-circle'
        : 'fa-minus-circle';

    $text = $type === 'income'
        ? __('Add Income')
        : __('Add Expense');
@endphp

<button
    type="button"
    class="btn {{ $btnClass }} {{ $sizeClasses[$size] ?? '' }} {{ $class }}"
    @click="$dispatch('openCashflowModal', { dukaId: {{ $duka?->id ?? 'null' }}, type: '{{ $type }}' })"
    {{ $attributes }}
>
    <i class="fas {{ $icon }} me-2"></i>
    {{ $text ?? $text }}
</button>
