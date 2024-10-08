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
        return Unzer.ajaxService.get(`${baseUrl}/paymentMethods?storeId=${Unzer.config.store.id}`, (exception) => {
            return Promise.reject(exception);
        });
    };

    Unzer.PaymentMethodService = {
        getAll
    };
})();
