<?php
namespace library\alledia\osmap;

use \UnitTester;
use AspectMock\Test;
use Codeception\Util\Stub;
use \Codeception\Example;

/**
 * @coversDefaultClass \Alledia\OSMap\Router
 */
class RouterCest
{
    protected $container;

    public function _before(UnitTester $I)
    {

    }

    public function _after(UnitTester $I)
    {
        Test::clean();
    }

    /**
     * @covers: ::convertRelativeUriToFullUri
     *
     * @example {"base": "http://example.com/test/", "input":"/images/test/myimage1.png", "expected":"http://example.com/test/images/test/myimage1.png"}
     * @example {"base": "http://example.com/test/", "input":"images/test/myimage1.png", "expected":"http://example.com/test/images/test/myimage1.png"}
     * @example {"base": "http://example.com/test", "input":"/images/test/myimage1.png", "expected":"http://example.com/test/images/test/myimage1.png"}
     * @example {"base": "http://example.com/test", "input":"images/test/myimage1.png", "expected":"http://example.com/test/images/test/myimage1.png"}
     */
    public function tryConvertRelativeUriToFullUrl(UnitTester $I, Example $example)
    {
        Test::double('\Alledia\OSMap\Router', ['getFrontendBase' => $example->offsetGet('base')]);
        $router = new \Alledia\OSMap\Router;

        $I->assertEquals(
            $example->offsetGet('expected'),
            $router->convertRelativeUriToFullUri($example->offsetGet('input'))
        );
    }

    /**
     * @covers: ::getFrontendBase
     *
     * @example ["http://example.com/subfolder/", "http://example.com/subfolder/"]
     * @example ["http://example.com/", "http://example.com/"]
     * @example ["http://example.com/administrator/", "http://example.com/"]
     * @example ["http://example.com/subfolder/administrator/", "http://example.com/subfolder/"]
     */
    public function tryGetFrontendBaseUrl(UnitTester $I, Example $example)
    {
        $base     = $example[0];
        $expected = $example[1];

        Test::double('\JUri', ['base' => $base]);
        $router = new \Alledia\OSMap\Router;

        $I->assertEquals($expected, $router->getFrontendBase());
    }
}
