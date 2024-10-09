<?php

declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$collection = $objectManager->create(\Magento\SalesRule\Model\ResourceModel\Rule\Collection::class);

foreach ($collection->getItems() as $rule) {
    if ($rule->getId()) {
        $rule->delete();
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
