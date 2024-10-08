/**
 * @typedef DropdownComponentModel
 *
 * @property {Option[]} options
 * @property {string?} name
 * @property {string?} value
 * @property {string?} placeholder
 * @property {(value: string) => void?} onChange
 * @property {boolean?} updateTextOnChange
 * @property {boolean?} searchable
 * @property {'top' | 'bottom'?} orientation
 * @property {string?} title
 * @property {string?} description
 * @property {string?} className
 * @property {boolean?} isIcon
 */

/**
 * Single-select dropdown component.
 *
 * @param {DropdownComponentModel} props
 *
 * @constructor
 */
const DropdownComponent = ({
    options,
    name,
    value = '',
    placeholder,
    onChange,
    updateTextOnChange = true,
    searchable = false,
    orientation = 'bottom',
    title = '',
    description = '',
    className = '',
    isIcon = false
}) => {
    const { elementGenerator: generator, translationService } = Unzer;
    const filterItems = (text) => {
        const filteredItems = text
            ? options.filter((option) => option.label.toLowerCase().includes(text.toLowerCase()))
            : options;

        if (filteredItems.length === 0) {
            selectButton.classList.add('adls--no-results');
        } else {
            selectButton.classList.remove('adls--no-results');
        }

        renderOptions(filteredItems);
    };

    const renderOptions = (options) => {
        list.innerHTML = '';
        options.forEach((option) => {
            const listItem = generator.createElement(
                'li',
                'unzer-dropdown-list-item' + (option === selectedItem ? ' adls--selected' : ''),
                option.label
            );
            list.append(listItem);

            listItem.addEventListener('click', () => {
                selectedItem = option;
                hiddenInput.value = option.value;
                updateTextOnChange && (buttonSpan.innerHTML = translationService.translate(option.label));
                list.classList.remove('adls--show');
                list.childNodes.forEach((node) => node.classList.remove('adls--selected'));
                listItem.classList.add('adls--selected');
                wrapper.classList.remove('adls--active');
                buttonSpan.classList.add('adls--selected');
                titleSpan.classList.add('adls--title-selected');
                selectButton.classList.remove('adls--search-active');
                onChange && onChange(option.value);
            });
        });
    };

    const hiddenInput = generator.createElement('input', 'unzer-hidden-input', '', { type: 'hidden', name, value });
    const wrapper = generator.createElement(
        'div',
        'adl-single-select-dropdown adlv--' + orientation + `${isIcon ? ' unzer-dropdown-max-width' : ''}`
    );

    const selectButton = generator.createElement(
        'button',
        `unzer-dropdown-button unzer-field-component ${isIcon ? 'unzer-icon-dropdown' : ''}`,
        '',
        {
            type: 'button'
        }
    );

    let selectedItem = options.find((option) => option.value === value);
    const buttonSpan = generator.createElement(
        'span',
        selectedItem ? 'adls--selected' : '',
        selectedItem ? selectedItem.label : placeholder
    );

    const titleSpan = generator.createElement(
        'span',
        'unzer-dropdown-title' + (selectedItem ? ' adls--title-selected' : ''),
        title
    );

    const buttonWrapper = generator.createElement(
        'div',
        'unzer-dropdown-button-wrapper',
        '',
        [],
        [titleSpan, buttonSpan]
    )

    selectButton.append(buttonWrapper);

    const searchInput = generator.createElement('input', 'adl-text-input', '', {
        type: 'text',
        placeholder: translationService.translate('general.search')
    });
    searchInput.addEventListener('input', (event) => filterItems(event.currentTarget?.value || ''));
    if (searchable) {
        selectButton.append(searchInput);
    }

    const list = generator.createElement('ul', 'unzer-dropdown-list');
    renderOptions(options);

    selectButton.addEventListener('click', () => {
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

    Unzer.utilities.onClickOutside(wrapper, () => {
        list.classList.remove('adls--show');
        wrapper.classList.remove('adls--active');
        selectButton.classList.remove('adls--search-active');
    });

    const descriptionSpan = generator.createElement('span', 'unzer-dropdown-description', description, [], [])

    wrapper.append(hiddenInput, selectButton, list, descriptionSpan);

    return wrapper;
};

Unzer.components.Dropdown = {
    create: DropdownComponent
};