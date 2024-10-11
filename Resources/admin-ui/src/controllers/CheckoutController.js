Unzer.CheckoutController = function () {
    /**
     *
     * @type {HTMLElement}
     */
    const page = Unzer.pageService.getContentPage();

    /**
     * @type {[{
     *     @property {string?} name
     *     @property {string?} description
     *     @property {string?} image
     *     @property {boolean} state
     * }]}
     */
    let paymentMethods = [];

    let icons = [
        {
            language: '',
            icon: Unzer.imagesProvider.languageIcon
        }
    ];

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

    //todo: this is language icon retrieval
    // Unzer.utilities.showLoader();
    //
    // Unzer.CheckoutService.icons()
    //     .then((result) => {
    //         icons = [...icons, ...result];
    //         render();
    //     })
    //     .catch((ex) => Unzer.utilities.createToasterMessage(ex.message, true))
    //     .finally(Unzer.utilities.hideLoader);

    const getData = () => {
        Unzer.utilities.showLoader();

        Unzer.PaymentMethodService.getAll()
            .then((result) => {
                paymentMethods = result;
                render();
            })
            .catch((ex) => {
                Unzer.utilities.createToasterMessage(ex.message, true);
            })
            .finally(Unzer.utilities.hideLoader);

        render();
    };

    const render = () => {
        Unzer.pageService.clearComponent(page);

        const buttons = Unzer.components.Button.createList([
            {
                type: 'primary',
                label: 'Paymnent page settings',
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
                                description: x.subtitle,
                                name: x.name,
                                state: x.isEnabled,
                                image: x.image,
                                onClick: () => openSettingModal(x)
                            })
                        )
                    );

                    Unzer.components.TwoColumnLayout.updateRight(
                        page,
                        right.map((x) =>
                            Unzer.components.PaymentMethodComponent.create({
                                description: x.subtitle,
                                name: x.name,
                                state: x.isEnabled,
                                image: x.image,
                                onClick: () => openSettingModal(x)
                            })
                        )
                    );
                }
            }),
            Unzer.components.TwoColumnLayout.create(
                left.map((x) =>
                    Unzer.components.PaymentMethodComponent.create({
                        description: x.subtitle,
                        name: x.name,
                        state: x.isEnabled,
                        image: x.image,
                        onChange: () => {},
                        onClick: () => openSettingModal(x)
                    })
                ),
                right.map((x) =>
                    Unzer.components.PaymentMethodComponent.create({
                        description: x.subtitle,
                        name: x.name,
                        state: x.isEnabled,
                        image: x.image,
                        onChange: () => {},
                        onClick: () => openSettingModal(x)
                    })
                )
            )
        );
    };

    /**
     * Opens modal for payment method
     */
    const openSettingModal = (paymentMethod) => {
        if (!paymentMethod.isEnabled) {
           window.location.replace(`https://www.unzer.com/en/${paymentMethod.name}`);

           return;
        }

        const modal = Unzer.components.Modal.create({
            title: 'Title',
            canClose: true,
            fullHeight: true,
            description:
                'With instalment payments from Unzer, you gain more financial freedom and incentivise your customers to buy. Paying in instalments is a particuarly popular payment method for more expensive purchases. You can increase your average basket sizes and conversion rate – with no risk. ' +
                '<a href="">Learn more</a>',
            content: [
                Unzer.components.TextDropdownComponent.create({
                    isIcon: true,
                    value: "default",
                    options: [{ label: Unzer.imagesProvider.languageIcon, value: "default" }]
                }, {
                    maxWidth: false,
                    title: "Payment method name",
                    subtitle: "Payment method name on the checkout."
                },
                    'unzer-text-dropdown-max-width'),
                Unzer.components.TextDropdownComponent.create({
                    isIcon: true,
                    value: "default",
                    options: [{ label: Unzer.imagesProvider.languageIcon, value: "default" }]
                }, {
                    maxWidth: false,
                    title: "Payment method description",
                    subtitle: "Payment method description on the checkout."
                },
                    'unzer-text-dropdown-max-width'),
                Unzer.components.DropdownField.create({
                    title: 'checkout.modal.method',
                    description: "checkout.modal.methodDescription",
                    options: [
                        { label: 'Capture', value: 'capture' },
                        { label: 'Authorize', value: 'authorize' }
                    ]
                }),
                Unzer.components.DropdownField.create({
                    title: 'Charge on status changed',
                    description: "Select the status that will trigger the payment capture.",
                    options: [
                        { label: 'Capture', value: 'capture' },
                        { label: 'Authorize', value: 'authorize' }
                    ]
                }),
                Unzer.components.MoneyInputField.create({
                    minAmountTitle: 'checkout.modal.minAmount',
                    maxAmountTitle: 'checkout.modal.maxAmount'
                }),
                Unzer.components.TextField.create({
                    title: 'checkout.modal.surcharge',
                    type: 'number',
                    maxWidth: false
                }),
                Unzer.components.MultiselectDropdownField.create({
                    title: 'checkout.modal.restrictCountries',
                    useAny: false,
                    values: [],
                    options: [
                        { label: 'Capture', value: 'capture' },
                        { label: 'Authorize', value: 'authorize' }
                    ]
                })
            ],
            buttons: [
                {
                    type: 'ghost-black',
                    label: 'Cancel',
                    onClick: () => modal.close()
                },
                {
                    type: 'secondary',
                    label: 'general.saveChanges',
                    onClick: () => {}
                }
            ]
        });

        modal.open();
    };
};
