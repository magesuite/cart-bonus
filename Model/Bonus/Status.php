<?php

namespace MageSuite\CartBonus\Model\Bonus;

class Status
{
    /**
     * @var int
     */
    protected $progressPercentage = 100;

    protected $remainingAmountForNextBonus = 0;

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * @var \MageSuite\CartBonus\Model\Bonus[]
     */
    protected $bonuses = [];

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    public function __construct(\Magento\Framework\Pricing\Helper\Data $pricingHelper)
    {
        $this->pricingHelper = $pricingHelper;
    }

    public function getAwardedBonusesCount() {
        $awardedBonuses = 0;

        foreach($this->bonuses as $bonus) {
            if(!$bonus->wasAwarded()) {
                continue;
            }

            $awardedBonuses++;
        }

        return $awardedBonuses;
    }

    /**
     * @param $bonuses \MageSuite\CartBonus\Model\Bonus[]
     * @return $this
     */
    public function setBonuses($bonuses) {
        $this->bonuses = $bonuses;

        return $this;
    }

    /**
     * @return \MageSuite\CartBonus\Model\Bonus[]
     */
    public function getBonuses() {
        return $this->bonuses;
    }

    /**
     * @return int
     */
    public function getBonusesCount()
    {
        return count($this->bonuses);
    }

    /**
     * @return int
     */
    public function getProgressPercentage()
    {
        return $this->progressPercentage;
    }

    /**
     * @param int $progressPercentage
     */
    public function setProgressPercentage($progressPercentage)
    {
        $this->progressPercentage = $progressPercentage;
    }

    /**
     * @return int
     */
    public function getRemainingAmountForNextBonus()
    {
        return $this->remainingAmountForNextBonus;
    }

    /**
     * @return int
     */
    public function getRemainingAmountForNextBonusWithCurrency()
    {
        return $this->pricingHelper->currency($this->getRemainingAmountForNextBonus());
    }

    /**
     * @param int $remainingAmountForNextBonus
     */
    public function setRemainingAmountForNextBonus($remainingAmountForNextBonus)
    {
        $this->remainingAmountForNextBonus = $remainingAmountForNextBonus;
    }
}
