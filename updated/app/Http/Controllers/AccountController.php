<?php

namespace App\Http\Controllers;

use App\Models\TenantAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function index()
    {
        $tenantId = Auth::id();

        $account = TenantAccount::where('tenant_id', $tenantId)->first();

        return view('account.index', compact('account'));
    }

    public function create()
    {
        $tenantId = Auth::id();

        // Check if account already exists
        $existingAccount = TenantAccount::where('tenant_id', $tenantId)->first();
        if ($existingAccount) {
            return redirect()->route('accountsetup')->with('error', 'Account already exists. You can edit it instead.');
        }

        return view('account.create');
    }

    public function store(Request $request)
    {
        $tenantId = Auth::id();

        // Check if account already exists
        $existingAccount = TenantAccount::where('tenant_id', $tenantId)->first();
        if ($existingAccount) {
            return redirect()->route('accountsetup')->with('error', 'Account already exists. You can edit it instead.');
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'currency' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'company_name', 'phone', 'email', 'address',
            'currency', 'timezone', 'website', 'description'
        ]);
        $data['tenant_id'] = $tenantId;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('account', 'public');
            $data['logo'] = basename($logoPath);
        }

        TenantAccount::create($data);

        return redirect()->route('accountsetup')->with('success', 'Account created successfully!');
    }

    public function show()
    {
        $tenantId = Auth::id();

        $account = TenantAccount::where('tenant_id', $tenantId)->firstOrFail();

        return view('account.show', compact('account'));
    }

    public function edit()
    {
        $tenantId = Auth::id();

        $account = TenantAccount::where('tenant_id', $tenantId)->firstOrFail();

        return view('account.edit', compact('account'));
    }

    public function update(Request $request)
    {
        $tenantId = Auth::id();

        $account = TenantAccount::where('tenant_id', $tenantId)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'currency' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'company_name', 'phone', 'email', 'address',
            'currency', 'timezone', 'website', 'description'
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($account->logo && Storage::disk('public')->exists('account/' . $account->logo)) {
                Storage::disk('public')->delete('account/' . $account->logo);
            }

            $logoPath = $request->file('logo')->store('account', 'public');
            $data['logo'] = basename($logoPath);
        }

        $account->update($data);

        return redirect()->route('accountsetup')->with('success', 'Account updated successfully!');
    }

    public function destroy()
    {
        $tenantId = Auth::id();

        $account = TenantAccount::where('tenant_id', $tenantId)->firstOrFail();

        // Delete logo file if exists
        if ($account->logo && Storage::disk('public')->exists('account/' . $account->logo)) {
            Storage::disk('public')->delete('account/' . $account->logo);
        }

        $account->delete();

        return redirect()->route('accountsetup')->with('success', 'Account deleted successfully!');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only(['name', 'email']);

        // Handle profile picture removal
        if ($request->input('remove_profile_picture') == '1') {
            if ($user->profile_picture && Storage::disk('public')->exists('profiles/' . $user->profile_picture)) {
                Storage::disk('public')->delete('profiles/' . $user->profile_picture);
            }
            $data['profile_picture'] = null;
        }
        // Handle profile picture upload
        elseif ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists('profiles/' . $user->profile_picture)) {
                Storage::disk('public')->delete('profiles/' . $user->profile_picture);
            }

            $profilePath = $request->file('profile_picture')->store('profiles', 'public');
            $data['profile_picture'] = basename($profilePath);
        }

        $user->update($data);

        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }

    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'default_password' => 'required|string|min:4|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $tenant = Auth::user()->tenant;
        $tenant->update([
            'default_password' => $request->default_password,
        ]);

        return redirect()->back()->with('success', 'Security settings updated successfully!');
    }
}
