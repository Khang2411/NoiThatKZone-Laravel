<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminBannerController extends Controller
{
    function list()
    {
        return Inertia::render('Banner/BannerAdd', []);

    }
}
