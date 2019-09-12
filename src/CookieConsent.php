<?php

namespace Innoweb\CookieConsent;

use SilverStripe\Control\Cookie;
use SilverStripe\Core\Config\Configurable;

class CookieConsent
{
    use Configurable;

    const COOKIE_NAME = 'CookieConsent';

    private static $cookieGroups = [];

    private static $include_javascript = true;

    private static $include_css = true;

    private static $create_default_pages = true;

    public static function accepted()
    {
        if (Cookie::get(CookieConsent::COOKIE_NAME) == 'true') {
            return true;
        }
        return false;
    }

    public static function accept()
    {
        Cookie::set(CookieConsent::COOKIE_NAME, 'true', 0, null, null, false, false);
    }

    public static function revoke()
    {
        Cookie::force_expiry(CookieConsent::COOKIE_NAME, null, null, false, false);
    }

}
