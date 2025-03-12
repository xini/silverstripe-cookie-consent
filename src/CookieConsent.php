<?php

namespace Innoweb\CookieConsent;

use Exception;
use Innoweb\CookieConsent\Model\CookieGroup;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Cookie;
use SilverStripe\Control\Director;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;

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

    private static $required_groups = [
        self::NECESSARY
    ];

    private static $cookies = [];

    private static $include_css = true;

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
    private static $cookie_expiry = 60;

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
     * Set consent cookies for all hosts allowed through SS_ALLOWED_HOSTS config
     *
     * @config
     * @var bool
     */
    private static $include_all_allowed_hosts = false;

    /**
     * Check if there is consent for the given cookie
     *
     * @param $group
     * @return bool
     * @throws Exception
     */
    public static function check($group = CookieConsent::NECESSARY)
    {
        $cookies = self::config()->get('cookies');
        if (!isset($cookies[$group])) {
            throw new Exception(sprintf(
                "The cookie group '%s' is not configured. You need to add it to the cookies config on %s",
                $group,
                self::class
            ));
        }

        $consent = self::getConsent();
        return array_search($group, $consent) !== false;
    }

    /**
     * Grant consent for the given cookie group
     *
     * @param $group
     */
    public static function grant($group)
    {
        $consent = self::getConsent();
        if (is_array($group)) {
            $consent = array_merge($consent, $group);
        } else {
            array_push($consent, $group);
        }
        self::setConsent($consent);
    }

    /**
     * Grant consent for all the configured cookie groups
     */
    public static function grantAll()
    {
        $consent = array_keys(Config::inst()->get(CookieConsent::class, 'cookies'));
        self::setConsent($consent);
    }

    /**
     * Remove consent for the given cookie group
     *
     * @param $group
     */
    public static function remove($group)
    {
        $consent = self::getConsent();
        $key = array_search($group, $consent);
        $cookies = Config::inst()->get(CookieConsent::class, 'cookies');
        if (isset($cookies[$group])) {
            foreach ($cookies[$group] as $host => $cookies) {
                $host = ($host === CookieGroup::LOCAL_PROVIDER)
                    ? Director::host()
                    : str_replace('_', '.', $host);
                foreach ($cookies as $cookie) {
                    Cookie::force_expiry($cookie, null, $host);
                }
            }
        }

        unset($consent[$key]);
        self::setConsent($consent);
    }

    /**
     * Get the current configured consent
     *
     * @return array
     */
    public static function getConsent()
    {
        return explode(',', Cookie::get(self::config()->get('cookie_name')) ?? '');
    }

    /**
     * Save the consent
     *
     * @param $consent
     */
    public static function setConsent($consent)
    {
        $consent = array_filter(array_unique(array_merge($consent, self::config()->get('required_groups'))));
		$request = Controller::curr()->getRequest();
        $secure = Director::is_https($request) && Session::config()->get('cookie_secure');
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
}
