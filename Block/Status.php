<?php

namespace MageSuite\CartBonus\Block;

class Status extends \Magento\Framework\View\Element\Template
{

    protected $_template = 'MageSuite_CartBonus::status.phtml';

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \MageSuite\CartBonus\Service\StatusBuilder
     */
    protected $statusBuilder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \MageSuite\CartBonus\Service\StatusBuilder $statusBuilder,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->cart = $cart;
        $this->statusBuilder = $statusBuilder;
    }

    public function getBonusesStatus()
    {
        $totals = $this->cart->getQuote()->getTotals();
        $currentCartValue = $totals['subtotal']['value'];

        return $this->statusBuilder->build($currentCartValue);
    }
}
