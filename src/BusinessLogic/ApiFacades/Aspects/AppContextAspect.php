<?php

namespace Unzer\Core\BusinessLogic\ApiFacades\Aspects;

use Exception;
use Unzer\Core\BusinessLogic\Bootstrap\Aspect\Aspect;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;

/**
 * Class AppContextAspect.
 *
 * @package Unzer\Core\BusinessLogic\ApiFacades\Aspects
 */
class AppContextAspect implements Aspect
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
