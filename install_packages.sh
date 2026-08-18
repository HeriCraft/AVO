#!/bin/sh
composer require laravel/horizon laravel/telescope darkaonline/l5-swagger --ignore-platform-reqs
php artisan horizon:install
php artisan telescope:install
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
php artisan config:publish cors
