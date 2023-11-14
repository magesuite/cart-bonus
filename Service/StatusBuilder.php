<?php

namespace MageSuite\CartBonus\Service;

class StatusBuilder
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \MageSuite\CartBonus\Model\Bonus\StatusFactory
     */
    protected $statusFactory;

    /**
     * @var \MageSuite\CartBonus\Model\BonusFactory
     */
    protected $bonusFactory;

    public function __construct(
        \MageSuite\CartBonus\Model\Bonus\StatusFactory $statusFactory,
        \MageSuite\CartBonus\Model\BonusFactory $bonusFactory,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->statusFactory = $statusFactory;
        $this->bonusFactory = $bonusFactory;
    }

    /**
     * @param int $cartValue
     * @return \MageSuite\CartBonus\Model\Bonus\Status
     */
    public function build($cartValue)
    {
        /** @var \MageSuite\CartBonus\Model\Bonus\Status $status */
        $status = $this->statusFactory->create();

        $rules = $this->getBonusCartRules();

        $bonuses = [];

        /** @var \Magento\SalesRule\Model\Rule $rule */
        foreach ($rules as $rule) {
            $minimumCartValue = $this->getMinimumRequiredCartValue($rule);

            if($minimumCartValue == null) {
                continue;
            }

            /** @var \MageSuite\CartBonus\Model\Bonus $bonus */
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


    /**
     * @param $status \MageSuite\CartBonus\Model\Bonus\Status
     * @param $cartValue float
     * @return int
     */
    protected function calculateCurrentProgress($status, $cartValue)
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

    /**
     * @return \Magento\Framework\DataObject[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getBonusCartRules()
    {
        $ruleCollection = $this->ruleCollectionFactory->create();

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        $rules = $ruleCollection
            ->setValidationFilter($websiteId, $customerGroupId)
            ->addFieldToFilter('is_visible_as_cart_bonus', ['eq' => 1])
            ->getItems();

        return $rules;
    }

    /**
     * @param $rule \Magento\SalesRule\Model\Rule
     * @return float|null
     */
    protected function getMinimumRequiredCartValue($rule)
    {
        $conditions = $rule->getConditions();

        /** @var $condition \Magento\Rule\Model\Condition\Combine */
        foreach ($conditions->getConditions() as $condition) {
            if(!in_array($condition->getAttribute(), ['base_subtotal_total_incl_tax', 'base_subtotal'])) {
                return null;
            }

            if(!in_array($condition->getOperator(), ['>', '>='])) {
                return null;
            }

            return $condition->getValue();
        }
    }
}
