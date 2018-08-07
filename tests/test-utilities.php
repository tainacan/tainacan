<?php

namespace Tainacan\Tests;

/**
 * Class TestUtilities
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class TestUtilities extends TAINACAN_UnitTestCase {


    function test_initials() {
        

        $string = 'Roberto Carlos';

        $this->assertEquals('RC', tainacan_get_initials($string));

        $string = 'Ronaldo';

        $this->assertEquals('RO', tainacan_get_initials($string));
        $this->assertEquals('R', tainacan_get_initials($string, true));

        $string = 'Marilia Mendonça das Neves Costa Silva Fonseca';

        $this->assertEquals('MF', tainacan_get_initials($string));
        $this->assertEquals('M', tainacan_get_initials($string, true));
        
        $string = 'Machado de Assis';

        $this->assertEquals('MA', tainacan_get_initials($string));

        $string = 'b';

        $this->assertEquals('B', tainacan_get_initials($string));

        $string = '';

        $this->assertEquals('', tainacan_get_initials($string));




	}
}