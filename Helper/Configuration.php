<?php

declare(strict_types=1);

namespace MageSuite\CartBonus\Helper;

class Configuration
{
    public const XML_PATH_GENERAL_EXCLUDE_VIRTUAL_PRODUCT_FROM_CALULATION = 'cart_bonus/general/exclude_virtual_product_from_calculation';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isVirtualProductExcludedFromCalculation($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GENERAL_EXCLUDE_VIRTUAL_PRODUCT_FROM_CALULATION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
