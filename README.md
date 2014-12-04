Graceful Cache for Laravel 4
================

#### A way to further optimize your caching layer

This package is a simple way to optimize performance by never allowing a flood of requests to hit the database (or other data source).


##### The problem Graceful Caching is trying to solve:
When a cache value expires, requests go directly to the data store. However, if this cache clear happens during a traffic spike, many more requests will fall through than you might like. The effects of this can vary from slightly slower response times, to violating an API's rate limiting, to actually crashing your database.


##### The solution:
Instead of allowing all requests to fallback to the database, we will detect when a cache value is about to expire and instead extend it's life for subsequent requests while fetching the updated value from the source.

##### Example workflow:
* No cached value. Request 1 comes in, we fetch the result from the database, save the value to the cache, and return the results.
* Cached value from Request 1. N-1 requests come in and receive the cached value immediately.
* Cached value from Request 1. Request N comes in and sees the cached value is about to expire, and so we write back that cached value with an extended expiration time. After that, we talk to the database and get the updated value behind the scenes.
* Cached value from Request 1, extended by Request N. While request N is talking to the database, all other requests still see the cached value.
* Cached value from Request 1, extended by Request N. Request N gets a value back from the database, and so updates the cache.
* Cached value from Request N.

As you can see, there is always a valid cached value. Thus, many fewer requests actually hit the database (or api or any other layer you want hidden behind the cache).

### Required setup

In the `require` key of `composer.json` file add the following

    "maherio/graceful-cache": "1.0.*"

Run the Composer update comand

    $ composer update

In your `config/app.php` add `'GracefulCache\ServiceProvider'` to the end of the `$providers` array

```php
'providers' => array(

    'Illuminate\Foundation\Providers\ArtisanServiceProvider',
    'Illuminate\Auth\AuthServiceProvider',
    ...
    'GracefulCache\ServiceProvider',

),
```

