<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;


class LandingPageController extends Controller
{
  public function index()
    {

        $plans = Plan::where('is_active', true)
                     ->with('features')
                     ->orderBy('price', 'asc')
                     ->get();

        return view('welcome', compact('plans'));
    }
}
