<?php

namespace Innoweb\CookieConsent\Forms;

use Innoweb\CookieConsent\CookieConsent;
use Innoweb\CookieConsent\Model\CookieGroup;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;

/**
 * Class CookieConsentForm
 *
 * @author Bram de Leeuw
 */
class CookieConsentForm extends Form
{
    protected $extraClasses = array('cookie-consent-form');

    public function __construct(Controller $controller, $name)
    {
        $fields = FieldList::create();
        $cookieGroups = CookieGroup::get();
        $data = CookieConsent::getConsent();

        /** @var CookieGroup $cookieGroup */
        foreach ($cookieGroups as $cookieGroup) {
            $fields->add($field = $cookieGroup->createField());
            if (in_array($cookieGroup->ConfigName, $data)) {
                $field->setValue(1);
            }
        }

        $actions = FieldList::create(FormAction::create('submitConsent', _t(__CLASS__ . '.Save', 'Save')));
        parent::__construct($controller, $name, $fields, $actions);
    }

    /**
     * Submit the consent
     *
     * @param $data
     * @param Form $form
     */
    public function submitConsent($data, Form $form)
    {
        $consent = [];
        $consent = array_merge($consent, CookieConsent::config()->get('required_groups'));
        foreach (CookieConsent::config()->get('cookies') as $group => $cookies) {
            if (isset($data[$group]) && $data[$group]) {
                array_push($consent, $group);
            } elseif ($group !== CookieGroup::REQUIRED_DEFAULT) {
                $consent = array_diff($consent, [$group]);
            }
        }
        CookieConsent::setConsent($consent);

        $form->sessionMessage(_t(__CLASS__ . '.FormMessage', 'Your preferences have been saved'), 'good');

        // get redirectBack URL like in RequestHandler::redirectBack()
        $controller = $this->getController();
        $url = $controller->getBackURL()
            ?: $controller->getReturnReferer()
                ?: Director::baseURL();

        // Only direct to absolute urls
        $url = Director::absoluteURL((string) $url);
        $url = Controller::join_links($url, '?acceptCookies=' . implode(',', CookieConsent::getConsent()));
        return $controller->redirect($url);
    }
}
