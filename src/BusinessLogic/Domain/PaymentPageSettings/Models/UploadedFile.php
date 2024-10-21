<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models;

use SplFileInfo;

class UploadedFile
{
    /**
     * @var string|null
     */
    private ?string $url = null;

    /**
     * @var SplFileInfo|null
     */
    private ?SplFileInfo $fileInfo = null;

    /**
     * @param string|null $url
     * @param SplFileInfo|null $fileInfo
     */
    public function __construct(?string $url = null, ?SplFileInfo $fileInfo = null)
    {
        $this->url = $url;
        $this->fileInfo = $fileInfo;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return SplFileInfo|null
     */
    public function getFileInfo(): ?SplFileInfo
    {
        return $this->fileInfo;
    }

    /**
     * @return bool
     */
    public function isFileInfo(): bool
    {
        return $this->fileInfo !== null;
    }

}