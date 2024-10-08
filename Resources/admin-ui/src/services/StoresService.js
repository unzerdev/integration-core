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
    const get = () => {
        return Unzer.ajaxService.get(`${baseUrl}/stores`, (exception) => {
            return Promise.reject(exception);
        });
    };

    Unzer.StoresService = {
        get
    };
})();
