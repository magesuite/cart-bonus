<?php

declare(strict_types=1);

namespace MageSuite\CartBonus\Service;

class StatusBuilder
{
    protected \MageSuite\CartBonus\Model\Bonus\StatusFactory $statusFactory;
    protected \MageSuite\CartBonus\Model\BonusFactory $bonusFactory;
    protected \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Customer\Model\Session $customerSession;
    protected \Magento\Checkout\Model\Session $checkoutSession;

    public function __construct(
        \MageSuite\CartBonus\Model\Bonus\StatusFactory $statusFactory,
        \MageSuite\CartBonus\Model\BonusFactory $bonusFactory,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->statusFactory = $statusFactory;
        $this->bonusFactory = $bonusFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    public function build(?float $cartValue): \MageSuite\CartBonus\Model\Bonus\Status
    {
        $quoteItems = $this->checkoutSession->getQuote()->getAllItems();
        $status = $this->statusFactory->create();

        $rules = $this->getBonusCartRules();

        $bonuses = [];

        foreach ($rules as $rule) {
            $minimumCartValue = $this->getMinimumRequiredCartValue($rule, $quoteItems);

            if ($minimumCartValue == null) {
                continue;
            }

            $bonus = $this->bonusFactory->create();

            $bonus->setMinimumCartValue($minimumCartValue);
            $bonus->setLabel($rule->getStoreLabel());
            $bonus->setIsLabelVisibleBeforeAwarding((bool)$rule->getIsLabelVisibleByDefault());

            $bonuses[] = $bonus;
        }

        usort($bonuses, function ($bonus1, $bonus2) {
            return $bonus1->getMinimumCartValue() <=> $bonus2->getMinimumCartValue();
        });

        $status->setBonuses($bonuses);
        $this->calculateCurrentProgress($status, $cartValue);

        return $status;
    }

    protected function calculateCurrentProgress(\MageSuite\CartBonus\Model\Bonus\Status $status, ?float $cartValue): void
    {
        $previousBonusMinimumCartValue = 0;

        foreach ($status->getBonuses() as $bonus) {
            if ($bonus->getMinimumCartValue() <= $cartValue) {
                $bonus->setWasAwarded(true);
                $previousBonusMinimumCartValue = $bonus->getMinimumCartValue();
                continue;
            }

            $progressPercentage = round(($cartValue - $previousBonusMinimumCartValue) * 100 / ($bonus->getMinimumCartValue() - $previousBonusMinimumCartValue), 0);

            $status->setProgressPercentage($progressPercentage);
            $status->setRemainingAmountForNextBonus($bonus->getMinimumCartValue() - $cartValue);

            break;
        }
    }

    protected function getBonusCartRules(): array
    {
        $ruleCollection = $this->ruleCollectionFactory->create();

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        return $ruleCollection
            ->setValidationFilter($websiteId, $customerGroupId)
            ->addFieldToFilter('is_visible_as_cart_bonus', ['eq' => 1])
            ->getItems();
    }

    protected function getMinimumRequiredCartValue(\Magento\SalesRule\Model\Rule $rule, array $quoteItems): ?float
    {
        $conditions = $rule->getConditions();

        if (!$this->validateRuleActions($rule, $quoteItems)) {
            return null;
        }

        /** @var $condition \Magento\Rule\Model\Condition\Combine */
        foreach ($conditions->getConditions() as $condition) {
            if (!in_array($condition->getAttribute(), ['base_subtotal_total_incl_tax', 'base_subtotal'])) {
                return null;
            }

            if (!in_array($condition->getOperator(), ['>', '>=']) || $condition->getValue() == null) {
                return null;
            }

            return (float)$condition->getValue();
        }
    }

    protected function validateRuleActions(\Magento\SalesRule\Model\Rule $rule, array $quoteItems): bool
    {
        $actions = $rule->getActions()->getActions();

        if (empty($actions)) {
            return true;
        }

        foreach ($quoteItems as $item) {
            if (!$rule->getActions()->validate($item)) {
                return false;
            }
        }

        return true;
    }
}
