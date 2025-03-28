<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp;

use Compwright\EasyApi\ApiClient;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * @property LoggerInterface $logger
 */
class DomoUploader implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private ApiClient $api,
        private CsvStreamer $csvStreamer,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @template T of Operation
     * @param T $operation
     * @return T
     */
    private function execute(Operation $operation)
    {
        $this->api->__invoke(
            $operation->operation,
            $operation->result
        );
        return $operation;
    }

    public function findStreamIdByDatasetName(string $datasetName): ?string
    {
        $dataset = $this->execute(
            new Operations\FindDataset($datasetName)
        );

        if ($dataset->found()) {
            return $dataset->getStreamId();
        }

        return null;
    }

    public function findOrCreateStreamByDatasetName(string $datasetName, Schema $columns, string $description = ''): string
    {
        $foundStreamId = $this->findStreamIdByDatasetName($datasetName);
        if ($foundStreamId) {
            return $foundStreamId;
        }

        $dataset = $this->execute(
            new Operations\CreateDataset($columns, $datasetName, $description)
        );

        return $dataset->getStreamId();
    }

    /**
     * @param iterable<int, object|array<string, mixed>> $data
     */
    public function uploadDataToStream(string $streamId, $data, int $chunkSize = 100, int $maxAttempts = 2): void
    {
        try {
            $execution = $this->execute(
                new Operations\CreateStreamExecution($streamId)
            );

            $executionId = $execution->getExecutionId();

            $this->logger->info(sprintf(
                'Initialized upload (%s:%s)',
                $streamId,
                $executionId
            ));

            $chunks = $this->csvStreamer->streamChunks($data, $chunkSize);

            foreach ($chunks as $i => $chunk) {
                $this->uploadChunk($streamId, $executionId, $i + 1, $chunk, $maxAttempts);
            }

            $commit = $this->execute(new Operations\CommitStreamExecution($streamId, $executionId));

            $this->logger->info(sprintf(
                '%d rows %s',
                $commit->getCommittedRowCount(),
                $commit->getCurrentState()
            ));
        } catch (Throwable $e) {
            if (isset($executionId)) {
                $this->logger->error(sprintf(
                    'Aborting upload %s:%s',
                    $streamId,
                    $executionId
                ));

                $this->execute(
                    new Operations\AbortStreamExecution($streamId, $executionId)
                );
            }

            throw $e;
        }
    }

    /**
     * @param resource $csvChunk
     */
    public function uploadChunk(string $streamId, string $executionId, int $chunkNumber, $csvChunk, int $maxAttempts): void
    {
        // Retry if unsuccessful
        for ($i = 1, $remainingAttempts = $maxAttempts; $remainingAttempts >= 0; $remainingAttempts--, $i++) {
            try {
                $this->logger->info(sprintf(
                    'Uploading part %d, attempt %d/%d (%s:%s)',
                    $chunkNumber,
                    $i,
                    $maxAttempts,
                    $streamId,
                    $executionId
                ));

                $this->execute(
                    new Operations\UploadStreamExecutionDataPart($streamId, $executionId, $chunkNumber, $csvChunk)
                );

                return;
            } catch (Throwable $e) {
                if ($remainingAttempts === 0) {
                    $this->logger->error(sprintf(
                        'Upload part %d failed after %d attempts (%s:%s)',
                        $chunkNumber,
                        $maxAttempts,
                        $streamId,
                        $executionId
                    ));

                    throw $e;
                }

                $this->logger->warning(sprintf(
                    'Upload failed for part %d, attempt %d/%d! retrying (%s:%s)',
                    $chunkNumber,
                    $i,
                    $maxAttempts,
                    $streamId,
                    $executionId
                ));
            }
        }
    }
}
