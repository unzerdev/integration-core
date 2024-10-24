<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\Uploader;

use SplFileInfo;

/**
 * Interface ImageService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\Uploader
 */
interface UploaderService
{
    /**
     * @param SplFileInfo $file
     * @param string $name
     * @return string
     */
    public function uploadImage(SplFileInfo $file, string $name = ''): string;
}
