<?php

namespace App\Http\Controllers;

use App\Models\StaffPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $permissions = StaffPermission::where('officer_id', $user->id)->get();

        return response()->json(['permissions' => $permissions]);
    }
}
