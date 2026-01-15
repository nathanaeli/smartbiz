<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Duka;
use App\Models\DukaSubscription;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Start a free trial for a new Duka
     */
    public function startTrial(Duka $duka, Plan $plan)
    {
        return DukaSubscription::create([
            'tenant_id'  => $duka->tenant_id,
            'duka_id'    => $duka->id,
            'plan_id'    => $plan->id,
            'plan_name'  => $plan->name,
            'amount'     => 0, // Free
            'start_date' => now(),
            'end_date'   => now()->addDays(14), // 14-day trial
            'status'     => 'trialing',
        ]);
    }

    /**
     * Convert trial or renew to paid subscription
     */
    public function subscribe(Duka $duka, Plan $plan, $transactionId = null)
    {
        return DukaSubscription::create([
            'tenant_id'      => $duka->tenant_id,
            'duka_id'        => $duka->id,
            'plan_id'        => $plan->id,
            'plan_name'      => $plan->name,
            'amount'         => $plan->price,
            'start_date'     => now(),
            'end_date'       => $plan->billing_cycle === 'monthly' ? now()->addMonth() : now()->addYear(),
            'status'         => 'active',
            'transaction_id' => $transactionId,
        ]);
    }
}
