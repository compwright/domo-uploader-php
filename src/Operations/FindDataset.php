<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp\Operations;

use Compwright\DomoUploaderPhp\Operation;
use Compwright\EasyApi\Operation as InnerOperation;
use Compwright\EasyApi\Result\Json\Result;

/**
 * @property-read Result $result
 */
class FindDataset extends Operation
{
    public function __construct(string $datasetName)
    {
        $operation = InnerOperation::fromSpec('GET /v1/streams/search?q=dataSource.name:%s')
            ->bindArgs($datasetName);
        $this->setOperation($operation);
        $this->setResult(new Result());
    }

    public function found(): bool
    {
        $data = $this->result->data();
        return count($data) > 0;
    }

    public function getStreamId(): string
    {
        $found = $this->result->data()[0] ?? [];
        // @phpstan-ignore-next-line argument.type, offsetAccess.nonOffsetAccessible
        return strval($found['id'] ?? '');
    }

    public function getDatasetId(): string
    {
        $found = $this->result->data()[0] ?? [];
        // @phpstan-ignore-next-line argument.type, offsetAccess.nonOffsetAccessible
        return strval($found['dataSet']['id'] ?? '');
    }
}
