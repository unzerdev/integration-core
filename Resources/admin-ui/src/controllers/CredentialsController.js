Unzer.CredentialsController = function () {
    /**
     *
     * @type {HTMLElement}
     */
    const page = Unzer.pageService.getContentPage();

    /**
     * renders credentials page
     * @param {StateParamsModel} params
     */
    this.display = (params) => {
        if (!Unzer.config.store.isLoggedIn) {
            Unzer.stateController.navigate('login');

            return;
        }

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
                        type: 'secondary'
                    }
                ])
            }),
            Unzer.components.RadioButtonGroupField.create({
                label: 'login.environment.title',
                options: [
                    { label: 'login.environment.live', value: 'live' },
                    { label: 'login.environment.sandbox', value: 'sandbox' }
                ],
                onChange: (value) => {
                  Unzer.components.PageHeader.updateEnvironment(value === "sandbox")
                },
                value: 'live'
            }),
            Unzer.components.TextField.create({
                label: 'login.credentials.title',
                title: 'login.credentials.public',
                fieldClasses: 'unzer-input-wrapper-padding',
                description: 'login.credentials.description'
            }),
            Unzer.components.TextField.create({
                title: 'login.credentials.private',
                type: 'password',
                fieldClasses: 'unzer-input-wrapper-padding'
            }),
            Unzer.components.TextField.create({
                label: 'credentials.notifications',
                description: 'credentials.notificationsDescription',
                disabled: true,
                title: 'credentials.registrationDate',
                fieldClasses: 'unzer-input-wrapper-padding',
                value: 'May 20, 2024 13:00'
            }),
            Unzer.components.TextField.create({
                title: 'credentials.webhook',
                disabled: true,
                fieldClasses: 'unzer-input-wrapper-padding',
                value: 's-whk-1'
            }),
            Unzer.components.TextField.create({
                title: 'credentials.webhookUrl',
                disabled: true,
                fieldClasses: 'unzer-input-wrapper-padding',
                value: 'https://google.com'
            }),
            Unzer.components.TextField.create({
                title: 'credentials.event',
                disabled: true,
                fieldClasses: 'unzer-input-wrapper-padding',
                value: 'All'
            }),
            Unzer.components.Button.create({
                type: 'primary',
                label: 'credentials.reRegister',
                onClick: reRegisterWebhooks
            })
        );
    };

    const openDisconnectModal = () => {

        const modal = Unzer.components.Modal.create({
            title: 'Disconnect',
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
                    label: 'Cancel',
                    onClick: () => modal.close()
                },
                {
                    type: 'secondary',
                    label: 'Disconnect',
                    onClick: () => disconnect(modal)
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
          Unzer.stateController.navigate('login');
        })
        .catch((ex) => {
          Unzer.utilities.createToasterMessage(ex.errorMessage, true);
        })
        .finally(Unzer.utilities.hideLoader);
    }


  /**
   * ReRegister webhooks
   * @param loginData
   */

  function reRegisterWebhooks() {
    Unzer.utilities.showLoader();
    Unzer.LoginService.reRegisterWebhooks()
        .then(() => {
        })
        .catch((ex) => {
          Unzer.utilities.createToasterMessage(ex.errorMessage, true);
        })
        .finally(Unzer.utilities.hideLoader);
  }
};
