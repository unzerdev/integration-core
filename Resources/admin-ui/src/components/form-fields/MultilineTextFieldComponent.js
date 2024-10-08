/**
 * @typedef {FormField} MultilineTextFieldComponentModel
 * @property {number? } rows
 */

/**
 * Creates a textarea input field.
 *
 * @param {MultilineTextFieldComponentModel} props The properties.
 * @return {HTMLElement}
 */
const MultilineTextFieldComponent = ({
    className = '',
    label,
    description,
    error,
    horizontal,
    rows,
    onChange,
    ...rest
}) => {
    const { elementGenerator: generator } = Unzer;

    return generator.createFieldWrapper(
        generator.createTextArea({
            className: 'adl-multiline-text-input ' + className,
            rows,
            onChange,
            ...rest
        }),
        label,
        description,
        error,
        horizontal
    );
};

Unzer.components.MultilineTextField = {
    create: MultilineTextFieldComponent
};
