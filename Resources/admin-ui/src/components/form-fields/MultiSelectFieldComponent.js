/**
 * Creates the multi-select dropdown field.
 *
 * @param {FormField & MultiselectDropdownComponentModel} props The properties.
 * @return {HTMLDivElement}
 */
const MultiSelectFieldComponent = ({ label, description, descriptionPositionUp, error, horizontal, ...dropdownProps }) => {
    return Unzer.elementGenerator.createFieldWrapper(
        Unzer.components.MultiselectDropdown.create(dropdownProps),
        label,
        description,
        descriptionPositionUp,
        error,
        horizontal
    );
};

Unzer.components.MultiselectDropdownField = {
    create: MultiSelectFieldComponent
};
