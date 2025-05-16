<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    public function index()
    {
        return view('dashboards.clientdashboard', ['title' => 'Client Dashboard']);
    }
}
