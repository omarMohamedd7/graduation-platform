<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class dashboard_controller extends Controller
{
    public function index(Request $request)
    {
        // load the one‑to‑one proposal relationship
        $proposal = $request->user()->proposal;
   

        // pass it into the view
        return view('dashboard.index', compact('proposal'));
    }
}
