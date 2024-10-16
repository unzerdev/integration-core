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
    dropdownProps.options = dropdownProps.options.map( x=> {
      const imageUrl = x.label !== "default"
          ? `${Unzer.config.flagsUrl}/${x.label}.svg`
          : `${Unzer.config.flagsUrl}/country-xx.svg`;
      return {
        label: `<img src="${imageUrl}" alt="${x.value}"/>`,
        value: x.value
      };
    })
    return generator.createElement("div", `unzer-text-dropdown ${className}`, '', [], [
        Unzer.components.TextField.create({ className: `${textFieldProps.className} unzer-label-input-field-no-radius`, ...textFieldProps }),
        Unzer.components.Dropdown.create(dropdownProps),
    ]);

};


Unzer.components.TextDropdownComponent = {
    create: TextDropdownComponent
};
