/**
 * @typedef TableFilterParams
 *
 * @property {Option[]?} options
 * @property {string?} name
 * @property {string[]?} values
 * @property {(values: string[]) => void?} onChange
 * @property {string?} label
 * @property {string?} selectPlaceholder
 * @property {boolean?} isMultiselect
 */
/**
 * @typedef DateTimeFilterParams
 *
 * @property {Date?} minDate
 * @property {Date?} maxDate
 * @property {string?} placeholder
 * @property {(value: Date[]) => void?} onChange
 * @property {boolean?} [useTime=true]
 * @property {boolean?} [useRange=true]
 */
const { elementGenerator: generator, components, translationService } = Unzer;

const preventDefaults = (e) => {
    e.preventDefault();
    e.stopPropagation();
};

/**
 * Compares contents of two arrays.
 *
 * @param {string[]} a1
 * @param {string[]} a2
 * @return {boolean}
 */
const arraysHaveSameContent = (a1, a2) => {
    if (a1.length !== a2.length) {
        return false;
    }

    for (let i = 0; i < a1.length; i++) {
        if (!a2.includes(a1[i])) {
            return false;
        }
    }

    for (let i = 0; i < a2.length; i++) {
        if (!a1.includes(a2[i])) {
            return false;
        }
    }

    return true;
};

/**
 * Gets the label to be displayed in the main button.
 *
 * @param {string} label The default label when nothing is selected.
 * @param {Option[]} options Possible options.
 * @param {string[]} values Selected values.
 * @return {string}
 */
const getButtonLabel = (label, options, values) => {
    if (values.length === 0) {
        return label;
    }

    if (values.length < 2) {
        return values.map((value) => options.find((o) => o.value === value)?.label || value).join(', ');
    }

    return `${values.length} ${label.toLowerCase()}`;
};

/**
 * Gets the label to be displayed in the main button.
 *
 * @param {string} label The default label when nothing is selected.
 * @param {Option[]} options Possible options.
 * @param {string[]} values Selected values.
 * @return {string}
 */
const getButtonTooltip = (label, options, values) => {
    if (values.length === 0) {
        return '';
    }

    if (values.length < 2) {
        return label;
    }

    return values.map((value) => options.find((o) => o.value === value).label).join(', ');
};

/**
 * Renders the main button.
 *
 * @param {string} label
 * @param {Option[]} options
 * @param {string[]} values
 * @param {() => void} onClick
 * @param {() => void} onClear
 * @return {HTMLButtonElement}
 */
const renderButton = (label, options = [], values, onClick, onClear) => {
    const button = components.Button.create({
        type: 'secondary',
        className: 'unzer-filter-button' + (values.length > 0 ? ' adls--selected' : ''),
        label: getButtonLabel(label, options, values),
        onClick: onClick
    });

    const deleteButton = generator.createElement('button', 'unzer-delete-text-button');
    deleteButton.addEventListener('click', (e) => {
        preventDefaults(e);
        onClear();
    });

    button.append(
        deleteButton,
        generator.createElement('span', 'unzer-tooltip', getButtonTooltip(label, options, values))
    );

    return button;
};

/**
 * Renders selected options.
 *
 * @param {Option[]} options
 * @param {string[]} selectedValues
 * @param {(value: string) =>, void} onRemove
 * @return {HTMLElement[]}
 */
const getOptionsList = (options, selectedValues, onRemove) => {
    return selectedValues.map((value) => {
        const deleteButton = generator.createElementFromHTML('<button class="adlt--remove-item"></button>');
        deleteButton.addEventListener('click', (e) => {
            preventDefaults(e);
            onRemove(value);
        });

        const element = generator.createElement(
            'li',
            'unzer-selected-data-item',
            options.find((o) => o.value === value).label,
            null
        );

        element.prepend(deleteButton);

        return element;
    });
};

/**
 * Creates a table filter element.
 *
 * @param {{type: 'select' | 'date'} & (TableFilterParams | DateTimeFilterParams)} args
 * @return {{create: (function(): *), reset: reset}}
 * @constructor
 */
const TableFilterComponent = ({
    type = 'select',
    options: untranslatedOptions,
    name = '',
    values = [],
    onChange,
    label: labelKey = '',
    selectPlaceholder = '',
    isMultiselect = true,
    ...dateArgs
}) => {
    let selectedValues = [...values];
    const options =
        untranslatedOptions?.map((option) => ({
            value: option.value,
            label: translationService.translate(option.label)
        })) || [];

    const label = translationService.translate(labelKey);

    const createDropdown = () =>
        components.Dropdown.create({
            options,
            name,
            placeholder: selectPlaceholder,
            onChange: handleSelectChange,
            value: isMultiselect ? undefined : selectedValues[0],
            updateTextOnChange: !isMultiselect,
            searchable: true
        });

    const createDatePicker = () =>
        components.DateTimePicker.create({
            placeholder: selectPlaceholder,
            value: selectedValues,
            ...dateArgs,
            onChange: handleDateChange
        });

    const createFilterContainerContent = () => {
        dataContainer.append(generator.createElement('span', 'unzer-data-label', label));
        if (type === 'date') {
            dataContainer.append(createDatePicker());
        } else {
            dataContainer.append(
                createDropdown(),
                generator.createElement(
                    'ul',
                    'unzer-selected-data',
                    '',
                    null,
                    isMultiselect
                        ? getOptionsList(options, selectedValues, (value) => handleSelectChange(value, false))
                        : []
                )
            );
        }

        clearButton.disabled = selectedValues.length === 0;
        applyButton.disabled = arraysHaveSameContent(selectedValues, values);
    };

    const fireOnChange = (values, propagate = true) => {
        selectedValues = values;
        handleSelectedValuesChange();
        filterContainer.classList.remove('adls--open');
        values.length ? button.classList.add('adls--selected') : button.classList.remove('adls--selected');
        if (type === 'date') {
            button.firstElementChild.innerHTML = selectedValues?.length
                ? selectedValues.map((date) => Unzer.utilities.formatDate(date, true)).join(' - ')
                : label;
            button.lastElementChild.innerHTML = label;
        } else {
            button.firstElementChild.innerHTML = getButtonLabel(label, options, selectedValues);
            button.lastElementChild.innerHTML = getButtonTooltip(label, options, selectedValues);
        }

        dataContainer.innerHTML = '';
        propagate && onChange?.(selectedValues);
    };

    const handleSelectedValuesChange = () => {
        const list = filterContainer.querySelector('.unzer-selected-data');
        if (list && isMultiselect) {
            list.innerHTML = '';
            list.append(...getOptionsList(options, selectedValues, (value) => handleSelectChange(value, false)));
        } else if (!isMultiselect && selectedValues.length === 0) {
            // reset value for the dropdown
            const previousDD = filterContainer.querySelector('.adl-single-select-dropdown');
            dataContainer.insertBefore(createDropdown(), previousDD);

            previousDD?.remove();
        }

        clearButton.disabled = selectedValues.length === 0;
        applyButton.disabled = arraysHaveSameContent(selectedValues, values);
    };

    const handleSelectChange = (value, add = true) => {
        if (add) {
            isMultiselect && !selectedValues.includes(value) && selectedValues.push(value);
            !isMultiselect && (selectedValues = [value]);
        } else if (isMultiselect) {
            selectedValues = selectedValues.filter((v) => v !== value);
        } else {
            selectedValues = [];
        }

        handleSelectedValuesChange();
    };

    const handleDateChange = (dates) => {
        selectedValues = dates;
        handleSelectedValuesChange();
    };

    const closeFilter = () => {
        filterContainer.classList.remove('adls--open');
        dataContainer.innerHTML = '';
    };

    const button = renderButton(
        label,
        options,
        values,
        () => {
            if (filterContainer.classList.contains('adls--open')) {
                dataContainer.innerHTML = '';
            } else {
                createFilterContainerContent();
            }

            filterContainer.classList.toggle('adls--open');
        },
        () => {
            fireOnChange([]);
        }
    );

    const clearButton = components.Button.create({
        type: 'secondary',
        size: 'small',
        label: 'general.clear',
        className: 'adlm--blue',
        disabled: values.length === 0,
        onClick: () => {
            selectedValues = [];
            handleSelectedValuesChange();
        }
    });
    const applyButton = components.Button.create({
        type: 'primary',
        size: 'small',
        label: 'general.apply',
        className: 'adlm--blue',
        disabled: true,
        onClick: () => fireOnChange(selectedValues)
    });

    const dataContainer = generator.createElement('div', 'unzer-dropdown-data');
    const filterContainer = generator.createElement('div', 'unzer-dropdown-container', '', null, [
        generator.createElement('div', 'unzer-content', '', null, [
            generator.createElement('div', 'unzer-filter-header', '', null, [
                generator.createElement('span', '', 'dataTable.filter'),
                generator.createElement('button', 'unzer-close-button', '', { onClick: closeFilter })
            ]),
            dataContainer,
            generator.createElement('span', 'unzer-buttons', '', null, [clearButton, applyButton])
        ])
    ]);

    const create = () => {
        const element = generator.createElement('div', 'adl-multiselect-filter', '', null, [button, filterContainer]);

        Unzer.utilities.onClickOutside(element, (target) => {
            if (!target.parentElement?.parentElement?.classList.contains('month-item')) {
                closeFilter();
            }
        });

        return element;
    };

    return {
        create,
        reset: () => {
            fireOnChange([], false);
        }
    };
};

Unzer.components.TableFilter = TableFilterComponent;
