<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp\Operations;

use Compwright\DomoUploaderPhp\Operation;
use Compwright\DomoUploaderPhp\Schema;
use Compwright\EasyApi\Operation as InnerOperation;
use Compwright\EasyApi\OperationBody\JsonBody;
use Compwright\EasyApi\Result\Json\Result;

/**
 * @property-read Result $result
 */
class CreateDataset extends Operation
{
    public function __construct(Schema $columns, string $name, string $description = '')
    {
        $body = new JsonBody([
            'dataSet' => [
                'name' => $name,
                'description' => $description,
                'schema' => [
                    'columns' => $columns->jsonSerialize(),
                ],
            ],
            'updateMethod' => 'APPEND',
        ]);
        $operation = InnerOperation::fromSpec('POST /v1/streams')
            ->setBody($body);
        $this->setOperation($operation);
        $this->setResult(new Result());
    }

    public function getStreamId(): string
    {
        $data = $this->result->data();
        // @phpstan-ignore-next-line argument.type
        return strval($data['id'] ?? '');
    }

    public function getDatasetId(): string
    {
        $data = $this->result->data();
        // @phpstan-ignore-next-line argument.type
        return strval($data['dataSet']['id'] ?? '');
    }
}
