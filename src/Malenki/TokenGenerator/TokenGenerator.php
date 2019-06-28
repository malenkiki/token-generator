<?php
/*
 * Copyright (c) 2019 Michel Petit <petit.michel@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Malenki\TokenGenerator;

use Malenki\TokenGenerator\Token;
use Malenki\TokenGenerator\Flavor\Flavor;
use Malenki\TokenGenerator\Flavor\RandomFlavor;
use Malenki\TokenGenerator\Flavor\McryptFlavor;
use Malenki\TokenGenerator\Flavor\OpensslFlavor;
use Malenki\TokenGenerator\Formater\HexFormater;
use Malenki\TokenGenerator\Formater\Base64Formater;
use Malenki\TokenGenerator\Formater\AlphaFormater;
use Malenki\TokenGenerator\Formater\NumFormater;
use Malenki\TokenGenerator\Exception\InvalidForcedFlavorException;
use Malenki\TokenGenerator\Exception\FormaterNotDefinedException;
use Malenki\TokenGenerator\Exception\FormaterShortNameAlreadyDefinedException;
use Malenki\TokenGenerator\Exception\FormaterFqcnAlreadyDefinedException;
use Malenki\TokenGenerator\Exception\VoidResultException;
use RangeException;

class TokenGenerator
{
    protected $length;
    protected $flavor;
    protected $formaters;

    public function __construct(int $length = 32, Flavor $flavor = null)
    {
        if ($length <= 0) {
            throw new RangeException('Length must be greater than zero.');
        }

        $this->length  = $length;
        $this->formaters = [
            'hex'    => HexFormater::class,
            'base64' => Base64Formater::class,
            'alpha'  => AlphaFormater::class,
            'num'    => NumFormater::class
        ];

        $this->attachDefault($flavor);
    }

    public function addFormater(string $shortName, string $fqcn)
    {
        if (array_key_exists($shortName, $this->formaters)) {
            throw new FormaterShortNameAlreadyDefinedException();
        }

        if (in_array($fqcn, $this->formaters)) {
            throw new FormaterFqcnAlreadyDefinedException();
        }

        $this->formaters[$shortName] = $fqcn;
    }

    public function run(string $formater = 'hex') : Token
    {
        if (!array_key_exists($formater, $this->formaters)) {
            throw new FormaterNotDefinedException("$formater is not defined.");
        }

        if (!$this->flavor) {
            throw new \RuntimeException(
                'Unknow internal error occurs, no loaded flavor!'
            );
        }

        $fmt = new $this->formaters[$formater]();

        $token = new Token($this->length, $this->flavor, $fmt);

        if (!$token->valid()) {
            throw new VoidResultException(
                'Void token! Please check your custom Flavor!'
            );
        }

        return $token;
    }

    public function __toString() : string
    {
        try {
            return (string) $this->run();
        } catch(\Exception $e) {
            return '';
        }
    }

    protected function attachDefault(Flavor $flavor = null) : void
    {
        if (is_object($flavor)) {
            if ($flavor->valide()) {
                $this->flavor = $flavor;
                return;
            } else {
                throw new InvalidForcedFlavorException(
                    sprintf(
                        'Cannot force flavor %s because it cannot pass valid test.',
                        get_class($flavor)
                    )
                );
            }
        }

        $flavors = [
            RandomFlavor::class,
            OpensslFlavor::class,
            McryptFlavor::class // obsolet for php 7.2+
        ];

        foreach ($flavors as $flavor) {
            $f = new $flavor();

            if (!$f->valide()) {
                unset($f);
                continue;
            }

            $this->flavor = $f;
            break;
        }
    }
}