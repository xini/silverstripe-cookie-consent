<?php

namespace Innoweb\CookieConsent\Middleware;

if (!class_exists('TractorCow\Fluent\Middleware\DetectLocaleMiddleware')) {
    return;
}

use Innoweb\CookieConsent\CookieConsent;
use SilverStripe\Control\HTTPRequest;
use TractorCow\Fluent\Middleware\DetectLocaleMiddleware;

class DetectLocaleWithCookieConsentMiddleware extends DetectLocaleMiddleware
{
    protected function setPersistLocale(HTTPRequest $request, $locale)
    {
        if(CookieConsent::check(CookieConsent::PREFERENCES)){
            parent::setPersistLocale($request, $locale);
        }
    }
}
