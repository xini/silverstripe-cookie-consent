<?php

namespace Innoweb\CookieConsent\Model;

use Innoweb\CookieConsent\CookieConsent;
use Override;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

/**
 * A description for a used cookie
 *
 * @property string ConfigName
 * @property string Title
 * @property string Provider
 * @property string Purpose
 * @property string Expiry
 * @property string Type
 *
 * @method CookieGroup Group()
 */
class CookieDescription extends DataObject
{
    private static $table_name = 'CookieDescription';

    private static $db = [
        'ConfigName' => 'Varchar(255)',
        'Title' => 'Varchar(255)',
        'Provider' => 'Varchar(255)',
        'Purpose' => 'Varchar(255)',
        'Expiry' => 'Varchar(255)'
    ];

    private static $has_one = [
        'Group' => CookieGroup::class
    ];

    private static $summary_fields = [
        'Title',
        'Provider',
        'Purpose',
        'Expiry'
    ];

    private static $translate = [
        'Purpose',
        'Expiry'
    ];

    private static $singular_name = 'Cookie description';

    private static $plural_name = 'Cookie descriptions';

    #[Override]
    public function getCMSFields()
    {
        $fields = FieldList::create(TabSet::create('Root', $mainTab = Tab::create('Main')));
        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Title', $this->fieldLabel('Title')),
            TextField::create('Provider', $this->fieldLabel('Provider')),
            TextField::create('Purpose', $this->fieldLabel('Purpose')),
            TextField::create('Expiry', $this->fieldLabel('Expiry'))
        ]);

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    public function getProviderLabel()
    {
        if ($this->Provider == CookieGroup::LOCAL_PROVIDER) {
            return Director::host();
        }

        return $this->Provider;
    }

    /**
     * Cookies without a config definition can be deleted
     *
     * @param null $member
     * @return bool
     */
    #[Override]
    public function canDelete($member = null)
    {
        $cookieConfig = Config::inst()->get(CookieConsent::class, 'cookies');
        $found = false;
        foreach ($cookieConfig as $group => $domains) {
            if ($found) {
                break;
            }

            foreach ($domains as $cookies) {
                if ($found) {
                    break;
                }

                $found = in_array($this->ConfigName, $cookies);
            }
        }

        return !$found;
    }
}
