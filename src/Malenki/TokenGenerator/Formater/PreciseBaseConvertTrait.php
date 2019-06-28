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

namespace Malenki\TokenGenerator\Formater;

use RuntimeException;

trait PreciseBaseConvertTrait
{
    /**
     * From Clifford’s idea exposed here https://www.php.net/manual/fr/function.base-convert.php#109660
     */
    protected function bcBaseConvert(string $str, int $frombase = 10, int $tobase = 36) : string
    {
        if (!extension_loaded('bcmath')) {
            throw new RuntimeException('BCMath extension is mandatory to use this formater!');
        }

        if (intval($frombase) != 10) {
            $len = strlen($str);
            $q   = 0;

            for ($i = 0; $i < $len; $i++) {
                $r = base_convert($str[$i], $frombase, 10);
                $q = bcadd(bcmul($q, $frombase), $r);
            }
        } else {
            $q = $str;
        }
    
        if (intval($tobase) != 10) {
            $s = '';

            while (bccomp($q, '0', 0) > 0) {
                $r = intval(bcmod($q, $tobase));
                $s = base_convert($r, 10, $tobase) . $s;
                $q = bcdiv($q, $tobase, 0);
            }
        } else {
            $s = $q;
        }
    
        return (string) $s; 
    }
}