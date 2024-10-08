/**
 * Creates a form group.
 *
 * @param {{fields: FormField[], title: string, className?: string}} props The properties.
 * @return {HTMLElement}
 */
const FormGroupComponent = ({ fields, title, className = '' }) => {
    return Unzer.elementGenerator.createElement('div', `adl-form-group adl-field-wrapper ${className}`, '', null, [
        Unzer.elementGenerator.createElement('span', 'unzer-title', title),
        Unzer.components.FormFields.create(fields.map((field) => ({ ...field, horizontal: true })))
    ]);
};

Unzer.components.FormGroup = {
    create: FormGroupComponent
};
