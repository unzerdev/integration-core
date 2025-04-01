/**
 * @typedef PaymentMethodComponentModel
 *
 * @property {string?} name
 * @property {string?} description
 * @property {string | null} image
 * @property {boolean} state
 * @property {function} onClick
 * @property {function} onChange
 */

/**
 * Creates a search box component.
 *
 * @param {PaymentMethodComponentModel} props
 *
 * @return HTMLElement
 * @constructor
 */
const PaymentMethodComponent = ({ description = '', image = null, name = '', state = false, onClick, onChange }) => {
    const generator = Unzer.elementGenerator;

    const headline = generator.createElement(
        'div',
        'unzer-payment-method-headline',
        '',
        [],
        [
            generator.createElement('span', 'unzer-payment-method-title', name, [], []),
            generator.createElement('span', 'unzer-payment-method-description', description, [], [])
        ]
    );
  const imageUrl = image !== "" ? `${Unzer.config.imagesUrl}/${image}.svg` : `${Unzer.config.imagesUrl}/default.svg` ;
  const defaultImageUrl = `${Unzer.config.imagesUrl}/default.svg`;
  const altName = Unzer.translationService.translate('paymentMethods.logo');
  const imgElement = generator.createElementFromHTML(
      `<img src="${imageUrl}" alt=${defaultImageUrl}>`
  );

  imgElement.onerror = function () {
    this.src = defaultImageUrl;
  };

    const logo = generator.createElement(
        'div',
        'unzer-payment-method-logo',
        '',
        [],
        [imgElement]
    );
    const paymentInfo = generator.createElement('div', 'unzer-payment-method-info', '', [], [logo, headline]);

    const settings = generator.createElement(
        'a',
        'unzer-payment-method-settings',
        state ? 'general.settings' : 'general.learnMore',
        [],
        []
    );

    onClick && settings.addEventListener('click', () => onClick());

    const operations = generator.createElement(
        'div',
        'unzer-payment-method-operations',
        '',
        [],
        [
            settings,
            Unzer.components.ToggleField.create({
                value: state,
                onChange: (value) => {
                    const translation = value ? 'general.settings' : 'general.learnMore';
                    settings.innerHTML = Unzer.translationService.translate(translation);
                    onChange(value);
                }
            })
        ]
    );

    return generator.createElement('div', 'unzer-payment-method-container', '', [], [paymentInfo, operations]);
};

Unzer.components.PaymentMethodComponent = {
    create: PaymentMethodComponent
};
