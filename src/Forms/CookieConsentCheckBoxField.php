<?php

namespace Innoweb\CookieConsent\Forms;

use Innoweb\CookieConsent\CookieConsent;
use Innoweb\CookieConsent\Model\CookieGroup;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\View\Requirements;

/**
 * Class CookieConsentCheckBoxField
 *
 * @author Bram de Leeuw
 */
class CookieConsentCheckBoxField extends CheckboxField
{
    /**
     * @var CookieGroup
     */
    protected $cookieGroup;

    public function __construct(CookieGroup $cookieGroup)
    {
        $this->cookieGroup = $cookieGroup;
        parent::__construct(
            $cookieGroup->ConfigName,
            $cookieGroup->Title,
            $cookieGroup->isRequired()
        );

        $this->setDisabled($cookieGroup->isRequired());
    }

    public function Field($properties = [])
    {
        if (Config::inst()->get(CookieConsent::class, 'include_css')) {
            Requirements::css('innoweb/silverstripe-cookie-consent:client/dist/css/cookieconsentcheckboxfield.css');
        }

        return parent::Field($properties);
    }

    public function getContent()
    {
        return $this->cookieGroup->dbObject('Content');
    }

    public function getCookieGroup()
    {
        return $this->cookieGroup;
    }
}
