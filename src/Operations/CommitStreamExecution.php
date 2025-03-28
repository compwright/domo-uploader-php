<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp\Operations;

use Compwright\DomoUploaderPhp\Operation;
use Compwright\EasyApi\Operation as InnerOperation;
use Compwright\EasyApi\Result\Json\Result;

/**
 * @property Result $result
 */
class CommitStreamExecution extends Operation
{
    public function __construct(string $streamId, string $executionId)
    {
        $operation = InnerOperation::fromSpec('PUT /v1/streams/%s/executions/%s/commit')
            ->bindArgs($streamId, $executionId);
        $this->setOperation($operation);
        $this->setResult(new Result());
    }

    public function getCurrentState(): string
    {
        $data = $this->result->data();
        // @phpstan-ignore-next-line argument.type
        return strval($data['currentState'] ?? '');
    }

    public function getCommittedRowCount(): int
    {
        $data = $this->result->data();
        // @phpstan-ignore-next-line argument.type
        return intval($data['rows'] ?? 0);
    }
}
