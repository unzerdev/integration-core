(() => {
    /**
     *
     * @type {string}
     */
    const baseUrl = Unzer.config.storeUrl;

    /**
     *
     * @returns {Promise<*>}
     */
    const getOrderStatuses = () => {
        return Unzer.ajaxService.get(`${baseUrl}`, (exception) => {
            return Promise.reject(exception);
        });
    };

    Unzer.StoresService = {
        getOrderStatuses
    };
})();
