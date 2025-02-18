(() => {
    let callValidationState = '';

    /**
     * Handles the server response.
     * @param {Response} response
     * @param {(error: Record<string, any>) => Promise<any>} errorCallback
     * @returns {Record<string, any>}
     */
    const handleResponse = (response, errorCallback) => {
        if (!errorCallback) {
            errorCallback = Unzer.responseService.errorHandler;
        }

        try {
            if (response.ok) {
                return response.json();
            }

            if (response.status === 401) {
                // reset the state so all requests should become obsolete
                callValidationState = Math.random().toString(36);

                return response.json().then(Unzer.responseService.unauthorizedHandler).catch(errorCallback);
            }

            if (response.status === 400) {
                return response.json().then(errorCallback);
            }
        } catch (e) {}

        return errorCallback({ status: response.status, error: response.statusMessage });
    };

    /**
     * Performs GET ajax request.
     *
     * @param {string} url The URL to call.
     * @param {(error: Record<string, any>) => Promise<void>?} errorCallback
     * @param {boolean?} [fallthrough=false]
     * @returns {Promise<any>}
     */
    const get = (url, errorCallback, fallthrough = false) =>
        call({
            method: 'GET',
            url,
            errorCallback,
            fallthrough
        });

    /**
     * Performs POST ajax request.
     *
     * @param {string} url The URL to call.
     * @param {Record<string, any>?} data
     * @param {Record<string, string>?} customHeader
     * @param {(error: Record<string, any>) => Promise<void>?} errorCallback
     */
    const post = (url, data, customHeader, errorCallback) =>
        call({
            method: 'POST',
            url,
            data,
            errorCallback,
            customHeader
        });

    /**
     * Performs PUT ajax request.
     *
     * @param {string} url The URL to call.
     * @param {Record<string, any>} data
     * @param {Record<string, string>?} customHeader
     * @param {(error: Record<string, any>) => Promise<void>?} errorCallback
     */
    const put = (url, data, customHeader, errorCallback) =>
        call({
            method: 'PUT',
            url,
            data,
            errorCallback,
            customHeader
        });

    /**
     * Performs DELETE ajax request.
     *
     * @param {string} url The URL to call.
     * @param {Record<string, any>?} data
     * @param {(error: Record<string, any>) => Promise<void>?} errorCallback
     */
    const del = (url, data, errorCallback) =>
        call({
            method: 'DELETE',
            url,
            data,
            errorCallback
        });

    /**
     * Performs ajax call.
     *
     * @param {'GET' | 'POST' | 'PUT' | 'DELETE'} method The HTTP method.
     * @param {string} url The URL to call.
     * @param {Record<string, any>?} data The data to send.
     * @param {(error: Record<string, any>) => Promise<any>?} errorCallback An error callback. If not set, the
     *     default one will be used.
     * @param {Record<string, string>?} customHeader Custom HTTP header.
     * @param {boolean} fallthrough Indicates whether the request should not be made obsolete if 401 happened.
     *
     * @returns {Promise<Record<string, any>>}
     */
    const call = ({ method, url, data, errorCallback, customHeader, fallthrough = false }) => {
        const callState = callValidationState;

        return new Promise((resolve, reject) => {

            const headers = {
                'Content-Type': 'application/json',
                ...(customHeader || {})
            };

            if (headers['Content-Type'] === 'multipart/form-data') {
                delete headers['Content-Type'];
            }

            const body = data
                ? headers['Content-Type'] === 'application/json'
                    ? JSON.stringify(data)
                    : data
                : undefined;

            fetch(url, { method, headers, body , credentials: 'include'}).then((response) => {
                if (!fallthrough && callState !== callValidationState) {
                    // Obsolete request. Some call cancelled all other requests.
                    console.debug('cancelling an obsolete request', url);
                    reject({ errorCode: 0 });
                } else {
                    handleResponse(response, errorCallback).then(resolve).catch(reject);
                }
            });
        });
    };

    Unzer.ajaxService = {
        get,
        post,
        put,
        delete: del
    };
})();
