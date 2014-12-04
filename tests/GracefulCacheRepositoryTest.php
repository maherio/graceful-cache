<?php namespace GracefulCache;

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
        $repository = new Repository($this->cacheStoreMock);

        $originalValue = 'thisisatestvalue';
        $cacheLength = 10; //10 mins
        $newValue = $repository->getModifiedValue($originalValue, $cacheLength);

        $this->assertContains('thisisatestvalue', $newValue);
        $this->assertContains(Repository::$gracefulPrefix, $newValue);
    }

    public function testGetOriginalValue() {
        $repository = new Repository($this->cacheStoreMock);

        $newValue = serialize('thisisatestvalue') . Repository::$gracefulPrefix . '1111';
        $originalValue = $repository->getOriginalValue($newValue);

        $this->assertEquals('thisisatestvalue', $originalValue);
    }

    public function testGetOriginalValueFromOldCacheValue() {
        $repository = new Repository($this->cacheStoreMock);

        $newValue = 'thisisatestvalue';
        $originalValue = $repository->getOriginalValue($newValue);

        $this->assertEquals('thisisatestvalue', $originalValue);
    }

    public function testExpirationTime() {
        $repository = new Repository($this->cacheStoreMock);

        $newValue = 'thisisatestvalue' . Repository::$gracefulPrefix . '1111';
        $expirationTime = $repository->getExpirationTime($newValue);

        $this->assertEquals('1111', $expirationTime);
    }

    public function testExpirationTimeFromOldCacheValue() {
        $repository = new Repository($this->cacheStoreMock);

        $newValue = 'thisisatestvalue';
        $expirationTime = $repository->getExpirationTime($newValue);

        $this->assertEquals(0, $expirationTime);
    }

    ///////////////////
    /// get and put ///
    ///////////////////

    public function testPutString() {
        $cacheKey = 'abcd';
        $cacheValue = 'thisisatestvalue';
        $cacheLength = 10; //10 mins

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . Repository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('put')
            ->with($cacheKey, $modifiedValue, $cacheLength)
            ->andReturn(true);

        $repository = new Repository($this->cacheStoreMock);
        $repository->put($cacheKey, $cacheValue, $cacheLength);
    }

    public function testPutArray() {
        $cacheKey = 'abcd';
        $cacheValue = [
            'testKey' => 'thisisatestvalue'
        ];
        $cacheLength = 10; //10 minds

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . Repository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('put')
            ->with($cacheKey, $modifiedValue, $cacheLength)
            ->andReturn(true);

        $repository = new Repository($this->cacheStoreMock);
        $repository->put($cacheKey, $cacheValue, $cacheLength);
    }

    public function testGetCacheMiss() {
        $cacheKey = 'abcd';
        $defaultValue = 'thisisatestvalue';

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn(null);

        $repository = new Repository($this->cacheStoreMock);

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
        $modifiedValue = serialize($cacheValue) . Repository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn($modifiedValue);

        $repository = new Repository($this->cacheStoreMock);
        $returnedValue = $repository->get($cacheKey);

        $this->assertEquals($cacheValue, $returnedValue);
    }

    public function testGetStringOldValue() {
        $cacheKey = 'abcd';
        $cacheValue = 'thisisatestvalue';
        $cacheLength = 10; //10 mins
        $expirationTime = time() + (Repository::$extendMinutes * 60);
        $modifiedValue = serialize($cacheValue) . Repository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn($cacheValue);
        $this->cacheStoreMock->shouldReceive('put')
            ->with($cacheKey, $modifiedValue, Repository::$extendMinutes)
            ->andReturn(true);

        $repository = new Repository($this->cacheStoreMock);
        $returnedValue = $repository->get($cacheKey);

        //old values should return null from the cache, so they're updated with the new one
        $this->assertNull($returnedValue);
    }

    public function testGetStringGraceful() {
        $cacheKey = 'abcd';
        $cacheValue = 'thisisatestvalue';
        $cacheLength = 0.1; //6 seconds

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . Repository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn($modifiedValue);

        $expirationTime = time() + (Repository::$extendMinutes * 60);
        $modifiedValue = serialize($cacheValue) . Repository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('put')
            ->with($cacheKey, $modifiedValue, Repository::$extendMinutes)
            ->andReturn(true);

        $repository = new Repository($this->cacheStoreMock);
        $returnedValue = $repository->get($cacheKey);

        //it should be null for this request so the cache refreshes
        $this->assertNull($returnedValue);
    }

    public function testGetArrayNormal() {
        $cacheKey = 'abcd';
        $cacheValue = [
            'testKey' => 'thisisatestvalue'
        ];
        $cacheLength = 10; //10 mins

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . Repository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn($modifiedValue);

        $repository = new Repository($this->cacheStoreMock);
        $returnedValue = $repository->get($cacheKey);

        $this->assertEquals($cacheValue, $returnedValue);
    }

    public function testGetArrayOldValue() {
        $cacheKey = 'abcd';
        $cacheValue = [
            'testKey' => 'thisisatestvalue'
        ];
        $cacheLength = 10; //10 mins
        $expirationTime = time() + (Repository::$extendMinutes * 60);
        $modifiedValue = serialize($cacheValue) . Repository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn($cacheValue);
        $this->cacheStoreMock->shouldReceive('put')
            ->with($cacheKey, $modifiedValue, Repository::$extendMinutes)
            ->andReturn(true);

        $repository = new Repository($this->cacheStoreMock);
        $returnedValue = $repository->get($cacheKey);

        //old values should return null so the cache is updated
        $this->assertNull($returnedValue);
    }

    public function testGetArrayGraceful() {
        $cacheKey = 'abcd';
        $cacheValue = [
            'testKey' => 'thisisatestvalue'
        ];
        $cacheLength = 0.1; //6 seconds

        $expirationTime = time() + ($cacheLength * 60);
        $modifiedValue = serialize($cacheValue) . Repository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('get')
            ->with($cacheKey)
            ->andReturn($modifiedValue);

        $expirationTime = time() + (Repository::$extendMinutes * 60);
        $modifiedValue = serialize($cacheValue) . Repository::$gracefulPrefix . $expirationTime;

        $this->cacheStoreMock->shouldReceive('put')
            ->with($cacheKey, $modifiedValue, Repository::$extendMinutes)
            ->andReturn(true);

        $repository = new Repository($this->cacheStoreMock);
        $returnedValue = $repository->get($cacheKey);

        $this->assertNull($returnedValue);
    }

}
