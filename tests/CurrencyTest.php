<?php
/**
 * @package SugiPHP.Money
 */

namespace SugiPHP\Money;

use PHPUnit_Framework_TestCase;

/**
 * Currency class Test
 */
class CurrencyTest extends PHPUnit_Framework_TestCase
{
    public function testCreateWithDefaultPrecision()
    {
        $eur = new Currency("EUR");
        $this->assertSame("EUR", $eur->getCode());
        $this->assertSame(Currency::getDefaultPrecision("EUR"), $eur->getPrecision());
    }

    public function testCreateWithCustomlySetDefaultPrecision()
    {
        Currency::$currencyPrecisions = array("EUR" => 3);
        $this->assertSame(3, Currency::getDefaultPrecision("EUR"));

        $eur = new Currency("EUR");
        $this->assertSame(3, $eur->getPrecision());
    }

    /**
     * @expectedException \SugiPHP\Money\UnknownCurrencyException
     */
    public function testUnknownCurrency()
    {
        new Currency("NOCURRENCY");
    }

    public function testUnknownCurrencyWithPrecision()
    {
        $newCurrency = new Currency("NEWCURRENCY", 8);
        $this->assertSame(8, $newCurrency->getPrecision(8));
    }

    public function testSetPrecision()
    {
        $eur = new Currency("EUR", 2);
        $this->assertSame(2, $eur->getPrecision());
        $eur->setPrecision(3);
        $this->assertSame(3, $eur->getPrecision());
    }

    public function testEqualAndSame()
    {
        $eur1 = new Currency("EUR", 2);
        $eur2 = new Currency("EUR", 2);
        $this->assertTrue($eur1->isEqualTo($eur2));
        $this->assertTrue($eur2->isEqualTo($eur1));
        $this->assertTrue($eur1->isSameTo($eur2));
        $this->assertTrue($eur2->isSameTo($eur1));
    }

    public function testNotEqual()
    {
        $eur = new Currency("EUR", 2);
        $usd = new Currency("USD", 2);
        $this->assertFalse($eur->isEqualTo($usd));
        $this->assertFalse($usd->isEqualTo($eur));
        $this->assertFalse($eur->isSameTo($usd));
        $this->assertFalse($usd->isSameTo($eur));
    }

    public function testEqualButNotSame()
    {
        $eur1 = new Currency("EUR", 5);
        $eur2 = new Currency("EUR", 10);
        $this->assertTrue($eur1->isEqualTo($eur2));
        $this->assertTrue($eur2->isEqualTo($eur1));
        $this->assertFalse($eur1->isSameTo($eur2));
        $this->assertFalse($eur2->isSameTo($eur1));
    }
}
