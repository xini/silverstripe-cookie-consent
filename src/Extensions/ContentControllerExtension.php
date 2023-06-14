<?php

namespace Innoweb\CookieConsent\Extensions;

use Exception;
use Innoweb\CookieConsent\CookieConsent;
use Innoweb\CookieConsent\Pages\CookiePolicyPage;
use Innoweb\CookieConsent\Pages\CookiePolicyPageController;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

/**
 * Class ContentControllerExtension
 * @package Innoweb\CookieConsent
 * @property ContentController owner
 */
class ContentControllerExtension extends Extension
{
    private static $allowed_actions = [
        'acceptAllCookies',
        'acceptNecessaryCookies',
    ];

    /**
     * Place the necessary js and css
     *
     * @throws \Exception
     */
    public function onAfterInit()
    {
        if (!($this->owner instanceof Security) && !CookieConsent::check()) {
            if (Config::inst()->get(CookieConsent::class, 'include_css')) {
                Requirements::css('innoweb/silverstripe-cookie-consent:client/dist/css/cookieconsent.css');
            }
        }
    }

    /**
     * Method for checking cookie consent in template
     *
     * @param $group
     * @return bool
     * @throws Exception
     */
    public function CookieConsent($group = CookieConsent::NECESSARY)
    {
        return CookieConsent::check($group);
    }

    /**
     * Get consent cookie name
     *
     * @return string
     */
    public function getCookieConsentCookieName()
    {
        return Config::inst()->get(CookieConsent::class, 'cookie_name');
    }

    /**
     * Get consent cookie name
     *
     * @return string
     */
    public function getCookieConsentCookieExpiry()
    {
        return Config::inst()->get(CookieConsent::class, 'cookie_expiry');
    }

    /**
     * Check if we can promt for concent
     * We're not on a Securty or Cooky policy page and have no concent set
     *
     * @return bool
     */
    public function PromptCookieConsent()
    {
        $controller = Controller::curr();
        $securiy = $controller ? $controller instanceof Security : false;
        $cookiePolicy = $controller ? $controller instanceof CookiePolicyPageController : false;
        $hasConsent = CookieConsent::check();
        $prompt = !$securiy && !$cookiePolicy && !$hasConsent;
        $this->owner->extend('updatePromptCookieConsent', $prompt);
        return $prompt;
    }

    /**
     * Get an instance of the cookie policy page
     *
     * @return CookiePolicyPage|DataObject
     */
    public function getCookiePolicyPage()
    {
        return CookiePolicyPage::instance();
    }

    public function acceptAllCookies()
    {
        CookieConsent::grantAll();

        if (Director::is_ajax()) {
            return "ok";
        } else {
            // Get the url the same as the redirect back method gets it
            $url = $this->owner->getBackURL()
                ?: $this->owner->getReturnReferer()
                    ?: Director::baseURL();

            $cachebust = uniqid();
            if (parse_url($url, PHP_URL_QUERY)) {
                $url = Director::absoluteURL("$url&acceptCookies=$cachebust");
            } else {
                $url = Director::absoluteURL("$url?acceptCookies=$cachebust");
            }

            $this->owner->redirect($url);
        }
    }

    public function getAcceptAllCookiesLink()
    {
        return Controller::join_links($this->getOwner()->Link(), 'acceptAllCookies');
    }

    public function getAcceptAllCookiesGroups()
    {
        return implode(',', array_keys(Config::inst()->get(CookieConsent::class, 'cookies')));
    }

    public function acceptNecessaryCookies()
    {
        CookieConsent::grant(CookieConsent::NECESSARY);

        if (Director::is_ajax()) {
            return "ok";
        } else {
            // Get the url the same as the redirect back method gets it
            $url = $this->owner->getBackURL()
                ?: $this->owner->getReturnReferer()
                    ?: Director::baseURL();

            $cachebust = uniqid();
            if (parse_url($url, PHP_URL_QUERY)) {
                $url = Director::absoluteURL("$url&acceptCookies=$cachebust");
            } else {
                $url = Director::absoluteURL("$url?acceptCookies=$cachebust");
            }

            $this->owner->redirect($url);
        }
    }

    public function getAcceptNecessaryCookiesLink()
    {
        return Controller::join_links($this->getOwner()->Link(), 'acceptNecessaryCookies');
    }
}
