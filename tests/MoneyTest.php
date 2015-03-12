<?php
/**
 * @package SugiPHP.Money
 */

namespace SugiPHP\Money;

use PHPUnit_Framework_TestCase;

class MoneyTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Currency::$currencyPrecisions = include(__DIR__."/../precisions.php");
    }

    public function testCreateIn3Forms()
    {
        new Money(100, new Currency("USD"));
        new Money(15.07, "EUR");
        new Money("1974 BGN");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateTrowsException()
    {
        new Money(1, 2, 3);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateTrowsExceptionOnNonNumeric()
    {
        new Money("foobar", "BGN");
    }

    /**
     * @expectedException \SugiPHP\Money\UnknownCurrencyException
     */
    public function testCreateTrowsExceptionOnUnknownCurrency()
    {
        new Money(100, "foo");
    }

    public function testGetAmountAndCurrency()
    {
        $price = new Money(100, $euro = new Currency("EUR"));
        $this->assertEquals(100, $price->getAmount());
        $this->assertEquals($euro, $price->getCurrency());
    }

    public function testEqualOrNot()
    {
        $eur = new Money(100, new Currency("EUR"));
        $this->assertTrue($eur->isEqualTo(new Money(100, new Currency("EUR"))));
        $this->assertFalse($eur->isEqualTo(new Money(50, new Currency("EUR"))));
        $this->assertTrue($eur->isEqualTo(new Money(100, new Currency("EUR", 10))));
        $this->assertTrue($eur->isEqualTo(new Money(100.0001, new Currency("EUR"))));
        $this->assertTrue($eur->isEqualTo(new Money(99.999, new Currency("EUR"))));
        $this->assertFalse($eur->isEqualTo(new Money(100, new Currency("USD"))));
    }

    public function testStringToMoney()
    {
        $moneys = array(
            array("1 USD", 1, "USD", "1.00 USD"),
            array("1 EUR", 1, "EUR", "1.00 EUR"),

            array("+1 EUR", 1, "EUR", "1.00 EUR"),
            array("-1 EUR", -1, "EUR", "-1.00 EUR"),

            array("100 EUR", 100, "EUR", "100.00 EUR"),
            array("100.0 EUR", 100, "EUR", "100.00 EUR"),
            array("100,0 EUR", 100, "EUR", "100.00 EUR"),
            array("100.00 EUR", 100, "EUR", "100.00 EUR"),
            array("100,00 EUR", 100, "EUR", "100.00 EUR"),
            array("100.00001 EUR", 100, "EUR", "100.00 EUR"),
            array("100,00001 EUR", 100, "EUR", "100.00 EUR"),

            array("9999.99 EUR", 9999.99, "EUR", "9999.99 EUR"),

            array("0.01 EUR", 0.01, "EUR", "0.01 EUR"),
            array("0.00000000001 EUR", 0, "EUR", "0.00 EUR"),
            array("-0.00000000001 EUR", 0, "EUR", "0.00 EUR"),
        );

        foreach ($moneys as $value) {
            list($string, $amount, $currency, $print) = $value;
            $money = new Money($string);
            $this->assertEquals($amount, $money->getAmount());
            $this->assertEquals($currency, $money->getCurrency()->getCode());
            $newString = $money->__toString($string);
            $this->assertSame($print, $newString);
            $newMoney = new Money($newString);
            $this->assertEquals($amount, $newMoney->getAmount());
            $this->assertEquals($currency, $newMoney->getCurrency()->getCode());
        }
    }

    /**
     * Tests for exchangeTo() method.
     */
    public function testExchange()
    {
        $exManager = ExchangeManager::getInstance();
        $exManager->add(new ExchangeRate("BGN/EUR 0.511280836"));

        $money = new Money("100 BGN");
        // with string
        $this->assertEquals("51.13 EUR", $money->exchangeTo("EUR")->__toString());
        // with Currency object
        $this->assertEquals("51.13 EUR", $money->exchangeTo(new Currency("EUR"))->__toString());
    }
}
