/**
 * @typedef {FormField} TextFieldComponentModel
 * @property {'text' | 'number'} type
 * @property {string} subtitle
 * @property {string} fieldClasses
 * @property {boolean} maxWidth
 */

/**
 * Creates a text input field.
 *
 * @param {TextFieldComponentModel} props The properties.
 * @return {HTMLElement}
 */
const TextFieldComponent = ({ className = '', subtitle = '', descriptionPositionUp = true,fieldClasses = '', maxWidth = true, label, description, error, horizontal, onChange, title, ...rest }) => {
    const { elementGenerator: generator } = Unzer;
    const classes = maxWidth ? "adl-text-input unzer-label-input-container-max-width" : "adl-text-input"

    return generator.createFieldWrapper(
        generator.createTextWithLabel({
            className: `${classes} ${className}`,
            onChange,
            label: title,
            description: subtitle,
            ...rest
        }),
        label,
        description,
        descriptionPositionUp,
        error,
        fieldClasses,
        horizontal
    );
};

Unzer.components.TextField = {
    create: TextFieldComponent
};
