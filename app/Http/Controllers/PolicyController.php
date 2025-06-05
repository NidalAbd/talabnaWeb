<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function index(Request $request)
    {
        // Determine which policy to show based on the query parameter
        if ($request->has('terms')) {
            return view('terms_of_service');
        } elseif ($request->has('child_safety')) {
            return view('policy');
        } else {
            return view('privacy_policy');
        }
    }
}
