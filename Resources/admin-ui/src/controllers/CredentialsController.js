Unzer.CredentialsController = function () {
    /**
     * @type {HTMLElement}
     */
    const page = Unzer.pageService.getContentPage();

    /**
     * @type {{privateKey: string, environment, publicKey: string}}
     */
    const values = {
        environment: Unzer.config.store.mode,
        privateKey: Unzer.connectionData.privateKey,
        publicKey: Unzer.connectionData.publicKey
    };

    let openEnvModal = false;

    /**
     * @type {{webhookID: string, registrationDate: string, events: string, webhookUrl: string}}
     */
    let webhookData = {
        registrationDate: '',
        webhookID: '',
        events: '',
        webhookUrl: '',
    }

    /**
     * @type {{privateKey: string, publicKey: string}}
     */
    let connectionData = {
        publicKey: '',
        privateKey: '',
    }

    /**
     * renders credentials page
     */
    this.display = () => {
        if (!Unzer.config.store.isLoggedIn) {
            Unzer.stateController.navigate('login');

            return;
        }

        getData();
    };

    /**
     * Gets credentials data
     */

    const getData = () => {
        Unzer.utilities.showLoader();

        Unzer.LoginService
            .getCredentialsData()
            .then((result) => {

                if (result.webhookData) {
                    webhookData = result.webhookData;
                }

                if (result.connectionData) {
                    connectionData = result.connectionData;
                }
                render();
            })
            .catch((ex) => {
                Unzer.utilities.createToasterMessage(ex.errorMessage, true);
            })
            .finally(Unzer.utilities.hideLoader);
    }

    const render = () => {
        Unzer.pageService.clearComponent(page);
        page.append(
            Unzer.components.PageHeading.create({
                title: 'credentials.title',
                description: 'credentials.description',
                button: Unzer.components.Button.createList([
                    {
                        label: 'credentials.disconnect',
                        type: 'ghost',
                        onClick: () => {
                            openDisconnectModal();
                        }
                    },
                    {
                        label: 'credentials.saveChanges',
                        type: 'secondary',
                        onClick: () => {
                            if (openEnvModal) {
                                openSaveChangesModal();

                                return;
                            }

                            saveChanges()
                        }
                    }
                ])
            }),
            Unzer.components.RadioButtonGroupField.create({
                label: 'login.environment.title',
                options: [
                    {label: 'login.environment.live', value: 'live'},
                    {label: 'login.environment.sandbox', value: 'sandbox'}
                ],
                onChange: (value) => {
                    openEnvModal = value !== Unzer.config.store.mode;
                    values.environment = value;
                    Unzer.components.PageHeader.updateEnvironment(value === "sandbox")
                },
                value: Unzer.config.store.mode
            }),
            Unzer.components.TextField.create({
                label: 'login.credentials.title',
                title: 'login.credentials.public',
                fieldClasses: 'unzer-input-wrapper-padding',
                description: 'login.credentials.description',
                value: connectionData.publicKey,
                onChange: (value) => {
                    connectionData.publicKey = value;
                }
            }),
            Unzer.components.TextField.create({
                title: 'login.credentials.private',
                type: 'password',
                fieldClasses: 'unzer-input-wrapper-padding',
                value: connectionData.privateKey,
                onChange: (value) => {
                    connectionData.privateKey = value;
                }
            }),
            Unzer.components.TextField.create({
                label: 'credentials.notifications',
                description: 'credentials.notificationsDescription',
                disabled: true,
                title: 'credentials.registrationDate',
                fieldClasses: 'unzer-input-wrapper-padding',
                value: webhookData.registrationDate
            }),
            Unzer.components.TextField.create({
                title: 'credentials.webhook',
                disabled: true,
                fieldClasses: 'unzer-input-wrapper-padding',
                value: webhookData.webhookID
            }),
            Unzer.components.TextField.create({
                title: 'credentials.webhookUrl',
                disabled: true,
                fieldClasses: 'unzer-input-wrapper-padding',
                value: webhookData.webhookUrl
            }),
            Unzer.components.TextField.create({
                title: 'credentials.event',
                disabled: true,
                fieldClasses: 'unzer-input-wrapper-padding',
                value: webhookData.events
            }),
            Unzer.components.Button.create({
                type: 'primary',
                label: 'credentials.reRegister',
                onClick: reRegisterWebhooks
            })
        );
    }

    const openDisconnectModal = () => {

        const modal = Unzer.components.Modal.create({
            title: 'general.disconnect',
            canClose: true,
            description: '',
            content: [
                Unzer.components.PageHeading.create({
                    title: "credentials.disconnectWarning"
                })
            ],
            buttons: [
                {
                    type: 'ghost-black',
                    label: 'general.cancel',
                    onClick: () => modal.close()
                },
                {
                    type: 'secondary',
                    label: 'general.disconnect',
                    onClick: () => disconnect(modal)
                }
            ]
        });

        modal.open();
    };

    const openSaveChangesModal = () => {

        const modal = Unzer.components.Modal.create({
            title: 'general.saveChanges',
            canClose: true,
            description: '',
            paymentMethod: true,
            content: [
                Unzer.components.PageHeading.create({
                    title: "credentials.saveChangesWarning"
                })
            ],
            buttons: [
                {
                    type: 'ghost-black',
                    label: 'general.no',
                    onClick: () => saveChanges(modal)
                },
                {
                    type: 'secondary',
                    label: 'general.yes',
                    onClick: () => modal.close()
                }
            ]
        });

        modal.open();
    };


    /**
     * Disconnect store
     *
     * @param modal
     */

    function disconnect(modal) {
        Unzer.utilities.showLoader();
        Unzer.LoginService.disconnect()
            .then(() => {
                modal.close();
                Unzer.stateController.navigate('login', {}, true);
                Unzer.components.PageHeader.updateCredentials();
            })
            .catch((ex) => {
                Unzer.utilities.createToasterMessage(ex.errorMessage, true);
            })
            .finally(Unzer.utilities.hideLoader);
    }


    /**
     * ReRegister webhooks
     *
     */

    function reRegisterWebhooks() {
        Unzer.utilities.showLoader();
        Unzer.LoginService.reRegisterWebhooks()
            .then((response) => {
                if (response.webhookData) {
                    webhookData = response.webhookData;
                }
                render();
            })
            .catch((ex) => {
                Unzer.utilities.createToasterMessage(ex.errorMessage, true);
            })
            .finally(Unzer.utilities.hideLoader);
    }

    /**
     * @param modal
     */
    function saveChanges(modal) {
        Unzer.utilities.showLoader();
        console.log(values);

        Unzer.LoginService.login(values)
            .then(() => {
                console.log(values);
            })
            .catch((ex) => {
                Unzer.utilities.createToasterMessage(ex.errorMessage, true);
            })
            .finally(() => {
                Unzer.utilities.hideLoader();
                if (modal) {
                    modal.close();
                }
            });
    }
};
