(() => {
    const UtilityService = () => {
        let loaderCount = 0;
        /** @type {HTMLElement} */
        let loader;

        /**
         * Shows the HTML node.
         *
         * @param {HTMLElement} element
         */
        const showElement = (element) => {
            element?.classList.remove('adls--hidden');
        };

        /**
         * Hides the HTML node.
         *
         * @param {HTMLElement} element
         */
        const hideElement = (element) => {
            element?.classList.add('adls--hidden');
        };

        /**
         * Enables loading spinner.
         */
        const showLoader = () => {
            if (loaderCount === 0) {
                loader = Unzer.elementGenerator.createLoader({ type: 'large', fullPage: true });
                document.body.append(loader);
            }

            loaderCount++;
        };

        /**
         * Hides loading spinner.
         */
        const hideLoader = () => {
            loaderCount--;
            if (loaderCount === 0) {
                loader.remove();
            }
        };

        /**
         * Shows flash message.
         *
         * @note Only one flash message will be shown at the same time.
         *
         * @param {string} message
         * @param {'error' | 'warning' | 'success'} status
         * @param {number?} clearAfter Time in ms to remove alert message.
         */
        const createFlashMessage = (message, status, clearAfter) => {
            return Unzer.elementGenerator.createFlashMessage(message, status, clearAfter);
        };

        /**
         * Creates the 401 error flash message.
         *
         * @param {string} message
         */
        const create401FlashMessage = (message) => {
            remove401Message();
            const messageElement = Unzer.elementGenerator.createFlashMessage(message, 'error');
            messageElement.classList.add('adlv--401-error');
            Unzer.pageService.getHeaderSection().append(messageElement);
        };

        /**
         * Removes the 401 flash message.
         */
        const remove401Message = () => {
            Unzer.pageService
                .getHeaderSection()
                .querySelectorAll(`.adlv--401-error`)
                .forEach((e) => e.remove());
        };

        /**
         * Creates a toaster message.
         *
         * @param {string} message A message translation key.
         * @param {boolean} error
         */
        const createToasterMessage = (message, error= false) => {
            document.getElementById('adl-page').append(Unzer.elementGenerator.createToaster(message, error));
        };

        /**
         * Updates a form's footer state based on the number of changes.
         *
         * @param {number} numberOfChanges
         * @param {boolean} disableCancel
         */
        const renderFooterState = (numberOfChanges, disableCancel = true) => {
            const cancel = document.querySelector('.adl-form-footer .unzer-actions .unzer-cancel');
            if (numberOfChanges) {
                document.querySelector('.adl-form-footer .unzer-changes-count')?.classList.add('adls--active');
                document.querySelector('.adl-form-footer .unzer-actions .unzer-save').disabled = false;
                cancel && (cancel.disabled = false);
            } else {
                document.querySelector('.adl-form-footer .unzer-changes-count')?.classList.remove('adls--active');
                document.querySelector('.adl-form-footer .unzer-actions .unzer-save').disabled = true;
                cancel && (cancel.disabled = disableCancel);
            }
        };

        /**
         * Creates deep clone of an object with object's properties.
         * Removes object's methods.
         *
         * @note Object cannot have values that cannot be converted to json (undefined, infinity etc).
         *
         * @param {object} obj
         * @return {object}
         */
        const cloneObject = (obj) => JSON.parse(JSON.stringify(obj || {}));

        /**
         * Gets the first ancestor element with the corresponding class name.
         *
         * @param {HTMLElement} element
         * @param {string} className
         * @return {HTMLElement}
         */
        const getAncestor = (element, className) => {
            let parent = element?.parentElement;

            while (parent) {
                if (parent.classList.contains(className)) {
                    break;
                }

                parent = parent.parentElement;
            }

            return parent;
        };

        /**
         * Calls the callback function if a user clicks outside provided element.
         *
         * @param {HTMLElement} element
         * @param {(target: HTMLElement) => any} handler
         */
        const onClickOutside = (element, handler) => {
            document.documentElement.addEventListener('click', (event) => {
                if (!element.contains(event.target) && event.target !== element) {
                    handler(event.target);
                }
            });
        };

        /**
         * @param {Date | null} value
         * @param {boolean} withTime
         * @return {string}
         */
        const formatDate = (value, withTime = true) => {
            if (!value || Number.isNaN(value.getTime()) || value.getTime() === 0) {
                return '/';
            }

            const date = new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }).format(value);

            if (withTime) {
                const time = new Intl.DateTimeFormat('en-US', {
                    hour: 'numeric',
                    minute: 'numeric',
                    second: 'numeric',
                    hour12: false
                }).format(value);

                return `${date} ${Unzer.translationService.translate('general.at')} ${time}`;
            }

            return date;
        };

        return {
            showLoader,
            hideLoader,
            showElement,
            hideElement,
            create401FlashMessage,
            remove401Message,
            createFlashMessage,
            createToasterMessage,
            renderFooterState,
            cloneObject,
            getAncestor,
            onClickOutside,
            formatDate
        };
    };

    Unzer.utilities = UtilityService();
})();
