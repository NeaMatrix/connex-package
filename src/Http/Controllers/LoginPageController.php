<?php

namespace Torgodly\Connex\Http\Controllers;

use Illuminate\Routing\Controller;

class LoginPageController extends Controller
{
    public function showLogin()
    {
        return view(config('connex.views.login'));
    }

    public function showLoginConfirm()
    {
        return view(config('connex.views.login_confirm'));
    }
}
