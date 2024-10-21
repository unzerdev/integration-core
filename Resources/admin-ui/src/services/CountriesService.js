(() => {
    
    /**
     *
     * @returns {Promise<*>}
     */
    const get = () => {
        return Unzer.ajaxService.get(`${Unzer.config.countryUrl}?storeId=${Unzer.config.store.storeId}`, (exception) => {
            return Promise.reject(exception);
        });
    };

    Unzer.CountriesService = {
        get
    };
})();
