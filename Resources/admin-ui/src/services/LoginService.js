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
        return Unzer.ajaxService.post(`${Unzer.config.connectionUrl}?storeId=${Unzer.config.store.storeId}`, data, null, (exception) => {
            return Promise.reject(exception);
        });
    };

    Unzer.LoginService = {
        login
    };
})();
