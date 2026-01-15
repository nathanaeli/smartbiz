<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Duka;
use App\Models\TenantOfficer;
use Carbon\Carbon;

class NotificationsDropdown extends Component
{
    public $notifications = [];
    public $unreadCount = 0;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = auth()->user();

        if ($user->hasRole('officer')) {
            $assignment = TenantOfficer::where('officer_id', $user->id)->where('status', true)->first();
            $tenantId = $assignment ? $assignment->tenant_id : null;
        } elseif ($user->hasRole('tenant')) {
            $tenantId = $user->tenant ? $user->tenant->id : null;
        } else {
            // superadmin or others
            $tenantId = null;
        }

        if (!$tenantId) {
            $this->notifications = [];
            $this->unreadCount = 0;
            return;
        }

        // Get dukas with expiring subscriptions (within 7 days)
        $expiringDukas = Duka::where('tenant_id', $tenantId)
            ->whereHas('dukaSubscriptions', function($query) {
                $query->where('status', 'active')
                      ->where('end_date', '>', now())
                      ->where('end_date', '<=', now()->addDays(7));
            })
            ->with(['dukaSubscriptions' => function($query) {
                $query->where('status', 'active')
                      ->where('end_date', '>', now())
                      ->where('end_date', '<=', now()->addDays(7))
                      ->latest('end_date');
            }])
            ->get();

        $this->notifications = [];

        foreach ($expiringDukas as $duka) {
            $subscription = $duka->dukaSubscriptions->first();
            if ($subscription) {
                $daysLeft = now()->diffInDays($subscription->end_date, false);

                $this->notifications[] = [
                    'id' => 'duka_' . $duka->id . '_subscription',
                    'type' => 'subscription_expiring',
                    'title' => 'Subscription Expiring Soon',
                    'message' => "Duka '{$duka->name}' subscription expires in {$daysLeft} days",
                    'time' => $subscription->end_date->format('M d, Y'),
                    'icon' => 'warning',
                    'color' => $daysLeft <= 1 ? 'danger' : ($daysLeft <= 3 ? 'warning' : 'info'),
                    'url' => route('duka.show', $duka->id),
                    'read' => false
                ];
            }
        }

        // Get expired subscriptions (past due)
        $expiredDukas = Duka::where('tenant_id', $tenantId)
            ->whereHas('dukaSubscriptions', function($query) {
                $query->where('status', 'active')
                      ->where('end_date', '<', now());
            })
            ->with(['dukaSubscriptions' => function($query) {
                $query->where('status', 'active')
                      ->where('end_date', '<', now())
                      ->latest('end_date');
            }])
            ->get();

        foreach ($expiredDukas as $duka) {
            $subscription = $duka->dukaSubscriptions->first();
            if ($subscription) {
                $daysExpired = $subscription->end_date->diffInDays(now());

                $this->notifications[] = [
                    'id' => 'duka_' . $duka->id . '_expired',
                    'type' => 'subscription_expired',
                    'title' => 'Subscription Expired',
                    'message' => "Duka '{$duka->name}' subscription expired {$daysExpired} days ago",
                    'time' => $subscription->end_date->format('M d, Y'),
                    'icon' => 'error',
                    'color' => 'danger',
                    'url' => route('duka.show', $duka->id),
                    'read' => false
                ];
            }
        }

        // Sort notifications by urgency (expired first, then by days left)
        $this->notifications = collect($this->notifications)->sortBy(function($notification) {
            if ($notification['type'] === 'subscription_expired') return 0;
            return $notification['days_left'] ?? 999;
        })->values()->all();

        $this->unreadCount = count(array_filter($this->notifications, function($n) {
            return !$n['read'];
        }));
    }

    public function markAsRead($notificationId)
    {
        // In a real app, you'd store this in the database
        // For now, we'll just update the local array
        foreach ($this->notifications as &$notification) {
            if ($notification['id'] === $notificationId) {
                $notification['read'] = true;
                break;
            }
        }
        $this->unreadCount = count(array_filter($this->notifications, function($n) {
            return !$n['read'];
        }));
    }

    public function render()
    {
        return view('livewire.notifications-dropdown');
    }
}
