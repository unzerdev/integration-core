(() => {
    /**
     * The ResponseService constructor.
     *
     * @constructor
     */
    function ResponseService() {
        /**
         * Handles an error response from the submit action.
         *
         * @param {{error?: string, errorCode?: string, status?: number}} response
         * @returns {Promise<void>}
         */
        this.errorHandler = (response) => {
            if (response.status !== 401) {
                const { utilities, pageService, elementGenerator } = Unzer;
                let container = document.querySelector('.unzer-flash-message-wrapper');
                if (!container) {
                    container = elementGenerator.createElement('div', 'unzer-flash-message-wrapper');
                    pageService.getContentPage().prepend(container);
                }

                pageService.clearComponent(container);

                if (response.error) {
                    container.prepend(utilities.createFlashMessage(response.error, 'error'));
                } else if (response.errorCode) {
                    container.prepend(utilities.createFlashMessage('general.errors.' + response.errorCode, 'error'));
                } else {
                    container.prepend(utilities.createFlashMessage('general.errors.unknown', 'error'));
                }
            }

            return Promise.reject(response);
        };

        /**
         * Handles 401 response.
         *
         * @param {{error?: string, errorCode?: string}} response
         * @returns {Promise<void>}
         */
        this.unauthorizedHandler = (response) => {
            Unzer.utilities.create401FlashMessage(`general.errors.${response.errorCode}`);
            // TODO redirect to the proper page for 401
            Unzer.stateController.navigate('/login', null, true);

            return Promise.reject({ ...response, status: 401 });
        };
    }

    Unzer.responseService = new ResponseService();
})();
