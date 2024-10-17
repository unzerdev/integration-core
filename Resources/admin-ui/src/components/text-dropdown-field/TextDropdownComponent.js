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

  const languages = dropdownProps.languages || [];

  const languageOptions = languages.map(x => {
    const imageUrl = x.flag !== "default"
        ? `${Unzer.config.flagsUrl}/${x.flag}.svg`
        : `${Unzer.config.flagsUrl}/country-xx.svg`;
    return {
      label: `<img src="${imageUrl}" alt="${x.code}"/>`,
      value: x.code
    };
  });

  dropdownProps.options = [
    ...dropdownProps.options,
    ...languageOptions
  ];

    return generator.createElement("div", `unzer-text-dropdown ${className}`, '', [], [
        Unzer.components.TextField.create({ className: `${textFieldProps.className} unzer-label-input-field-no-radius`, ...textFieldProps }),
        Unzer.components.Dropdown.create(dropdownProps),
    ]);

};


Unzer.components.TextDropdownComponent = {
    create: TextDropdownComponent
};
