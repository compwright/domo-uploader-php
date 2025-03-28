<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp;

use Compwright\EasyApi\ApiClient;
use Compwright\EasyApi\OperationRequestFactory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DomoUploaderFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ?callable */
    private $rootHandler;

    private ?string $token = null;

    public function __construct(?callable $rootHandler = null, ?string $token = null, ?LoggerInterface $logger = null)
    {
        $this->rootHandler = $rootHandler;
        $this->token = $token;
        $this->logger = $logger;
    }

    private function operationRequestFactory(): OperationRequestFactory
    {
        $httpFactory = new HttpFactory();
        return new OperationRequestFactory($httpFactory, $httpFactory);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function new(?string $token = null): DomoUploader
    {
        $token ??= $this->token;

        if (!$token) {
            throw new InvalidArgumentException('$token is required');
        }

        return new DomoUploader(
            api: new ApiClient(
                new Client([
                    'base_uri' => 'https://api.domo.com',
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json'
                    ],
                    'handler' => HandlerStack::create($this->rootHandler),
                ]),
                $this->operationRequestFactory()
            ),
            csvStreamer: new CsvStreamer(),
            logger: $this->logger ?? new NullLogger()
        );
    }
}
