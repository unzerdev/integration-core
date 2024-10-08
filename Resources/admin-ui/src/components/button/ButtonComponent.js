/**
 * Creates a button or a list of buttons.
 *
 * @typedef ButtonComponentModel
 * @property {string?} label
 * @property {'primary' | 'secondary' | 'ghost'?} type
 * @property {'small' | 'medium'?} size
 * @property {string?} className
 * @property {[key: string]?} properties
 * @property {() => void?} onClick
 */

const generator = Unzer.elementGenerator;

/**
 * @param {ButtonComponentModel} props
 *
 * @return {HTMLButtonElement}
 */
const createButton = ({ type, size, className, onClick, label, ...properties }) => {
    const cssClass = ['adl-button'];
    type && cssClass.push('adlt--' + type);
    size && cssClass.push('adlm--' + size);
    className && cssClass.push(className);

    const button = generator.createElement('button', cssClass.join(' '), '', { type: 'button', ...properties }, [
        generator.createElement('span', '', label)
    ]);

    onClick &&
        button.addEventListener(
            'click',
            () => {
                onClick();
            },
            false
        );

    return button;
};

/**
 * Creates a list of buttons.
 *
 * @param {ButtonComponentModel[]} buttons
 * @return {HTMLElement}
 */
const createButtonList = (buttons) => {
    return generator.createElement('div', 'adl-buttons', '', null, buttons.map(createButton));
};

Unzer.components.Button = {
    create: createButton,
    createList: createButtonList
};
