(() => {
  /**
   * @typedef ValidationMessage
   * @property {string} code The message code.
   * @property {string} field The field name that the error is related to.
   * @property {string} message The error message.
   */

  const validationRule = {
    numeric: 'numeric',
    integer: 'integer',
    required: 'required',
    greaterThanZero: 'greaterThanZero',
    minValue: 'minValue',
    maxValue: 'maxValue',
    nonNegative: 'nonNegative',
    greaterThanX: 'greaterThanX'
  };

  const { pageService, utilities, translationService } = Unzer;

  /**
   * Validates if the input has a value. If the value is not set, adds an error class to the input element.
   *
   * @param {HTMLInputElement|HTMLSelectElement} input
   * @param {string?} message
   * @return {boolean}
   */
  const validateRequiredField = (input, message) => {
    return validateField(input, !input.value?.trim() || (input.type === 'checkbox' && !input.checked), message);
  };

  /**
   * Validates a numeric input.
   *
   * @param {HTMLInputElement} input
   * @param {string?} message
   * @return {boolean} Indication of the validity.
   */
  const validateNumber = (input, message) => {
    const ruleset = input.dataset?.validationRule ? input.dataset.validationRule.split(',') : [];
    let result = true;

    if (!validateField(input, Number.isNaN(input.value), message)) {
      return false;
    }

    const value = Number(input.value);
    ruleset.forEach((rule) => {
      if (!result) {
        // break on first false rule
        return;
      }

      let condition = false;
      let subValue = null;
      if (rule.includes('|')) {
        [rule, subValue] = rule.split('|');
      }

      // condition should be positive for valid values
      switch (rule) {
        case validationRule.integer:
          condition = Number.isInteger(value);
          break;
        case validationRule.greaterThanZero:
          condition = value > 0;
          break;
        case validationRule.minValue:
          condition = value >= Number(subValue);
          break;
        case validationRule.maxValue:
          condition = value <= Number(subValue);
          break;
        case validationRule.nonNegative:
          condition = value >= 0;
          break;
        case validationRule.required:
          condition = !!input.value?.trim();
          break;
        case validationRule.greaterThanX:
          condition = value >= Number(document.querySelector(`input[name="${subValue}"]`)?.value);
          break;
        default:
          return;
      }

      if (!validateField(input, !condition, message)) {
        result = false;
      }
    });

    return result;
  };

  /**
   * Validates a list of numbers.
   *
   * @param {HTMLInputElement} input
   * @param {boolean} [required=true]
   * @param {boolean} [decimal=true]
   * @returns {boolean}
   */
  const validateNumberList = (input, required = true, decimal = true) => {
    let error;
    const value = input.value;
    if (!value.trim()) {
      if (!required) {
        return true;
      }

      error = 'validation.requiredField';
    } else {
      const values = value.split(',').map((value) => value.trim());
      if (values.map((value) => Number.isNaN(Number(value)) || Number(value) <= 0).includes(true)) {
        error = decimal ? 'validation.invalidNumberInList' : 'validation.invalidWholeNumberInList';
      } else if (
          values.filter((value, index) => {
            return values.indexOf(value) !== index;
          }).length > 0
      ) {
        error = 'validation.duplicateNumberInList';
      } else if (!decimal) {
        values.forEach((value) => {
          if (!Number.isInteger(Number(value))) {
            error = 'validation.invalidWholeNumberInList';
          }
        });
      }
    }

    return validateField(input, !!error, error);
  };

  /**
   * Validates if the input is a valid email. If not, adds the error class to the input element.
   *
   * @param {HTMLInputElement} input
   * @param {string?} message
   * @return {boolean}
   */
  const validateEmail = (input, message) => {
    let regex =
        /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    return validateField(input, !regex.test(String(input.value).toLowerCase()), message);
  };

  /**
   * Validates if the input is a valid URL. If not, adds an error class to the input element.
   *
   * @param {HTMLInputElement} element
   * @param {string} input
   * @param {string?} message
   * @param {string?} childId
   * @param {string?} descriptionId
   * @return {boolean}
   */
  const validateUrl = (element, input, message, childId, descriptionId ) => {
    let regex = /(https?:\/\/)([\w\-])+\.([a-zA-Z]{2,63})([\/\w-]*)*\/?\??([^#\n\r]*)?#?([^\n\r]*)/m;

    return validateFieldGeneric(element, !regex.test(String(input).toLowerCase()), message, childId, descriptionId);
  };

  /**
   * Validates if the input field is longer than a specified number of characters.
   * If so, adds an error class to the input element.
   *
   * @param {HTMLInputElement} input
   * @param {string?} message
   * @return {boolean}
   */
  const validateMaxLength = (input, message) => {
    return validateField(input, input.dataset.maxLength && input.value.length > input.dataset.maxLength, message);
  };

  /**
   * Handles validation errors. These errors come from the back end.
   *
   * @param {ValidationMessage[]} errors
   */
  const handleValidationErrors = (errors) => {
    for (const error of errors) {
      markFieldGroupInvalid(`[name=${error.field}]`, error.message);
    }
  };

  /**
   * Marks a field as invalid.
   *
   * @param {string} fieldSelector The field selector.
   * @param {string} message The message to display.
   * @param {Element} [parent] A parent element.
   */
  const markFieldGroupInvalid = (fieldSelector, message, parent) => {
    if (!parent) {
      parent = pageService.getContentPage();
    }

    const inputEl = parent.querySelector(fieldSelector);
    inputEl && setError(inputEl, message);
  };


  /**
   * Sets error for an input.
   *
   * @param {HTMLElement} element
   * @param {string?} message
   * @param {string} childId
   * @param {string?} descriptionSpanId
   */
  const setErrorGeneric = (element, message, childId,  descriptionSpanId) => {

    if (!element) {
      return;
    }

    if(!childId) {
      return;
    }

    const descriptionSpan = element.querySelector(descriptionSpanId);

    if (descriptionSpan) {
      descriptionSpan.classList.add('hidden');
    }

    const child = element.querySelectorAll(childId);
    if (child) {
      child.forEach(x => x.classList.add("unzer-error"));
    }


    element.classList.add('adls--error');
    if (message) {
      let errorField = element.querySelector('.unzer-input-error');
      if (!errorField) {
        errorField = Unzer.elementGenerator.createElement('span', 'unzer-input-error', message);
        element.append(errorField);
      }

      errorField.innerHTML = translationService.translate(message);
    }
  };

  /**
   * Sets error for an input.
   *
   * @param {HTMLElement} element
   * @param {string?} message
   */
  const setError = (element, message) => {
    const parent = getFieldElement(element);

    const child = element.querySelectorAll('.unzer-label-input-field');

    if (!parent) {
      return;
    }

    if (child) {
      child.forEach(x => x.classList.add("unzer-label-input-field-error"));
    }

    parent.classList.add('adls--error');
    if (message) {
      let errorField = parent.querySelector('.unzer-input-error');
      if (!errorField) {
        errorField = Unzer.elementGenerator.createElement('span', 'unzer-input-error', message);
        parent.append(errorField);
      }

      errorField.innerHTML = translationService.translate(message);
    }
  };

  /**
   * Removes error from input form group element.
   *
   * @param {HTMLElement} element
   */
  const removeError = (element) => {
    getFieldElement(element)?.classList.remove('adls--error');
  };

  /**
   * Validate Color-picker-component
   *
   * @param element
   * @param errorCondition
   * @param message
   * @param {string?} childId
   * @param {string?} descriptionId
   * @returns {boolean}
   */

  const validateColorComponent = (element, value, message, childId, descriptionId) => {
    const hexPattern = /^#[0-9A-Fa-f]{6}$/;

    return validateFieldGeneric(element,!hexPattern.test(value),message, childId, descriptionId);
  }

  /**
   * Removes error from color picker field
   *
   * @param element
   * @param {string} childId
   * @param {string?} descriptionId
   */
  const removeErrorGeneric = (element, childId, descriptionId) => {
    if (!element) {
      return;
    }

    let descriptionSpan = element.querySelector(descriptionId);
    if (descriptionSpan) {
      descriptionSpan.classList.remove('hidden');
    }

    const field = element.querySelectorAll(childId);
    if (field) {
      field.forEach(x => x.classList.remove("unzer-error"));
    }

    const errorField = element.querySelector('.unzer-input-error');
    if (errorField) {
      errorField.remove();
    }
  };

  /**
   * Validates the condition against the input field and marks field invalid if the error condition is met.
   *
   * @param {HTMLElement} element
   * @param {boolean} errorCondition Error condition.
   * @param {string?} message
   *
   * @return {boolean}
   */
  const validateField = (element, errorCondition, message) => {
    if (errorCondition) {
      setError(element, message);

      return false;
    }

    removeError(element);

    return true;
  };

  /**
   * Validates the condition against the input field and marks field invalid if the error condition is met.
   *
   * @param {HTMLElement} element
   * @param {boolean} errorCondition Error condition.
   * @param {string?} message
   * @param {string?} childId
   * @param {string?} descriptionId
   * @return {boolean}
   */
  const validateFieldGeneric = (element, errorCondition, message, childId, descriptionId) => {
    if (errorCondition) {
      setErrorGeneric(element, message, childId, descriptionId);

      return false;
    }

    removeErrorGeneric(element, childId, descriptionId);

    return true;
  };

  const getFieldElement = (element) => {
    return element.classList.contains('adl-field-wrapper')
        ? element
        : utilities.getAncestor(element, 'adl-field-wrapper');
  };

  Unzer.validationService = {
    setError,
    removeError,
    validateEmail,
    validateNumber,
    validateNumberList,
    validateUrl,
    validateMaxLength,
    validateField,
    validateRequiredField,
    handleValidationErrors,
    validateColorComponent
  };
})();
