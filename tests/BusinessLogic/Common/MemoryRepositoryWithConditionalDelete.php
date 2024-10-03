<?php

namespace Unzer\Core\Tests\BusinessLogic\Common;

use Unzer\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;

class MemoryRepositoryWithConditionalDelete extends MemoryRepository implements ConditionallyDeletes
{
    use MockConditionalDelete;

    const THIS_CLASS_NAME = __CLASS__;
}
