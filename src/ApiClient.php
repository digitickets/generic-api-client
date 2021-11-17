<?php

namespace GenericApiClient;

use GenericApiClient\Consts\Request;
use GenericApiClient\Exceptions\MalformedApiResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    /**
     * @var string[]
     */
    protected $defaultQueryParameters = [];

    /**
     * @var string
     */
    protected $apiRootUrl;

    /**
     * @var Client
     */
    protected $guzzleClient;

    public function __construct(
        string $apiUrl
    ) {
        $this->guzzleClient = new Client();
        $this->apiRootUrl = $apiUrl;
    }

    /**
     * @return string[]
     */
    public function getDefaultQueryParameters(): array
    {
        return $this->defaultQueryParameters;
    }

    public function addDefaultQueryParameter(string $key, string $value)
    {
        $this->defaultQueryParameters[$key] = $value;
    }

    public function removeDefaultQueryParameter(string $key)
    {
        unset($this->defaultQueryParameters[$key]);
    }

    /**
     * Makes a request to the API and returns the response. The returned value is a PSR ResponseInterface, so that you
     * can access the status and headers of the response too.
     * To access the actual data pass that response to $this->parseResponse().
     *
     * @param string $method
     * @param string $endpoint
     * @param array $bodyParameters Keys/values to be sent as part of the request body (e.g. for POST requests).
     *     Does nothing for GET requests.
     * @param array $queryParameters Keys/values to be appended to the query string in the URL.
     * @param array $headers
     *
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(
        string $method,
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        // Allow leading or no leading slash in the provided endpoint.
        $url = rtrim($this->apiRootUrl, '/').'/'.ltrim($endpoint, '/');

        $queryParameters = array_merge(
            $this->defaultQueryParameters,
            $queryParameters
        );

        if ($method === Request::METHOD_GET) {
            $body = null;
        } else {
            $headers['content-type'] = 'application/x-www-form-urlencoded';
            $body = http_build_query($bodyParameters);
        }

        $url .= (strpos($url, '?') !== false ? '&' : '?').http_build_query($queryParameters);

        $request = new \GuzzleHttp\Psr7\Request(
            $method,
            $url,
            $headers,
            $body
        );

        return $this->guzzleClient->send(
            $request,
            [
                // Don't throw exceptions for "bad" responses. Return them as a response object.
                RequestOptions::HTTP_ERRORS => false,
            ]
        );
    }

    public function get(
        string $endpoint,
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        return $this->request(Request::METHOD_GET, $endpoint, [], $queryParameters, $headers);
    }

    public function post(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        return $this->request(Request::METHOD_POST, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    public function put(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        return $this->request(Request::METHOD_PUT, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    public function patch(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        return $this->request(Request::METHOD_PATCH, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    public function delete(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        return $this->request(Request::METHOD_DELETE, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    /**
     * Decode the JSON in a response body and return it as an array.
     *
     * @param ResponseInterface $response
     *
     * @return array
     * @throws MalformedApiResponseException
     */
    public function parseResponse(ResponseInterface $response): array
    {
        try {
            return \GuzzleHttp\json_decode($response->getBody(), true);
        } catch (InvalidArgumentException $e) {
            // Re-throwing the same exception but containing the response so the problem can be debugged.
            throw new MalformedApiResponseException(
                $response,
                rtrim($e->getMessage(), '.').". Response body:\n".$response->getBody(),
                $e->getCode(),
                $e
            );
        }
    }
}
