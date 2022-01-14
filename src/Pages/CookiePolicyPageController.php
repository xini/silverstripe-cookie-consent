<?php

namespace Innoweb\CookieConsent\Pages;

use Innoweb\CookieConsent\CookieConsent;
use \PageController;
use Innoweb\CookieConsent\Forms\CookieConsentForm;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\View\Requirements;

/**
 * Class CookiePolicyPageController
 * @mixin CookiePolicyPage
 */
class CookiePolicyPageController extends PageController
{
    private static $allowed_actions = array(
        'Form'
    );

    /**
     * Get the CookieConsentForm
     *
     * @return CookieConsentForm
     */
    public function Form()
    {
        return CookieConsentForm::create($this, 'Form');
    }
}
