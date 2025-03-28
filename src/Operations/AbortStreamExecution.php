<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp\Operations;

use Compwright\DomoUploaderPhp\Operation;
use Compwright\EasyApi\Operation as InnerOperation;
use Compwright\EasyApi\Result\Json\Result;

class AbortStreamExecution extends Operation
{
    public function __construct(string $streamId, string $executionId)
    {
        $operation = InnerOperation::fromSpec('PUT /v1/streams/%s/executions/%s/abort')
            ->bindArgs($streamId, $executionId);
        $this->setOperation($operation);
        $this->setResult(new Result());
    }
}
