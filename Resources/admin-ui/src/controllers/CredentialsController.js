﻿Unzer.CredentialsController = function () {
  /**
   *
   * @type {HTMLElement}
   */
  const page = Unzer.pageService.getContentPage();

  const values = {
    environment: Unzer.config.store.mode,
    privateKey: Unzer.connectionData.privateKey,
    publicKey: Unzer.connectionData.publicKey
  };

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
              type: 'secondary',
              onClick: saveChanges
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
          value: values.publicKey,
          onChange: (value) => {
            values.publicKey = value;
          }
        }),
        Unzer.components.TextField.create({
          title: 'login.credentials.private',
          type: 'password',
          fieldClasses: 'unzer-input-wrapper-padding',
          value: values.privateKey,
          onChange: (value) => {
            values.privateKey = value;
          }
        }),
        Unzer.components.TextField.create({
          label: 'credentials.notifications',
          description: 'credentials.notificationsDescription',
          disabled: true,
          title: 'credentials.registrationDate',
          fieldClasses: 'unzer-input-wrapper-padding',
          value: Unzer.webhookData.registrationDate
        }),
        Unzer.components.TextField.create({
          title: 'credentials.webhook',
          disabled: true,
          fieldClasses: 'unzer-input-wrapper-padding',
          value: Unzer.webhookData.webhookID
        }),
        Unzer.components.TextField.create({
          title: 'credentials.webhookUrl',
          disabled: true,
          fieldClasses: 'unzer-input-wrapper-padding',
          value: Unzer.webhookData.webhookUrl
        }),
        Unzer.components.TextField.create({
          title: 'credentials.event',
          disabled: true,
          fieldClasses: 'unzer-input-wrapper-padding',
          value: Unzer.webhookData.events
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
   * @param loginData
   */

  function reRegisterWebhooks() {
    Unzer.utilities.showLoader();
    Unzer.LoginService.reRegisterWebhooks()
        .then(() => {
          Unzer.stateController.navigate('credentials', {}, true);
        })
        .catch((ex) => {
          Unzer.utilities.createToasterMessage(ex.errorMessage, true);
        })
        .finally(Unzer.utilities.hideLoader);
  }

  /**
   * Connect store
   * @param loginData
   */
  function saveChanges() {
    Unzer.utilities.showLoader();
    console.log(values);

    Unzer.LoginService.login(values)
        .then(() => {
          console.log(values);
        })
        .catch((ex) => {

        })
        .finally(Unzer.utilities.hideLoader);
  }
};
