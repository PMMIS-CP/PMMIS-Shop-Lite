<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class AdminController extends Controller
{
    public function __construct()
    {
        View::share('adminPrefix', 'admin');
    }
}