/**
 * @typedef PageHeadingComponentModel
 *
 * @property {string?} title
 * @property {string?} description
 * @property {string?} className
 * @property {HTMLElement?} button
 */

/**
 * Creates page heading component.
 *
 * @param {PageHeadingComponentModel} props
 *
 * @constructor
 */
const PageHeadingComponent = ({ title, description, className, button }) => {
    const { elementGenerator: generator } = Unzer;
    const cssClass = ['adl-page-heading'];
    className && cssClass.push(className);

    return generator.createElement('div', cssClass.join(' '), '', null, [
        generator.createElement('div', '', '', null, [
            generator.createElement('h2', 'unzer-title', title),
            generator.createElement('p', 'unzer-description', description)
        ]),
        button ? button : ''
    ]);
};

Unzer.components.PageHeading = {
    create: PageHeadingComponent
};
