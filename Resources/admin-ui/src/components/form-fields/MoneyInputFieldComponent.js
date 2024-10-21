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

        const value = {
            minAmount: amountVal || null,
            maxAmount: currencyVal || null
        };

        wrapper.querySelector(`[name=${name}]`).value = JSON.stringify(value);
        onChange?.(value);
    };

    const input = Unzer.components.TextField.create({
        value: value?.minAmount || 0,
        className: 'adl-text-input',
        type: 'number',
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
        name: name + '_currency',
        type: 'number',
        min: 0,
        dataset: {
            validationRule: 'nonNegative'
        },
        title: maxAmountTitle,
        onChange: handleChange,
    })

    const wrapper = generator.createElement('div', 'adl-money-input', '', null, [
        generator.createElement('input', '', '', { type: 'hidden', name: name, value: JSON.stringify(value) }),
        input,
        dropdown
    ]);

    return generator.createFieldWrapper(wrapper, label, description, error);
};


Unzer.components.MoneyInputField = {
    create: MoneyInputFieldComponent,
};
