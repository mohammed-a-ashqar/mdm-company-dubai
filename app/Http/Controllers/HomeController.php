<?php

namespace App\Http\Controllers;

use App\Support\DashboardData;

class HomeController extends Controller
{
    public function index()
    {
        $dash = DashboardData::make();

        return view('home', compact('dash'));
    }
}
