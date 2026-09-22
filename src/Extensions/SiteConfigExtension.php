<?php

declare(strict_types=1);

namespace Innoweb\CookieConsent\Extensions;

use Innoweb\CookieConsent\CookieConsent;
use Innoweb\CookieConsent\Model\CookieGroup;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class SiteConfigExtension
 * @package Innoweb\CookieConsent
 */
class SiteConfigExtension extends Extension
{
    private static array $db = [
        'CookieConsentTitle' => 'Varchar(255)',
        'CookieConsentContent' => 'HTMLText',
        'CookieOptOutTitle' => 'Varchar(255)',
        'CookieOptOutContent' => 'HTMLText',
        'CookieDoNotSellTitle' => 'Varchar(255)',
        'CookieDoNotSellContent' => 'HTMLText',
    ];

    private static array $translate = [
        'CookieConsentTitle',
        'CookieConsentContent',
        'CookieOptOutTitle',
        'CookieOptOutContent',
        'CookieDoNotSellTitle',
        'CookieDoNotSellContent',
    ];

    public function updateCMSFields(FieldList $fields): void
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
            _t(self::class . '.CookieConsent', 'Cookie Consent')
        );
        $fields->addFieldToTab(
            'Root.CookieConsent',
            TabSet::create('CookieConsentTabs')
        );

        $fields->findOrMakeTab(
            'Root.CookieConsent.CookieConsentTabs.Cookies',
            _t(self::class . '.Cookies', 'Cookies')
        );
        $fields->addFieldsToTab(
            'Root.CookieConsent.CookieConsentTabs.Cookies',
            [
                GridField::create('Cookies', _t(self::class . '.Cookies', 'Cookies'), CookieGroup::get(), GridFieldConfig_RecordEditor::create())
            ]
        );

        if (($config = CookieConsent::config()->get('opt_in')) && is_array($config) && count($config)) {
            $fields->findOrMakeTab(
                'Root.CookieConsent.CookieConsentTabs.OptInGDPR',
                _t(self::class . '.OptInGDPRPopup', 'Opt-In / GDPR Popup')
            );
            $fields->addFieldsToTab(
                'Root.CookieConsent.CookieConsentTabs.OptInGDPR',
                [
                    TextField::create('CookieConsentTitle', _t(self::class . '.CookieConsentTitle', 'Cookie Consent Title')),
                    HtmlEditorField::create('CookieConsentContent', _t(self::class . '.CookieConsentContent', 'Cookie Consent Content')),
                ]
            );
        }

        if (($config = CookieConsent::config()->get('opt_out')) && is_array($config) && count($config)) {
            $fields->findOrMakeTab(
                'Root.CookieConsent.CookieConsentTabs.OptOut',
                _t(self::class . '.OptOutPopup', 'Opt-Out Popup')
            );
            $fields->addFieldsToTab(
                'Root.CookieConsent.CookieConsentTabs.OptOut',
                [
                    TextField::create('CookieOptOutTitle', _t(self::class . '.OptOutPopupTitle', 'Opt-Out Popup Title')),
                    HtmlEditorField::create('CookieOptOutContent', _t(self::class . '.OptOutPopupContent', 'Opt-Out Popup Content')),
                ]
            );
        }

        if (($config = CookieConsent::config()->get('do_not_sell')) && is_array($config) && count($config)) {
            $fields->findOrMakeTab(
                'Root.CookieConsent.CookieConsentTabs.DoNotSell',
                _t(self::class . '.DoNotSellPopup', 'Do-Not-Sell Popup')
            );
            $fields->addFieldsToTab(
                'Root.CookieConsent.CookieConsentTabs.DoNotSell',
                [
                    TextField::create('CookieDoNotSellTitle', _t(self::class . '.DoNotSellPopupTitle', 'Do-Not-Sell Popup Title')),
                    HtmlEditorField::create('CookieDoNotSellContent', _t(self::class . '.DoNotSellPopupContent', 'Opt-Out Popup Content')),
                ]
            );
        }
    }

    /**
     * Set the defaults this way beacause the SiteConfig is probably already created
     *
     * @throws ValidationException
     */
    public function onRequireDefaultRecords(): void
    {
        if ($config = SiteConfig::current_site_config()) {
            if (empty($config->CookieConsentTitle)) {
                $config->CookieConsentTitle = _t(self::class . '.DefaultCookieConsentTitle', 'This website uses cookies');
            }

            if (empty($config->CookieConsentContent)) {
                $config->CookieConsentContent = _t(self::class . '.DefaultCookieConsentContent', '<p>We process your personal data using cookies to ensure the proper functioning of the website. With your consent, we may also use cookies for analytical or marketing purposes. You can adjust your consent to these non-essential cookies by clicking "Manage cookie settings" or you can reject them by clicking "Necessary cookies only". Your consent may be withdrawn at any time through the link to the cookie policy in the footer of the website and changing to your preferred settings. For more information on the use of cookies, please click "Manage cookie settings".</p>');
            }

            if (empty($config->CookieOptOutTitle)) {
                $config->CookieOptOutTitle = _t(self::class . '.DefaultCookieOptOutTitle', 'This website uses cookies');
            }

            if (empty($config->CookieOptOutContent)) {
                $config->CookieOptOutContent = _t(self::class . '.DefaultCookieOptOutContent', '<p>We process your personal data using cookies to ensure the proper functioning of the website. You have the right to opt out of the sale or sharing of your personal information by clicking below.</p>');
            }

            if (empty($config->CookieDoNotSellTitle)) {
                $config->CookieDoNotSellTitle = _t(self::class . '.DefaultCookieDoNotSellTitle', 'We value your privacy');
            }

            if (empty($config->CookieDoNotSellContent)) {
                $config->CookieDoNotSellContent = _t(self::class . '.DefaultCookieDoNotSellContent', '<p>We process your personal data using cookies to ensure the proper functioning of the website. You have the right to opt out of the sale or sharing of your personal information by clicking below.</p>');
            }

            $config->write();
        }
    }

    public function getCookieGroups()
    {
        return CookieGroup::get();
    }
}
