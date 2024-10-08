/**
 * Creates a color picker component.
 *
 * @param  props
 *
 * @constructor
 */
const ColorPickerComponent = ({ defaultColor, className, onColorChange, label = '', description = '' }) => {
    const { elementGenerator: generator } = Unzer;
    const cssClass = ['unzer-color-picker'];
    className && cssClass.push(className);

    const inputElement = generator.createElement('input', 'unzer-color-input', '', {
        type: 'text',
        value: defaultColor || '#ffffff',
    });

    const inputLabel = generator.createElement('label', 'unzer-label-input-label ', label, { for: inputElement.id });

    const colorPickerElement = generator.createElement('input', 'unzer-color-picker-circle', '', {
        type: 'color',
        value: defaultColor || '#ffffff',
    });

    colorPickerElement.addEventListener('input', () => {
        const selectedColor = colorPickerElement.value;
        inputElement.value = selectedColor;
        onColorChange && onColorChange(selectedColor);
    });

    const wrapper = generator.createElement('span', 'unzer-color-picker', "", [], [])
    const descriptionSpan = generator.createElement('span', 'unzer-color-description', description, [], [])

    wrapper.append(
        inputElement,
        inputLabel,
        colorPickerElement
    )

    return generator.createElement('div', 'unzer-color-picker-container', '', null, [wrapper, descriptionSpan]);
};

Unzer.components.ColorPickerComponent = {
    create: ColorPickerComponent
};
