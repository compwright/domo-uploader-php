<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp\Operations;

use Compwright\DomoUploaderPhp\Operation;
use Compwright\EasyApi\Operation as InnerOperation;
use Compwright\EasyApi\Result\Json\Result;

/**
 * @property Result $result
 */
class CreateStreamExecution extends Operation
{
    public function __construct(string $streamId)
    {
        $operation = InnerOperation::fromSpec('POST /v1/streams/%s/executions')
            ->bindArgs($streamId);
        $this->setOperation($operation);
        $this->setResult(new Result());
    }

    public function getExecutionId(): string
    {
        $data = $this->result->data();
        // @phpstan-ignore-next-line argument.type
        return strval($data['id'] ?? '');
    }
}
