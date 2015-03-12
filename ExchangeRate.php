<?php
/**
 * @package SugiPHP.Money
 */

namespace SugiPHP\Money;

use InvalidArgumentException;

class ExchangeRate
{
    /**
     * Source Currency. The currency from which the exchange will be done.
     *
     * @var Currency
     */
    protected $sourceCurrency;

    /**
     * Target Currency. The currency to which the exchange will be done.
     *
     * @var Currency
     */
    protected $targetCurrency;

    /**
     * Conversion rate.
     *
     * @var numeric
     */
    protected $rate;

    /**
     * Exchange Rate Object Constructor.
     *
     *     __construct(Currency $source, Currency $target, numeric $rate);
     *
     *     __construct($string);
     *
     * @throws InvalidArgumentException If conversion ratio is not numeric
     * @throws InvalidArgumentException If conversion ratio is 0
     */
    public function __construct($source, $target = null, $rate = null)
    {
        $paramCount = func_num_args();
        if (1 === $paramCount) {
            $this->createFromString($source);
        } elseif ($paramCount === 3) {
            $this->setRate($rate);
            if (is_string($source)) {
                $source = new Currency($source);
            }
            if (is_string($target)) {
                $target = new Currency($target);
            }
            $this->sourceCurrency = $source;
            $this->targetCurrency = $target;
        } else {
            throw new InvalidArgumentException("ExchangeRate constructor expects 1 or 3 params. {$paramCount} given");
        }
    }

    /**
     * Returns string formated the same way fromString() static method expects:
     * "BGN/EUR 0.511280836"
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSourceCurrency()."/".$this->getTargetCurrency()." ".$this->GetRate();
    }

    /**
     * Returns the exchange rate.
     *
     * @return numeric
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Returns the source currency.
     *
     * @return Currency
     */
    public function getSourceCurrency()
    {
        return $this->sourceCurrency;
    }

    /**
     * Returns the destination currency.
     *
     * @return Currency
     */
    public function getTargetCurrency()
    {
        return $this->targetCurrency;
    }

    /**
     * Converts amount from source to target currency.
     * If $money parameter is in target currency reverse conversions will be made and
     * money of source currency will be returned.
     *
     * @param numeric|Money $money
     *
     * @return Money
     *
     * @throws InvalidArgumentException
     */
    public function exchange($money)
    {
        if (is_numeric($money)) {
            return new Money($money * $this->rate, $this->targetCurrency);
        }

        if (!($money instanceof Money)) {
            throw new InvalidArgumentException("exchange method accepts only numeric or Money objects.");
        }

        $currency = $money->getCurrency();
        if ($currency->isEqualTo($this->sourceCurrency)) {
            return new Money($money->getAmount() * $this->rate, $this->targetCurrency);
        }

        if ($currency->isEqualTo($this->targetCurrency)) {
            return new Money($money->getAmount() / $this->rate, $this->sourceCurrency);
        }

        throw new InvalidArgumentException(
            sprintf(
                "exchange method expected %s or %s, but %s received",
                $this->sourceCurrency,
                $this->targetCurrency,
                $currency
            )
        );
    }

    public function invert()
    {
        $cur = $this->sourceCurrency;
        $this->sourceCurrency = $this->targetCurrency;
        $this->targetCurrency = $cur;
        $this->setRate(1 / $this->rate);
    }

    /**
     * Sets exchange rate for the current currencies.
     *
     * @param numeric $rate Conversion rate.
     *
     * @throws InvalidArgumentException If conversion ratio is not numeric
     */
    protected function setRate($rate)
    {
        if (!is_numeric($rate)) {
            throw new InvalidArgumentException("Conversion rate must be a numeric value");
        }
        if (empty($rate)) {
            throw new InvalidArgumentException("Conversion rate must be a non zero value");
        }

        $this->rate = $rate;
    }

    /**
     * This will create an Exchange Rate object from the given string.
     * Example: "BGN/USD 1.4567".
     *
     * @param string $string
     *
     * @return ExchangeRate
     *
     * @throws InvalidArgumentException If the string cannot be converted to Money object
     */
    protected function createFromString($string)
    {
        $currency1 = "(?P<currency1>[A-Z]{3,3})";
        $currency2 = "(?P<currency2>[A-Z]{3,3})";
        $rate = "(?P<rate>[0-9]*\.?[0-9]+)";
        $pattern = "~^".$currency1."/".$currency2."\s+".$rate."$~";
        if (!preg_match($pattern, $string, $matches)) {
            throw new InvalidArgumentException(
                "Can't create currency pair from ISO string {$string}, format of string is invalid"
            );
        };

        $this->setRate($matches["rate"]);
        $this->sourceCurrency = new Currency($matches["currency1"]);
        $this->targetCurrency = new Currency($matches["currency2"]);
    }
}
