<?php

namespace Innoweb\CookieConsent\Model;

use Exception;
use Innoweb\CookieConsent\CookieConsent;
use Innoweb\CookieConsent\Forms\CookieConsentCheckBoxField;
use Innoweb\CookieConsent\Gridfield\GridFieldConfigCookies;
use Override;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\HasManyList;

/**
 * CookieGroup that holds type of cookies
 * You can add these groups trough the yml config
 *
 * @property string ConfigName
 * @property string Title
 * @property string Content
 *
 * @method HasManyList Cookies()
 */
class CookieGroup extends DataObject
{
    const REQUIRED_DEFAULT = 'Necessary';

    const LOCAL_PROVIDER = 'local';

    private static $table_name = 'CookieGroup';

    private static $db = [
        'ConfigName' => 'Varchar(255)',
        'Title' => 'Varchar(255)',
        'Content' => 'HTMLText',
    ];

    private static $indexes = [
        'ConfigName' => true
    ];

    private static $has_many = [
        'Cookies' => CookieDescription::class . '.Group'
    ];

    private static $translate = [
        'Title',
        'Content'
    ];

    /**
     * @return FieldList|mixed
     */
    #[Override]
    public function getCMSFields()
    {
        $fields = FieldList::create(TabSet::create('Root', $mainTab = Tab::create('Main')));
        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Title', $this->fieldLabel('Title')),
            HtmlEditorField::create('Content', $this->fieldLabel('Content')),
            GridField::create('Cookies', $this->fieldLabel('Cookies'), $this->Cookies(), GridFieldConfigCookies::create())
        ]);

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    /**
     * Check if this group is the required default
     *
     * @return bool
     */
    public function isRequired()
    {
        return CookieConsent::isRequired($this->ConfigName);
    }

    /**
     * Create a Cookie Consent checkbox based on the current cookie group
     *
     * @return CookieConsentCheckBoxField
     */
    public function createField()
    {
        return CookieConsentCheckBoxField::create($this);
    }

    /**
     * @throws Exception
     */
    #[Override]
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        $cookiesConfig = CookieConsent::config()->get('cookies');
        $necessaryGroups = CookieConsent::config()->get('required_groups');
        if ($cookiesConfig && $necessaryGroups) {
            foreach (array_unique($necessaryGroups) as $necessary) {
                if (!isset($cookiesConfig[$necessary])) {
                    throw new Exception(sprintf("The required default cookie set is missing, make sure to set the '%s' group", $necessary));
                }
            }

            foreach ($cookiesConfig as $groupName => $providers) {
                if (!$group = self::get()->find('ConfigName', $groupName)) {
                    $group = self::create([
                        'ConfigName' => $groupName,
                        'Title' => _t(self::class . ('.' . $groupName), $groupName),
                        'Content' => _t(self::class . sprintf('.%s_Content', $groupName), $groupName)
                    ]);

                    $group->write();
                    DB::alteration_message(sprintf('Cookie group "%s" created', $groupName), 'created');
                }

                foreach ($providers as $providerName => $cookies) {
                    $providerLabel = $providerName === self::LOCAL_PROVIDER ? self::LOCAL_PROVIDER : str_replace('_', '.', $providerName);

                    foreach ($cookies as $cookieName) {
                        $cookie = CookieDescription::get()->filter([
                            'ConfigName' => $cookieName,
                            'Provider' => $providerLabel
                        ])->first();

                        if (!$cookie) {
                            $cookie = CookieDescription::create([
                                'ConfigName' => $cookieName,
                                'Title' => $cookieName,
                                'Provider' => $providerLabel,
                                'Purpose' => _t(sprintf('CookieConsent_%s.%s_Purpose', $providerName, $cookieName), $cookieName),
                                'Expiry' => _t(sprintf('CookieConsent_%s.%s_Expiry', $providerName, $cookieName), 'Session')
                            ]);

                            $group->Cookies()->add($cookie);
                            $cookie->flushCache();
                            DB::alteration_message(sprintf('Cookie "%s" created and added to group "%s"', $cookieName, $groupName), 'created');
                        }
                    }
                }

                $group->flushCache();
            }
        }
    }

    #[Override]
    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    /**
     * Make deletable if not defined in config
     *
     * @param null $member
     * @return bool
     */
    #[Override]
    public function canDelete($member = null)
    {
        $cookieConfig = CookieConsent::config()->get('cookies');
        return !isset($cookieConfig[$this->ConfigName]);
    }
}
