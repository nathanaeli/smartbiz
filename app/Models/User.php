<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;   // ← REQUIRED
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity, HasRoles;  // ← REQUIRED

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_picture',
        'status',
        'tenant_id'

    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the duka associated with the user.
     */
    public function duka()
    {
        return $this->hasOne(Duka::class, 'tenant_id');
    }

    /**
     * Get the tenant associated with the user.
     */
    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }

    /**
     * Get the tenant officers (when user is a tenant, get their officers).
     */
    public function tenantOfficers()
    {
        return $this->hasMany(TenantOfficer::class, 'tenant_id');
    }

    /**
     * Get the officer assignments (when user is an officer, get their assignments).
     */
    public function officerAssignments()
    {
        return $this->hasMany(TenantOfficer::class, 'officer_id');
    }

    /**
     * Get the tenant account associated with the user.
     */
    public function tenantAccount()
    {
        return $this->hasOne(TenantAccount::class, 'tenant_id');
    }

    /**
     * Get the profile picture URL
     */
    public function getProfilePictureUrlAttribute(): string
    {
        if (!$this->profile_picture) {
            return asset('images/no-profile.png');
        }

        // Check if profile_picture is already a full URL
        if (filter_var($this->profile_picture, FILTER_VALIDATE_URL)) {
            return $this->profile_picture;
        }

        // Otherwise, treat as local filename
        return asset('storage/profiles/' . $this->profile_picture);
    }

    /**
     * Get the messages sent by the user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token, $isApiRequest = false)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token, $isApiRequest));
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permissionName)
    {
        // If user is super-admin or tenant, they have all permissions
        if ($this->hasRole('superadmin') || $this->hasRole('tenant')) {
            return true;
        }

        // If user is officer, check staff permissions based on active assignments
        if ($this->hasRole('officer')) {
            // Get tenant ID from officer's active assignments
            $assignment = TenantOfficer::where('officer_id', $this->id)
                ->where('status', true)
                ->first();

            if ($assignment) {
                return StaffPermission::where('tenant_id', $assignment->tenant_id)
                    ->where('officer_id', $this->id)
                    ->where('permission_name', $permissionName)
                    ->where('is_granted', true)
                    ->exists();
            }
        }

        return false;
    }

    /**
     * Get user's permissions
     */
    public function getPermissions()
    {
        if ($this->hasRole('superadmin') || $this->hasRole('tenant')) {
            return collect(['all']); // Super-admins and tenants have all permissions
        }

        if ($this->hasRole('officer')) {
            // Get tenant ID from officer's active assignments
            $assignment = TenantOfficer::where('officer_id', $this->id)
                ->where('status', true)
                ->first();

            if ($assignment) {
                return StaffPermission::where('tenant_id', $assignment->tenant_id)
                    ->where('officer_id', $this->id)
                    ->where('is_granted', true)
                    ->pluck('permission_name');
            }
        }

        return collect();
    }


    /**
     * Activity Log Options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')         // recommended for filtering
            ->logFillable()              // log name & email changes
            ->logOnlyDirty()             // only changed fields
            ->dontSubmitEmptyLogs();     // avoid empty logs
    }
}
