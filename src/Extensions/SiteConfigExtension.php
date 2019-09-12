<?php

namespace Innoweb\CookieConsent\Extensions;

use Innoweb\CookieConsent\Model\CookieGroup;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\FieldList;
use SilverStripe\SiteConfig\SiteConfig;

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

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.CookieConsent', array(
            TextField::create('CookieConsentTitle', $this->owner->fieldLabel('CookieConsentTitle')),
            HtmlEditorField::create('CookieConsentContent', $this->owner->fieldLabel('CookieConsentContent')),
            GridField::create('Cookies', 'Cookies', CookieGroup::get(), GridFieldConfig_RecordEditor::create())
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
                $config->CookieConsentTitle = _t(__CLASS__ . '.CookieConsentTitle', 'This website uses cookies');
            }

            if (empty($config->CookieConsentContent)) {
                $config->CookieConsentContent = _t(__CLASS__ . '.CookieConsentContent', '<p>This website or its third-party tools use cookies, which are necessary for its functioning and required to achieve the purposes illustrated in the cookie policy.<br>You accept the use of cookies by closing or dismissing this notice, by clicking a link or button or by continuing to browse otherwise.</p>');
            }

            $config->write();
        }
    }
    
    public function getCookieGroups() {
        return CookieGroup::get();
    }
}
