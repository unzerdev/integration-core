Unzer.components.NumberField = {
    /**
     * @param {FormField & {isInteger: boolean, validationRule?: string}} config
     * @returns {HTMLElement}
     */
    create: (config) => {
        const validationRules = [];
        config.isInteger && validationRules.push('integer');
        config.validationRule && validationRules.push(config.validationRule);

        return Unzer.components.TextField.create({
            type: 'number',
            step: config.isInteger ? '1' : '0.01',
            dataset: {
                validationRule: validationRules.join(',')
            },
            ...config
        });
    }
};
