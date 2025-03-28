<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp\Operations;

use Compwright\DomoUploaderPhp\Operation;
use Compwright\EasyApi\Operation as InnerOperation;
use Compwright\EasyApi\OperationBody\ResourceBody;
use Compwright\EasyApi\Result\Json\Result;

class UploadStreamExecutionDataPart extends Operation
{
    /**
     * @param resource $csvData
     */
    public function __construct(string $streamId, string $executionId, int $partId, $csvData)
    {
        $operation = InnerOperation::fromSpec('PUT /v1/streams/%s/executions/%s/part/%s')
            ->bindArgs($streamId, $executionId, $partId)
            ->setBody(new ResourceBody($csvData, 'text/csv'));
        $this->setOperation($operation);
        $this->setResult(new Result());
    }
}
