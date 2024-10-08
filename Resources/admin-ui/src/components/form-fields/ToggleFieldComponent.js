/**
 * Creates a toggle field.
 *
 * @param {FormField} props The properties.
 * @return {HTMLElement}
 */
const ToggleFieldComponent = ({ className = '', label, description, error, onChange, value, ...rest }) => {
    const { elementGenerator: generator } = Unzer;

    /** @type HTMLInputElement */
    const toggleInput = generator.createElement('input', 'unzer-toggle-input', '', {
        type: 'checkbox',
        checked: value,
        ...rest
    });

    onChange && toggleInput.addEventListener('change', () => onChange(toggleInput.checked));

    const field = generator.createElement('div', 'adl-field-wrapper adlt--toggle-input', '', null, [
        generator.createElement('h3', 'unzer-field-title', label, null, [
            generator.createElement('label', 'adl-toggle', '', null, [
                toggleInput,
                generator.createElement('span', 'unzer-toggle-round')
            ])
        ])
    ]);

    if (description) {
        field.appendChild(generator.createElement('span', 'unzer-field-subtitle', description));
    }

    if (error) {
        field.appendChild(generator.createElement('span', 'unzer-input-error', error));
    }

    return field;
};

Unzer.components.ToggleField = {
    create: ToggleFieldComponent
};
