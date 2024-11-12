const createHeader = () => {
    return Unzer.elementGenerator.createElementFromHTML(`
            <div class="unzer-logo">
                 ${Unzer.imagesProvider.logo}
             </div>`);
};

/**
 * Creates dropdown
 * @param {Option[]} options
 * @param {(value: string) => any} onStoreSelect
 * @returns {HTMLElement}
 */
const createDropdown = (options = [], onStoreSelect) => {
    const generator = Unzer.elementGenerator;

    const optionElements = [];
    let defaultOption = null;
    options.forEach((option) => {
        const optionElement = generator.createElement('div', '', option.label, {
            onclick: () =>
                selectOption(
                    optionElement,
                    Unzer.config.stores.find((x) => x.storeId === option.value),
                    onStoreSelect
                )
        });
        optionElements.push(optionElement);
        if (option.value === Unzer.config.store.storeId) {
            defaultOption = optionElement;
        }
    });

    const dropdownContent = generator.createElement('div', 'dropdown-content', '', {}, optionElements);
    const dropdownLabel = generator.createElement('span', 'dropdown-label', 'Select');
    const dropdown = generator.createElement('div', 'dropdown', '', {onclick: (event) => event.stopPropagation()}, [
        dropdownLabel,
        dropdownContent
    ]);

    dropdown.addEventListener(
        'click',
        (event) => {
            toggleDropdown(event.currentTarget);
        },
        false
    );

    Unzer.utilities.onClickOutside(dropdown, () => {
        const dropdowns = document.getElementsByClassName('dropdown');
        for (let i = 0; i < dropdowns.length; i++) {
            let openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    });

    selectOption(defaultOption, Unzer.config.store, null);

    return dropdown;
};

const toggleDropdown = (element) => {
    element.classList.toggle('show');
};

/**
 *
 * @param {HTMLElement} option,
 * @param store
 * @param {(value: string) => any} onStoreSelect
 */
const selectOption = (option, store, onStoreSelect) => {
    const dropdown = option.closest('.dropdown');
    const label = dropdown.querySelector('.dropdown-label');
    label.textContent = option.textContent;
    dropdown.classList.remove('show');
    if (onStoreSelect) {
        toggleDropdown(dropdown);
        onStoreSelect(store);
    }
};

const createConnection = () => {
    const generator = Unzer.elementGenerator;

    const icon = Unzer.elementGenerator.createElementFromHTML(`
            <div class="unzer-header-icon">
                 ${Unzer.imagesProvider.connection}
             </div>`);

    const connectionInfo = generator.createElement(
        'div',
        'unzer-header-connection-info',
        `login.credentials.header|${Unzer.config.store.publicKey.substring(0, 9) + "***"}`,
        [],
        []
    );

    const connection = generator.createElement('div', 'unzer-header-connection', '', [], [icon, connectionInfo]);
    connection.addEventListener('click', () => Unzer.stateController.navigate('credentials'));

    return connection;
};

/**
 * Initializes the main page header.
 *
 * @param {Option[]?} stores
 * @param {(value: string) => any} onStoreSelect
 */
const render = (stores, onStoreSelect) => {
    const generator = Unzer.elementGenerator;
    const container = document.querySelector('#adl-main-header .adl-page-header');
    Unzer.pageService.clearComponent(container);

    const verticalLine = generator.createElement('div', 'unzer-vertical-line', '', [], []);
    const dropdownWrapper = createDropdown(stores, onStoreSelect);
    const logoWrapper = generator.createElement(
        'div',
        'unzer-logo-wrapper',
        '',
        [],
        [createHeader(), verticalLine, dropdownWrapper]
    );

    logoWrapper.addEventListener('click', () => {
        window.history.back()
    })

    const wrapper = generator.createElement('div', 'unzer-header-wrapper', '', [], [logoWrapper]);

    if (Unzer.config.store.isLoggedIn) {
        wrapper.append(createConnection());
    }

    const sandbox = generator.createElement('div', 'unzer-header-sandbox', `login.sandbox|credentials?store=${Unzer.config.store.storeId}`, [], []);

    container.append(wrapper);
    container.append(sandbox);

};

/**
 * Updates header, whether to show credentials or not
 */
const updateCredentials = () => {
    const credentials = document.querySelector('.unzer-header-connection');
    if (credentials) {
        Unzer.pageService.clearComponent(credentials);
    }

    if (Unzer.config.store.isLoggedIn) {
        credentials?.append(createConnection());
    }
};

/**
 *
 * @param isSandbox
 */
const updateEnvironment = (isSandbox) => {
    const environment = document.querySelector('.unzer-header-sandbox');

    if (isSandbox) {
        environment.classList.remove('unzer-header-sandbox-none');
    } else {
        environment.classList.add('unzer-header-sandbox-none');
    }
};

Unzer.components.PageHeader = {
    render,
    updateCredentials,
    updateEnvironment
};
