/**
 * Creates two column layout
 *
 * @param {DropdownComponentModel} dropdownProps
 * @param {TextFieldComponentModel} textFieldProps
 * @param {string} className
 *
 * @constructor
 */
const TextDropdownComponent = (dropdownProps, textFieldProps, values = [], onChange = (x) => {
}, className = '', selected = {
    locale: 'default',
    value: ''
}) => {
    const generator = Unzer.elementGenerator;
    dropdownProps.options = [
        {label: Unzer.imagesProvider.languageIcon, value: "default"},
        ...dropdownProps.options.map(x => {
            const imageUrl = x.value !== "default"
                ? `${Unzer.config.flagsUrl}/${x.label}.svg`
                : `${Unzer.config.imagesUrl}`;
            return {
                label: `<img src="${imageUrl}" alt="${x.value}"/>`,
                value: x.value,
                title: x.value
            };
        })
    ];


    const wrapper = generator.createElement("div", `unzer-text-dropdown ${className}`, '', [], []);


    dropdownProps.onChange = (value) => {
        selected = values?.find(x => x.locale == value);
        if (!selected) {
            selected = {locale: value, value: ''};
            values.push(selected);
        }

        textFieldProps.value = selected.value
        wrapper.replaceChild(Unzer.components.TextField.create({className: `${textFieldProps.className} unzer-label-input-field-no-radius`, ...textFieldProps}), wrapper.firstChild);

        onChange(values);
    }

    textFieldProps.onChange = (value) => {
        selected = values.find(x => x.locale == selected.locale);
        if (!selected) {
            selected = {locale: 'default', value: value};
            values.push(selected);
        }

        selected.value = value;

        onChange(values);
    }

    const text = Unzer.components.TextField.create({className: `${textFieldProps.className} unzer-label-input-field-no-radius`, ...textFieldProps});
    const dropdown = Unzer.components.Dropdown.create(dropdownProps);
    wrapper.append(text);
    wrapper.append(dropdown);

    return wrapper;

};


Unzer.components.TextDropdownComponent = {
    create: TextDropdownComponent
};
