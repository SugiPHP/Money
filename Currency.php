<?php
/**
 * @package SugiPHP.Money
 */

namespace SugiPHP\Money;

use InvalidArgumentException;

/**
 * Currency class
 */
class Currency
{
    public static $currencyPrecisions;

    public static function getDefaultPrecision($code)
    {
        if (is_null(static::$currencyPrecisions)) {
            static::$currencyPrecisions = include(__DIR__."/precisions.php");
        }

        return isset(static::$currencyPrecisions[$code]) ? static::$currencyPrecisions[$code] : null;
    }

    /**
     * Currency code.
     * SHOULD be 3-char ISO code.
     *
     * @var string
     */
    protected $code;

    /**
     * Precision (currency minor units).
     *
     * @var integer
     */
    protected $precision;

    /**
     * Currency Constructor.
     *
     * @param string $code
     * @param integer $precision
     *
     * @throws InvalidArgumentException If amount is not integer
     * @throws InvalidArgumentException If amount is not integer
     */
    public function __construct($code, $precision = null)
    {
        if (!is_string($code)) {
            throw new InvalidArgumentException("Currency code should be a string");
        }

        if (is_null($precision)) {
            $precision = static::getDefaultPrecision($code);
        }
        if (is_null($precision)) {
            throw new UnknownCurrencyException("Currency precision is not set and there is no default for {$code}");
        };

        $this->code = $code;
        $this->precision = $precision;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getCode();
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set's a precision (minor units).
     *
     * @param integer $precision
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    /**
     * Returns the precision for the currency.
     *
     * @return integer
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * Checks if the Currency is equal to the given one
     *
     * @param Currency $other
     *
     * @return boolean
     */
    public function isEqualTo(Currency $other)
    {
        return $other->getCode() === $this->code;
    }

    /**
     * Checks if the Currency is equal and the precision is same.
     *
     * @param Currency $other
     *
     * @return boolean
     */
    public function isSameTo(Currency $other)
    {
        return $this->isEqualTo($other) && ($other->getPrecision() === $this->precision);
    }
}
