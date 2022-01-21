<?php

namespace GenericApiClientTests\Fixtures;

use GenericApiClient\ApiClient;
use GuzzleHttp\Client;

class ApiClientWithCustomQueryParametersFixture extends ApiClient
{
    public function getDefaultQueryParameters(): array
    {
        return array_merge(
            $this->defaultQueryParameters,
            [
                // Return something that will change every time we call it.
                'time' => microtime(true),
            ]
        );
    }

    /**
     * @param Client $guzzleClient
     */
    public function setGuzzleClient(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }
}
