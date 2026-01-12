<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models;

use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions\InvalidUrlException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

class DomainUrls
{
    /**
     * @var null|string
     */
    private ?string $helpUrl = null;

    /**
     * @var null|string
     */
    private ?string $contactUrl = null;

    /**
     * @var null|string
     */
    private ?string $termsAndConditions = null;

    /**
     * @var null|string
     */
    private ?string $privacyPolicy = null;

    /**
     * @var null|string
     */
    private ?string $imprint = null;

    /**
     * @throws InvalidUrlException
     */
    public function __construct(
        ?string $helpUrl = null,
        ?string $contactUrl = null,
        ?string $termsAndConditions = null,
        ?string $privacyPolicy = null,
        ?string $imprint = null)
    {
        $this->validateUrl($helpUrl);
        $this->validateUrl($contactUrl);
        $this->validateUrl($termsAndConditions);
        $this->validateUrl($privacyPolicy);
        $this->validateUrl($imprint);

        $this->helpUrl = $helpUrl;
        $this->contactUrl = $contactUrl;
        $this->termsAndConditions = $termsAndConditions;
        $this->privacyPolicy = $privacyPolicy;
        $this->imprint = $imprint;
    }

    /**
     * @throws InvalidUrlException
     */
    private function validateUrl($url)
    {
        if (!$url) {
            return;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException(
                new TranslatableLabel('Url is not valid.', 'designPage.invalidUrl')
            );
        }
    }

    public function getHelpUrl(): ?string
    {
        return $this->helpUrl;
    }

    public function getContactUrl(): ?string
    {
        return $this->contactUrl;
    }

    public function getTermsAndConditions(): ?string
    {
        return $this->termsAndConditions;
    }

    public function getPrivacyPolicy(): ?string
    {
        return $this->privacyPolicy;
    }

    public function getImprint(): ?string
    {
        return $this->imprint;
    }
}
