<?php
/**
 * @package SugiPHP.Money
 */

namespace SugiPHP\Money;

use InvalidArgumentException;

class ExchangeManager
{
    /**
     * @var ExchangeManager
     */
    public static $exchangeManager;

    /**
     * It will return singleton object.
     *
     * @return ExchangeManager
     */
    public static function getInstance()
    {
        if (is_null(static::$exchangeManager)) {
            static::$exchangeManager = new ExchangeManager();
        }

        return static::$exchangeManager;
    }

    /**
     * @var array [ExchangeRates]
     */
    public $rates = [];

    /**
     * Adding an exchange rate object to the manager.
     *
     * Note that adding an exchange rate with same currencies but with different exchange rate
     * can be done. However only the first rate added will be used.
     *
     * @param ExchangeRate|string $exchageRate
     */
    public function add($exchageRate)
    {
        if (is_string($exchageRate)) {
            $this->rates[] = new ExchangeRate($exchageRate);
        } elseif ($exchageRate instanceof ExchangeRate) {
            $this->rates[] = $exchageRate;
        } else {
            throw new InvalidArgumentException("ExchangeManager::add() method accepts ExchangeRate object or string.");
        }
    }

    /**
     * This method should have several forms:
     *
     *  exchange(Money $source, Currency $target)
     *
     *  exchange(string $source, Currency $target)
     *
     *  exchange(string $source, string $target)
     *
     *  exchange($amount, Currency $source, Currency $target)
     *
     * @return Money or NULL if the conversion could not be done.
     */
    public function exchange()
    {
        $paramCount = func_num_args();
        if (3 === $paramCount) {
            return $this->exchangeAmount(func_get_arg(0), func_get_arg(1), func_get_arg(2));
        }

        if (2 === $paramCount) {
            return $this->exchangeMoney(func_get_arg(0), func_get_arg(1));
        }

        throw new InvalidArgumentException("exchange method expects 2 or 3 parameters. {$paramCount} given.");
    }

    /**
     * Searches for exchange rate for the specified currencies.
     * If there is no exchange rate from the source to target the reverse search is done.
     *
     * @param Currency $source
     * @param Currency $target
     *
     * @return ExchangeRate or NULL if there is no exchange rate
     */
    public function findExRate(Currency $source, Currency $target)
    {
        foreach ($this->rates as $exRate) {
            if ($this->isEqualCurrencies($exRate, $source, $target)) {
                return $exRate;
            }
        }

        // reverse exchange search is done only after the first search.
        foreach ($this->rates as $exRate) {
            if ($this->isEqualCurrencies($exRate, $target, $source)) {
                return $exRate;
            }
        }
    }

    /**
     * Checks source and target currencies provided are equal to the $exRate object's source and target
     *
     * @param ExchangeRate $exRate
     * @param Currency $source
     * @param Currency $target
     *
     * @return boolean
     */
    protected function isEqualCurrencies($exRate, Currency $source, Currency $target)
    {
        return $exRate->getSourceCurrency()->isEqualTo($source) && $exRate->getTargetCurrency()->isEqualTo($target);
    }

    private function exchangeAmount($amount, $source, $target)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException("ExchangeManager::exchange() method expects numeric as first parameter");
        }

        if (is_string($source)) {
            $source = new Currency($source);
        }

        if (is_string($target)) {
            $target = new Currency($target);
        }

        if (!$exRate = $this->findExRate($source, $target)) {
            throw new ExchangeRateException("Exchange rate between $source and $target is unknown");
        }

        return $exRate->exchange(new Money($amount, $source));
    }

    private function exchangeMoney($source, $target)
    {
        if (is_string($source)) {
            $source = new Money($source);
        } elseif (!($source instanceof Money)) {
            throw new InvalidArgumentException("ExchangeManager::exchange() method expects Money as first parameter");
        }

        if (is_string($target)) {
            $target = new Currency($target);
        } elseif (!($target instanceof Currency)) {
            throw new InvalidArgumentException("ExchangeManager::exchange() method expects Currency as second parameter");
        }

        if (!$exRate = $this->findExRate($source->getCurrency(), $target)) {
            throw new ExchangeRateException("Exchange rate between $source and $target is unknown");
        }

        return $exRate->exchange($source);
    }
}
