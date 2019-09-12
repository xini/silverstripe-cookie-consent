<?php

namespace Innoweb\CookieConsent\Extensions;

use Innoweb\CookieConsent\CookieConsent;
use Innoweb\CookieConsent\Pages\CookiePolicyPage;
use Innoweb\CookieConsent\Pages\CookiePolicyPageController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

class ContentControllerExtension extends Extension
{
    private static $allowed_actions = [
        'acceptCookies',
        'revokeCookies',
    ];

    public function CookiesAccepted()
    {
        return CookieConsent::accepted();
    }

    public function CookieConsent() {
        // check controller
        if ($this->owner instanceof Security || $this->owner instanceof CookiePolicyPageController) {
            return false;
        }
        // check if cookies already accepted
        if (CookieConsent::accepted()) {
            return false;
        }
        // show cookie consent
        if (Config::inst()->get(CookieConsent::class, 'include_javascript')) {
            Requirements::javascript('innoweb/silverstripe-cookie-consent:client/dist/javascript/bundle.js', ['defer' => true]);
        }
        if (Config::inst()->get(CookieConsent::class, 'include_css')) {
            Requirements::css('innoweb/silverstripe-cookie-consent:client/dist/css/cookie-consent.css');
        }
        return $this->owner->renderWith('Innoweb/CookieConsent/Includes/CookieConsent');
    }
    
    public function getCookiePolicyPage()
    {
        return CookiePolicyPage::get()->first();
    }

    public function acceptCookies()
    {
        CookieConsent::accept();

        // Get the url the same as the redirect back method gets it
        $url = $this->owner->getBackURL()
            ?: $this->owner->getReturnReferer()
                ?: Director::baseURL();
        $cachebust = uniqid();
        $url = Controller::join_links(Director::absoluteURL($url), "?cache=$cachebust");
        $this->owner->redirect($url);
    }

    public function getAcceptCookiesLink()
    {
        return Controller::join_links($this->owner->Link(), 'acceptCookies');
    }

    public function revokeCookies()
    {
        CookieConsent::revoke();
        
        // Get the url the same as the redirect back method gets it
        $url = $this->owner->getBackURL()
            ?: $this->owner->getReturnReferer()
            ?: Director::baseURL();
        $cachebust = uniqid();
        $url = Controller::join_links(Director::absoluteURL($url), "?cache=$cachebust");
        $this->owner->redirect($url);
    }
    
    public function getRevokeCookiesLink()
    {
        return Controller::join_links($this->owner->Link(), 'revokeCookies');
    }
}
