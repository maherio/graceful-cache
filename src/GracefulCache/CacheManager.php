<?php namespace GracefulCache;

use Illuminate\Cache\CacheManager as BaseCacheManager,
    Illuminate\Cache\StoreInterface;

class CacheManager extends BaseCacheManager
{
    /**
     * Create a new cache repository with the given implementation.
     *
     * @param  \Illuminate\Cache\StoreInterface  $store
     * @return \GracefulCache\Repository
     */
    protected function repository(StoreInterface $store)
    {
        return new Repository($store);
    }

    /**
     * Create a new cache repository with the given implementation.
     *
     * @param  \Illuminate\Cache\StoreInterface  $store
     * @return \GracefulCache\Repository
     */
    public function buildRepository(StoreInterface $store)
    {
        return $this->repository($store);
    }
}
