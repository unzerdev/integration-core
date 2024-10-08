/**
 * Creates dropdown wrapper around the provided dropdown element.
 *
 * @param {FormField & DropdownComponentModel} props The properties.
 * @return {HTMLDivElement}
 */
const SingleSelectFieldComponent = ({ label, error, horizontal, ...dropdownProps }) => {
    return Unzer.elementGenerator.createFieldWrapper(
        Unzer.components.Dropdown.create(dropdownProps),
        label,
        error,
        horizontal
    );
};

Unzer.components.DropdownField = {
    create: SingleSelectFieldComponent
};
