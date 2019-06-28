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

use Malenki\TokenGenerator\Flavor\Flavor;
use Malenki\TokenGenerator\Formater\Formater;

class Token
{
    protected $length;
    protected $original;
    protected $generated;
    protected $flavor;
    protected $formater;

    public function __construct(int $length, Flavor $flavor, Formater $formater)
    {
        $this->length    = $length;
        $this->original  = $flavor->generate($length);
        $this->generated = $formater->format($this->original);
        $this->flavor    = get_class($flavor);
        $this->formater  = get_class($formater);
    }

    public function getOriginalBytesString() : string
    {
        return $this->original;
    }

    public function getOriginalBytesLength() : int
    {
        return $this->length;
    }

    public function getUsedFlavor() : string
    {
        return $this->flavor;
    }

    public function getUsedFormater() : string
    {
        return $this->formater;
    }

    public function get() : string
    {
        return $this->generated;
    }

    public function valid() : bool
    {
        return !empty($this->generated);
    }

    public function __toString()
    {
        return $this->get();
    }
}