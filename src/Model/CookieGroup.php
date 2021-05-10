<?php

namespace Innoweb\CookieConsent\Model;

use Innoweb\CookieConsent\CookieConsent;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;

class CookieGroup extends DataObject
{
    private static $table_name = 'CookieGroup';
    private static $singular_name = 'Cookie Group';
    private static $plural_name = 'Cookie Groups';
    
    private static $db = array(
        'Title' => 'Varchar(255)',
        'Content' => 'HTMLText',
        'Sort' => 'Int',
    );

    private static $translate = array(
        'Title',
        'Content'
    );

    private static $default_sort = 'Sort ASC';
    
    public function getCMSFields()
    {
        $fields = FieldList::create(TabSet::create('Root', Tab::create('Main')));
        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', $this->fieldLabel('Title')),
            HtmlEditorField::create('Content', $this->fieldLabel('Content'))
        ));

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        
        if (!CookieGroup::get() || !CookieGroup::get()->exists()) {
            $cookiesConfig = CookieConsent::config()->get('cookieGroups');
            if ($cookiesConfig) {
                foreach ($cookiesConfig as $groupName) {
                    $group = self::create(array(
                        'Title' => _t(__CLASS__ . ".CookieGroupHeader{$groupName}", $groupName),
                        'Content' => _t(__CLASS__ . ".CookieGroupContent{$groupName}", $groupName)
                    ));
                    $group->write();
                    DB::alteration_message(sprintf('Cookie group "%s" created', $groupName), 'created');
                }
            }
        }
    }

}
