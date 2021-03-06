<?php

namespace Ceres\Contexts;

use IO\Helper\ContextInterface;
use IO\Services\ItemLastSeenService;
use IO\Services\NotificationService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Http\Request;
use Ceres\Config\CeresConfig;
use IO\Services\CategoryService;
use IO\Services\ItemCrossSellingService;
use IO\Services\SessionStorageService;
use IO\Services\TemplateService;
use IO\Services\BasketService;

class GlobalContext implements ContextInterface
{
    protected $params = [];
    
    /** @var CeresConfig $ceresConfig  */
    public $ceresConfig = null;
    
    public $lang;
    public $metaLang;
    public $template = [];
    public $templateName;
    public $categories;
    public $categoryBreadcrumbs;
    public $notifications;
    public $basket;
    public $webstoreConfig;

    public function init($params)
    {
        $this->params = $params;

        /** @var SessionStorageService $sessionStorageService */
        $sessionStorageService = pluginApp(SessionStorageService::class);

        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);

        /** @var TemplateService $templateService */
        $templateService = pluginApp(TemplateService::class);

        /** @var ItemCrossSellingService $crossSellingService */
        $crossSellingService = pluginApp(ItemCrossSellingService::class);

        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);

        /** @var ItemLastSeenService $itemLastSeenService */
        $itemLastSeenService = pluginApp(ItemLastSeenService::class);

        /** @var BasketService $basketService */
        $basketService = pluginApp(BasketService::class);

        $this->ceresConfig = pluginApp(CeresConfig::class);
        $this->webstoreConfig = $webstoreConfigService->getWebstoreConfig();

        $this->lang = $sessionStorageService->getLang();
        $this->metaLang = 'de';
        if($this->lang == 'en')
        {
            $this->metaLang = $this->lang;
        }

        if($templateService->isCategory() || $templateService->isItem())
        {
            $this->categoryBreadcrumbs = $categoryService->getHierarchy();
            $crossSellingService->setType($this->ceresConfig->itemLists->crossSellingType);
            $itemLastSeenService->setLastSeenMaxCount($this->ceresConfig->itemLists->lastSeenNumber);
        }

        $this->categories = $categoryService->getNavigationTree($this->ceresConfig->header->showCategoryTypes, $this->lang, 6);
        $this->notifications = pluginApp(NotificationService::class)->getNotifications();

        $this->basket = $basketService->getBasketForTemplate();
    }
    
    protected function getParam($key, $defaultValue = null)
    {
        if(is_null($this->params[$key]))
        {
            return $defaultValue;
        }
        
        return $this->params[$key];
    }
}