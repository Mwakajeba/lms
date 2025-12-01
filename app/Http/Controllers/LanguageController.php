<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    /**
     * Switch application language
     */
    public function switchLanguage(Request $request, $locale)
    {
        $validLocales = ['en', 'sw', 'fr', 'es', 'ar'];
        
        if (!in_array($locale, $validLocales)) {
            return redirect()->back()->with('error', 'Invalid language selected.');
        }

        // Store locale in session
        session(['locale' => $locale]);

        // Update user's preferred language if authenticated
        if (Auth::check()) {
            Auth::user()->update(['locale' => $locale]);
        }

        return redirect()->back()->with('success', 'Language changed successfully!');
    }

    /**
     * Get available languages
     */
    public function getAvailableLanguages()
    {
        return [
            'en' => [
                'name' => 'English',
                'native_name' => 'English',
                'flag' => '🇺🇸'
            ],
            'sw' => [
                'name' => 'Swahili',
                'native_name' => 'Kiswahili',
                'flag' => '🇹🇿'
            ],
            'fr' => [
                'name' => 'French',
                'native_name' => 'Français',
                'flag' => '🇫🇷'
            ],
            'es' => [
                'name' => 'Spanish',
                'native_name' => 'Español',
                'flag' => '🇪🇸'
            ],
            'ar' => [
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'flag' => '🇸🇦'
            ]
        ];
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage()
    {
        $locale = app()->getLocale();
        $languages = $this->getAvailableLanguages();
        
        return $languages[$locale] ?? $languages['en'];
    }
}
