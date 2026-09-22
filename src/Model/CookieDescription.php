<?php

declare(strict_types=1);

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
    private static string $table_name = 'CookieDescription';

    private static array $db = [
        'ConfigName' => 'Varchar(255)',
        'Title' => 'Varchar(255)',
        'Provider' => 'Varchar(255)',
        'Purpose' => 'Varchar(255)',
        'Expiry' => 'Varchar(255)'
    ];

    private static array $has_one = [
        'Group' => CookieGroup::class
    ];

    private static array $summary_fields = [
        'Title',
        'Provider',
        'Purpose',
        'Expiry'
    ];

    private static array $translate = [
        'Purpose',
        'Expiry'
    ];

    private static string $singular_name = 'Cookie description';

    private static string $plural_name = 'Cookie descriptions';

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
     */
    #[Override]
    public function canDelete($member = null): bool
    {
        $cookieConfig = Config::inst()->get(CookieConsent::class, 'cookies');
        $found = false;
        foreach ($cookieConfig as $domains) {
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
