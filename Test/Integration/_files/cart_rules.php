<?php

declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$serializer = $objectManager->create(\Magento\Framework\Serialize\Serializer\Json::class);

if (!function_exists('build_condition_for_minimal_cart_value')) {
    function build_condition_for_minimal_cart_value(int $cartValue): string {
       return '{"type":"Magento\\\\SalesRule\\\\Model\\\\Rule\\\\Condition\\\\Combine","attribute":null,"operator":null,"value":"1","is_value_processed":null,"aggregator":"all","conditions":[{"type":"Magento\\\\SalesRule\\\\Model\\\\Rule\\\\Condition\\\\Address","attribute":"base_subtotal_total_incl_tax","operator":">=","value":"' . $cartValue . '","is_value_processed":false}]}';
    }
}

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = $objectManager->create(\Magento\SalesRule\Model\Rule::class);
$salesRule->setData(
    [
        'name' => 'Free gift for 15 euro',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'conditions_serialized' => build_condition_for_minimal_cart_value(15),
        'actions_serialized' => $serializer->serialize([
            'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
            'attribute' => null,
            'operator' => null,
            'value' => '1',
            'is_value_processed' => null,
            'aggregator' => 'all',
            'conditions' => [
                [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                    'attribute' => 'quote_item_sku',
                    'operator' => '!=',
                    'value' => 'simple',
                    'is_value_processed' => false,
                ]
            ]
        ]),
        'simple_action' => \MageSuite\FreeGift\SalesRule\Action\GiftOnceAction::ACTION,
        'discount_amount' => 0,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getWebsite()->getId()
        ],
        'discount_qty' => 0,
        'apply_to_shipping' => 1,
        'simple_free_shipping' => 0,
        'is_visible_as_cart_bonus' => 1,
        'store_labels' => [
            0 => 'Free gift for 15 euro label'
        ]
    ]
);

$salesRule->save();

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = $objectManager->create(\Magento\SalesRule\Model\Rule::class);
$salesRule->setData(
    [
        'name' => 'Free gift for 50 euro',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'conditions_serialized' => build_condition_for_minimal_cart_value(50),
        'simple_action' => \MageSuite\FreeGift\SalesRule\Action\GiftOnceAction::ACTION,
        'discount_amount' => 0,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getWebsite()->getId()
        ],
        'discount_qty' => 0,
        'apply_to_shipping' => 1,
        'simple_free_shipping' => 0,
        'is_visible_as_cart_bonus' => 1,
        'store_labels' => [
            0 => 'Free gift for 50 euro label'
        ]
    ]
);

$salesRule->save();

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = $objectManager->create(\Magento\SalesRule\Model\Rule::class);
$salesRule->setData(
    [
        'name' => 'Free gift for 100 euro but not visible as cart bonus',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'conditions_serialized' => build_condition_for_minimal_cart_value(100),
        'simple_action' => \MageSuite\FreeGift\SalesRule\Action\GiftOnceAction::ACTION,
        'discount_amount' => 0,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getWebsite()->getId()
        ],
        'discount_qty' => 0,
        'apply_to_shipping' => 1,
        'simple_free_shipping' => 0,
        'is_visible_as_cart_bonus' => 0
    ]
);

$salesRule->save();
