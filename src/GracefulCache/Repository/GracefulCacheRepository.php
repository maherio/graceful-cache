<?php namespace GracefulCache\Repository;

use Illuminate\Cache\Repository;

class GracefulCacheRepository extends Repository {

    /**
     * The prefix of the string to be appended to each cache value. This is an added
     * precaution to ensure the correct value will be stripped from the end result
     * @var string
     */
    public static $gracefulPrefix = '?GracefulCacheExpiration=';

    /**
     * The length of time (seconds) we extend each about-to-expire cache key
     * @var integer
     */
    public static $extendMinutes = 1;

    /**
     * The threshold, in seconds, for how much longer a key has until expiration
     * before it is re-fetched.
     * @var integer
     */
    public static $expireThreshold = 60;

    /**
     * Adds a timing suffix to the value, in order to show when the cached value
     * would expire
     * @param  string $value   The original value the client wishes to cache
     * @param  int    $minutes The length of time this is going to be cached for
     * @return string          The modified value with expiration time appended
     */
    public function getModifiedValue($value, $minutes) {
        $serializedValue = serialize($value);
        $expirationTime = time() + ($minutes * 60);
        return $serializedValue . static::$gracefulPrefix . $expirationTime;
    }

    /**
     * Gets the original cache value, without the modified expiration time
     * @param  string $value The full value stored in the cache
     * @return string        The unmodified cached value
     */
    public function getOriginalValue($value) {
        $suffixIndex = strpos($value, static::$gracefulPrefix);
        $suffixLength = strlen(substr($value, $suffixIndex));
        $originalSerializedValue = substr($value, 0, 0 - $suffixLength);
        return unserialize($originalSerializedValue);
    }

    /**
     * Gets the expiration time from the given modified cache value
     * @param  string $value The modified cache value, with expiration time appended
     * @return int           The timestamp of when this value will expire
     */
    public function getExpirationTime($value) {
        $expirationIndex = strpos($value, static::$gracefulPrefix);
        return (int) substr($value, $expirationIndex + strlen(static::$gracefulPrefix));
    }

    /**
     * Retrieve an item from the cache by key. If the item is about to expire soon,
     * extend the existing cache entry (for other requests) before returning the item
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null) {
        $value = $this->store->get($key);

        if(!is_null($value)) {
            //get the original value and the expiration time
            $originalValue = $this->getOriginalValue($value);
            $expirationTime = $this->getExpirationTime($value) - $this->expireThreshold;

            //Check if this cache entry is going to expire soon (within {threshold} seconds)
            if(time() > $expirationTime) {
                //to solve this, the value of the existing cache key will be extended
                //while the new value is fetched
                $this->put($key, $originalValue, $this->extendMinutes);
            }

            //make sure to return the original value and not the one with the expiration time in it
            $value = $originalValue;
        }

        return ! is_null($value) ? $value : value($default);
    }

    /**
     * Store an item in the cache. Add the amount of time to cache it for
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  \DateTime|int  $minutes
     * @return void
     */
    public function put($key, $value, $minutes) {
        $minutes = $this->getMinutes($minutes);

        //get the modified value with the expiration time
        $modifiedValue = $this->getModifiedValue($value, $minutes);

        $this->store->put($key, $modifiedValue, $minutes);
    }

}
