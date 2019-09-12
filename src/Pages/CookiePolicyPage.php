<?php

namespace Innoweb\CookieConsent\Pages;

use Innoweb\CookieConsent\CookieConsent;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;
use Page;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;

class CookiePolicyPage extends Page
{
    private static $table_name = 'CookiePolicyPage';
    private static $singular_name = 'Cookie Policy Page';
    private static $plural_name = 'Cookie Policy Pages';
    private static $description = 'Cookie Policy Page';
    
    private static $db = [
        'FooterContent' => 'HTMLText',
    ];
    
    private static $defaults = [
        'ShowInMenus' => 0,
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        
        $fields->insertAfter(
            'Content', 
            HTMLEditorField::create(
                'FooterContent',
                'Content below cookie descriptions'
            )
        );
        
        $content = $fields->dataFieldByName('Content');
        if ($content) {
            $content->setTitle('Content above cookie descriptions');
        }

        return $fields;
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        
        if (Config::inst()->get(CookieConsent::class, 'create_default_pages') && !self::get()->exists()) {
            $page = self::create();
            $page->write();
            $page->copyVersionToStage(Versioned::DRAFT, Versioned::LIVE);
            $page->flushCache();
            DB::alteration_message('Cookie Policy page created', 'created');
        }
        
        if ($page = CookiePolicyPage::get()->first()) {
            if (empty($page->Content)) {
                $page->Content = _t(__CLASS__ . '.DefaultContent', "<p>Cookies consist of portions of code installed in the browser that assist the Owner in providing the Service according to the purposes described. Some of the purposes for which Cookies are installed may also require the User's consent.</p><p>Where the installation of Cookies is based on consent, such consent can be freely withdrawn at any time following the instructions provided in this document.</p>");
            }
            if (empty($page->FooterContent)) {
                $page->FooterContent = _t(__CLASS__ . '.DefaultFooterContent', "<h2>How to provide or withdraw consent to the installation of Cookies</h2><p>In addition to what is specified in this document, the User can manage preferences for Cookies directly from within their own browser and prevent – for example – third parties from installing Cookies.<br />Through browser preferences, it is also possible to delete Cookies installed in the past, including the Cookies that may have saved the initial consent for the installation of Cookies by this website.<br />Users can, for example, find information about how to manage Cookies in the most commonly used browsers at the following addresses: Google Chrome, Mozilla Firefox, Apple Safari and Microsoft Internet Explorer.</p><p>With regard to Cookies installed by third parties, Users can manage their preferences and withdrawal of their consent by clicking the related opt-out link (if provided), by using the means provided in the third party's privacy policy, or by contacting the third party.</p><p>Notwithstanding the above, the Owner informs that Users may follow the instructions provided on the subsequently linked initiatives by the EDAA (EU), the Network Advertising Initiative (US) and the Digital Advertising Alliance (US), DAAC (Canada), DDAI (Japan) or other similar services. Such initiatives allow Users to select their tracking preferences for most of the advertising tools. The Owner thus recommends that Users make use of these resources in addition to the information provided in this document.</p>");
            }
            $page->write();
        }
    }

    public function canCreate($member = null, $context = [])
    {
        if (self::get()->exists()) {
            return false;
        } else {
            return parent::canCreate($member);
        }
    }

    public function canDelete($member = null)
    {
        return false;
    }
}
