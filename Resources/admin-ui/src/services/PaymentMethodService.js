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
    const getAll = () => {
        return Unzer.ajaxService.get(`${baseUrl}/paymentMethod?storeId=${Unzer.config.store.storeId}`, (exception) => {
            return Promise.reject(exception);
        });
    };

    Unzer.PaymentMethodService = {
        getAll
    };
})();
