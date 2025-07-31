<?php

namespace Innoweb\CookieConsent\Pages;

use Innoweb\CookieConsent\CookieConsent;
use Page;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;

/**
 * Model for creating a default cookie policy page
 * This is the page where cookie settings can be edited and user can read about what cookies your using
 */
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
        'ShowInMenus' => 0
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->insertAfter(
            'Content',
            HTMLEditorField::create(
                'FooterContent',
                'Content below cookie descriptions'
            )->addExtraClass('stacked')
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

    }

    public function populateDefaults()
    {
        parent::populateDefaults();

        $this->Title = _t(__CLASS__ . '.Title', 'Cookie Policy');
        $this->Content = _t(__CLASS__ . '.DefaultContent', '<p>Cookies are small text files that may be stored on your computer or other device when you visit a website. Cookies are generally used to make websites work or work more efficiently, to keep track of your movements on the website, to display embedded videos, and for similar activities.</p><p>This website uses first-party cookies (cookies placed by this website), meaning only this website can read them. In addition, this website uses external services, which also set their own cookies, known as third-party cookies, to display content from external providers.</p><p>We use multiple categories of cookies on this website. Please see below their definitions. You can change your cookie preferences in the "Manage Cookies" section below by adjusting the respective checkbox for the selected category and clicking "Save".</p>');
        $this->FooterContent = _t(__CLASS__ . '.DefaultFooterContent', '<h2>How to provide or withdraw cookie consent</h2><p>In addition to using the "Manage Cookies" form provided above, the user can manage preferences for cookies directly from within their browser and prevent, for example, third parties from installing cookies.</p><p>Through browser preferences, it is also possible to delete cookies installed in the past, including the cookies that may have saved the initial consent for the installation of cookies by this website.</p><p>Users can, for example, find information about how to manage cookies in the most commonly used browsers at the following addresses: <a rel="noopener nofollow" href="https://support.google.com/chrome/answer/95647" target="_blank">Google Chrome</a>, <a rel="noopener nofollow" href="https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences" target="_blank">Mozilla Firefox</a>, <a rel="noopener nofollow" href="https://support.apple.com/guide/safari/manage-cookies-and-website-data-sfri11471/" target="_blank">Apple Safari</a> and <a rel="noopener nofollow" href="https://support.microsoft.com/en-us/windows/delete-and-manage-cookies-168dab11-0753-043d-7c16-ede5947fc64d" target="_blank">Microsoft Edge</a>.</p><h2>Owner and Data Controller</h2><p>[Name and address]</p><h3>Contact details:</h3><p>The Privacy Officer<br>[Name, email, phone, address]</p><p>Since the installation of third-party cookies and other tracking systems through the services used within this website cannot be technically controlled by the owner, any specific references to cookies installed by third parties are to be considered indicative. To obtain complete information, the user is kindly requested to consult the privacy policy for the respective third-party cookies listed in this notice.</p><p>Given the objective complexity surrounding the identification of technologies based on cookies, you are encouraged to contact us should you wish to receive any further information on the use of cookies by this website.</p>');
    }

    /**
     * Get the active cookie policy page
     *
     * @return CookiePolicyPage|DataObject
     */
    public static function instance()
    {
        return self::get()->first();
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
        return true;
    }
}
