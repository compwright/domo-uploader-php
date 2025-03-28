<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp\Operations;

use Compwright\DomoUploaderPhp\Operation;
use Compwright\EasyApi\Operation as InnerOperation;
use Compwright\EasyApi\Result\EmptyResult;

class DeleteDataset extends Operation
{
    public function __construct(string $datasetId)
    {
        $operation = InnerOperation::fromSpec('DELETE /v1/datasets/%s')
            ->bindArgs($datasetId);
        $this->setOperation($operation);
        $this->setResult(new EmptyResult());
    }
}
