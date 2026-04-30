<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;

class LocaleController extends Controller
{
    /**
     * Provision a new web server.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'code' => 'required|string|in:en,ar'
        ]);

        App::setLocale($request->code);

        session()->put('locale', $request->code);

        return redirect()->back();
    }
}
