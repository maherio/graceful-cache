<?php namespace GracefulCache\Repository;

use PHPUnit_Framework_TestCase;
use Mockery;

class GracefulCacheRepositoryTest extends PHPUnit_Framework_TestCase {
    protected $cacheStoreMock;

    public function setUp() {
        parent::setUp();

        $this->cacheStoreMock = Mockery::mock('Illuminate\Cache\StoreInterface');
    }

    //////////////////////
    /// helper methods ///
    //////////////////////

    public function testUpdateValue() {
        $repository = new GracefulCacheRepository($this->cacheStoreMock);

        $originalValue = 'thisisatestvalue';
        $cacheLength = 10; //10 mins
        $newValue = $repository->getModifiedValue($originalValue, $cacheLength);

        $this->assertContains('thisisatestvalue', $newValue);
        $this->assertContains(GracefulCacheRepository::$gracefulPrefix, $newValue);
    }

    public function testGetOriginalValue() {
        $repository = new GracefulCacheRepository($this->cacheStoreMock);

        $newValue = serialize('thisisatestvalue') . GracefulCacheRepository::$gracefulPrefix . '1111';
        $originalValue = $repository->getOriginalValue($newValue);

        $this->assertEquals('thisisatestvalue', $originalValue);
    }

    public function testExpirationTime() {
        $repository = new GracefulCacheRepository($this->cacheStoreMock);

        $newValue = 'thisisatestvalue' . GracefulCacheRepository::$gracefulPrefix . '1111';
        $expirationTime = $repository->getExpirationTime($newValue);

        $this->assertEquals('1111', $expirationTime);
    }

    ///////////////////
    /// get and put ///
    ///////////////////

    public function testPutString() {
        $cacheKey = 'abcd';
        $cacheValue = 'thisisatestvalue';
        $cacheLength = 10; //10 mins

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . GracefulCacheRepository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('put')
            ->with($cacheKey, $modifiedValue, $cacheLength)
            ->andReturn(true);

        $repository = new GracefulCacheRepository($this->cacheStoreMock);
        $repository->put($cacheKey, $cacheValue, $cacheLength);
    }

    public function testPutArray() {
        $cacheKey = 'abcd';
        $cacheValue = [
            'testKey' => 'thisisatestvalue'
        ];
        $cacheLength = 10; //10 minds

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . GracefulCacheRepository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('put')
            ->with($cacheKey, $modifiedValue, $cacheLength)
            ->andReturn(true);

        $repository = new GracefulCacheRepository($this->cacheStoreMock);
        $repository->put($cacheKey, $cacheValue, $cacheLength);
    }

    public function testGetCacheMiss() {
        $cacheKey = 'abcd';
        $defaultValue = 'thisisatestvalue';

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn(null);

        $repository = new GracefulCacheRepository($this->cacheStoreMock);

        $value = $repository->get($cacheKey, $defaultValue);
        $this->assertEquals($defaultValue, $value);

        $value = $repository->get($cacheKey);
        $this->assertNull($value);
    }

    public function testGetStringNormal() {
        $cacheKey = 'abcd';
        $cacheValue = 'thisisatestvalue';
        $cacheLength = 10; //10 mins

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . GracefulCacheRepository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn($modifiedValue);

        $repository = new GracefulCacheRepository($this->cacheStoreMock);
        $returnedValue = $repository->get($cacheKey);

        $this->assertEquals($cacheValue, $returnedValue);
    }

    public function testGetStringGraceful() {
        $cacheKey = 'abcd';
        $cacheValue = 'thisisatestvalue';
        $cacheLength = 0.1; //6 seconds

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . GracefulCacheRepository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn($modifiedValue);

        $expirationTime = time() + (GracefulCacheRepository::$extendMinutes * 60);
        $modifiedValue = serialize($cacheValue) . GracefulCacheRepository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('put')
            ->with($cacheKey, $modifiedValue, GracefulCacheRepository::$extendMinutes)
            ->andReturn(true);

        $repository = new GracefulCacheRepository($this->cacheStoreMock);
        $returnedValue = $repository->get($cacheKey);

        $this->assertEquals($cacheValue, $returnedValue);
    }

    public function testGetArrayNormal() {
        $cacheKey = 'abcd';
        $cacheValue = [
            'testKey' => 'thisisatestvalue'
        ];
        $cacheLength = 10; //10 mins

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . GracefulCacheRepository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn($modifiedValue);

        $repository = new GracefulCacheRepository($this->cacheStoreMock);
        $returnedValue = $repository->get($cacheKey);

        $this->assertEquals($cacheValue, $returnedValue);
    }

    public function testGetArrayGraceful() {
        $cacheKey = 'abcd';
        $cacheValue = [
            'testKey' => 'thisisatestvalue'
        ];
        $cacheLength = 0.1; //6 seconds

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . GracefulCacheRepository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn($modifiedValue);

        $expirationTime = time() + (GracefulCacheRepository::$extendMinutes * 60);
        $modifiedValue = serialize($cacheValue) . GracefulCacheRepository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('put')
            ->with($cacheKey, $modifiedValue, GracefulCacheRepository::$extendMinutes)
            ->andReturn(true);

        $repository = new GracefulCacheRepository($this->cacheStoreMock);
        $returnedValue = $repository->get($cacheKey);

        $this->assertEquals($cacheValue, $returnedValue);
    }

}
