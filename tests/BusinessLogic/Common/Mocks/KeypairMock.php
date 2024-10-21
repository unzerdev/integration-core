<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use UnzerSDK\Resources\Keypair;

/**
 * Class KeypairMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class KeypairMock extends Keypair
{
    /**
     * @var string|null
     */
    public ?string $publicKey = null;

    /**
     * @var array
     */
    public static array $types = [];

    /**
     * @var array
     */
    public static array $paymentTypes = [];

    /**
     * @return string|null
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     *
     * @return void
     */
    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @param array $types
     *
     * @return void
     */
    public function setAvailablePaymentTypes(array $types): void
    {
        self::$types = $types;
    }

    /**
     * @return array
     */
    public function getAvailablePaymentTypes(): array
    {
        return self::$types;
    }

    /**
     * @return object[]
     */
    public function getPaymentTypes(): array
    {
        return !empty(self::$paymentTypes) ? self::$paymentTypes : [
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["PRZELEWY24"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C1A4AE013EA94",
                        "currency" => ["PLN"]
                    ]
                ],
                "type" => "przelewy24",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["INV"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C2EA3437FCE3A",
                        "currency" => ["EUR"],
                        "pmpId" => "pmp"
                    ]
                ],
                "type" => "paylater-invoice",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["FLEXIPAY_INSTALMENT"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C740EBD82D14B",
                        "currency" => ["EUR"],
                        "pmpId" => "pmp"
                    ]
                ],
                "type" => "paylater-installment",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["TWINT"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C07F33B5446D6",
                        "currency" => ["CHF"],
                        "pmpId" => "pmp"
                    ]
                ],
                "type" => "twint",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["BCMC"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C9044293A03D0",
                        "currency" => ["EUR"]
                    ]
                ],
                "type" => "bancontact",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["ALIPAY"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C2A5D8D849696",
                        "currency" => ["EUR"]
                    ]
                ],
                "type" => "alipay",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["APPLEPAY"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C24E39D61D293",
                        "currency" => ["EUR"]
                    ]
                ],
                "type" => "applepay",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["PFEFINANCE"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C1DDB5BB60B06",
                        "currency" => ["CHF"]
                    ]
                ],
                "type" => "post-finance-efinance",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => [
                            "DANKORT",
                            "SERVIRED",
                            "POSTEPAY",
                            "CARTEBLEUE",
                            "VISAELECTRON",
                            "MAESTRO",
                            "VISA",
                            "MASTER"
                        ],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C4B7DC4C78004",
                        "currency" => ["EUR"]
                    ]
                ],
                "type" => "card",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => true
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["PREP"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C4190129ACE64",
                        "currency" => ["EUR"]
                    ]
                ],
                "type" => "prepayment",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["PAYPAL"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C4B7DC4C78004",
                        "currency" => ["EUR"]
                    ]
                ],
                "type" => "paypal",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["PFCARD"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C1DDB5BB60B06",
                        "currency" => ["CHF"]
                    ]
                ],
                "type" => "post-finance-card",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["VISA", "MASTER"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7CA3BB1DFDD635",
                        "currency" => ["EUR"],
                        "pmpId" => "pmp"
                    ]
                ],
                "type" => "googlepay",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["WECHATPAY"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C2A5D8D849696",
                        "currency" => ["EUR"]
                    ]
                ],
                "type" => "wechatpay",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => [],
                        "countries" => ["AT", "DE"],
                        "channel" => "31HA07BC81AE5E9FBF7C95457064722A",
                        "currency" => ["EUR"],
                        "pmpId" => "pmp"
                    ]
                ],
                "type" => "paylater-direct-debit",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["IDEAL_BRAND"],
                        "countries" => [],
                        "channel" => "31HA07BC81AE5E9FBF7C63526331B8A9",
                        "currency" => ["EUR"]
                    ]
                ],
                "type" => "ideal",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ],
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["EPS"],
                        "countries" => [],
                        "channel" => "31HA07BC8111E3AD602A112386E0FD19",
                        "currency" => ["EUR"]
                    ]
                ],
                "type" => "EPS",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ]
        ];
    }

    /**
     * @param array $paymentTypes
     *
     * @return void
     */
    public function setPaymentTypes(array $paymentTypes): void
    {
        self::$paymentTypes = $paymentTypes;
    }
}
