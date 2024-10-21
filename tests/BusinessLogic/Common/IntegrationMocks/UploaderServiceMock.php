<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use SplFileInfo;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;

class UploaderServiceMock implements UploaderService
{
    /**
     * @var SplFileInfo|null
     */
    private ?SplFileInfo $file;

    /**
     * @var string|null
     */
    private ?string $path;
    /**
     * @inheritDoc
     */
    public function uploadImage(SplFileInfo $file): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function removeImage(string $path): bool
    {
        return true;
    }

    /**
     * @param string|null $path
     *
     * @return void
     */

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }
}