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
     *
     * @return string
     */
    public function uploadImage(SplFileInfo $file): string;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function removeImage(string $path): bool;
}