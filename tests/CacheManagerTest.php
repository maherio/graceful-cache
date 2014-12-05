<?php namespace GracefulCache;

use PHPUnit_Framework_TestCase;
use Mockery;

class CacheManagerTest extends PHPUnit_Framework_TestCase
{
    protected $appMock;
    protected $cacheStoreMock;

    public function setUp()
    {
        parent::setUp();
        $this->appMock = Mockery::mock('Illuminate\Contracts\Foundation\Application');
        $this->cacheStoreMock = Mockery::mock('Illuminate\Cache\StoreInterface');
    }

    public function testBuildingRepository()
    {
        $manager = new CacheManager($this->appMock);
        $repository = $manager->buildRepository($this->cacheStoreMock);

        $this->assertTrue(is_subclass_of($repository, 'Illuminate\Cache\Repository'));
        $this->assertEquals(get_class($repository), 'GracefulCache\Repository');
    }

}
