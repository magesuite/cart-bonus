<?php

namespace MageSuite\CartBonus\Block;

class Status extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'MageSuite_CartBonus::status.phtml';

    protected \Magento\Checkout\Model\Cart $cart;
    protected \MageSuite\CartBonus\Service\StatusBuilder $statusBuilder;
    protected \MageSuite\CartBonus\Helper\Configuration $configuration;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \MageSuite\CartBonus\Service\StatusBuilder $statusBuilder,
        \MageSuite\CartBonus\Helper\Configuration $configuration,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cart = $cart;
        $this->statusBuilder = $statusBuilder;
        $this->configuration = $configuration;
    }

    public function getBonusesStatus()
    {
        return $this->statusBuilder->build($this->getCartValue());
    }

    public function getCartValue(): ?float
    {
        $quote = $this->cart->getQuote();
        $totals = $quote->getTotals();
        $subtotal = $totals['subtotal']['value'];

        if (!$this->configuration->isVirtualProductExcludedFromCalculation($quote->getStoreId())) {
            return $subtotal;
        }

        foreach ($quote->getAllItems() as $item) {
            if (!$item->getIsVirtual()) {
                continue;
            }

            $subtotal -= $item->getRowTotalInclTax();
        }

        return $subtotal;
    }
}
