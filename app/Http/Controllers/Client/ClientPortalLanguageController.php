<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ClientPortalLanguageController extends Controller
{
    /**
     * Store the client's portal language preference (EN/ID).
     * Used via AJAX when user toggles language on client portal pages.
     */
    public function store(Request $request): JsonResponse
    {
        $lang = $request->input('lang', 'en');

        // Only accept valid language codes
        if (! in_array($lang, ['en', 'id'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid language code.',
            ], 422);
        }

        // Store in session so future page loads can read it server-side
        Session::put('client_portal_lang', $lang);

        return response()->json([
            'success' => true,
            'lang' => $lang,
        ]);
    }
}
