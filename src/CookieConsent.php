<?php

namespace Innoweb\CookieConsent;

use Innoweb\CookieConsent\Model\CookieGroup;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Cookie;
use SilverStripe\Control\Director;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;

class CookieConsent
{
    use Extensible;
    use Injectable;
    use Configurable;

    const NECESSARY = 'Necessary';
    const ANALYTICS = 'Analytics';
    const MARKETING = 'Marketing';
    const EXTERNAL = 'External';
    const PREFERENCES = 'Preferences';

    const CONSENT_TYPE_GPC = 'gpc';
    const CONSENT_TYPE_OPT_IN = 'optin';
    const CONSENT_TYPE_OPT_OUT = 'optout';
    const CONSENT_TYPE_DO_NOT_SELL = 'donotsell';

    const CONSENT_ORIGIN_AUTO = 'auto';
    const CONSENT_ORIGIN_USER = 'user';

    private static $required_groups = [
        self::NECESSARY
    ];

    private static $cookies = [];

    private static $include_css = true;
    private static $include_js = true;

    private static $create_default_pages = true;

    /**
     * Use this name when setting the consent cookie
     *
     * @config
     * @var string
     */
    private static $cookie_name = 'CookieConsent';

    /**
     * The expiry time in days for a consent persistence cookie
     *
     * @config
     * @var int
     */
    private static $cookie_expiry = 365;

    /**
     * Use this path when setting the consent cookie
     *
     * @config
     * @var string
     */
    private static $cookie_path = null;

    /**
     * Use this domain when setting the consent cookie
     *
     * @config
     * @var string
     */
    private static $cookie_domain = null;

    /**
     * Use http-only cookies. Set to true if you don't need js access.
     *
     * @config
     * @var bool
     */
    private static $cookie_http_only = false;

    /**
     * Use this name when using the cookie consent http header
     *
     * @config
     * @var string
     */
    private static $header_name = 'X-Cookie-Consent';

    /**
     * Set consent cookies for all hosts allowed through SS_ALLOWED_HOSTS config
     *
     * @config
     * @var bool
     */
    private static $include_all_allowed_hosts = false;

    /**
     * Set the name of the geolocation header to check for consent
     *
     * @config
     * @var string
     */
    private static $geolocation_header_name = null;

    /**
     * Check if there is consent for the given cookie type
     *
     * @param $group
     * @return bool
     */
    public static function check($group = CookieConsent::NECESSARY)
    {
        $cookies = self::config()->get('cookies');
        if (!isset($cookies[$group])) {
            Injector::inst()->get(LoggerInterface::class)->error(sprintf(
                "The cookie group '%s' is not configured. You need to add it to the cookies config on %s",
                $group,
                self::class
            ));
            return false;
        }

        $consent = self::getConsent();
        return array_search($group, $consent) !== false;
    }

    /**
     * Grant consent for the given cookie group, keeping existing consent
     *
     * @param $group
     */
    public static function grant($group, $origin = CookieConsent::CONSENT_ORIGIN_USER)
    {
        $consent = self::getConsent();
        if (is_array($group)) {
            $consent = array_merge($consent, $group);
        } else {
            array_push($consent, $group);
        }
        self::setConsent($consent, $origin);
    }

    /**
     * Grant consent for all the configured cookie groups
     */
    public static function grantAll($origin = CookieConsent::CONSENT_ORIGIN_USER)
    {
        $consent = array_keys(Config::inst()->get(CookieConsent::class, 'cookies'));
        self::setConsent($consent, $origin);
    }

    /**
     * Remove the cookies for the given cookie group
     *
     * @param $group
     */
    public static function removeCookiesForGroup($group)
    {
        $cookies = Config::inst()->get(CookieConsent::class, 'cookies');
        if (isset($cookies[$group])) {
            // go through cookies set on request and check if they are set for this group
            foreach ($_COOKIE as $cookieName => $value) {
                // check if the cookie is set for this group
                foreach ($cookies[$group] as $configuredHost => $configuredCookies) {

                    // get host and host without subdomain
                    $hosts = [];
                    $hosts[] = ($configuredHost === CookieGroup::LOCAL_PROVIDER)
                        ? Director::host()
                        : str_replace('_', '.', $configuredHost);

                    if (substr_count($hosts[0], '.') > 1) {
                        $hostParts = explode('.', $hosts[0]);
                        $count = count($hostParts);
                        for ($i = 1; $i < ($count - 1); $i++) {
                            array_shift($hostParts);
                            $hosts[] = implode('.', $hostParts);
                        }
                    }

                    foreach ($configuredCookies as $configuredCookie) {
                        if (preg_match('/^' . str_replace('*', '.*', $configuredCookie) . '$/', $cookieName)) {
                            foreach ($hosts as $host) {
                                Cookie::force_expiry($cookieName, null, $host);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get the current configured consent
     *
     * @return array
     */
    public static function getConsent()
    {
        $consent = [];
        // get consent data from cookie
        if ($value = Cookie::get(self::config()->get('cookie_name'))) {
            $consent = explode(',', $value);
        }
        // get consent data from http header (for example when in use behind CDN)
        if (Controller::has_curr()
            && ($request = Controller::curr()->getRequest())
            && ($value = $request->getHeader(self::config()->get('header_name')))
        ) {
            $consent = explode(',', urldecode($value));
        }
        // remove first token showing origin
        if (isset($consent[0])
            && ($consent[0] === self::CONSENT_ORIGIN_AUTO || $consent[0] === self::CONSENT_ORIGIN_USER)
        ) {
            $consent = array_slice($consent, 1);
        }
        return $consent;
    }

    /**
     * Get the origin of the consent
     *
     * @return string|null
     */
    public static function getConsentOrigin()
    {
        $origin = null;
        // get consent data from cookie
        if ($value = Cookie::get(self::config()->get('cookie_name'))) {
            $consent = explode(',', $value);
        }
        // get consent data from http header (for example when in use behind CDN)
        if (Controller::has_curr()
            && ($request = Controller::curr()->getRequest())
            && ($value = $request->getHeader(self::config()->get('header_name')))
        ) {
            $consent = explode(',', urldecode($value));
        }
        // get first token showing origin
        if (isset($consent[0])
            && ($consent[0] === self::CONSENT_ORIGIN_AUTO || $consent[0] === self::CONSENT_ORIGIN_USER)
        ) {
            $origin = $consent[0];
        }
        return $origin;
    }

    /**
     * Save the consent
     *
     * @param $consent
     */
    public static function setConsent($consent, $origin = self::CONSENT_ORIGIN_USER)
    {
        // gather the new consent to be set
        $consent = is_array($consent) ? $consent : [$consent];
        $consent = array_filter(array_unique(array_merge($consent, self::config()->get('required_groups'))));
        // get currently set consent
        $currentConsent = self::getConsent();
        // get the diff and remove consent to the ones that are not required anymore
        $obsoleteConsent = array_diff($currentConsent, $consent);
        foreach ($obsoleteConsent as $group) {
            self::removeCookiesForGroup($group);
        }
        // add type to first position of consent array
        array_unshift($consent, $origin);
        // check whether the cookie is secure
        $secure = false;
        if (Controller::has_curr()
            && ($request = Controller::curr()->getRequest())
        ) {
            $secure = Director::is_https($request) && Session::config()->get('cookie_secure');
        }
        // set the new cookie
        Cookie::set(
            self::config()->get('cookie_name'),
            implode(',', $consent),
            self::config()->get('cookie_expiry'),
            self::config()->get('cookie_path'),
            self::config()->get('cookie_domain'),
            $secure,
            self::config()->get('cookie_http_only')
        );
    }

    /**
     * Check if the group is required
     *
     * @param $group
     * @return bool
     */
    public static function isRequired($group)
    {
        return in_array($group, self::config()->get('required_groups'));
    }

    /**
     * Get country from configured header
     * @return string|null
     */
    public static function getCountry()
    {
        if (Controller::has_curr()
            && ($request = Controller::curr()->getRequest())
        ) {
            if ((Director::isDev() || Director::isTest()) && $request->getVar('country')) {
                return strtoupper((string) $request->getVar('country'));
            }

            if ($header = self::config()->get('geolocation_header_name')) {

                // return country from configured header
                if ($country = $request->getHeader($header)) {
                    return strtoupper((string) $country);
                }
            }
        }

        return null;
    }

    public static function getConsentType()
    {
        // check GPC
        if (($gpcConfig = self::config()->get('global_privacy_control'))
            && Controller::has_curr()
            && ($request = Controller::curr()->getRequest())
            && (int) $request->getHeader('Sec-GPC') === 1
            && ($gpcConfig === true || (is_array($gpcConfig) && in_array($country, $gpcConfig)))
        ) {
            return self::CONSENT_TYPE_GPC;
        }

        // check if geo location based consent is enabled
        if ($country = self::getCountry()) {

            // check opt-in
            if (($optinConfig = self::config()->get('opt_in'))
                && is_array($optinConfig)
                && in_array($country, $optinConfig)
            ) {
                return self::CONSENT_TYPE_OPT_IN;
            }

            // check opt-out
            if (($optoutConfig = self::config()->get('opt_out'))
                && is_array($optoutConfig)
                && in_array($country, $optoutConfig)
            ) {
                return self::CONSENT_TYPE_OPT_OUT;
            }

            // check do-not-sell
            if (($donotsellConfig = self::config()->get('do_not_sell'))
                && is_array($donotsellConfig)
                && in_array($country, $donotsellConfig)
            ) {
                return self::CONSENT_TYPE_DO_NOT_SELL;
            }
        }

        // fall back is GDPR opt in
        return self::CONSENT_TYPE_OPT_IN;
    }
}
