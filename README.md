# Generic API Client

Provides a standard interface for accessing an API.

## Installation

    composer require digitickets/generic-api-client

## Usage
```
<?php

require __DIR__.'/vendor/autoload.php';

use GenericApiClient\ApiClient;

$apiClient = new ApiClient('https://some-api.com);

$response = $apiClient->get('dogs'); // Returns a PSR ResponseInterface.

// You can get an array of data from the response object with this method:
$dogs = $apiClient->parseResponse($response);

print_r($dogs);
// Returns:
// Array
// (
//     [0] => Array
//         (
//             [dogID] => 11
//             [name] => Merlin
```

## Testing

To run tests:

    composer test
