<?php

namespace Innoweb\CookieConsent\Extensions;

use Innoweb\CookieConsent\CookieConsent;
use Innoweb\CookieConsent\Model\CookieGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TabSet;
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
        'CookieConsentContent' => 'HTMLText',
        'CookieOptOutTitle' => 'Varchar(255)',
        'CookieOptOutContent' => 'HTMLText',
        'CookieDoNotSellTitle' => 'Varchar(255)',
        'CookieDoNotSellContent' => 'HTMLText',
    );

    private static $translate = array(
        'CookieConsentTitle',
        'CookieConsentContent',
        'CookieOptOutTitle',
        'CookieOptOutContent',
        'CookieDoNotSellTitle',
        'CookieDoNotSellContent',
    );

    /**
     * @param FieldList $fields
     * @return FieldList|void
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'CookieConsentTitle',
            'CookieConsentContent',
            'CookieOptOutTitle',
            'CookieOptOutContent',
            'CookieDoNotSellTitle',
            'CookieDoNotSellContent',
            'Cookies',
        ]);

        $fields->findOrMakeTab(
            'Root.CookieConsent',
            _t(__CLASS__ . '.CookieConsent', 'Cookie Consent')
        );
        $fields->addFieldToTab(
            'Root.CookieConsent',
            TabSet::create('CookieConsentTabs')
        );

        $fields->findOrMakeTab(
            'Root.CookieConsent.CookieConsentTabs.Cookies',
            _t(__CLASS__ . '.Cookies', 'Cookies')
        );
        $fields->addFieldsToTab(
            'Root.CookieConsent.CookieConsentTabs.Cookies',
            [
                GridField::create('Cookies', _t(__CLASS__ . '.Cookies', 'Cookies'), CookieGroup::get(), GridFieldConfig_RecordEditor::create())
            ]
        );

        if (($config = CookieConsent::config()->get('opt_in')) && is_array($config) && count($config)) {
            $fields->findOrMakeTab(
                'Root.CookieConsent.CookieConsentTabs.OptInGDPR',
                _t(__CLASS__ . '.OptInGDPRPopup', 'Opt-In / GDPR Popup')
            );
            $fields->addFieldsToTab(
                'Root.CookieConsent.CookieConsentTabs.OptInGDPR',
                [
                    TextField::create('CookieConsentTitle', _t(__CLASS__ . '.CookieConsentTitle', 'Cookie Consent Title')),
                    HtmlEditorField::create('CookieConsentContent', _t(__CLASS__ . '.CookieConsentContent', 'Cookie Consent Content')),
                ]
            );
        }

        if (($config = CookieConsent::config()->get('opt_out')) && is_array($config) && count($config)) {
            $fields->findOrMakeTab(
                'Root.CookieConsent.CookieConsentTabs.OptOut',
                _t(__CLASS__ . '.OptOutPopup', 'Opt-Out Popup')
            );
            $fields->addFieldsToTab(
                'Root.CookieConsent.CookieConsentTabs.OptOut',
                [
                    TextField::create('CookieOptOutTitle', _t(__CLASS__ . '.OptOutPopupTitle', 'Opt-Out Popup Title')),
                    HtmlEditorField::create('CookieOptOutContent', _t(__CLASS__ . '.OptOutPopupContent', 'Opt-Out Popup Content')),
                ]
            );
        }

        if (($config = CookieConsent::config()->get('do_not_sell')) && is_array($config) && count($config)) {
            $fields->findOrMakeTab(
                'Root.CookieConsent.CookieConsentTabs.DoNotSell',
                _t(__CLASS__ . '.DoNotSellPopup', 'Do-Not-Sell Popup')
            );
            $fields->addFieldsToTab(
                'Root.CookieConsent.CookieConsentTabs.DoNotSell',
                [
                    TextField::create('CookieDoNotSellTitle', _t(__CLASS__ . '.DoNotSellPopupTitle', 'Do-Not-Sell Popup Title')),
                    HtmlEditorField::create('CookieDoNotSellContent', _t(__CLASS__ . '.DoNotSellPopupContent', 'Opt-Out Popup Content')),
                ]
            );
        }
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
                $config->CookieConsentContent = _t(__CLASS__ . '.DefaultCookieConsentContent', '<p>We process your personal data using cookies to ensure the proper functioning of the website. With your consent, we may also use cookies for analytical or marketing purposes. You can adjust your consent to these non-essential cookies by clicking "Manage cookie settings" or you can reject them by clicking "Necessary cookies only". Your consent may be withdrawn at any time through the link to the cookie policy in the footer of the website and changing to your preferred settings. For more information on the use of cookies, please click "Manage cookie settings".</p>');
            }

            if (empty($config->CookieOptOutTitle)) {
                $config->CookieOptOutTitle = _t(__CLASS__ . '.DefaultCookieOptOutTitle', 'This website uses cookies');
            }
            if (empty($config->CookieOptOutContent)) {
                $config->CookieOptOutContent = _t(__CLASS__ . '.DefaultCookieOptOutContent', '<p>We process your personal data using cookies to ensure the proper functioning of the website. You have the right to opt out of the sale or sharing of your personal information by clicking below.</p>');
            }

            if (empty($config->CookieDoNotSellTitle)) {
                $config->CookieDoNotSellTitle = _t(__CLASS__ . '.DefaultCookieDoNotSellTitle', 'We value your privacy');
            }
            if (empty($config->CookieDoNotSellContent)) {
                $config->CookieDoNotSellContent = _t(__CLASS__ . '.DefaultCookieDoNotSellContent', '<p>We process your personal data using cookies to ensure the proper functioning of the website. You have the right to opt out of the sale or sharing of your personal information by clicking below.</p>');
            }
            $config->write();
        }
    }

    public function getCookieGroups()
    {
        return CookieGroup::get();
    }
}
