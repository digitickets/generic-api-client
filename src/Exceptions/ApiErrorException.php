<?php

namespace GenericApiClient\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;

class ApiErrorException extends Exception
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(ResponseInterface $response, array $data)
    {
        parent::__construct("Response code {$response->getStatusCode()} received");

        $this->response = $response;
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
