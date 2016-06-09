<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use \Response;
use \Auth;
use \App\User;
use \App\Setting;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    public function cur_user()
    {
        return Response::json(
            Auth::user()->toArray(),
            200
        );
    }
    public function test($test)
    {
        return Response::json(
            $test,
            200
        );
    }
}
