<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Request;

class TestController extends Controller
{
    use WithoutMiddleware;

    public function hello()
    {
        return 'Hello World';
    }
}
