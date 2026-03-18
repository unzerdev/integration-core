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
     * @var null|string
     */
    private ?string $subscriptionAgreement = null;

    /**
     * @param string|null $helpUrl
     * @param string|null $contactUrl
     * @param string|null $termsAndConditions
     * @param string|null $privacyPolicy
     * @param string|null $imprint
     * @param string|null $subscriptionAgreement
     *
     * @throws InvalidUrlException
     */
    public function __construct(
        ?string $helpUrl = null,
        ?string $contactUrl = null,
        ?string $termsAndConditions = null,
        ?string $privacyPolicy = null,
        ?string $imprint = null,
        ?string $subscriptionAgreement = null
    ) {
        $this->validateUrl($helpUrl);
        $this->validateUrl($contactUrl);
        $this->validateUrl($termsAndConditions);
        $this->validateUrl($privacyPolicy);
        $this->validateUrl($imprint);
        $this->validateUrl($subscriptionAgreement);

        $this->helpUrl = $helpUrl;
        $this->contactUrl = $contactUrl;
        $this->termsAndConditions = $termsAndConditions;
        $this->privacyPolicy = $privacyPolicy;
        $this->imprint = $imprint;
        $this->subscriptionAgreement = $subscriptionAgreement;
    }

    /**
     * @return string|null
     */
    public function getHelpUrl(): ?string
    {
        return $this->helpUrl;
    }

    /**
     * @return string|null
     */
    public function getContactUrl(): ?string
    {
        return $this->contactUrl;
    }

    /**
     * @return string|null
     */
    public function getTermsAndConditions(): ?string
    {
        return $this->termsAndConditions;
    }

    /**
     * @return string|null
     */
    public function getPrivacyPolicy(): ?string
    {
        return $this->privacyPolicy;
    }

    /**
     * @return string|null
     */
    public function getImprint(): ?string
    {
        return $this->imprint;
    }

    /**
     * @return string|null
     */
    public function getSubscriptionAgreement(): ?string
    {
        return $this->subscriptionAgreement;
    }

    /**
     * @return bool
     */
    public function hasAny(): bool
    {
        return !empty($this->helpUrl) || !empty($this->contactUrl)
            || !empty($this->termsAndConditions) || !empty($this->privacyPolicy) || !empty($this->imprint)
            || !empty($this->subscriptionAgreement);
    }

    /**
     * @param ?string $url
     * @return void
     *
     * @throws InvalidUrlException
     */
    private function validateUrl(?string $url = null): void
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
}
