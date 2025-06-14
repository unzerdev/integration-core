﻿Unzer.CheckoutController = function () {
    /**
     *
     * @type {HTMLElement}
     */
    const page = Unzer.pageService.getContentPage();

    const chargeStatusFlag = Unzer.config.chargeStatusFlag ?? true;
    /**
     * @type {[{
     *     @property {string?} name
     *     @property {string?} description
     *     @property {string?} image
     *     @property {boolean} state
     * }]}
     */
    let paymentMethods = [];

    let countries = [];

    let orderStatuses = [];

    /**
     *
     * @type {{bookingMethod: null,
     * surcharge: number,
     * restrictedCountries: [string],
     * sendBasketData: boolean,
     * enableClickToPay: boolean,
     * minOrderAmount: number,
     * name: [{locale: string, value: string}],
     * description: [{locale: string, value: string}],
     * statusIdToCharge: null,
     * maxOrderAmount: null}}
     */
    let paymentMethodConfig = {
        name: [
            {
                locale: "",
                value: ""
            }
        ],
        description: [
            {
                locale: "",
                value: ""
            }
        ],
        bookingMethod: null,
        statusIdToCharge: null,
        minOrderAmount: null,
        maxOrderAmount: null,
        surcharge: null,
        restrictedCountries: null,
        sendBasketData: false,
        enableClickToPay: false
    }

    let icons = [
        {
            language: '',
            icon: Unzer.imagesProvider.languageIcon
        }
    ];

    const docsUrl = {
        "paylater-direct-debit": "direct-debit-secured",
        "paylater-installment": "unzer-installment-upl",
        "paylater-invoice": "unzer-invoice-upl",
        "wechatpay": "wechat-pay",
        "sepa-direct-debit": "unzer-direct-debit",
        "prepayment": "unzer-prepayment",
        "openbanking-pis": "open-banking"
    }

    /**
     * renders checkout page
     * @param {StateParamsModel} params
     */
    this.display = (params) => {
        if (!Unzer.config.store.isLoggedIn) {
            Unzer.stateController.navigate('login');

            return;
        }

        getData();
    };

    const getPaymentMethodConfig = (paymentMethod) => {
        Unzer.utilities.showLoader();

        Unzer.PaymentMethodService.getConfiguration(paymentMethod.type)
            .then((result) => {
                Object.assign(paymentMethodConfig, {
                    bookingMethod: result.bookingMethod,
                    name: (result.name && result.name.length > 0) ? result.name : [
                        {
                            locale: 'default',
                            value: paymentMethod.name
                        }
                    ],
                    sendBasketData: result.sendBasketData,
                    surcharge: result.surcharge,
                    description: (result.description && result.description.length > 0) ? result.description : [
                        {
                            locale: 'default',
                            value: Unzer.translationService.translate('checkout.modal.defaultDescription')
                        }
                    ],
                    restrictedCountries: result.restrictedCountries,
                    minOrderAmount: result.minOrderAmount,
                    maxOrderAmount: result.maxOrderAmount,
                    statusIdToCharge: result.statusIdToCharge
                });

                openSettingModal(result, paymentMethod)
            })
            .catch((ex) => {
                Unzer.utilities.createToasterMessage(ex.message, true);
            })
            .finally(Unzer.utilities.hideLoader);
    }

    const enablePaymentMethod = (type, enabled, name) => {
        Unzer.utilities.showLoader();

        Unzer.PaymentMethodService.enable(type, enabled)
            .then(() => {
                if (enabled) {
                    Unzer.utilities.createToasterMessage(`checkout.page.enabled|${name}`, false);
                }

                if (!enabled) {
                    Unzer.utilities.createToasterMessage(`checkout.page.disabled|${name}`, false);
                }
            })
            .catch((ex) => {
                Unzer.utilities.createToasterMessage(ex.message, true);
            })
            .finally(Unzer.utilities.hideLoader);
    }


    const getData = () => {
        Unzer.utilities.showLoader();

        Promise.all([
                Unzer.PaymentMethodService
                    .getAll()
                    .then((result) => {
                        paymentMethods = result;
                        render();
                    })
                    .catch((ex) => {
                        Unzer.utilities.createToasterMessage(ex.message, true);
                        return Promise.reject();
                    }),
                Unzer.CountriesService
                    .get()
                    .then((result) => {
                        countries = result
                    }).catch(),
                Unzer.StoresService
                    .getOrderStatuses()
                    .then(result => {
                        orderStatuses = result;
                    })
                    .catch(ex => {
                        Unzer.utilities.createToasterMessage(ex.message, true);
                        return Promise.reject();
                    })
            ])
            .then(() => {
                render();
            })
            .finally(Unzer.utilities.hideLoader);

    };

    const render = () => {
        Unzer.pageService.clearComponent(page);

        const buttons = Unzer.components.Button.createList([
            {
                type: 'primary',
                label: 'checkout.page.settings',
                className: 'adlt--settings',
                onClick: () => Unzer.stateController.navigate('design')
            }
        ]);

        const midpoint = Math.ceil(paymentMethods.length / 2);
        const left = paymentMethods.slice(0, midpoint);
        const right = paymentMethods.slice(midpoint);

        page.append(
            Unzer.components.PageHeading.create({
                title: 'checkout.page.paymentMethodTitle',
                description: 'checkout.page.paymentMethodDescription',
                button: buttons
            }),
            Unzer.components.SearchBox.create({
                onSearch: (value) => {
                    const results = paymentMethods.filter((item) =>
                        item.name.toLocaleLowerCase().includes(value.toLowerCase())
                    );
                    const midpoint = Math.ceil(results.length / 2);
                    const left = results.slice(0, midpoint);
                    const right = results.slice(midpoint);

                    Unzer.components.TwoColumnLayout.updateLeft(
                        page,
                        left.map((x) =>
                            Unzer.components.PaymentMethodComponent.create({
                                description: x.description,
                                name: x.name,
                                state: x.enabled,
                                image: x.type ?? "",
                                onChange: (value) => {
                                    x.enabled = value;
                                    enablePaymentMethod(x.type, value, x.name);
                                },
                                onClick: () => getPaymentMethodConfig(x)
                            })
                        )
                    );

                    Unzer.components.TwoColumnLayout.updateRight(
                        page,
                        right.map((x) =>
                            Unzer.components.PaymentMethodComponent.create({
                                description: x.description,
                                name: x.name,
                                state: x.enabled,
                                image: x.type ?? "",
                                onChange: (value) => {
                                    x.enabled = value;
                                    enablePaymentMethod(x.type, value, x.name);
                                },
                                onClick: () => getPaymentMethodConfig(x)
                            })
                        )
                    );
                }
            }),
            Unzer.components.TwoColumnLayout.create(
                left.map((x) =>
                    Unzer.components.PaymentMethodComponent.create({
                        description: x.description,
                        name: x.name,
                        state: x.enabled,
                        image: x.type ?? "",
                        onChange: (value) => {
                            x.enabled = value;
                            enablePaymentMethod(x.type, value, x.name);
                        },
                        onClick: () => getPaymentMethodConfig(x)
                    })
                ),
                right.map((x) =>
                    Unzer.components.PaymentMethodComponent.create({
                        description: x.description,
                        name: x.name,
                        state: x.enabled,
                        image: x.type ?? "",
                        onChange: (value) => {
                            x.enabled = value;
                            enablePaymentMethod(x.type, value, x.name);
                        },
                        onClick: () => getPaymentMethodConfig(x)
                    })
                )
            )
        );
    };


    const getPaynmentMethodUrl = (key) => {
        return docsUrl[key] || key;
    }

    /**
     * Opens modal for payment method
     */
    const openSettingModal = (config, paymentMethod) => {
        if (!paymentMethod.enabled) {
            window.open(
                `https://docs.unzer.com/payment-methods/${getPaynmentMethodUrl(paymentMethod.type.toString().toLowerCase())}`,
                '_blank'
            ).focus();

            return;
        }

        const nameField = Unzer.components.TextDropdownComponent.create(
            {
                isIcon: true,
                value: "default",
                options: Unzer.config.locales?.map(x => ({ value: x.code, label: x.flag, title: x.name }))
            }, {
                maxWidth: false,
                value: paymentMethodConfig?.name?.find(x => x.locale == 'default')?.value ?? '',
                title: "checkout.fields.paymentMethodName.label",
                subtitle: "checkout.fields.paymentMethodName.description"
            },
            paymentMethodConfig?.name?.map(x => ({ locale: x.locale, value: x.value })),
            (value) => {
                paymentMethodConfig.name = value;
            },
            'unzer-text-dropdown-max-width',
            paymentMethodConfig?.name?.find(x => x.locale === 'default') ?? {
                locale: 'default',
                value: ''
            }
        );
        let descriptionField = Unzer.components.TextDropdownComponent.create(
            {
                isIcon: true,
                value: "default",
                options: Unzer.config.locales?.map(x => ({ value: x.code, label: x.flag, title: x.name }))
            }, {
                maxWidth: false,
                title: "checkout.fields.paymentMethodDescription.label",
                value: paymentMethodConfig?.description?.find(x => x.locale == 'default')?.value ?? '',
                subtitle: "checkout.fields.paymentMethodDescription.description"
            },
            paymentMethodConfig?.description?.map(x => ({ locale: x.locale, value: x.value })),
            (value) => {
                paymentMethodConfig.description = value;
            },
            'unzer-text-dropdown-max-width',
            paymentMethodConfig?.description?.find(x => x.locale === 'default') ?? {
                locale: 'default',
                value: ''
            }
        );

        let content = [
            nameField,
            descriptionField
        ];

        const bookingField = Unzer.components.DropdownField.create({
            title: 'checkout.modal.method',
            description: "checkout.modal.methodDescription",
            options: [
                { label: 'Charge', value: 'charge' },
                { label: 'Authorize', value: 'authorize' }
            ],
            value: paymentMethodConfig.bookingMethod,
            onChange: (value) => {
                paymentMethodConfig.bookingMethod = value
            }
        });

        if (config.bookingAvailable) {
            content.push(bookingField);
        }

        if (config.chargeAvailable && chargeStatusFlag) {
            content.push(Unzer.components.DropdownField.create({
                title: 'checkout.modal.chargeTitle',
                description: "checkout.modal.chargeDescription",
                onChange: (value) => {
                    paymentMethodConfig.statusIdToCharge = value
                },
                value: paymentMethodConfig.statusIdToCharge,
                options: orderStatuses.map(x => ({ label: x.name, value: x.id }))
            }))
        }
        const minMaxField = Unzer.components.MoneyInputField.create({
            minAmountTitle: 'checkout.modal.minAmount',
            maxAmountTitle: 'checkout.modal.maxAmount',
            value: {
                minAmount: paymentMethodConfig.minOrderAmount,
                maxAmount: paymentMethodConfig.maxOrderAmount
            },
            onChange: (value) => {
                paymentMethodConfig.minOrderAmount = value?.minAmount
                paymentMethodConfig.maxOrderAmount = value?.maxAmount
            }
        });

        const handleArrowClick = (step) => {

            let surcharge = surchargeField.querySelector(`[name=${'surcharge'}]`);
            let currentValue = parseFloat(surcharge.value) || 0;

            let input = Math.max(0, currentValue + step);
            surchargeField.querySelector(`[name=${'surcharge'}]`).value = JSON.stringify(input);
            handleSurchargeChange(input);
        }

        const handleSurchargeChange = (value) => {
            const numericValue = String(value).replace(/[^0-9.]/g, '');

            surchargeField.querySelector(`[name=${'surcharge'}]`).value = numericValue;

            paymentMethodConfig.surcharge = numericValue;
        };

        const surchargeField = Unzer.components.TextField.create({
            title: 'checkout.modal.surcharge',
            type: 'text',
            class: 'adl-text-input',
            name: 'surcharge',
            min: 0,
            value: paymentMethodConfig?.surcharge || 0,
            maxWidth: false,
            onChange: handleSurchargeChange
        });

        const { elementGenerator: generator } = Unzer;

        const descriptionSpan = generator.createElement(
            'span',
            'description',
            'checkout.modal.surchargeDescription',
            [],
            []
        )

        const surchargeFieldWrapper = generator.createElement('div', 'surcharge-wrapper', '', null, [
            surchargeField,
            descriptionSpan,
            generator.createElement('button', 'arrow-up', '', {
                type: 'button',
                onclick: () => {
                    handleArrowClick(1);
                }
            }),
            generator.createElement('button', 'arrow-down', '', {
                type: 'button',
                onclick: () => {
                    handleArrowClick(-1);
                }
            })
        ]);

        content.push(
            minMaxField,
            surchargeFieldWrapper,
            Unzer.components.MultiselectDropdownField.create({
                title: 'checkout.modal.restrictCountries',
                useAny: false,
                orientation: "top",
                options: countries.map(x => ({ value: x.code, label: x.name })),
                values: paymentMethodConfig.restrictedCountries?.map(x => x.code),
                description: "checkout.modal.restrictCountriesDescription",
                descriptionPositionUp: false,
                onChange: (values) => {
                    paymentMethodConfig.restrictedCountries = values;
                }
            })
        )

        if (config.displayClickToPay) {
            content.push(
                Unzer.components.ToggleField.create({
                    label: "checkout.modal.clicktopay",
                    description: "checkout.modal.clicktopayDescription",
                    value: paymentMethodConfig.enableClickToPay ?? false,
                    onChange: (value) => {
                        paymentMethodConfig.enableClickToPay = value;
                    }
                })
            )
        }
        if (config.displaySendBasketData) {
            content.push(
                Unzer.components.ToggleField.create({
                    label: "checkout.modal.basketData",
                    description: "checkout.modal.basketDataDescription",
                    value: paymentMethodConfig.sendBasketData ?? false,
                    onChange: (value) => {
                        paymentMethodConfig.sendBasketData = value;
                    }
                })
            )
        }


        const modal = Unzer.components.Modal.create({
            title: config.typeName,
            canClose: true,
            description: `paymentMethods.${config.type}|${getPaynmentMethodUrl(paymentMethod.type.toString().toLowerCase())}`,
            content: content,
            image: config.type,
            buttons: [
                {
                    type: 'ghost-black',
                    label: 'general.cancel',
                    onClick: () => modal.close()
                },
                {
                    type: 'secondary',
                    label: 'general.saveChanges',
                    onClick: () => {
                        let isValid = true;
                        isValid &= Unzer.validationService.validateField(
                            minMaxField,
                            paymentMethodConfig?.minOrderAmount != null && paymentMethodConfig.maxOrderAmount != null &&
                            parseInt(
                                paymentMethodConfig.minOrderAmount,
                                10
                            ) > parseInt(paymentMethodConfig.maxOrderAmount, 10),
                            'validation.minGreaterThanMax'
                        );
                        if (isValid) {
                            isValid &= Unzer.validationService.validateField(
                                minMaxField,
                                paymentMethodConfig?.minOrderAmount != null && paymentMethodConfig.maxOrderAmount != null &&
                                (parseInt(paymentMethodConfig.minOrderAmount, 10) < 0 || parseInt(
                                    paymentMethodConfig.maxOrderAmount,
                                    10
                                ) < 0),
                                'validation.greaterThanZero'
                            );
                        }
                        isValid &= Unzer.validationService.validateField(
                            surchargeField,
                            paymentMethodConfig?.surcharge != null &&
                            (parseInt(paymentMethodConfig?.surcharge, 10) < 0),
                            'validation.greaterThanZero'
                        );
                        isValid &= Unzer.validationService.validateField(
                            bookingField,
                            config.bookingAvailable && (paymentMethodConfig.bookingMethod?.length === 0 ||
                                !paymentMethodConfig.bookingMethod),
                            'validation.requiredField'
                        );
                        isValid && upsertPaymentMethodConfiguration(paymentMethod.type, modal);
                    }
                }
            ]
        });

        modal.open();
    };

    const upsertPaymentMethodConfiguration = (type, modal) => {
        Unzer.utilities.showLoader();

        Unzer.PaymentMethodService.upsert(type, paymentMethodConfig)
            .then(result => {
                Unzer.utilities.createToasterMessage('checkout.page.configurationSaved');
                modal.close();
            })
            .catch(error => {
                Unzer.utilities.createToasterMessage(error.message, true);
            })
            .finally(Unzer.utilities.hideLoader)
    }
};
