/**
 * @typedef Amount
 * @property {number} minAmount
 * @property {number} maxAmount
 */
/**
 * @typedef {FormField} MoneyInputComponentModel
 * @property {Amount?} value
 * @property {number?} step
 * @property {string?} currencyPlaceholder
 * @property {string?} minAmountTitle,
 * @property {string?} maxAmountTitle,
 * @property {(value: Amount) => any?} onChange
 */

/**
 * Money input component.
 *
 * @param {MoneyInputComponentModel} props
 */
const MoneyInputFieldComponent = ({
    name,
    value = {},
    minAmountTitle,
    maxAmountTitle,
    label,
    description,
    error,
    step = 1,
    currencyPlaceholder,
    onChange
}) => {
    const { elementGenerator: generator } = Unzer;
    const handleChange = () => {
        let amountVal = wrapper.querySelector(`[name=${name}_amount]`).value;
        let currencyVal = wrapper.querySelector(`[name=${name}_currency]`).value;

      amountVal = amountVal.replace(/[^0-9.]/g, '');
      currencyVal = currencyVal.replace(/[^0-9.]/g, '');

      wrapper.querySelector(`[name=${name}_amount]`).value = amountVal;
      wrapper.querySelector(`[name=${name}_currency]`).value = currencyVal;

      const value = {
            minAmount: amountVal || null,
            maxAmount: currencyVal || null
        };

        wrapper.querySelector(`[name=${name}]`).value = JSON.stringify(value);
        onChange?.(value);
    };

  const handleArrowClick = (direction, targetWrapper) => {
    const amountInput = targetWrapper === 'input-wrapper'
        ? wrapper.querySelector(`[name=${name}_amount]`)
        : wrapper.querySelector(`[name=${name}_currency]`);

    let currentValue = parseFloat(amountInput.value) || 0;

    if (direction === 'up') {
      currentValue += step;
    } else if (direction === 'down') {
      currentValue = Math.max(0, currentValue - step);
    }

    amountInput.value = currentValue;
    handleChange();
  };

    const input = Unzer.components.TextField.create({
        value: value?.minAmount || 0,
        className: 'adl-text-input',
        type: 'text',
        inputmode: 'numeric',
        dataset: {
            validationRule: 'nonNegative'
        },
        name: name + '_amount',
        step: step,
        min: 0,
        title: minAmountTitle,
        onChange: handleChange
    })

    const dropdown = Unzer.components.TextField.create({
        value: value?.maxAmount || 0,
        className: 'adl-text-input',
        name: name + '_currency',
        type: 'text',
        min: 0,
        dataset: {
            validationRule: 'nonNegative'
        },
        title: maxAmountTitle,
        onChange: handleChange,
    })

  const inputWrapper = generator.createElement('div', 'input-wrapper', '', null, [
    input,
    generator.createElement('button', 'arrow-up', '', { type: 'button', onclick: () => handleArrowClick('up', 'input-wrapper') }),
    generator.createElement('button', 'arrow-down', '', { type: 'button', onclick: () => handleArrowClick('down', 'input-wrapper'), })
  ]);

  const dropdownWrapper = generator.createElement('div', 'dropdown-wrapper', '', null, [
    dropdown,
    generator.createElement('button', 'arrow-up', '', { type: 'button', onclick: () => handleArrowClick('up', 'dropdown-wrapper') }),
    generator.createElement('button', 'arrow-down', '', { type: 'button', onclick: () => handleArrowClick('down', 'dropdown-wrapper') })
  ])


  const wrapper = generator.createElement('div', 'adl-money-input', '', null, [
        generator.createElement('input', '', '', { type: 'hidden', name: name, value: JSON.stringify(value) }),
        inputWrapper,
        dropdownWrapper
    ]);


    return generator.createFieldWrapper(wrapper, label, description, error);
};


Unzer.components.MoneyInputField = {
    create: MoneyInputFieldComponent,
};
