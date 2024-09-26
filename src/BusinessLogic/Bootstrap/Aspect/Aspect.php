<?php

namespace Unzer\Core\BusinessLogic\Bootstrap\Aspect;

/**
 * Interface Aspect
 *
 * @package Unzer\Core\BusinessLogic\Bootstrap\Aspect
 */
interface Aspect
{
    /**
     * @param callable $callee
     * @param array $params
     *
     * @return mixed
     */
    public function applyOn(callable $callee, array $params = []);
}
