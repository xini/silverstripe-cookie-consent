<?php

declare(strict_types=1);

namespace Innoweb\CookieConsent\Forms;

use SilverStripe\ORM\FieldType\DBField;
use Innoweb\CookieConsent\CookieConsent;
use Innoweb\CookieConsent\Model\CookieGroup;
use Override;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\View\Requirements;

/**
 * Class CookieConsentCheckBoxField
 *
 * @author Bram de Leeuw
 */
class CookieConsentCheckBoxField extends CheckboxField
{
    public function __construct(protected CookieGroup $cookieGroup)
    {
        parent::__construct(
            $this->cookieGroup->ConfigName,
            $this->cookieGroup->Title,
            $this->cookieGroup->isRequired()
        );

        $this->setDisabled($this->cookieGroup->isRequired());
    }

    #[Override]
    public function Field($properties = [])
    {
        if (Config::inst()->get(CookieConsent::class, 'include_css')) {
            Requirements::css('innoweb/silverstripe-cookie-consent:client/dist/css/cookieconsentcheckboxfield.css');
        }

        return parent::Field($properties);
    }

    public function getContent(): ?DBField
    {
        return $this->cookieGroup->dbObject('Content');
    }

    public function getCookieGroup()
    {
        return $this->cookieGroup;
    }
}
