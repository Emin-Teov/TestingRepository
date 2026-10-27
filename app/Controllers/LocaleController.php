<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Http\RedirectResponse;
use Framework\Session\Session;

final class LocaleController
{
    public function update(string $locale, Session $session): RedirectResponse
    {
        if (array_key_exists($locale, config('app.locales'))) {
            $session->put('locale', $locale);
        }

        return back();
    }
}
