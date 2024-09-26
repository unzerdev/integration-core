<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility;

use Unzer\Core\Infrastructure\Utility\GuidProvider;

/**
 * Class TestGuidProvider.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility
 */
class TestGuidProvider extends GuidProvider
{
    /**
     * @var string
     */
    private string $guid = '';

    /**
     * @return string
     */
    public function generateGuid(): string
    {
        if (empty($this->guid)) {
            return parent::generateGuid();
        }

        return $this->guid;
    }

    /**
     * @param string $guid
     */
    public function setGuid(string $guid): void
    {
        $this->guid = $guid;
    }
}
