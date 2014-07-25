<?php
namespace Ma27\Kernel\Tests\Value;
use Ma27\Kernel\Value\KernelKeys;

/**
 * @package Ma27\Kernel\Tests\Value
 * @coversDefaultClass \Ma27\Kernel\Value\KernelKeys
 */
class KernelKeysTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::createList
     * @api
     */
    public function testKeyList()
    {
        $paramKeyList = KernelKeys::createList();
        $ref          = new \ReflectionClass('\Ma27\Kernel\Value\KernelKeys');
        $actualConsts = $ref->getConstants();

        $this->assertCount(count($actualConsts), $paramKeyList);
        $c = count($paramKeyList);
        for ($i = 0; $i < $c; $i++) {
            $key = $paramKeyList[$i];

            $this->assertContains($key, $actualConsts);
            $constName = array_search($key, $actualConsts);
            $this->assertTrue(is_string($constName));

            $this->assertTrue(defined(get_class(new KernelKeys()) . '::' . $constName));
        }
    }
}
 