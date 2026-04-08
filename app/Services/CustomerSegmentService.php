<?php

namespace App\Services;

use App\Models\User;

class CustomerSegmentService
{
    /**
     * Determine the lifecycle segment of a customer.
     *
     * @return string  'new' | 'returning' | 'vip' | 'dormant'
     */
    public function segment(User $user): string
    {
        $orderCount = $user->orders()->count();
        $totalSpent = (float) $user->orders()->sum('grand_total');
        $lastOrderAt = $user->orders()->max('placed_at');

        // Dormant: has orders but last order > 90 days ago
        if ($orderCount > 0 && $lastOrderAt && now()->diffInDays($lastOrderAt) > 90) {
            return 'dormant';
        }

        // VIP: 5+ orders OR lifetime spend > ₹10,000
        if ($orderCount >= 5 || $totalSpent > 10000) {
            return 'vip';
        }

        // Returning: 2-4 orders
        if ($orderCount >= 2) {
            return 'returning';
        }

        // New: 0-1 orders
        return 'new';
    }

    /**
     * Return a human-readable label + CSS class for the segment.
     *
     * @return array{label: string, color: string}
     */
    public function segmentBadge(User $user): array
    {
        $seg = $this->segment($user);

        return match ($seg) {
            'vip'       => ['label' => 'VIP Customer',      'color' => 'success'],
            'returning' => ['label' => 'Returning Customer', 'color' => 'primary'],
            'dormant'   => ['label' => 'Dormant',            'color' => 'warning'],
            default     => ['label' => 'New Customer',       'color' => 'info'],
        };
    }
}
