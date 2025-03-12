<?php

namespace Innoweb\CookieConsent\Pages;

use Innoweb\CookieConsent\Forms\CookieConsentForm;
use PageController;

/**
 * Class CookiePolicyPageController
 * @mixin CookiePolicyPage
 */
class CookiePolicyPageController extends PageController
{
    private static $allowed_actions = [
        'Form'
    ];

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
