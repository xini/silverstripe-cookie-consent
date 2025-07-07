<?php

use Innoweb\CookieConsent\CookieConsent;
use SilverStripe\Control\HTTPRequest;
use TractorCow\Fluent\Middleware\DetectLocaleMiddleware;

class DetectLocaleMiddlewareCookieConsentInjector extends DetectLocaleMiddleware
{
    protected function setPersistLocale(HTTPRequest $request, $locale)
    {
        if(CookieConsent::check(CookieConsent::NECESSARY)){
            parent::setPersistLocale($request, $locale);
        }
    }
}
