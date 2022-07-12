# MSVC API PHP

## About
This package makes wrapping to fast and easy send requests 
to [laravel microservice](https://github.com/mggflow/laravel-microservice-base) API 
from Server side code.

## Usage
To install:
```
composer require mggflow/msvc-api
```

Example:
```
 use MGGFLOW\Microservices\Api;
 
 $msvcName = "msvc_name";
 $apiUrl = "https://url.to/api";
// Create instance of API.
 $api = new Api($msvcName, $apiUrl);
// Send request to action "hello" of microservice with param "name".
 $resp = $api->hello(["name" => "John"])->send();
// Returns json decoded response or false.
 var_dump($resp);
```