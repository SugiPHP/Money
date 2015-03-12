<?php
/**
 * @package SugiPHP.Money
 */

namespace SugiPHP\Money;

use InvalidArgumentException;

class Money
{
    /**
     * Amount of money in specified Currency.
     *
     * @var integer
     */
    protected $amount;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * Money Object Constructor
     *
     * It can take 3 forms:
     *
     *     __construct(15.07 new Currency("USD"));
     *
     *     __construct(15.07 "USD");
     *
     *     __construct("15.07 USD");
     *
     * @throws InvalidArgumentException If amount is not integer
     */
    public function __construct($amount, $currency = null)
    {
        $paramCount = func_num_args();
        if (1 === $paramCount) {
            $this->createFromString($amount);
        } elseif (2 === $paramCount) {
            if (is_string($currency)) {
                $currency = new Currency($currency);
            }
            $this->currency = $currency;
            $this->setAmount($amount);
        } else {
            throw new InvalidArgumentException("Money constructor expects 1 or 2 parameters. {$paramCount} given.");
        }
    }

    /**
     * It's a kind of magic print.
     *
     * This function doesn't use money_format() and doesn't use regional options.
     * It will always print the money in same format: "10000.00 USD"
     *
     * @return string
     */
    public function __toString()
    {
        return number_format($this->getAmount(), $this->currency->getPrecision(), ".", "")
            . " " . $this->currency->__toString();
    }

    /**
     * Returns the amount of money for the current Currency.
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Returns the Currency.
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Checks the Money are equal to other.
     *
     * @param Money $other
     *
     * @return boolean Return TRUE if the Currency and the amount are same.
     */
    public function isEqualTo(Money $other)
    {
        return $this->currency->isEqualTo($other->getCurrency()) && $other->getAmount() == $this->amount;
    }

    /**
     * Converts money to other currency.
     *
     * @param mixed $currency Currency Object or string (3-chars ISO code)
     *
     * @return Money
     */
    public function exchangeTo($currency)
    {
        if (is_string($currency)) {
            $currency = new Currency($currency);
        }

        return ExchangeManager::getInstance()->exchange($this, $currency);
    }

    /**
     * Sets the amount considering precision of the currency.
     *
     * @param numeric $amount
     */
    protected function setAmount($amount)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException("Amount must be a numeric value");
        }

        $precision = $this->currency->getPrecision();
        $amount = round($amount, $precision);
        if (0 === $precision) {
            $amount = (int) $amount;
        }

        $this->amount = $amount;
    }

    /**
     * This will create a Money object from the given string.
     * Example: "29.99 USD". Can handle negative values.
     *
     * @param string $string
     * @return Money
     *
     * @throws InvalidArgumentException If the string cannot be converted to Money object
     */
    private function createFromString($string)
    {
        $sign = "(?P<sign>[-\+])?";
        $money = "(?P<money>\d*)";
        $separator = "([.,])?";
        $decimals = "(?P<decimal>\d*)?";
        $currencyCode = "\s*(?P<currency>\w*)?";
        $pattern = "~^".$sign.$money.$separator.$decimals.$currencyCode."$~";

        if (!preg_match($pattern, trim($string), $matches)) {
            throw new InvalidArgumentException("The string could not be parsed as money");
        }

        if (!$currency = $matches["currency"]) {
            throw new InvalidArgumentException("No matching currency code in the string");
        }

        $amount = "-" == $matches["sign"] ? "-" : "";
        $amount .= $matches["money"];
        $amount .= isset($matches["decimal"]) ? ".".$matches["decimal"] : "";

        $this->currency = new Currency($currency);
        $this->setAmount($amount);
    }
}
