<?php

namespace MageSuite\CartBonus\Test\Integration\Service;

/**
 * @magentoDbIsolation enabled
 * @magentoDataFixture loadCartRules
 */
class StatusBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\CartBonus\Service\StatusBuilder
     */
    protected $statusBuilder;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->statusBuilder = $this->objectManager->create(\MageSuite\CartBonus\Service\StatusBuilder::class);
    }

    public function testStatusIsCalculatedCorrectly()
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

    public static function loadCartRules()
    {
        include __DIR__ . '/../_files/cart_rules.php';
    }
}
