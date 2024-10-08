/**
 * Creates two column layout
 *
 * @param {DropdownComponentModel} dropdownProps
 * @param {TextFieldComponentModel} textFieldProps
 * @param {string} className
 *
 * @constructor
 */
const TextDropdownComponent = (dropdownProps, textFieldProps, className = '') => {
    const generator = Unzer.elementGenerator;

    return generator.createElement("div", `unzer-text-dropdown ${className}`, '', [], [
        Unzer.components.TextField.create({ className: `${textFieldProps.className} unzer-label-input-field-no-radius`, ...textFieldProps }),
        Unzer.components.Dropdown.create(dropdownProps),
    ]);

};


Unzer.components.TextDropdownComponent = {
    create: TextDropdownComponent
};
