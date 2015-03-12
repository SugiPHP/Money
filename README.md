# Money #


The purpose of this class (classes) is to ease work with money in PHP. You will be able to do things like this:

```php
<?php
$exManager = ExchangeManager::getInstance();
$exManager->add("BGN/EUR 0.5113");

echo $exManager->exchange("100 BGN", "EUR"); // "51.13 EUR"

$someEur = new Money(51.13, "EUR");

echo $someEur->exchangeTo("BGN"); // "100.00 BGN"
```

So, lets begin with:

## Currency Class ##


There are several predefined currencies which are actually all "real" currencies defined as
[ISO standard](http://www.currency-iso.org/en/home/tables/table-a1.html). To work with those
currencies you should only pass their 3-chars ISO 4217 code in the constructor.

```php
<?php
$usd = new Currency("USD");
```

Of course you can define your own currencies or override default ones by providing the code and
the precision. You can thing of the precision like how many minor units (cents) are in the
currency and will be covered more in Money class.

```php
<?php
$bitcoin = new Currency("BTC", 10);
$fulldollar = new Currency("USD", 0); // default USD are with precision of 2 (the cents in 1 dollar)

// following will raise an UnknownCurrencyException, because LTC is not
// in the ISO standard and Currency class doesn't know the precision of the LTC
$litecoin = new Currency("LTC");
// so instead you MUST specify the precision. It's up to you what precision your application will use
$litecoin = new Currency("LTC", 8);

```

There is no much more you can do with the Currency class by itself, so let's move to the

## Money Class ##

You can create Money in several ways:

```php
<?php
$price = new Money(99.99, new Currency("USD"));
$price = new Money(99.99, "USD");
$price = new Money("99.99 USD");
```

No difference between above. But if you want to create your own currency or to override some of
the existing you MUST use first form or instantiation will fail raising an UnknownCurrencyException

```php
<?php
$bitcoins = new Money(0.000123, new Currency("BTC", 10));
$bitcoins->getAmount(); // 0.000123
echo $bitcoins; // will output 0.0001230000 BTC
```

#### Precision ####

Now about the precision defined in the Currency:

```php
<?php
$price = new Money(123.45678901, new Currency("USD"));
$price->getAmount(); // 123.46
echo $price; // will output "123.46 USD"
```

And that's because of the precision defined in the default USD currency. Also note that the second
decimal digit is not 5 but 6, because of the rounding.

But what if you want more or less decimal digits. You can do it this way:


```php
<?php
$usd = new Currency("USD", 0);
$price = new Money(123.45678901, $usd);
$price->getAmount(); // 123
echo $price; // will output "123 USD"

$price = new Money(123.45678901, new Currency("USD", 5));
$price->getAmount() // 123.45679
echo $price; // will output "123.45679 USD"
```

#### Comparison ####

Currently there is only one method to compare money:

```php
<?php
$usd = new Money("100 USD");
$usd->isEqualTo(new Money("100 BGN")); // FALSE
$usd->isEqualTo(new Money("99.99 USD")); // FALSE
$usd->isEqualTo(new Money("100.00 USD")); // TRUE
$usd->isEqualTo(new Money("100.001 USD")); // also returns TRUE due to the precision of the currency
```

#### Conversion (Exchange) ####

You can exchange money from one currency to another using `Money::exhange()` method. Be patient and
read further to understand how. (Well I have to finish readme first)... @todo

## ExchangeRate Class ##

To be able to make conversions from one currency to another you have to specify the exchange rate for
those currencies. To do it you can:

```php
<?php
$rate = new ExchangeRate(new Currency("EUR"), new Currency("USD"), 1.25);
// or
$rate = new ExchangeRate("EUR", "USD", 1.25);
// or even
$rate = new ExchangeRate("EUR/USD 1.25");
```

Now you can exhcange money from one currency to another:

```php
<?php
$eurXusd = new ExchangeRate("EUR/USD 1.25");
$usd = $eurXusd->exchange(100); // returns Money object
echo $usd; // 125.00 USD
// you can pass Money object
$hundredEur = new Money("100 EUR");
$usd = $eurXusd->exchange($hundredEur);
// back conversion
echo $eurXusd->exchange($usd); // 100 EUR
```

`invert()` method is used to swap currencies and inverting the rate. The new rate can be retreived with
`getRate()` method, the currencies with `getSourceCurrency()` and `getTargetCurrency()`


```php
<?php
$eurXusd = new ExchangeRate("EUR/USD 1.25");
echo $eurXusd->getSourceCurrency(); // EUR
$eurXusd->invert();
echo $eurXusd; // USD/EUR 0.8
echo $eurXusd->getSourceCurrency(); // USD
```
