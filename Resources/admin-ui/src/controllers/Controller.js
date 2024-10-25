Unzer.Controller = function () {
    /**
     *
     * @type {HTMLElement}
     */
    const page = Unzer.pageService.getContentPage();


    this.display = () => {
        Unzer.stateController.navigate("./login");
    };

};
