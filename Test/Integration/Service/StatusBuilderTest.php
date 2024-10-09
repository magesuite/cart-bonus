<?php

namespace MageSuite\CartBonus\Test\Integration\Service;

/**
 * @magentoDbIsolation enabled
 */
class StatusBuilderTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\CartBonus\Service\StatusBuilder $statusBuilder;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->statusBuilder = $this->objectManager->create(\MageSuite\CartBonus\Service\StatusBuilder::class);
    }

    /**
     * @magentoDataFixture MageSuite_CartBonus::Test/Integration/_files/cart_rules.php
     */
    public function testStatusIsCalculatedCorrectly(): void
    {
        $status = $this->statusBuilder->build(30);

        $this->assertEquals(1, $status->getAwardedBonusesCount());
        $this->assertEquals(2, $status->getBonusesCount());
        $this->assertEquals(20, $status->getRemainingAmountForNextBonus());
        $this->assertEquals('<span class="price">$20.00</span>', $status->getRemainingAmountForNextBonusWithCurrency());
        $this->assertEquals(43, $status->getProgressPercentage(), 0);

        list($firstBonus, $secondBonus) = $status->getBonuses();

        $this->assertTrue($firstBonus->wasAwarded());
        $this->assertFalse($secondBonus->wasAwarded());

        $this->assertEquals('Free gift for 15 euro label', $firstBonus->getLabel());
        $this->assertNull($secondBonus->getLabel());
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product.php
     * @magentoDataFixture MageSuite_CartBonus::Test/Integration/_files/cart_rules.php
     */
    public function testItValidatesRuleActions(): void
    {
        $status = $this->statusBuilder->build(30);
        $bonus = $status->getBonuses()[0];

        $this->assertFalse($bonus->wasAwarded());
        $this->assertEquals(50, $bonus->getMinimumCartValue());
    }
}
