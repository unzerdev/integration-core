<?php

namespace Unzer\Core\BusinessLogic\Bootstrap\Aspect;

use Closure;
use Exception;

/**
 * Class CompositeAspect
 *
 * @package Unzer\Core\BusinessLogic\Bootstrap\Aspect
 */
class CompositeAspect implements Aspect
{
    /**
     * @var Aspect
     */
    private Aspect $aspect;

    /**
     * @var Aspect|null
     */
    private ?Aspect $next = null;

    /**
     * @param Aspect $aspect
     */
    public function __construct(Aspect $aspect)
    {
        $this->aspect = $aspect;
    }

    /**
     * @param Aspect $aspect
     *
     * @return void
     */
    public function append(Aspect $aspect): void
    {
        $this->next = new self($aspect);
    }

    /**
     * @throws Exception
     */
    public function applyOn(callable $callee, array $params = [])
    {
        $callback = $callee;
        if ($this->next) {
            $callback = $this->getNextCallee($callee, $params);
        }

        return $this->aspect->applyOn($callback, $params);
    }

    /**
     * @param callable $callee
     * @param array $params
     *
     * @return Closure
     */
    private function getNextCallee(callable $callee, array $params = []): Closure
    {
        return function () use ($callee, $params) {
            return $this->next->applyOn($callee, $params);
        };
    }
}
