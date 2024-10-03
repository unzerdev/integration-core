<?php

namespace Unzer\Core\BusinessLogic\ApiFacades\Aspects;

use Exception;
use Unzer\Core\BusinessLogic\Bootstrap\Aspect\Aspect;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;

/**
 * Class StoreContextAspect.
 *
 * @package Unzer\Core\BusinessLogic\ApiFacades\Aspects
 */
class StoreContextAspect implements Aspect
{
    /**
     * @var string
     */
    private string $storeId;

    /**
     * @param string $storeId
     */
    public function __construct(string $storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @throws Exception
     */
    public function applyOn(callable $callee, array $params = [])
    {
        return StoreContext::doWithStore($this->storeId, $callee, $params);
    }
}
