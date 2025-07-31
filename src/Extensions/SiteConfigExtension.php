<?php

namespace Innoweb\CookieConsent\Extensions;

use Innoweb\CookieConsent\Model\CookieGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class SiteConfigExtension
 * @package Innoweb\CookieConsent
 */
class SiteConfigExtension extends DataExtension
{
    private static $db = array(
        'CookieConsentTitle' => 'Varchar(255)',
        'CookieConsentContent' => 'HTMLText'
    );

    private static $translate = array(
        'CookieConsentTitle',
        'CookieConsentContent'
    );

    /**
     * @param FieldList $fields
     * @return FieldList|void
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->findOrMakeTab('Root.CookieConsent', _t(__CLASS__ . '.CookieConsent', 'Cookie Consent'));
        $fields->addFieldsToTab('Root.CookieConsent', array(
            TextField::create('CookieConsentTitle', _t(__CLASS__ . '.CookieConsentTitle', 'Cookie Consent Title')),
            HtmlEditorField::create('CookieConsentContent', _t(__CLASS__ . '.CookieConsentContent', 'Cookie Consent Content')),
            GridField::create('Cookies', _t(__CLASS__ . '.Cookies', 'Cookies'), CookieGroup::get(), GridFieldConfig_RecordEditor::create())
        ));
    }

    /**
     * Set the defaults this way beacause the SiteConfig is probably already created
     *
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function requireDefaultRecords()
    {
        if ($config = SiteConfig::current_site_config()) {
            if (empty($config->CookieConsentTitle)) {
                $config->CookieConsentTitle = _t(__CLASS__ . '.DefaultCookieConsentTitle', 'This website uses cookies');
            }

            if (empty($config->CookieConsentContent)) {
                $config->CookieConsentContent = _t(__CLASS__ . '.DefaultCookieConsentContent', '<p>We processes your personal data using cookies to ensure the proper functioning of the website. With your consent, we may also use cookies for analytical or marketing purposes. You can adjust your consent to these non-essential cookies by clicking "Manage cookie settings" or you can reject them by clicking "Necessary cookies only". Your consent may be withdrawn at any time through the link to the cookie policy in the footer of the website and changing to your preferred settings. For more information on the use of cookies, please click "Manage cookie settings".</p>');
            }

            $config->write();
        }
    }

    public function getCookieGroups()
    {
        return CookieGroup::get();
    }
}
