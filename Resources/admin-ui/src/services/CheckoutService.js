(() => {
    /**
     *
     * @type {string}
     */
    const baseUrl = `${Unzer.config.apiUrl}/checkoutSettings`;

    /**
     *
     * @returns {Promise<*>}
     */
    const icons = () => {
        return Unzer.ajaxService.get(`${baseUrl}/icons`, (exception) => {
            return Promise.reject(exception);
        });
    };

    Unzer.CheckoutService = {
        icons
    };
})();
