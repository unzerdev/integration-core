/**
 * @typedef FileUploadComponentModel
 *
 * @property {string?} label
 * @property {string?} description
 * @property {string?} className
 * @property {Function?} onFileSelect
 */

/**
 * Creates a file upload component that can also handle URLs.
 *
 * @param {FileUploadComponentModel} props
 *
 * @constructor
 */
const FileUploadComponent = ({ label = 'aaa', description = 'aa', className = '', onFileSelect }) => {
    const { elementGenerator: generator } = Unzer;
    const cssClass = ['unzer-file-upload-box'];
    className && cssClass.push(className);

    const inputElement = generator.createElement('input', 'unzer-file-upload-input', '', {
        type: 'text',
        placeholder: ''
    });

    const fileInputElement = generator.createElement('input', 'unzer-file-input-field', '', {
        type: 'file',
        accept: 'image/*',
        id: "unzer-file-input",
    });

    onFileSelect && fileInputElement.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            inputElement.value = file.name;
            onFileSelect(file);
        }
    });

    const iconElement = generator.createElement('span', 'unzer-file-upload-icon', '', [], [
        generator.createElementFromHTML(Unzer.imagesProvider.uploadIcon)
    ]);

    iconElement.addEventListener('click', () => {
        fileInputElement.click();
    })

    const inputLabel = generator.createElement('label', 'unzer-label-input-label', label, { for: inputElement.id });
    const descriptionDiv = generator.createElement('span', 'unzer-dropdown-description', description, [], []);

    const inputWrapper = generator.createElement('div', 'unzer-file-upload-wrapper', '', [], [
        inputElement,
        inputLabel,
        fileInputElement,
        iconElement
    ]);

    return generator.createElement('div', cssClass.join(' '), '', null, [
        inputWrapper,
        descriptionDiv
    ]);
};

Unzer.components.FileUploadComponent = {
    create: FileUploadComponent
};
