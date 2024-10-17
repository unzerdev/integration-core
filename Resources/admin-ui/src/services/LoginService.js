(() => {
  /**
   *
   * @type {string}
   */
  const baseUrl = Unzer.config.apiUrl;

  /**
   *
   * @returns {Promise<*>}
   */
  const login = (data) => {
    return Unzer.ajaxService.post(
        `${Unzer.config.connectionUrl}?storeId=${Unzer.config.store.storeId}`,
        data,
        null,
        (exception) => {
          return Promise.reject(exception);
        }
    );
  };

  /**
   *
   * @returns {Promise<*>}
   */
  const disconnect = () => {
    return Unzer.ajaxService.delete(
        `${Unzer.config.credentialUrl}/disconnect?storeId=${Unzer.config.store.storeId}`,
        {},
        null,
        (exception) => {
          return Promise.reject(exception);
        }
    );
  };

  /**
   * @returns {Promise<*>}
   */
  const reRegisterWebhooks = () => {
    return Unzer.ajaxService.post(
        `${Unzer.config.credentialUrl}/reregister?storeId=${Unzer.config.store.storeId}`,
        {},
        null,
        (exception) => {
          return Promise.reject(exception);
        }
    );
  };


  /**
   * @returns {Promise<*>}
   */

  const getCredentialsData = () => {
    return Unzer.ajaxService.get(
        `${Unzer.config.credentialUrl}/getData?storeId=${Unzer.config.store.storeId}`,
        (exception) => {
          return Promise.reject(exception);
        }
    );
  };

  Unzer.LoginService = {
    login,
    disconnect,
    reRegisterWebhooks,
    getCredentialsData
  };
})();
