<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Http\RedirectResponse;

final class HomeController
{
    /**
     * The whole application is behind the login, so the home page is the dashboard.
     */
    public function index(): RedirectResponse
    {
        return to_route('admin.dashboard');
    }
}
