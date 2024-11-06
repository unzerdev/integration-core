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
    privateKey: '',
    publicKey: '',
    deleteConfig: false
  };

  let openEnvModal = false;

  /**
   * @type {{webhookID: string, registrationDate: string, events: string, webhookUrl: string}}
   */

  let liveData = {
    webhookData: {
      exists: false,
      registrationDate: '',
      webhookID: '',
      events: '',
      webhookUrl: ''
    },
    connectionData: {
      publicKey: '',
      privateKey: '',
    }
  };


  let sandboxData = {
    webhookData: {
      exists: false,
      registrationDate: '',
      webhookID: '',
      events: '',
      webhookUrl: ''
    },
    connectionData: {
      publicKey: '',
      privateKey: '',
    }
  };

  let environmentValues = values.environment === 'live' ? liveData : sandboxData;

  /**
   * renders credentials page
   */
  this.display = () => {
    if (!Unzer.config.store.isLoggedIn) {
      Unzer.stateController.navigate('login');

      return;
    }

    Unzer.components.PageHeader.updateEnvironment(false);

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

          if (result.live) {

            liveData.webhookData = result.live.webhookData ? { ...result.live.webhookData, exists: true } : liveData.webhookData;
            liveData.connectionData = result.live.connectionData || liveData.connectionData;
          }

          if (result.sandbox) {

            sandboxData.webhookData = result.sandbox.webhookData ? { ...result.sandbox.webhookData, exists: true } : sandboxData.webhookData;
            sandboxData.connectionData = result.sandbox.connectionData || sandboxData.connectionData;
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
            { label: 'login.environment.live', value: 'live' },
            { label: 'login.environment.sandbox', value: 'sandbox' }
          ],
          onChange: (value) => {
            openEnvModal = value !== Unzer.config.store.mode;
            values.environment = value;
            environmentValues = values.environment === 'live' ? liveData : sandboxData;
            render();
          },
          value: values.environment
        }),
        Unzer.components.TextField.create({
          label: 'login.credentials.title',
          title: 'login.credentials.public',
          fieldClasses: 'unzer-input-wrapper-padding',
          description: 'login.credentials.description',
          value: environmentValues.connectionData.publicKey,
          onChange: (value) => {
            environmentValues.connectionData.publicKey = value;
          }
        }),
        Unzer.components.TextField.create({
          title: 'login.credentials.private',
          type: 'password',
          fieldClasses: 'unzer-input-wrapper-padding',
          value: environmentValues.connectionData.privateKey,
          onChange: (value) => {
            environmentValues.connectionData.privateKey = value;
          }
        })
    );

    let notifications = Unzer.components.TextField.create({
      label: 'credentials.notifications',
      description: 'credentials.notificationsDescription',
      disabled: true,
      title: 'credentials.registrationDate',
      fieldClasses: 'unzer-input-wrapper-padding',
      value: environmentValues.webhookData.registrationDate
    });
    let webhookId = Unzer.components.TextField.create({
          title: 'credentials.webhook',
          disabled: true,
          fieldClasses: 'unzer-input-wrapper-padding',
          value: environmentValues.webhookData.webhookID
        });
    let webhookUrl =
        Unzer.components.TextField.create({
          title: 'credentials.webhookUrl',
          disabled: true,
          fieldClasses: 'unzer-input-wrapper-padding',
          value: environmentValues.webhookData.webhookUrl
        });
    let events =
        Unzer.components.TextField.create({
          title: 'credentials.event',
          disabled: true,
          fieldClasses: 'unzer-input-wrapper-padding',
          value: environmentValues.webhookData.events
        });

    let reregisterButton = Unzer.components.Button.create({
          type: 'primary',
          label: 'credentials.reRegister',
          onClick: reRegisterWebhooks
        });

    let webhookWrapper = Unzer.elementGenerator.createElement('div', 'webhook-wrapper', '', null, [
      notifications, webhookId, webhookUrl, events, reregisterButton
    ]);

    webhookWrapper.style.visibility = environmentValues.webhookData.exists ? 'visible' : 'hidden';

    page.append(webhookWrapper);
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
          onClick: () => {
            values.deleteConfig = true;
            saveChanges(modal)
          }
        },
        {
          type: 'secondary',
          label: 'general.yes',
          onClick: () => {
            values.deleteConfig = false;
            saveChanges(modal)
          }
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

    value = {
      environment : values.environment
    }
    Unzer.LoginService.reRegisterWebhooks(value)
        .then((response) => {

          console.log(response);
          let success = false;
          if(values.environment === "live"){
            liveData.webhookData = response.live.webhookData ? { ...response.live.webhookData, exists: true } : liveData.webhookData;
            Unzer.utilities.createToasterMessage('credentials.webhookRegistered', false);
            success = true;
          }

          if(values.environment === "sandbox"){
            sandboxData.webhookData = response.sandbox.webhookData ? { ...response.sandbox.webhookData, exists: true } : sandboxData.webhookData;
            Unzer.utilities.createToasterMessage('credentials.webhookRegistered', false);
            success = true;
          }

          if(!success) {
            Unzer.utilities.createToasterMessage('credentials.webhookUnsuccessful', true);
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

    values.privateKey = environmentValues.connectionData.privateKey;
    values.publicKey = environmentValues.connectionData.publicKey;
    Unzer.LoginService.reconnect(values)
        .then(() => {
          getData();
          Unzer.utilities.createToasterMessage("general.changesSaved", false);
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
