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
        return Unzer.ajaxService.post(`${baseUrl}/auth/login?storeId=${Unzer.config.store.id}`, data, null, (exception) => {
            return Promise.reject(exception);
        });
    };

    Unzer.LoginService = {
        login
    };
})();
