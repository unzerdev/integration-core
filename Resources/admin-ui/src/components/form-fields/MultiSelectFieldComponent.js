/**
 * Creates the multi-select dropdown field.
 *
 * @param {FormField & MultiselectDropdownComponentModel} props The properties.
 * @return {HTMLDivElement}
 */
const MultiSelectFieldComponent = ({ label, description, error, horizontal, ...dropdownProps }) => {
    return Unzer.elementGenerator.createFieldWrapper(
        Unzer.components.MultiselectDropdown.create(dropdownProps),
        label,
        description,
        error,
        horizontal
    );
};

Unzer.components.MultiselectDropdownField = {
    create: MultiSelectFieldComponent
};
