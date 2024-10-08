/**
 * @typedef Option
 * @property {string?} label
 * @property {any} value
 */

/**
 * @typedef {Object.<string, *>} ElementProps
 * @property {string?} name
 * @property {any?} value
 * @property {string?} className
 * @property {string?} placeholder
 * @property {boolean?} readOnly
 * @property {(value: any) => any?} onChange
 */

const translationService = Unzer.translationService;

/**
 * Creates a generic HTML node element and assigns provided class and inner text.
 *
 * @param {keyof HTMLElementTagNameMap} type Represents the name of the tag
 * @param {string?} className CSS class
 * @param {string?} innerHTMLKey Inner text translation key.
 * @param {Record<string, any>?} properties An object of additional properties.
 * @param {HTMLElement[]?} children
 * @returns {HTMLElement}
 */
const createElement = (type, className, innerHTMLKey, properties, children) => {
    const child = document.createElement(type);
    className && child.classList.add(...className.trim().split(' '));
    if (innerHTMLKey) {
        let params = innerHTMLKey.split('|');
        child.innerHTML = translationService.translate(params[0], params.slice(1));
    }

    if (properties) {
        if (properties.dataset) {
            Object.assign(child.dataset, properties.dataset);
            delete properties.dataset;
        }

        Object.assign(child, properties);
        if (properties.onChange) {
            child.addEventListener('change', properties.onChange, false);
        }

        if (properties.onClick) {
            child.addEventListener('click', properties.onClick, false);
        }
    }

    if (children) {
        child.append(...children);
    }

    return child;
};

/**
 * Creates an element out of provided HTML markup.
 *
 * @param {string} html
 * @returns {HTMLElement}
 */
const createElementFromHTML = (html) => {
    const element = document.createElement('div');
    element.innerHTML = Unzer.translationService.translateHtml(html);

    return element.firstElementChild;
};

/**
 * Creates a Loader.
 *
 * @param {{ type?: 'small' | 'large', variation?: 'dark', fullPage?: boolean }} props
 * @return {HTMLElement}
 */
const createLoader = ({ type, variation, fullPage }) => {
    const cssClass = ['adl-loader'];
    type && cssClass.push('adlt--' + type);
    variation && cssClass.push('adlm--' + variation);
    fullPage && cssClass.push('adlt--full-page');

    return createElement('div', cssClass.join(' '), '', null, [createElement('span', 'unzer-spinner', null)]);
};

/**
 * Creates an input field wrapper around the provided input element.
 *
 * @param {HTMLElement} input The input element.
 * @param {string?} label Label translation key.
 * @param {string?} description Description translation key.
 * @param {string?} error Error translation key.
 * @param {string?} className Error translation key.
 * @param {boolean?} horizontal Indicates horizontal layout.
 * @return {HTMLDivElement}
 */
const createFieldWrapper = (input, label, description, error, className = '', horizontal = false) => {
    const field = createElement('div', `adl-field-wrapper ${className}`);
    let textContainer;
    if (horizontal) {
        field.classList.add('adlt--horizontal');
        textContainer = createElement('div', 'unzer-text-container');
        field.append(textContainer);
    } else {
        textContainer = field;
    }

    label && textContainer.append(createElement('h3', 'unzer-field-title', label));

    description && textContainer.append(createElement('span', 'unzer-field-subtitle', description));

    const inputWrapper = createElement('div', 'unzer-input-wrapper', '', null, [
        input,
        createElement('span', 'unzer-input-error', error)
    ]);

    field.append(inputWrapper);

    return field;
};

/**
 * Creates a text input field.
 *
 * @param {ElementProps} props The properties.
 * @return {HTMLElement}
 */
const createText = ({ className = '', onChange, ...rest }) => {
    /** @type HTMLInputElement */
    const input = createElement('input', `unzer-field-component ${className}`, '', { type: 'text', ...rest });
    onChange && input.addEventListener('change', (event) => onChange(event.currentTarget?.value));

    return input;
};

/**
 * Creates a textarea input field.
 *
 * @param {ElementProps & {rows?: number}} props The properties.
 * @return {HTMLElement}
 */
const createTextArea = ({ className = '', onChange, rows = 3, ...rest }) => {
    /** @type HTMLTextAreaElement */
    const textarea = createElement('textarea', `unzer-field-component ${className}`, '', { rows, ...rest });
    onChange && textarea.addEventListener('change', (event) => onChange(event.currentTarget?.value));

    return textarea;
};

/**
 * Creates a flash message.
 *
 * @param {string|string[]} messageKey
 * @param {'error' | 'warning' | 'success'} status
 * @param {number?} clearAfter Time in ms to remove alert message.
 * @return {HTMLElement}
 */
const createFlashMessage = (messageKey, status, clearAfter) => {
    const hideHandler = () => {
        wrapper.remove();
    };
    const wrapper = createElement('div', `adl-alert adlt--${status}`);
    let messageBlock;
    if (Array.isArray(messageKey)) {
        const [titleKey, descriptionKey] = messageKey;
        messageBlock = createElement('div', 'unzer-alert-title', '', null, [
            createElement('span', 'unzer-message', '', null, [
                createElement('span', 'unzer-message-title', titleKey),
                createElement('span', 'unzer-message-description', descriptionKey)
            ])
        ]);
    } else {
        messageBlock = createElement('span', 'unzer-alert-title', messageKey);
    }

    const button = Unzer.components.Button.create({ onClick: hideHandler });

    if (clearAfter) {
        setTimeout(hideHandler, clearAfter);
    }

    wrapper.append(messageBlock, button);

    return wrapper;
};

/**
 * Adds a label with a hint.
 *
 * @param {HTMLElement} element
 * @param {string} hint
 * @param {string?} className
 * @returns HTMLElement
 */
const createHint = (element, hint, className = '') => {
    return createElement('div', `adl-hint ${className}`, '', null, [
        element,
        createElement('span', 'unzer-tooltip adlt--top', hint)
    ]);
};

/**
 * Creates a text input field with a floating label.
 *
 * @param {Object} props - The properties for the input field.
 * @param {string} props.className - Additional classes for the input field.
 * @param {string} props.description - Additional classes for the input field.
 * @param {string} props.label - The label text to display.
 * @param {function} [props.onChange] - The callback function to call on input change.
 * @param {...Object} rest - Additional properties for the input field.
 * @return {HTMLElement} The created input field element.
 */
const createTextWithLabel = ({ className = '', description, label = '', onChange, ...rest }) => {
    /** @type HTMLDivElement */
    const container = createElement('div', 'unzer-label-input-container');

    /** @type HTMLInputElement */
    const input = createElement('input', `unzer-label-input-field ${className}`, '', {
        type: 'text',
        placeholder: ' ',
        ...rest
    });
    onChange && input.addEventListener('input', (event) => onChange(event.currentTarget?.value));

    /** @type HTMLLabelElement */
    const inputLabel = createElement('label', 'unzer-label-input-label', label, { for: input.id });

    const descriptionSpan = createElement('span', 'unzer-dropdown-description', description ?? '', [], []);

    container.append(input, inputLabel, descriptionSpan);

    return container;
};

/**
 * Creates a toaster message.
 *
 * @param {string} label
 * @param {boolean} error
 * @param {number} timeout Clear timeout in ms.
 * @returns {HTMLElement}
 */
const createToaster = (label, error = true, timeout = 5000) => {
    const toaster = createElement('div', error ? 'adl-toaster-error' : 'adl-toaster', '', null, [
        createElement('span', 'unzer-toaster-title', label),
        createElement('button', 'adl-button', '', null, [createElement('span')])
    ]);

    toaster.children[1].addEventListener('click', () => toaster.remove());

    setTimeout(() => toaster.remove(), timeout);

    return toaster;
};

Unzer.elementGenerator = {
    createElement,
    createElementFromHTML,
    createTextWithLabel,
    createHint,
    createFieldWrapper,
    createText,
    createTextArea,
    createFlashMessage,
    createToaster,
    createLoader
};
