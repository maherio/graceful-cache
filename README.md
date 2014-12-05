Graceful Cache for Laravel 4
================

Tony should write a very salesy description of what this package is and why it is important.

### Required setup

In the `require` key of `composer.json` file add the following

    "maherio/graceful-cache": "dev-master"

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

### Cache driver extensions

```php
Cache::extend('elasticache', function() {
    $servers = Config::get('cache.memcached');
    $elasticache = new Illuminate\Cache\ElasticacheConnector();
    $memcached = $elasticache->connect($servers);
    return Cache::getRepository(new Illuminate\Cache\MemcachedStore($memcached, Config::get('cache.prefix')));
});
```

*The `getRepository` method is provided by the GracefulCache package and is not supported by the Laravel framework.*
