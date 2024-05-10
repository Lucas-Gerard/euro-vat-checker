<?php

namespace LucasG\EuroVatChecker;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;
use ValueError;

class EuroVatChecker
{
    private const DEFAULT_URI = 'https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat-number';

    private UriInterface|string $uri;
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;


    public function __construct(
        UriInterface|string $uri = self::DEFAULT_URI,
        ?ClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->uri = $uri;
        $this->client = $client ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws EuroVatCheckerException
     * @throws JsonException
     * @throws ValueError
     */
    public function validate(string $vatNumber): bool
    {
        return $this->getCompanyInfo($vatNumber)->valid;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws EuroVatCheckerException
     * @throws JsonException
     * @throws ValueError
     */
    public function getCompanyInfo(string $vatNumber): CompanyInfo
    {
        $response = $this->client->sendRequest($this->createRequest($vatNumber));
        $responseData = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($responseData) || array_key_exists('errorWrappers', $responseData)) {
            throw EuroVatCheckerException::fromErrorResponse($responseData);
        }

        return new CompanyInfo(
            Country::from($responseData['countryCode']),
            $responseData['vatNumber'],
            $responseData['valid'],
            $responseData['name'],
            $responseData['address']
        );
    }

    /**
     * @throws JsonException
     * @throws ValueError
     */
    private function createRequest(string $vatNumber): RequestInterface
    {
        $requestBody = json_encode($this->explodeVatNumber($vatNumber), JSON_THROW_ON_ERROR);

        return $this->requestFactory
            ->createRequest('POST', $this->uri)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream($requestBody));
    }

    /**
     * @return array{countryCode: string, vatNumber: string}
     * @throws ValueError
     */
    private function explodeVatNumber(string $vatNumber): array
    {
        if (($length = mb_strlen($vatNumber)) < 3) {
            throw new ValueError("A Vat Number must have at least 3 characters, got {$length}");
        }

        return [
            'countryCode' => Country::from(mb_substr($vatNumber, 0, 2))->value,
            'vatNumber' => mb_substr($vatNumber, 2)
        ];
    }
}
