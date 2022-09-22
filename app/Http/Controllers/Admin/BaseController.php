<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * @var string
     */
    protected $guard = 'admin';
}
