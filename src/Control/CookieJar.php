<?php

namespace Innoweb\CookieConsent\Control;

use Innoweb\CookieConsent\CookieConsent;
use LogicException;
use SilverStripe\Control\Cookie;
use SilverStripe\Control\CookieJar as CoreCookieJar;
use SilverStripe\Control\Session;

/**
 * This is a workaround for SS5 which doesn't allow setting the samesite paramater for cookies.
 * For SS6 this is being fixed in https://github.com/silverstripe/silverstripe-framework/issues/10342
 */
class CookieJar extends CoreCookieJar {

    protected function outputCookie(
        $name,
        $value,
        $expiry = 90,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = true
    ) {
        $sameSite = $this->getSameSite($name);
        Cookie::validateSameSite($sameSite);
        // if headers aren't sent, we can set the cookie
        if (!headers_sent($file, $line)) {
            return setcookie($name ?? '', $value ?? '', [
                'expires' => $expiry ?? 0,
                'path' => $path ?? '',
                'domain' => $domain ?? '',
                'secure' => $this->cookieIsSecure($sameSite, (bool) $secure),
                'httponly' => $httpOnly ?? false,
                'samesite' => $sameSite,
            ]);
        }

        if (Cookie::config()->uninherited('report_errors')) {
            throw new LogicException(
                "Cookie '$name' can't be set. The site started outputting content at line $line in $file"
            );
        }
        return false;
    }

    private function cookieIsSecure(string $sameSite, bool $secure): bool
    {
        return $sameSite === 'None' ? true : $secure;
    }

    private function getSameSite(string $name): string
    {
        if ($name === CookieConsent::config()->get('cookie_name')
            && CookieConsent::config()->get('include_all_allowed_hosts')
        ) {
            return Cookie::SAMESITE_NONE;
        }
        if ($name === session_name()) {
            return Session::config()->get('cookie_samesite') ?? Cookie::SAMESITE_LAX;
        }
        return Cookie::config()->get('default_samesite') ?? Cookie::SAMESITE_LAX;
    }
}
