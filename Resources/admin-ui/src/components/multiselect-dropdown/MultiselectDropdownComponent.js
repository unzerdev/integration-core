const preventDefaults = (e) => {
    e.preventDefault();
    e.stopPropagation();
};

/**
 * @typedef MultiselectDropdownComponentModel
 *
 * @property {Option[]} options
 * @property {string?} name
 * @property {string?} title
 * @property {string[]?} values
 * @property {string?} placeholder
 * @property {string?} selectedText
 * @property {(values: string[]) => void} onChange
 * @property {boolean?} updateTextOnChange
 * @property {boolean?} useAny
 * @property {boolean?} searchable
 * @property {'top' | 'bottom'?} orientation
 * @property {string?} className
 * */

/**
 * Multiselect dropdown component.
 *
 * @param {MultiselectDropdownComponentModel} params
 * @returns {HTMLElement}
 * @constructor
 */
const MultiselectDropdownComponent = ({
    options,
    name = '',
    title = '',
    values = [],
    placeholder,
    selectedText = 'general.selectedItems',
    onChange,
    updateTextOnChange = true,
    useAny = true,
    className = '',
    searchable = false,
    orientation = 'bottom'
}) => {
    const { elementGenerator: generator, translationService } = Unzer;

    const filterItems = (text) => {
        const filteredItems = text ? options.filter((option) => option.label.toLowerCase().includes(text.toLowerCase())) : options;

        if (filteredItems.length === 0) {
            selectButton.classList.add('adls--no-results');
        } else {
            selectButton.classList.remove('adls--no-results');
        }

        list.innerHTML = '';
        !text && renderAnyItem();
        filteredItems.forEach(renderOption);
    };

    options.forEach((option) => {
        option.label = translationService.translate(option.label);
    });

    const handleDisplayedItems = (fireChange = true) => {
        hiddenInput.value = selectedItems.map((item) => item.value).join(',');
        if (useAny) {
            /** @type HTMLElement */
            const anyItem = list.querySelector('.adlt--any');
            if (selectedItems.length > 0) {
                anyItem?.classList.remove('adls--selected');
            } else {
                anyItem.classList.toggle('adls--selected');

                list.querySelectorAll(':not(.adlt--any)').forEach((listItem) => {
                    listItem.classList.remove('adls--selected');
                    if (anyItem.classList.contains('adls--selected')) {
                        listItem.classList.add('adls--disabled');
                    } else {
                        listItem.classList.remove('adls--disabled');
                    }
                });
            }
        }

        let textToDisplay;
        if (selectedItems.length > 2) {
            textToDisplay = translationService.translate(selectedText, [selectedItems.length]);
        } else if (selectedItems.length !== 0) {
            textToDisplay = selectedItems.map((item) => item.label).join(', ') || translationService.translate(
                placeholder);
        } else {
            textToDisplay = '';
        }

        if (updateTextOnChange) {
            Unzer.pageService.clearComponent(buttonWrapper.lastElementChild);

            selectedItems.map(x => generator.createElement(
                'span',
                '',
                x.label
            )).forEach(item => buttonWrapper.lastElementChild.append(item));
        }

        fireChange && onChange?.(selectedItems.map((item) => item.value));
    };

    const createListItem = (additionalClass, label, htmlKey) => {
        const item = generator.createElement('li', `unzer-dropdown-list-item ${additionalClass}`, label, htmlKey, [
            generator.createElement('input', 'unzer-checkbox', '', { type: 'checkbox' })
        ]);
        list.append(item);
        return item;
    };

    const renderOption = (option) => {
        const listItem = createListItem(values?.includes(option.value) ? 'adls--selected' : '', option.label, null);

        selectedItems.forEach((item) => {
            if (option.value === item.value) {
                listItem.classList.add('adls--selected');
            }
        });

        listItem.addEventListener('click', () => {
            listItem.classList.toggle('adls--selected');
            listItem.childNodes[0].checked = listItem.classList.contains('adls--selected');
            if (!selectedItems.includes(option)) {
                selectedItems.push(option);
            } else {
                const index = selectedItems.indexOf(option);
                selectedItems.splice(index, 1);
            }

            handleDisplayedItems();
        });
    };

    const renderAnyItem = () => {
        if (useAny) {
            const anyItem = createListItem(
                'adlt--any' + (!selectedItems?.length ? ' adls--selected' : ''),
                'general.any',
                null
            );

            anyItem.addEventListener('click', () => {
                selectedItems = [];
                anyItem.childNodes[0].checked = anyItem.classList.contains('adls--selected');

                handleDisplayedItems();
            });
        }
    };

    let selectedItems = options.filter((option) => values?.includes(option.value));

    const hiddenInput = generator.createElement('input', 'unzer-hidden-input', '', {
        type: 'hidden', name, value: values?.join(',') || ''
    });
    const wrapper = generator.createElement(
        'div',
        'adl-multiselect-dropdown adlv--' + orientation + (className ? ' ' + className : '')
    );

    const buttonWrapper = generator.createElement(
        'div',
        'unzer-dropdown-button-wrapper',
        '',
        [],
        [
            generator.createElement(
                'span',
                'unzer-dropdown-title' + (selectedItems ? ' adls--title-selected' : ''),
                title
            ),
            generator.createElement('div', selectedItems ? 'adls--selected' : '', placeholder, [], []),
        ]
    )

    const selectButton = generator.createElement('button', 'unzer-dropdown-button unzer-field-component', '', {
        type: 'button'
    }, [buttonWrapper]);

    const searchInput = generator.createElement('input', 'adl-text-input', '', {
        type: 'text', placeholder: translationService.translate('general.search')
    });

    if (searchable) {
        searchInput.addEventListener('input', (event) => filterItems(event.currentTarget?.value || ''));
        selectButton.append(searchInput);
    }

    const list = generator.createElement('ul', 'unzer-dropdown-list');

    renderAnyItem();
    options.forEach(renderOption);

    selectButton.addEventListener('click', (e) => {
        preventDefaults(e);
        list.classList.toggle('adls--show');
        wrapper.classList.toggle('adls--active');
        if (searchable) {
            selectButton.classList.toggle('adls--search-active');
            if (selectButton.classList.contains('adls--search-active')) {
                searchInput.focus();
                searchInput.value = '';
                filterItems('');
            }
        }
    });

    Unzer.utilities.onClickOutside(list, () => {
        list.classList.remove('adls--show');
        wrapper.classList.remove('adls--active');
        selectButton.classList.remove('adls--search-active');
    });

    wrapper.append(hiddenInput, selectButton, list);

    values?.length && handleDisplayedItems(false);

    return wrapper;
};

Unzer.components.MultiselectDropdown = {
    create: MultiselectDropdownComponent
};
