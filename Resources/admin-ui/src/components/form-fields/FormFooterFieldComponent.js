/**
 * @typedef FormFooterFieldComponentModel
 * @property {() => void} onSave
 * @property {() => void} onCancel
 * @property {string?} cancelLabel
 * @property {string?} saveLabel
 * @property {HTMLButtonElement[]?} extraButtons
 * @property {boolean?} [showCancel=true]
 * @property {boolean?} [cancelAlwaysEnabled=true]
 */

/**
 * Creates a form footer field with save and cancel buttons.
 *
 * @param {FormFooterFieldComponentModel} props
 * @returns {HTMLElement}
 */
const FormFooterFieldComponent = ({
    onSave,
    onCancel,
    cancelLabel = 'general.cancel',
    saveLabel = 'general.saveChanges',
    extraButtons = [],
    showCancel = true,
    cancelAlwaysEnabled = false,
    showUnsavedChanges = false
}) => {
    const { elementGenerator: generator, components } = Unzer;
    const buttons = [
        ...extraButtons,
    ];

    let unsavedChangesText;

    if (showUnsavedChanges) {
        unsavedChangesText = 'general.unsavedChanges';
    } else {
        unsavedChangesText = '';
    }

    if (showCancel) {
        buttons.push(components.Button.create({
                type: 'secondary',
                className: 'unzer-cancel',
                label: cancelLabel,
                onClick: onCancel,
                disabled: !cancelAlwaysEnabled
            })
        )
    }

    buttons.push(components.Button.create({
            type: 'primary',
            className: 'unzer-save',
            label: saveLabel,
            onClick: onSave,
            disabled: true
        })
    )

    return generator.createElement('div', 'adl-form-footer', '', null, [
        generator.createElement('span', 'unzer-changes-count', unsavedChangesText),
        generator.createElement('div', 'unzer-actions', '', null, buttons)
    ]);
};

Unzer.components.FormFooterField = {
    create: FormFooterFieldComponent
};
