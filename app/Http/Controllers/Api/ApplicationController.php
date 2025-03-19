<?php     

namespace App\Http\Controllers\Api;

use App\User;
use App\Http\Controllers\Controller;


class ApplicationController extends Controller
{
    public function __invoke()
    {
        return view('application');
    }
}
