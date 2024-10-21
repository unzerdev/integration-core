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
        return Unzer.ajaxService.get(
            `${Unzer.config.paymentMethodsUrl}?storeId=${Unzer.config.store.storeId}`,
            (exception) => {
                return Promise.reject(exception);
            }
        );
    };


    /**
     * @param type {string }
     * @returns {Promise<*>}
     */
    const getConfiguration = (type) => {
        return Unzer.ajaxService.get(
            `${Unzer.config.paymentMethodsUrl}/${type}?storeId=${Unzer.config.store.storeId}`,
            (exception) => {
                return Promise.reject(exception);
            }
        );
    };

    /**
     * @param type {string }
     * @param enabled { boolean }
     * @returns {Promise<*>}
     */
    const enable = (type, enabled) => {
        return Unzer.ajaxService.post(
            `${Unzer.config.paymentMethodsUrl}/${type}/enable?storeId=${Unzer.config.store.storeId}`,
            {enabled: enabled},
            null,
            (exception) => {
                return Promise.reject(exception);
            }
        );
    };

    /**
     * @param type {string }
     * @param enabled { boolean }
     * @returns {Promise<*>}
     */
    const upsert = (type, data) => {
        return Unzer.ajaxService.post(
            `${Unzer.config.paymentMethodsUrl}/${type}?storeId=${Unzer.config.store.storeId}`,
            data,
            null,
            (exception) => {
                return Promise.reject(exception);
            }
        );
    };

    Unzer.PaymentMethodService = {
        getAll,
        getConfiguration,
        enable,
        upsert
    };
})();
