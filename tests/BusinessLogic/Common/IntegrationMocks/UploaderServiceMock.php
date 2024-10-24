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
     * @param SplFileInfo $file
     * @param string $name
     * @inheritDoc
     */
    public function uploadImage(SplFileInfo $file, string $name = ''): string
    {
        return $this->path;
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