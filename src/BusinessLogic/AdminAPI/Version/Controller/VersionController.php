<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Version\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\Version\Response\VersionResponse;
use Unzer\Core\BusinessLogic\Domain\Integration\Versions\VersionService;

/**
 * Class VersionController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Version\Controller
 */
class VersionController
{
    /**
     * @var VersionService
     */
    private VersionService $versionService;

    /**
     * @param VersionService $versionService
     */
    public function __construct(VersionService $versionService)
    {
        $this->versionService = $versionService;
    }

    /**
     * @return VersionResponse
     */
    public function getVersion(): VersionResponse
    {
        return new VersionResponse($this->versionService->getVersion());
    }
}
