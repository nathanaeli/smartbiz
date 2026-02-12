<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\Tenant;
use App\Models\DukaSubscription;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function checkout($tenant, $subscription)
    {
        try {
            $tenantId = Crypt::decrypt($tenant);
            $subscriptionId = Crypt::decrypt($subscription);
        } catch (\Exception $e) {
            \Log::error('Payment checkout decryption failed', [
                'tenant_param' => $tenant,
                'subscription_param' => $subscription,
                'error' => $e->getMessage(),
            ]);
            abort(404, 'Invalid checkout link.');
        }

        $tenant = Tenant::findOrFail($tenantId);
        $subscription = DukaSubscription::findOrFail($subscriptionId);

        \Log::info('Payment checkout accessed', [
            'tenant_id' => $tenantId,
            'subscription_id' => $subscriptionId,
            'amount' => $subscription->amount,
        ]);

        return view('payment.checkout', [
            'tenant'       => $tenant,
            'subscription' => $subscription,
            'duka'         => $subscription->duka,
            'plan'         => $subscription->plan,
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:duka_subscriptions,id',
            'tenant_id' => 'required|exists:tenants,id',
            'payment_method' => 'required|in:card,mpesa,airtel',
        ]);

        $subscription = DukaSubscription::findOrFail($request->subscription_id);
        $tenant = Tenant::findOrFail($request->tenant_id);

        // Here you would integrate with actual payment gateway
        // For now, simulate payment success
        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'payment_method' => $request->payment_method,
            'amount' => $subscription->amount,
            'currency' => 'TZS',
            'status' => 'paid',
            'transaction_id' => 'TXN_' . time(),
        ]);

        // Update subscription status
        $subscription->update([
            'status' => 'active',
            'payment_method' => $request->payment_method,
        ]);

        return redirect()->route('tenant.dashboard')->with('success', 'Payment completed successfully!');
    }
}
