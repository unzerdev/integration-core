/**
 * Creates a password input field.
 *
 * @param {FormField} props The properties.
 * @return {HTMLElement}
 */
const PasswordFieldComponent = ({ className = '', label, description, error, horizontal, onChange, ...rest }) => {
    const { elementGenerator: generator } = Unzer;

    const wrapper = generator.createElement('div', `adl-password adl-password-input ${className}`);
    const input = generator.createElement('input', 'unzer-field-component', '', { type: 'password', ...rest });
    const span = generator.createElement('span');
    span.addEventListener('click', () => {
        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    });
    onChange && input.addEventListener('change', (event) => onChange(event.currentTarget?.value));

    wrapper.append(input, span);

    return generator.createFieldWrapper(wrapper, label, description, error, horizontal);
};

Unzer.components.PasswordField = {
    create: PasswordFieldComponent
};
