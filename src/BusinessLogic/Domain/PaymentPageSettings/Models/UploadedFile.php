<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models;

use SplFileInfo;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions\InvalidImageUrlException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class UploadedFile.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models
 */
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
     *
     * @throws InvalidImageUrlException
     */
    public function __construct(?string $url = null, ?SplFileInfo $fileInfo = null)
    {
        $this->validateUrl($url);

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
    public function hasFileInfo(): bool
    {
        return $this->fileInfo !== null;
    }

    /**
     * @param string|null $url
     *
     * @return void
     *
     * @throws InvalidImageUrlException
     */
    private function validateUrl(?string $url): void
    {
        if (!$url) {
            return;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidImageUrlException(
                new TranslatableLabel('Url is not valid.', 'designPage.invalidUrl')
            );
        }
    }
}
