<?php

namespace GenericApiClient;

use GenericApiClient\Consts\Request;
use GenericApiClient\Exceptions\ApiErrorException;
use GenericApiClient\Exceptions\MalformedApiResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
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

    /**
     * @var ResponseInterface|null
     */
    protected $lastResponse;

    public function __construct(
        string $apiUrl
    ) {
        $this->guzzleClient = new Client();
        $this->setApiRootUrl($apiUrl);
    }

    /**
     * @return string
     */
    public function getApiRootUrl(): string
    {
        return $this->apiRootUrl;
    }

    /**
     * @param string $apiRootUrl
     */
    protected function setApiRootUrl(string $apiRootUrl)
    {
        $this->apiRootUrl = rtrim($apiRootUrl, '/').'/';
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
            $this->getDefaultQueryParameters(),
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

        $response = $this->guzzleClient->send(
            $request,
            [
                // Don't throw exceptions for "bad" responses. Return them as a response object.
                RequestOptions::HTTP_ERRORS => false,
            ]
        );

        $this->lastResponse = $response;

        return $response;
    }

    /**
     * Does the same thing as request() but returns the array of data instead of the Response object.
     * Throws an exception for non-200 responses.
     * You can get the Response object by calling getLastResponse().
     *
     * @param string $method
     * @param string $endpoint
     * @param array $bodyParameters
     * @param array $queryParameters
     * @param array $headers
     *
     * @return array
     * @throws ApiErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestData(
        string $method,
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): array {
        $response = $this->request($method, $endpoint, $bodyParameters, $queryParameters, $headers);

        $result = $this->parseResponse($response);

        if ($response->getStatusCode() !== 200) {
            throw new ApiErrorException($response, $result);
        }

        return $result;
    }

    public function get(
        string $endpoint,
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        return $this->request(Request::METHOD_GET, $endpoint, [], $queryParameters, $headers);
    }

    public function getData(
        string $endpoint,
        array $queryParameters = [],
        array $headers = []
    ): array {
        return $this->requestData(Request::METHOD_GET, $endpoint, [], $queryParameters, $headers);
    }

    public function post(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        return $this->request(Request::METHOD_POST, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    public function postData(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): array {
        return $this->requestData(Request::METHOD_POST, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    public function put(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        return $this->request(Request::METHOD_PUT, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    public function putData(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): array {
        return $this->requestData(Request::METHOD_POST, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    public function patch(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        return $this->request(Request::METHOD_PATCH, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    public function patchData(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): array {
        return $this->requestData(Request::METHOD_POST, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    public function delete(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): ResponseInterface {
        return $this->request(Request::METHOD_DELETE, $endpoint, $bodyParameters, $queryParameters, $headers);
    }

    public function deleteData(
        string $endpoint,
        array $bodyParameters = [],
        array $queryParameters = [],
        array $headers = []
    ): array {
        return $this->requestData(Request::METHOD_POST, $endpoint, $bodyParameters, $queryParameters, $headers);
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
        return JsonParser::parseJsonResponse($response);
    }

    /**
     * @return ResponseInterface|null
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
