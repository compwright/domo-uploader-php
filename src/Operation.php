<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp;

use Compwright\EasyApi\Operation as InnerOperation;
use Compwright\EasyApi\Result\Result;

abstract class Operation
{
    // @phpstan-ignore-next-line property.readOnlyAssignNotInConstructor
    public readonly InnerOperation $operation;

    // @phpstan-ignore-next-line property.readOnlyAssignNotInConstructor
    public readonly Result $result;

    protected function setOperation(InnerOperation $operation): void
    {
        // @phpstan-ignore-next-line property.readOnlyAssignNotInConstructor
        $this->operation = $operation;
    }

    protected function setResult(Result $result): void
    {
        // @phpstan-ignore-next-line property.readOnlyAssignNotInConstructor
        $this->result = $result;
    }

    public function __toString(): string
    {
        return $this->operation->getUri();
    }
}
