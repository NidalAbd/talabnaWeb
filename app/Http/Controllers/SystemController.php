<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function systemLogs()
    {
        return view('admin.system.logs');
    }

    public function apiManagement()
    {
        return view('admin.system.api_management');
    }
} 