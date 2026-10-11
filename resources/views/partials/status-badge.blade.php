@php
    $badgeClass = [
        'pending' => 'bg-secondary',
        'accepted' => 'bg-info text-dark',
        'in_progress' => 'bg-primary',
        'delivered' => 'bg-success',
        'canceled' => 'bg-danger',
    ][$status] ?? 'bg-secondary';
@endphp
<span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
