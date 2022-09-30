<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * @var string
     */
    protected $guard = 'recruiter';
}
