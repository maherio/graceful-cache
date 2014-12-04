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

