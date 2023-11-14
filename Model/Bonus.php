<?php


namespace MageSuite\CartBonus\Model;

class Bonus
{
    /**
     * @var bool
     */
    protected $wasAwarded = false;

    /**
     * @var bool
     */
    protected $isLabelVisibleBeforeAwarding = false;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var float
     */
    protected $minimumCartValue;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    public function __construct(\Magento\Framework\Pricing\Helper\Data $pricingHelper)
    {
        $this->pricingHelper = $pricingHelper;
    }

    public function setMinimumCartValue($value)
    {
        $this->minimumCartValue = $value;
    }

    public function getMinimumCartValue() {
        return $this->minimumCartValue;
    }

    public function getMinimumCartValueWithCurrency() {
        return $this->pricingHelper->currency($this->minimumCartValue);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if(!$this->wasAwarded() and !$this->isLabelVisibleBeforeAwarding) {
            return null;
        }

        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function wasAwarded() {
        return $this->wasAwarded;
    }

    /**
     * @param bool $wasAwarded
     */
    public function setWasAwarded($wasAwarded)
    {
        $this->wasAwarded = $wasAwarded;
    }

    /**
     * @param bool $isLabelVisibleBeforeAwarding
     */
    public function setIsLabelVisibleBeforeAwarding($isLabelVisibleBeforeAwarding)
    {
        $this->isLabelVisibleBeforeAwarding = $isLabelVisibleBeforeAwarding;
    }
}
