<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalesofficerControllers extends Controller
{


    public function officersalesstocks()
    {
        return view('salesofficer.index');
    }
}
