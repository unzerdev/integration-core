/**
 * @typedef SearchBoxComponentModel
 *
 * @property {string?} placeholder
 * @property {string?} className
 * @property {Function?} onSearch
 */

/**
 * Creates a search box component.
 *
 * @param {SearchBoxComponentModel} props
 *
 * @constructor
 */
const SearchBoxComponent = ({ placeholder, className, onSearch }) => {
    const { elementGenerator: generator } = Unzer;
    const cssClass = ['unzer-search-box'];
    className && cssClass.push(className);

    const inputElement = generator.createElement('input', 'unzer-search-input', '', {
        type: 'text',
        placeholder: placeholder || 'Search...'
    });

    onSearch && inputElement.addEventListener('input', () => {
        onSearch(inputElement.value)
    });

    const iconElement = generator.createElement('div', 'unzer-search-icon', '', null, [
        generator.createElementFromHTML(Unzer.imagesProvider.searchIcon)
    ]);


    return generator.createElement('div', cssClass.join(' '), '', null, [
        inputElement,
        iconElement
    ]);
};

Unzer.components.SearchBox = {
    create: SearchBoxComponent
};
