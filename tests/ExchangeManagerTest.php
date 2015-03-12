<?php
/**
 * @package SugiPHP.Money
 */

namespace SugiPHP\Money;

use PHPUnit_Framework_TestCase;

class ExchangeManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Currency::$currencyPrecisions = include(__DIR__."/../precisions.php");
    }

    public function testGetInstanceReturnsSameObject()
    {
        $exManager = ExchangeManager::getInstance();
        $exManager3 = new ExchangeManager();
        $exManager2 = ExchangeManager::getInstance();

        $this->assertSame($exManager, $exManager2);
        $this->assertNotSame($exManager, $exManager3);
        $this->assertEquals($exManager, $exManager3);
    }

    public function testAddingExchangeRates()
    {
        $exManager = new ExchangeManager();
        $exManager->add(new ExchangeRate("BGN/EUR 0.5113"));
        $this->assertCount(1, $exManager->rates);
        $exManager->add(new ExchangeRate("EUR/USD 1.25"));
        $this->assertCount(2, $exManager->rates);
    }

    public function testAddingRatesWithString()
    {
        $exManager = new ExchangeManager();
        $exManager->add("BGN/EUR 0.5113");
        $this->assertCount(1, $exManager->rates);
        $exManager->add("EUR/USD 1.25");
        $this->assertCount(2, $exManager->rates);
        $this->assertEquals("1.25 USD", $exManager->exchange(1, new Currency("EUR"), new Currency("USD"))->__toString());
        $this->assertEquals("51.13 EUR", $exManager->exchange(100, new Currency("BGN"), new Currency("EUR"))->__toString());
    }

    /**
     * @expectedException \SugiPHP\Money\ExchangeRateException
     */
    public function testManagerThrowsExchangeRateException()
    {
        $exManager = new ExchangeManager();
        $exManager->exchange(1, new Currency("EUR"), new Currency("USD"));
    }

    public function testManagerFindsProperExchangeRate()
    {
        $exManager = new ExchangeManager();
        $exManager->add(new ExchangeRate("BGN/EUR 0.5113"));

        $exRate = $exManager->findExRate(new Currency("BGN"), new Currency("EUR"));
        $exRate2 = $exManager->findExRate(new Currency("EUR"), new Currency("BGN"));
        $this->assertSame($exRate, $exRate2);
    }

    public function testManagerExchangeWith2and3Params()
    {
        $exManager = new ExchangeManager();
        $exManager->add("BGN/EUR 0.5113");

        $this->assertEquals("51.13 EUR", $exManager->exchange(100, new Currency("BGN"), new Currency("EUR"))->__toString());
        $this->assertEquals("51.13 EUR", $exManager->exchange(100, "BGN", new Currency("EUR"))->__toString());
        $this->assertEquals("51.13 EUR", $exManager->exchange(100, "BGN", "EUR")->__toString());
        $this->assertEquals("51.13 EUR", $exManager->exchange(new Money(100, new Currency("BGN")), new Currency("EUR"))->__toString());
        $this->assertEquals("51.13 EUR", $exManager->exchange("100 BGN", new Currency("EUR"))->__toString());
        $this->assertEquals("51.13 EUR", $exManager->exchange("100 BGN", "EUR")->__toString());
        $this->assertEquals("100.00 BGN", $exManager->exchange(51.13, new Currency("EUR"), new Currency("BGN"))->__toString());
        $this->assertEquals("100.00 BGN", $exManager->exchange(51.13, "EUR", new Currency("BGN"))->__toString());
        $this->assertEquals("100.00 BGN", $exManager->exchange(51.13, "EUR", "BGN")->__toString());
        $this->assertEquals("100.00 BGN", $exManager->exchange(new Money(51.13, new Currency("EUR")), new Currency("BGN"))->__toString());
        $this->assertEquals("100.00 BGN", $exManager->exchange("51.13 EUR", new Currency("BGN"))->__toString());
        $this->assertEquals("100.00 BGN", $exManager->exchange("51.13 EUR", "BGN")->__toString());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExchangeWithWrongStringAsFirstParam()
    {
        $exManager = new ExchangeManager();
        $exManager->add("BGN/EUR 0.5113");
        $exManager->exchange("foobar BGN", new Currency("EUR"));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExchangeWrongStringAsSecondParam()
    {
        $exManager = new ExchangeManager();
        $exManager->add("BGN/EUR 0.5113");
        $exManager->exchange("100 BGN", 123);
    }

    /**
     * @expectedException \SugiPHP\Money\UnknownCurrencyException
     */
    public function testExchangeThrowsUnknownCurrencyException()
    {
        $exManager = new ExchangeManager();
        $exManager->add("BGN/EUR 0.5113");
        $exManager->exchange("100 foobar", new Currency("EUR"));
    }

    /**
     * @expectedException \SugiPHP\Money\UnknownCurrencyException
     */
    public function testExchangeThrowsUnknownCurrencyExceptionSecondParam()
    {
        $exManager = new ExchangeManager();
        $exManager->add("BGN/EUR 0.5113");
        $exManager->exchange("100 BGN", "foobar");
    }

    public function testOverrideRateReturnsFirstFound()
    {
        $exManager = new ExchangeManager();
        $exManager->add(new ExchangeRate("EUR/USD 1.25"));
        $this->assertEquals("1.25 USD", $exManager->exchange(1, new Currency("EUR"), new Currency("USD"))->__toString());

        $exManager->add(new ExchangeRate("EUR/USD 1.50"));
        $this->assertEquals("1.25 USD", $exManager->exchange(1, new Currency("EUR"), new Currency("USD"))->__toString());
    }
}
