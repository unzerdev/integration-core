/**
 * @typedef ModalButtonConfig
 * @property {string} label
 * @property {string?} className
 * @property {'primary' | 'secondary'} type
 * @property {() => void} onClick
 */

/**
 * @typedef ModalConfiguration
 * @property {string?} title
 * @property {string?} description
 * @property {boolean?} fullHeight
 * @property {image?} image
 * @property {string?} className
 * @property {HTMLElement | HTMLElement[]} content The content of the body.
 * @property {ModalButtonConfig[]} buttons Footer buttons.
 * @property {{label: string, href: string}?} footerLink Footer link.
 * @property {(modal: HTMLDivElement) => void?} onOpen Will fire after the modal is opened.
 * @property {() => boolean?} onClose Will fire before the modal is closed.
 *      If the return value is false, the modal will not be closed.
 * @property {boolean} [canClose=true] Indicates whether to use an (X) button or click outside the modal
 * to close it. Defaults to true.
 * @property {boolean?} [fullWidthBody=false] Indicates whether to make body full width
 */

/**
 * @param {ModalConfiguration} configuration
 */
const ModalComponent = (configuration) => {
    const { pageService, translationService, utilities, components } = Unzer,
        config = configuration;


    /**
     * @type {HTMLDivElement}
     */
    let modal;

    /**
     * Closes the modal on Esc key.
     *
     * @param {KeyboardEvent} event
     */
    const closeOnEsc = (event) => {
        if (event.key === 'Escape') {
            this.close();
        }
    };

    /**
     * Closes the modal.
     */
    const close = () => {
        if (!config.onClose || config.onClose()) {
            window.removeEventListener('keyup', closeOnEsc);
            modal?.remove();
        }
    };

    /**
     * Opens the modal.
     */
    const open = () => {
        configuration.image = configuration.image ?? '';
        const contentClass = configuration.fullHeight ? "unzer-modal-content unzer-modal-max-height" : "unzer-modal-content";
        const header =
            '<div class="unzer-payment-method-info">\n' +
            '    <div class="unzer-payment-method-logo">\n' +
            configuration.image +
            '    </div>\n' +
            '    <div class="unzer-payment-method-headline">\n' +
            '        <span class="unzer-payment-method-title"></span>\n' +
            '        <span class="unzer-payment-method-description"></span>\n' +
            '    </div>\n' +
            '</div>'
        const modalTemplate =
            '<div id="adl-modal" class="adl-modal">\n' +
                `<div class="${contentClass}">` +
            '        <button class="adl-button adlt--ghost adlm--icon-only unzer-close-button"><span></span></button>' +
            '        <div class="unzer-title">' +
            header +
            '        </div>' +
            '        <div class="unzer-body-wrapper"><div class="unzer-body"></div></div>' +
            '        <div class="unzer-footer"></div>' +
            '    </div>' +
            '</div>';

        modal = Unzer.elementGenerator.createElementFromHTML(modalTemplate);
        const closeBtn = modal.querySelector('.unzer-close-button'),
            closeBtnSpan = modal.querySelector('.unzer-close-button span'),
            title = modal.querySelector('.unzer-payment-method-title'),
            description = modal.querySelector('.unzer-payment-method-description'),
            body = modal.querySelector('.unzer-body'),
            footer = modal.querySelector('.unzer-footer');

        if (config.canClose === false) {
            utilities.hideElement(closeBtn);
        } else {
            window.addEventListener('keyup', closeOnEsc);
            closeBtn.addEventListener('click', close);
            closeBtnSpan.style.display = 'flex';
            modal.addEventListener('click', (event) => {
                if (event.target.id === 'adl-modal') {
                    event.preventDefault();
                    close();

                    return false;
                }
            });
        }

        if (config.title) {
            title.innerHTML = translationService.translate(config.title);
        } else {
            utilities.hideElement(title);
        }

        if (config.description) {
            description.innerHTML = translationService.translate(config.description)
        } else {
            utilities.hideElement(description);
        }

        if (config.className) {
            modal.classList.add(config.className);
        }

        body.append(...(Array.isArray(config.content) ? config.content : [config.content]));
        if (configuration.fullWidthBody) {
            body.classList.add('adlm--full-width');
        }

        if (!config.buttons && !config.footerLink) {
            utilities.hideElement(footer);
        } else {
            if (config.buttons) {
                const buttonsWrapper = Unzer.elementGenerator.createElement(
                    'div',
                    'unzer-buttons',
                    '',
                    null,
                    config.buttons.map(components.Button.create)
                );
                footer.append(buttonsWrapper);
            }

            if (config.footerLink) {
                footer.classList.add('adlm--with-link');
                footer.append(
                    Unzer.elementGenerator.createElement('a', '', config.footerLink.label, {
                        href: config.footerLink.href,
                        target: '_blank'
                    })
                );
            }
        }

        pageService.getPage().appendChild(modal);
        config.onOpen?.(modal);
    };

    return {
        open,
        close
    };
};

Unzer.components.Modal = {
    create: ModalComponent
};
