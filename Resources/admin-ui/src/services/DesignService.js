(() => {
  /**
   * @type {string}
   */
  const baseUrl = Unzer.config.apiUrl;

  /**
   * @returns {Promise<*>}
   */
  const saveDesign = (data) => {
    return Unzer.ajaxService.post(
        `${baseUrl}/design/saveDesign?storeId=${Unzer.config.store.storeId}`,
        data,
        {'Content-Type': 'multipart/form-data'},
        (exception) => {
          return Promise.reject(exception);
        }
    );
  };

  /**
   * @returns {Promise | Promise<unknown>}
   */

  const getDesign = () => {
    return Unzer.ajaxService.get(
        `${baseUrl}/design/getDesign?storeId=${Unzer.config.store.storeId}`,
        (exception) => {
          return Promise.reject(exception);
        }
    );
  };

  /**
   * @returns {Promise<*>}
   */
  const createPreviewPage = (data) => {
    return Unzer.ajaxService.post(
        `${baseUrl}/design/createPreviewPage?storeId=${Unzer.config.store.storeId}`,
        data,
        {'Content-Type': 'multipart/form-data'},
        (exception) => {
          return Promise.reject(exception);
        }
    );
  };

  Unzer.DesignService = {
    saveDesign,
    getDesign,
    createPreviewPage
  };
})();