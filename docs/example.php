<?php

use Compwright\DomoUploaderPhp\Schema;
use Compwright\DomoUploaderPhp\DomoUploaderFactory;
use Compwright\OAuth2\Domo\DomoProviderFactory;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

require dirname(__DIR__) . '/vendor/autoload.php';

function generateFakeData(int $rows): Generator
{
    for ($i = 0; $i < $rows; $i++) {
        yield [
            'foo' => random_int(1000, 1999),
            'bar' => random_int(2000, 2999),
            'baz' => random_int(3000, 3999),
        ];
    }
}

$uploaderFactory = new DomoUploaderFactory(
    logger: new ConsoleLogger(
        new ConsoleOutput(ConsoleOutput::VERBOSITY_VERY_VERBOSE)
    )
);

$oauth = (new DomoProviderFactory())->new(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret'
);

$token = $oauth->getAccessToken('client_credentials', ['scope' => 'data']);

$uploader = $uploaderFactory->new($token->getToken());

$streamId = $uploader->findOrCreateStreamByDatasetName(
    datasetName: 'compwright-domo-test-' . random_int(1000, 1999),
    columns: Schema::fromMap([
        'foo' => 'DECIMAL',
        'bar' => 'DECIMAL',
        'baz' => 'DECIMAL',
    ]),
    description: 'compwright/domo-uploader-php test, ' . (new DateTimeImmutable())->format(DateTimeInterface::ATOM)
);

$uploader->uploadDataToStream(
    streamId: $streamId,
    data: generateFakeData(1000),
    chunkSize: 25,
    maxAttempts: 3
);
