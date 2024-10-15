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
  const saveDesign = (data) => {
    return Unzer.ajaxService.post(
        `${baseUrl}/saveDesign?storeId=${Unzer.config.store.storeId}`,
        data,
        null,
        (exception) => {
          return Promise.reject(exception);
        }
    );
  };

  Unzer.DesignService = {
    saveDesign
  };
})();