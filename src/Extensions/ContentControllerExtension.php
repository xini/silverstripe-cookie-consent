<?php

declare(strict_types=1);

namespace Innoweb\CookieConsent\Extensions;

use Exception;
use Innoweb\CookieConsent\CookieConsent;
use Innoweb\CookieConsent\Pages\CookiePolicyPage;
use Innoweb\CookieConsent\Pages\CookiePolicyPageController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Extension;
use SilverStripe\Model\List\ArrayList;
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
    private static array $allowed_actions = [
        'acceptCookies',
        'acceptAllCookies',
        'acceptNecessaryCookies',
    ];

    public function onBeforeInit(): void
    {
        // if no consent is set, check if we should set it based on geolocation/consent type
        if (!CookieConsent::check() && $type = CookieConsent::getConsentType()) {
            // check GPC
            if ($type == CookieConsent::CONSENT_TYPE_GPC) {
                // allow only necessary cookies and don't show popup
                CookieConsent::grant(CookieConsent::NECESSARY);
            }
        }
    }

    /**
     * Place the necessary js and css
     *
     * @throws Exception
     */
    public function onAfterInit(): void
    {
        if (!($this->getOwner() instanceof Security)
            && !CookieConsent::check()
            && Config::inst()->get(CookieConsent::class, 'include_css')
        ) {
            Requirements::css('innoweb/silverstripe-cookie-consent:client/dist/css/cookieconsent.css');
        }

        if (!($this->getOwner() instanceof Security)
            && Config::inst()->get(CookieConsent::class, 'include_js')
        ) {
            Requirements::javascript('innoweb/silverstripe-cookie-consent:client/dist/js/cookieconsent.js');
        }
    }

    /**
     * Check if only necessary cookies are accepted
     */
    public function OnlyNecessaryCookiesAccepted(): bool
    {
        $consent = CookieConsent::getConsent();
        return $consent && count($consent) === 1 && $consent[0] === CookieConsent::NECESSARY;
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
     * Get consent cookie expiry in days
     *
     * @return int
     */
    public function getCookieConsentCookieExpiry()
    {
        return Config::inst()->get(CookieConsent::class, 'cookie_expiry');
    }

    /**
     * Check if we should show opt-in popup
     *
     * @return bool
     */
    public function PromptCookieConsent()
    {
        $controller = Controller::curr();
        $type = CookieConsent::getConsentType();
        $securiy = $controller instanceof Controller && $controller instanceof Security;
        $cookiePolicy = $controller instanceof Controller && $controller instanceof CookiePolicyPageController;
        $hasConsent = count(CookieConsent::getConsent()) > 0;
        $prompt = ($type == CookieConsent::CONSENT_TYPE_OPT_IN) && !$securiy && !$cookiePolicy && !$hasConsent;
        $this->getOwner()->extend('updatePromptCookieConsent', $prompt);
        return $prompt;
    }

    /**
     * Check if we should show opt-out popup
     *
     * @return bool
     */
    public function PromptOptOutPopup()
    {
        $controller = Controller::curr();
        $type = CookieConsent::getConsentType();
        $securiy = $controller instanceof Controller && $controller instanceof Security;
        $cookiePolicy = $controller instanceof Controller && $controller instanceof CookiePolicyPageController;
        $hasConsent = count(CookieConsent::getConsent()) > 0;
        $prompt = ($type == CookieConsent::CONSENT_TYPE_OPT_OUT) && !$securiy && !$cookiePolicy && !$hasConsent;
        $this->getOwner()->extend('updateOptOutPopup', $prompt);
        return $prompt;
    }

    /**
     * Check if we should show do-not-sell popup
     *
     * @return bool
     */
    public function PromptDoNotSellPopup()
    {
        $controller = Controller::curr();
        $type = CookieConsent::getConsentType();
        $securiy = $controller instanceof Controller && $controller instanceof Security;
        $cookiePolicy = $controller instanceof Controller && $controller instanceof CookiePolicyPageController;
        $hasConsent = count(CookieConsent::getConsent()) > 0;
        $prompt = ($type == CookieConsent::CONSENT_TYPE_DO_NOT_SELL) && !$securiy && !$cookiePolicy && !$hasConsent;
        $this->getOwner()->extend('updateDoNotSellPopup', $prompt);
        return $prompt;
    }

    /**
     * Check if site only uses necessary cookies
     */
    public function SiteUsesNecessaryCookiesOnly(): bool
    {
        $categories = array_keys(Config::inst()->get(CookieConsent::class, 'cookies'));
        return count($categories) === 1 && $categories[0] === CookieConsent::NECESSARY;
    }

    public function AdditionalDomainsCookiesEnabled(): bool
    {
        $includeHosts = Config::inst()->get(CookieConsent::class, 'include_all_allowed_hosts');
        $additionalExist = $this->getAdditionalHosts() instanceof ArrayList && $this->getAdditionalHosts()->count();
        return ($includeHosts && $additionalExist);
    }

    /**
     * Check if cookies for all allowed domains should be set.
     * Used in template to load images for additional domains.
     */
    public function SetAdditionalDomainsCookies(): string|false
    {
        $includeHosts = Config::inst()->get(CookieConsent::class, 'include_all_allowed_hosts');
        $additionalExist = $this->getAdditionalHosts() instanceof ArrayList && $this->getAdditionalHosts()->count();
        $acceptParam = $this->getOwner()->getRequest()->getVar('acceptCookies') ?? false;
        return ($includeHosts && $additionalExist && $acceptParam !== false) ? $acceptParam : false;
    }

    public function getAdditionalHosts(): ?ArrayList
    {
        if (Environment::hasEnv('SS_ALLOWED_HOSTS')) {
            $data = [];
            $hosts = explode(',', (string) Environment::getEnv('SS_ALLOWED_HOSTS'));
            $hosts = array_diff($hosts, [Director::host()]);
            foreach ($hosts as $host) {
                $data[] = [
                    'Host' => $host,
                    'BaseURL' => Director::protocol() . $host,
                    'BaseLink' => Controller::join_links(
                        Director::protocol() . $host,
                        Director::makeRelative($this->getOwner()->Link('acceptCookies')),
                        '?acceptCookies='
                    ),
                    'FullLink' => Controller::join_links(
                        Director::protocol() . $host,
                        Director::makeRelative($this->getOwner()->Link('acceptCookies')),
                        '?acceptCookies=' . $this->getOwner()->getRequest()->getVar('acceptCookies')
                    ),
                ];
            }

            return ArrayList::create($data);
        }

        return null;
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

    public function acceptAllCookies(): ?string
    {
        CookieConsent::grantAll();

        if (Director::is_ajax()) {
            return "ok";
        }
        // Get the url the same as the redirect back method gets it
        $url = $this->getOwner()->getBackURL()
            ?: $this->getOwner()->getReturnReferer()
                ?: Director::baseURL();
        $cachebust = uniqid();
        $consent = implode(',', CookieConsent::getConsent());
        if (parse_url((string) $url, PHP_URL_QUERY)) {
            $url = Director::absoluteURL(sprintf('%s&acceptCookies=%s&cachebust=%s', $url, $consent, $cachebust));
        } else {
            $url = Director::absoluteURL(sprintf('%s?acceptCookies=%s&cachebust=%s', $url, $consent, $cachebust));
        }
        $this->getOwner()->redirect($url);

        return null;
    }

    public function getAcceptAllCookiesLink(): string
    {
        // add testing country param
        $countryParam = '';
        if ((Director::isDev() || Director::isTest())
            && ($controller = Controller::curr())
            && ($request = $controller->getRequest())
            && $request->getVar('country')
        ) {
            $countryParam = '?country=' . strtoupper((string) $request->getVar('country'));
        }

        return Controller::join_links(
            $this->getOwner()->Link(),
            'acceptAllCookies',
            $countryParam
        );
    }

    public function getAcceptAllCookiesGroups(): string
    {
        return implode(',', array_keys(Config::inst()->get(CookieConsent::class, 'cookies')));
    }

    public function acceptNecessaryCookies(): ?string
    {
        CookieConsent::setConsent(CookieConsent::NECESSARY);

        if (Director::is_ajax()) {
            return "ok";
        }
        // Get the url the same as the redirect back method gets it
        $url = $this->getOwner()->getBackURL()
            ?: $this->getOwner()->getReturnReferer()
                ?: Director::baseURL();
        $cachebust = uniqid();
        $consent = implode(',', CookieConsent::getConsent());
        if (parse_url((string) $url, PHP_URL_QUERY)) {
            $url = Director::absoluteURL(sprintf('%s&acceptCookies=%s&cachebust=%s', $url, $consent, $cachebust));
        } else {
            $url = Director::absoluteURL(sprintf('%s?acceptCookies=%s&cachebust=%s', $url, $consent, $cachebust));
        }
        $this->getOwner()->redirect($url);

        return null;
    }

    public function getAcceptNecessaryCookiesLink(): string
    {
        // add testing country param
        $countryParam = '';
        if ((Director::isDev() || Director::isTest())
            && ($controller = Controller::curr())
            && ($request = $controller->getRequest())
            && $request->getVar('country')
        ) {
            $countryParam = '?country=' . strtoupper((string) $request->getVar('country'));
        }

        return Controller::join_links(
            $this->getOwner()->Link(),
            'acceptNecessaryCookies',
            $countryParam
        );
    }

    /**
     * This action is used as an image source when setting cookies for multiple allowed hosts
     */
    public function acceptCookies()
    {
        if (($var = $this->getOwner()->getRequest()->getVar('acceptCookies'))
            && ($parts = explode(',', (string) $var))
            && ($groups = array_intersect($parts, array_keys(Config::inst()->get(CookieConsent::class, 'cookies'))))
            && count($groups)
        ) {
            CookieConsent::grant($groups);

            return "ok";
        }

        return $this->getOwner()->httpError(404, 'not found');
    }
}
