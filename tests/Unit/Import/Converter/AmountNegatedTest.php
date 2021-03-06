<?php
/**
 * AmountNegatedTest.php
 * Copyright (c) 2017 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Tests\Unit\Import\Converter;

use FireflyIII\Import\Converter\AmountNegated;
use Tests\TestCase;

/**
 * Class AmountNegatedTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AmountNegatedTest extends TestCase
{
    /**
     * @covers \FireflyIII\Import\Converter\AmountNegated
     */
    public function testConvert(): void
    {
        $values = [

            '0'                       => '0.000000000000',
            '0.0'                     => '0.000000000000',
            '0.1'                     => '-0.100000000000',
            '.2'                      => '-0.200000000000',
            '0.01'                    => '-0.010000000000',
            '1'                       => '-1.000000000000',
            '1.0'                     => '-1.000000000000',
            '1.1'                     => '-1.100000000000',
            '1.12'                    => '-1.120000000000',
            '1.10'                    => '-1.100000000000',
            '12'                      => '-12.000000000000',
            '12.3'                    => '-12.300000000000',
            '12.34'                   => '-12.340000000000',
            '123'                     => '-123.000000000000',
            '123.4'                   => '-123.400000000000',
            '123.45'                  => '-123.450000000000',
            '1234'                    => '-1234.000000000000',
            '1234.5'                  => '-1234.500000000000',
            '1234.56'                 => '-1234.560000000000',
            '1 234'                   => '-1234.000000000000',
            '1 234.5'                 => '-1234.500000000000',
            '1 234.56'                => '-1234.560000000000',
            '1,234'                   => '-1234.000000000000',
            '1,234.5'                 => '-1234.500000000000',
            '1,234.56'                => '-1234.560000000000',
            '123,456,789'             => '-123456789.000000000000',
            '0,0'                     => '0.000000000000',
            '0,1'                     => '-0.100000000000',
            ',2'                      => '-0.200000000000',
            '0,01'                    => '-0.010000000000',
            '1,0'                     => '-1.000000000000',
            '1,1'                     => '-1.100000000000',
            '1,12'                    => '-1.120000000000',
            '1,10'                    => '-1.100000000000',
            '12,3'                    => '-12.300000000000',
            '12,34'                   => '-12.340000000000',
            '123,4'                   => '-123.400000000000',
            '123,45'                  => '-123.450000000000',
            '1234,5'                  => '-1234.500000000000',
            '1234,56'                 => '-1234.560000000000',
            '1 234,5'                 => '-1234.500000000000',
            '1 234,56'                => '-1234.560000000000',
            '1.234'                   => '-1.234000000000', // will no longer match as 1234, but as 1.234
            '1.234,5'                 => '-1234.500000000000',
            '1.234,56'                => '-1234.560000000000',

            // many decimals
            '2.00'                    => '-2.000000000000',
            '3.000'                   => '-3.000000000000',
            '4.0000'                  => '-4.000000000000',
            '5.000'                   => '-5.000000000000',
            '6.0000'                  => '-6.000000000000',
            '7.200'                   => '-7.200000000000',
            '8.2000'                  => '-8.200000000000',
            '9.330'                   => '-9.330000000000',
            '10.3300'                 => '-10.330000000000',
            '11.444'                  => '-11.444000000000',
            '12.4440'                 => '-12.444000000000',
            '13.5555'                 => '-13.555500000000',
            '14.45678'                => '-14.456780000000',
            '15.456789'               => '-15.456789000000',
            '16.4567898'              => '-16.456789800000',
            '17.34567898'             => '-17.345678980000',
            '18.134567898'            => '-18.134567898000',
            '19.1634567898'           => '-19.163456789800',
            '20.16334567898'          => '-20.163345678980',
            '21.16364567898'          => '-21.163645678980',
            '22.163644567898'         => '-22.163644567898',
            '22.1636445670069'        => '-22.163644567006',

            // many decimals, mixed, large numbers
            '63522.00'                => '-63522.000000000000',
            '63523.000'               => '-63523.000000000000',
            '63524.0000'              => '-63524.000000000000',
            '63525.000'               => '-63525.000000000000',
            '63526.0000'              => '-63526.000000000000',
            '63527.200'               => '-63527.200000000000',
            '63528.2000'              => '-63528.200000000000',
            '63529.330'               => '-63529.330000000000',
            '635210.3300'             => '-635210.330000000000',
            '635211.444'              => '-635211.444000000000',
            '635212.4440'             => '-635212.444000000000',
            '635213.5555'             => '-635213.555500000000',
            '635214.45678'            => '-635214.456780000000',
            '635215.456789'           => '-635215.456789000000',
            '635216.4567898'          => '-635216.456789800000',
            '635217.34567898'         => '-635217.345678980000',
            '635218.134567898'        => '-635218.134567898000',
            '635219.1634567898'       => '-635219.163456789800',
            '635220.16334567898'      => '-635220.163345678980',
            '635221.16364567898'      => '-635221.163645678980',
            '635222.163644567898'     => '-635222.163644567898',

            // many decimals, mixed, also mixed thousands separators
            '63 522.00'               => '-63522.000000000000',
            '63 523.000'              => '-63523.000000000000',
            '63,524.0000'             => '-63524.000000000000',
            '63 525.000'              => '-63525.000000000000',
            '63,526.0000'             => '-63526.000000000000',
            '63 527.200'              => '-63527.200000000000',
            '63 528.2000'             => '-63528.200000000000',
            '63 529.330'              => '-63529.330000000000',
            '63,5210.3300'            => '-635210.330000000000',
            '63,5211.444'             => '-635211.444000000000',
            '63 5212.4440'            => '-635212.444000000000',
            '163 5219.1634567898'     => '-1635219.163456789800',
            '444 163 5219.1634567898' => '-4441635219.163456789800',
            '-0.34918323'             => '0.349183230000',
            '0.208'                   => '-0.208000000000',
            '-0.15'                   => '0.150000000000',
            '-0.03881677'             => '0.038816770000',
            '0.33'                    => '-0.330000000000',
            '-0.1'                    => '0.100000000000',
            '0.01124'                 => '-0.011240000000',
            '-0.01124'                => '0.011240000000',
            '0.115'                   => '-0.115000000000',
            '-0.115'                  => '0.115000000000',
            '1.33'                    => '-1.330000000000',
            '$1.23'                   => '-1.230000000000',
            '€1,44'                   => '-1.440000000000',
            '(33.52)'                 => '33.520000000000',
            '€(63.12)'                => '63.120000000000',
            '($182.77)'               => '182.770000000000',

            // double minus because why the hell not
            '--0.03881677'            => '-0.038816770000',
            '--0.33'                  => '-0.330000000000',
            '--$1.23'                 => '-1.230000000000',
            '--63 5212.4440'          => '-635212.444000000000',
            '--,2'                    => '-0.200000000000',

        ];
        foreach ($values as $value => $expected) {
            $converter = new AmountNegated;
            $result    = $converter->convert($value);
            $this->assertEquals($expected, $result, sprintf('The original value was %s, expected was %s', $value, $expected));
        }
    }

    /**
     * @covers \FireflyIII\Import\Converter\AmountNegated
     */
    public function testConvertNull(): void
    {
        $converter = new AmountNegated;
        $result    = $converter->convert(null);
        $this->assertEquals('0.000000000000', $result);
    }
}
