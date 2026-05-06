<?php
namespace Innoweb\CookieConsent\Gridfield;

use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

/**
 * Class GridFieldConfigCookies
 *
 * @author Bram de Leeuw
 */
class GridFieldConfigCookies extends GridFieldConfig
{
    public function __construct()
    {
        parent::__construct();
        $this->addComponent(GridFieldToolbarHeader::create());
        $this->addComponent(GridFieldButtonRow::create('before'));
        $this->addComponent($sort = GridFieldSortableHeader::create());
        $this->addComponent($filter = GridFieldFilterHeader::create());
        $this->addComponent(GridFieldEditableColumns::create());
        $this->addComponent(GridFieldDeleteAction::create());
        $this->addComponent(GridFieldAddNewInlineButton::create('toolbar-header-right'));

        $sort->setThrowExceptionOnBadDataType(false);
        $filter->setThrowExceptionOnBadDataType(false);

        $this->extend('updateConfig', $this);
    }
}
