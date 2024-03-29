# Token Generator

## Why?

To generate random hex string (by default) using given bytes length.

Very basic working example:

```php
use Malenki\TokenGenerator\TokenGenerator;
echo new TokenGenerator(); // yes, __toString available

// a560bd176d5961817050a4c09408b156952d8d53bd7602d28f2844b1faa2995c
```


This is inspired from example given by Akam on [php.net](https://www.php.net/) web site: <https://www.php.net/manual/fr/function.random-bytes.php#118932>

## How to get it?

Using __composer__ is the good way.

```sh
composer require malenki/token-generator
```

## Play with it

### Starting bytes

In constructor, you may give the byte length you want. If not, default is 32.

```php
use Malenki\TokenGenerator\TokenGenerator;

$tg = new TokenGenerator(4);
echo $tg->run(); // the method computing token and returning it

// 791f4dd3 by example
```

But keep in mind it is not the output length, for example, a byte length of 4 gives as a result an output having 8 bytes by default, because default flavors output hex string.

### Flavors

Internally, this generator use 3 differents flavors to build tokens, load in this order:

 - __Random__ flavor defined in `Malenki\TokenGenerator\Flavor\RandomFlavor`
 - __Openssl__ flavor defined in `Malenki\TokenGenerator\Flavor\OpensslFlavor`
 - __Mcrypt__ flavor defined in `Malenki\TokenGenerator\Flavor\McryptFlavor` (Note: not available if you have PHP 7.2+)

By default, it tests if the first, __random__, can run on your system, and run it. If it cannot, it tests the second, __openssl__ and so on.

### Formaters

Formaters allow you to have different output token types. So, by default, the formater used is `hex` (short name) defined in `Malenki\TokenGenerator\Formater\HexFormater` (FQCN).

Formaters are:

 - `hex` to get token as __hexadecimal string__
 - `alpha` to get token composed of __ASCII letters__ from __a__ to __z__
 - `num` to get token composed of __digits__
 - `base64` to get token as a __base 64 encoded string__ from its starting generated bytes

You choose the formater by passing its short name to the `TokenGenerator::run()` method. Examples:

```php
$tg = new TokenGenerator(2);
printf('A token using `alpha` formater: "%s"'.PHP_EOL, $tg->run('alpha'));
// A token using `alpha` formater: "rkv"
printf('A token using `num` formater: "%s"'.PHP_EOL, $tg->run('num'));
// A token using `num` formater: "60286"
printf('A token using `base64` formater: "%s"'.PHP_EOL, $tg->run('base64'));
// A token using `base64` formater: "8Yk="
printf('A token using `hex` formater: "%s"'.PHP_EOL, $tg->run('hex'));
// A token using `hex` formater: "971b"
```

### Token object

Generator does not returned a simple string, it returns an object from class Token, having `__toString()` to get hexadecimal format on string context.

This object contains informations about:

 - __flavor__ used to build it,
 - __formater__ used to format it
 - __bytes length__ choosen to generate it,
 - __original bytes string__ used to get it,
 - its validity too, if a custom flavor used to create it has some bugs (often void string)

 Example:

 ```php
 $tg = new TokenGenerator(2);
$token = $tg->run();

echo $token->getOriginalBytesLength() . PHP_EOL;
echo $token->getUsedFlavor() . PHP_EOL;
echo $token->getUsedFormater() . PHP_EOL;
echo $token->get() . PHP_EOL;
echo ($token->valid() ?  'yes' : 'no') . PHP_EOL;
echo $token . PHP_EOL;

// 2
// Malenki\TokenGenerator\Flavor\RandomFlavor
// Malenki\TokenGenerator\Formater\HexFormater
// 4b31
// yes
// 4b31
 ```



## Add your own flavor(s)

OK, you like this generator, but you want token having some specific building rules.

It is possible to create your own flavor and use it.

__First__ create your flavor class inherits from `Malenki\TokenGenerator\Flavor\Flavor` abstract class.

This abstract class has this methods you must implement:

```php
abstract class Flavor
{
    abstract public function valide() : bool; // can load on the system?
    abstract public function generate(int $length) : string; // generate the token
}
```

Lets define your class FQCN as `Some\Namespace\YourFlavor`.

__Second__, declare it to generator while instanciate it, so, it knows its existance and can use it.

```php
use Malenki\TokenGenerator\TokenGenerator;
use Some\Namespace\YourFlavor;

$tg = new TokenGenerator(32, new YourFlavor);
echo $tg->run();
```

And voilà! You get your first token using your own flavor!


## Add your own formatters

As easy as creating custom Flavor.

Just implements the abstract class `Malenki\TokenGenerator\Formater\Formater`:

```php
abstract class Formater
{
    abstract public function format(string $original) : string;
}
```

For example, your formater may include a date string before a hex part:

```php
use Malenki\TokenGenerator\Formater\Formater;

class CustomFormater extends Formater
{
    public function format(string $original) : string
    {
        $dt = new \DateTime();
        $dtStr = $dt->format('Y-m-d-');
        $preToken = bin2hex($original);

        return $dtStr.$preToken;
    }
}
```

Now, declare it to the generator:

```php
$tg = new TokenGenerator(2);
$tg->addFormater('custom', CustomFormater::class);
echo $tg->run('custom'). PHP_EOL;

// 2019-06-28-ea15
```

Simple, isn’t it?

