<?php

namespace App\Http\Controllers;

use App\Support\DashboardData;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $dash = DashboardData::make($request->integer('year') ?: null);

        return view('home', compact('dash'));
    }
}
