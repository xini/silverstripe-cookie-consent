<?php

declare(strict_types=1);

namespace Innoweb\CookieConsent\Pages;

use Innoweb\CookieConsent\Forms\CookieConsentForm;
use Override;
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
    #[Override]
    public function Form()
    {
        return CookieConsentForm::create($this, 'Form');
    }
}
