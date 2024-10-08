(() => {
    /**
     * Gets the main page DOM element.
     *
     * @returns {HTMLElement}
     */
    const getPage = () => document.getElementById('adl-page');
    /**
     * Gets the main page DOM element.
     *
     * @returns {HTMLElement}
     */
    const getContentPage = () => document.getElementById('unzer-main-page');

    /**
     * Gets the main header element.
     *
     * @returns {HTMLElement}
     */
    const getHeaderSection = () => document.getElementById('adl-header-section');

    /**
     * Clears the main page.
     *
     * @return {HTMLElement}
     */
    const clearContent = () => {
        clearComponent(getContentPage());
    };
    
    const removePaddings = () => {
        const content = document.querySelector('.unzer-content-holder');
        content.classList.add('unzer-content-holder-no-padding');
    }
    
    const addPaddings = () => {
        const content = document.querySelector('.unzer-content-holder');
        content.classList.remove('unzer-content-holder-no-padding');
    }

    /**
     * Removes component's children.
     *
     * @param {Element} component
     */
    const clearComponent = (component) => {
        while (component.firstChild) {
            component.removeChild(component.firstChild);
        }
    };

    Unzer.pageService = {
        getPage,
        getContentPage,
        getHeaderSection,
        clearContent,
        clearComponent,
        removePaddings,
        addPaddings
    };
})();
