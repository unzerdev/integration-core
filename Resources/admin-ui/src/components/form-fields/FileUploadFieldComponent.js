/**
 * @typedef {FormField} FileUploadFieldComponentModel
 * @property {string[]} supportedMimeTypes
 * @property {string?} invalidTypeErrorKey
 * @property {boolean?} [hasFilePreview=true]
 */

/**
 * Creates a file upload field.
 *
 * @param {FileUploadFieldComponentModel} props
 * @returns {HTMLDivElement}
 */
const FileUploadFieldComponent = ({
    name,
    placeholder,
    label,
    description,
    error,
    horizontal,
    value,
    onChange,
    supportedMimeTypes,
    invalidTypeErrorKey = 'validation.invalidImageType',
    hasFilePreview = true
}) => {
    const { elementGenerator: generator } = Unzer;

    /**
     * Prevents default event handling.
     * @param {Event} e
     */
    const preventDefaults = (e) => {
        e.preventDefault();
        e.stopPropagation();
    };

    const setActive = (e) => {
        preventDefaults(e);
        wrapper.classList.add('adls--active');
    };

    const setInactive = (e) => {
        preventDefaults(e);
        wrapper.classList.remove('adls--active');
    };

    const previewFile = (file, img) => {
        let reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onloadend = function () {
            img.src = reader.result;
        };
    };

    const handleDrop = (e) => {
        const file = e.dataTransfer?.files?.[0] || null;
        if (file) {
            handleFileChange(file);
        }
    };

    const handleFileChange = (file) => {
        if (!supportedMimeTypes.includes(file.type)) {
            Unzer.validationService.setError(wrapper, invalidTypeErrorKey);
            return;
        }

        if (file.size > 10000000) {
            Unzer.validationService.setError(wrapper, 'validation.invalidImageSize');
            return;
        }

        onChange(file);
        Unzer.validationService.removeError(wrapper);
        textElem.innerText = file.name;
        if (hasFilePreview) {
            textElem.classList.remove('adls--empty');
            const img = generator.createElement('img');
            textElem.prepend(img);
            previewFile(file, img);
        }
    };

    const wrapper = generator.createElement('div', 'adl-file-drop-zone unzer-field-component');
    const labelElem = generator.createElement('label');
    const textElem = generator.createElement('span', 'unzer-file-label' + (!value ? ' adls--empty' : ''), placeholder);
    if (value) {
        textElem.prepend(generator.createElement('img', '', '', { src: value }));
    }

    const fileUpload = generator.createElement(
        'input',
        '',
        '',
        { type: 'file', accept: supportedMimeTypes.join(','), name: name }
    );
    fileUpload.addEventListener('change', () => handleFileChange(fileUpload.files?.[0]));

    labelElem.append(textElem, fileUpload);
    wrapper.append(labelElem);

    ['dragenter', 'dragover'].forEach((eventName) => {
        wrapper.addEventListener(eventName, setActive, false);
    });
    ['dragleave', 'drop'].forEach((eventName) => {
        wrapper.addEventListener(eventName, setInactive, false);
    });
    wrapper.addEventListener('drop', handleDrop, false);

    return generator.createFieldWrapper(wrapper, label, description, error, horizontal);
};

Unzer.components.FileUploadField = {
    create: FileUploadFieldComponent
};
