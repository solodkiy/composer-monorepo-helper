<?php


namespace Solodkiy\ComposerMonorepoHelper;


class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $input
     * @param $expected
     * @dataProvider getNextMicroVersionsProvider
     */
    public function testGetNextMicroVersion($input, $expected)
    {
        $this->assertEquals($expected, Utils::getNextMicroVersion($input));
    }

    /**
     * @return array
     */
    public function getNextMicroVersionsProvider()
    {
        return [
            ['1.2.3.4', '1.2.3.5'],
            ['1', '1.0.0.1'],
            ['1.2', '1.2.0.1'],
            ['1.2.3.0', '1.2.3.1'],
            ['10.10.10.10', '10.10.10.11'],
        ];
    }

    /**
     * @param $input
     * @param $expected
     * @dataProvider getComposerJsonChecksumProvider
     */
    public function testGetComposerJsonChecksum($input, $expected)
    {
        $this->assertEquals($expected, Utils::getComposerJsonChecksum($input));
    }

    /**
     * @return array
     */
    public function getComposerJsonChecksumProvider()
    {
        return [
            ['{ "name": "test-pack", "version": "1.2.3.4", "extra": { } }', '89c0b416a2668bfb41041138bf48bc8d'],
            ['{ "name": "test-pack", "version": "1.2.3.5", "extra": { } }', '89c0b416a2668bfb41041138bf48bc8d'],
            ['{ "name": "test-pack", "version": "2", "extra": { "checksum": "test" } }', '89c0b416a2668bfb41041138bf48bc8d'],
            ['{ "name": "test-pack", "version": "3" }', '89c0b416a2668bfb41041138bf48bc8d'],

            ['{ "name": "test-pack2", "version": "3" }', '5b90d3720c7747c2f315c4f9797f67db'],
        ];
    }
}

