Unzer.LoginController = function () {
    /**
     *
     * @type {HTMLElement}
     */
    const page = Unzer.pageService.getContentPage();

    /**
     * renders login page
     * @param {StateParamsModel} params
     */
    this.display = () => {
        if (Unzer.config.store.isLoggedIn) {
            Unzer.pageService.addPaddings();
            Unzer.stateController.navigate("checkout");

            return;
        }

        Unzer.pageService.clearComponent(page);
        Unzer.pageService.removePaddings();

        page.append(
            Unzer.components.LoginComponent.create({
                onLogin: login
            })
        );
    };

    /**
     * Connect store
     * @param loginData
     */
    function login(loginData) {
        Unzer.utilities.showLoader();

        Unzer.LoginService.login(loginData)
            .then(() => {
                Unzer.stateController.navigate('login', {}, true);
                Unzer.components.PageHeader.updateCredentials();
            })
            .catch((ex) => {
                Unzer.utilities.createToasterMessage(ex.errorMessage, true);
            })
            .finally(Unzer.utilities.hideLoader);
    }
};
