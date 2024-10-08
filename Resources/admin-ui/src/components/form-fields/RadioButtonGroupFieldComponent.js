/**
 * Creates a radio group field.
 *
 * @param {FormField} props The properties.
 * @return {HTMLElement}
 */
const RadioButtonGroupFieldComponent = ({
    name,
    value,
    className,
    options,
    label,
    description,
    error,
    horizontal,
    onChange
}) => {
    const { elementGenerator: generator } = Unzer;

    const wrapper = generator.createElement('div', 'adl-radio-input-group');
    options.forEach((option) => {
        const label = generator.createElement('label', 'adl-radio-input');
        const props = { type: 'radio', value: option.value, name };
        if (value === option.value) {
            props.checked = 'checked';
        }

        label.append(
            generator.createElement('input', className, '', props),
            generator.createElement('span', '', option.label)
        );
        wrapper.append(label);
        onChange && label.addEventListener('click', () => onChange(option.value));
    });

    return generator.createFieldWrapper(wrapper, label, description, error, horizontal);
};

Unzer.components.RadioButtonGroupField = {
    create: RadioButtonGroupFieldComponent
};
