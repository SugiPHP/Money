<?php
/**
 * @package SugiPHP.Money
 */

namespace SugiPHP\Money;

use PHPUnit_Framework_TestCase;

class ExchangeRateTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Currency::$currencyPrecisions = include(__DIR__."/../precisions.php");
    }

    public function testExchange()
    {
        $conv = new ExchangeRate(new Currency("EUR"), new Currency("USD"), 1.141715);
        // converts 100 EUR to 114.17 USD
        $eur = new Money("100 EUR");
        $usd = new Money("114.17 USD");
        $this->assertEquals($usd, $conv->exchange($eur));
        $this->assertEquals($usd, $conv->exchange(100));
        // back rate conversions
        $this->assertEquals($eur, $conv->exchange($usd));
        // we cannot pass only target amount and expect to receive source!
        $this->assertNotEquals($eur, $conv->exchange(114.17));
    }

    public function testExchangeRateFromString()
    {
        $conv = new ExchangeRate("BGN/EUR 0.511280836");
        $this->assertEquals("51.13 EUR", $conv->exchange(100)->__toString());

        $conv = new ExchangeRate("BGN", "EUR", 0.511280836);
        $this->assertEquals("51.13 EUR", $conv->exchange(100)->__toString());
    }

    public function testToString()
    {
        $conv = new ExchangeRate("BGN/EUR 0.511280836");
        $this->assertEquals("BGN/EUR 0.511280836", $conv->__toString());

        $conv = new ExchangeRate(new Currency("EUR"), new Currency("USD"), 1.141715);
        $this->assertEquals("EUR/USD 1.141715", $conv->__toString());
    }

    public function testInvert()
    {
        $conv = new ExchangeRate("EUR/USD 1.25");
        $conv->invert();
        $this->assertEquals(0.8, $conv->getRate());
        $this->assertEquals("USD", $conv->getSourceCurrency()->__toString());
        $this->assertEquals("EUR", $conv->getTargetCurrency()->__toString());
        $this->assertEquals("USD/EUR 0.8", $conv->__toString());
        $conv->invert();
        $this->assertEquals("EUR/USD 1.25", $conv->__toString());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFromStringThrowsExceptionIfNotCurrency()
    {
        new ExchangeRate("BG/EUR 0.511280836");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFromStringThrowsExceptionIfNotNumeric()
    {
        new ExchangeRate("BGN/EUR foobar");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExchangeThrowsOnZeroRate()
    {
        new ExchangeRate(new Currency("EUR"), new Currency("USD"), 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExchangeThrowsOnNonNumericRate()
    {
        new ExchangeRate(new Currency("EUR"), new Currency("USD"), "foobar");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExchangeCannotConvertUnknownCurrencies()
    {
        $conv = new ExchangeRate(new Currency("EUR"), new Currency("USD"), 1.141715);
        $conv->exchange(new Money("100 BGN"));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExchangeThrowsExceptionOnNonNumericValues()
    {
        $conv = new ExchangeRate(new Currency("EUR"), new Currency("USD"), 1.141715);
        $conv->exchange("foobar");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExchangeThrowsExceptionOnNonMoneyObjects()
    {
        $conv = new ExchangeRate(new Currency("EUR"), new Currency("USD"), 1.141715);
        $conv->exchange(new \StdClass());
    }
}
