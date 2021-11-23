<?php

namespace GenericApiClientTests;

use GenericApiClient\ApiClient;
use GenericApiClient\Exceptions\MalformedApiResponseException;
use Psr\Http\Message\ResponseInterface;

class ApiClientE2ETest extends AbstractTestCase
{
    /**
     * @param string $apiUrl See https://reqres.in
     *
     * @return ApiClient
     */
    private function makeApiClient(string $apiUrl = "https://postman-echo.com/"): ApiClient
    {
        return new ApiClient($apiUrl);
    }

    /**
     * Test the setApiKey and getApiKey methods.
     */
    public function testGetSetDefaultQueryValues()
    {
        $apiClient = $this->makeApiClient();

        $this->assertSame([], $apiClient->getDefaultQueryParameters());
        $apiClient->addDefaultQueryParameter('hello', 'world');
        $this->assertSame(['hello' => 'world'], $apiClient->getDefaultQueryParameters());
        $apiClient->addDefaultQueryParameter('hello', 'is it me you\'re looking for?');
        $this->assertSame(['hello' => 'is it me you\'re looking for?'], $apiClient->getDefaultQueryParameters());
        $apiClient->removeDefaultQueryParameter('hello');
        $this->assertSame([], $apiClient->getDefaultQueryParameters());
    }

    public function test404()
    {
        $apiClient = $this->makeApiClient();
        $response = $apiClient->get('dogbirthdays');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }

    /**
     * Test that an exception is thrown when a non-JSON response is sent to parseResponse.
     */
    public function testNonJsonEndpointThrowsException()
    {
        $this->expectException(MalformedApiResponseException::class);
        $this->expectExceptionMessage("json_decode error: Syntax error");

        $apiClient = new ApiClient('https://anthonykuske.com/');
        // This is a response that just contains some text.
        $response = $apiClient->get('test.txt');
        $apiClient->parseResponse($response);
    }

    /**
     * Test that the raw Response is accessible in case of an exception in parseResponse.
     */
    public function testNonJsonEndpointReturnsResponseInException()
    {
        $response = null;

        try {
            // Create an API client to access the unversioned API root.
            $apiClient = new ApiClient('https://anthonykuske.com/');
            // This is a response that just contains some text.
            $response = $apiClient->get('test.txt');
            $apiClient->parseResponse($response);
        } catch (MalformedApiResponseException $e) {
            $response = $e->getResponse();
        }

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame("Hello world\n", (string) $response->getBody());
    }

    public function testGet()
    {
        $apiClient = $this->makeApiClient();
        $response = $apiClient->get('get');
        $result = $apiClient->parseResponse($response);

        $this->assertGreaterThan(0, count($result['headers']));
    }

    public function testGetData()
    {
        $apiClient = $this->makeApiClient();
        $result = $apiClient->getData('get');

        $this->assertGreaterThan(0, count($result['headers']));

        $response = $apiClient->getLastResponse();
        $result2 = $apiClient->parseResponse($response);
        $this->assertEquals($result2, $result);
    }

    public function testGetWithQueryParameters()
    {
        $apiClient = $this->makeApiClient();

        $response = $apiClient->get(
            'get',
            [
                'orderBy' => 'ref'
            ]
        );
        $result = $apiClient->parseResponse($response);

        $this->assertSame('ref', $result['args']['orderBy']);
    }

    public function testPost()
    {
        $apiClient = $this->makeApiClient();
        $response = $apiClient->post('post', ['name' => 'Savannah', 'job' => 'Cat']);
        $result = $apiClient->parseResponse($response);

        $this->assertSame('Savannah', $result['form']['name']);
        $this->assertSame('Cat', $result['form']['job']);
    }

    public function testPostData()
    {
        $apiClient = $this->makeApiClient();
        $result = $apiClient->postData('post', ['name' => 'Savannah', 'job' => 'Cat']);

        $this->assertSame('Savannah', $result['form']['name']);
        $this->assertSame('Cat', $result['form']['job']);
    }

    /**
     * Test a DELETE request.
     */
    public function testDelete()
    {
        $apiClient = $this->makeApiClient();

        $response = $apiClient->delete('delete', ['cat' => 'Savannah']);
        $result = $apiClient->parseResponse($response);

        $this->assertSame('Savannah', $result['form']['cat']);
    }

    /**
     * Test a PUT request.
     */
    public function testPut()
    {
        $apiClient = $this->makeApiClient();

        $response = $apiClient->put('put', ['cat' => 'Savannah']);
        $result = $apiClient->parseResponse($response);

        $this->assertSame('Savannah', $result['form']['cat']);
    }

    /**
     * Test a PATCH request.
     */
    public function testPatch()
    {
        $apiClient = $this->makeApiClient();

        $response = $apiClient->patch('patch', ['cat' => 'Savannah']);
        $result = $apiClient->parseResponse($response);

        $this->assertSame('Savannah', $result['form']['cat']);
    }
}
