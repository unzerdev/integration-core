/**
 * Creates form fields based on the fields configurations.
 *
 * @typedef {ElementProps} FormField
 * @property {'text' | 'textarea' | 'password' | 'number' | 'dropdown'| 'multiselect-dropdown' |'radio' | 'toggle' |
 *     'file' | 'money' | 'footer'} type
 * @property {boolean?} horizontal
 * @property {string?} label
 * @property {string?} description
 * @property {string?} error
 */

/**
 *
 * @param {FormField[]} fieldsDefs
 * @return {HTMLElement}
 */
const create = (fieldsDefs) => {
    const { elementGenerator: generator, components } = Unzer;

    /** @type HTMLElement[] */
    const fields = [];

    fieldsDefs.forEach(({ type, ...rest }) => {
        switch (type) {
            case 'text':
                fields.push(components.TextField.create(rest));
                break;
            case 'textarea':
                fields.push(components.MultilineTextField.create(rest));
                break;
            case 'password':
                fields.push(components.PasswordField.create(rest));
                break;
            case 'number':
                fields.push(components.NumberField.create(rest));
                break;
            case 'dropdown':
                fields.push(components.DropdownField.create(rest));
                break;
            case 'multiselect-dropdown':
                fields.push(components.MultiselectDropdownField.create(rest));
                break;
            case 'radio':
                fields.push(components.RadioButtonGroupField.create(rest));
                break;
            case 'toggle':
                fields.push(components.ToggleField.create(rest));
                break;
            case 'file':
                fields.push(components.FileUploadField.create(rest));
                break;
            case 'money':
                fields.push(components.MoneyInputField.create(rest));
                break;
            case 'footer':
                fields.push(components.FormFooterField.create(rest));
        }

        rest.className && fields[fields.length - 1].classList.add(...rest.className.trim().split(' '));
    });

    return generator.createElement('div', 'adl-form-fields', '', null, fields);
};

Unzer.components.FormFields = {
    create
};
