/**
 * @typedef PageHeadingComponentModel
 *
 * @property {string?} title
 * @property {string?} description
 * @property {string?} className
 * @property {boolean?} backIcon
 * @property {HTMLElement?} button
 */

/**
 * Creates page heading component.
 *
 * @param {PageHeadingComponentModel} props
 *
 * @constructor
 */
const PageHeadingComponent = ({ title, description, className, button, backIcon = false }) => {
    const { elementGenerator: generator } = Unzer;
    const cssClass = ['adl-page-heading'];
    className && cssClass.push(className);

    const titleDiv = generator.createElement('h2', 'unzer-title', title);
    if (backIcon) {
        let backArrow = generator.createElementFromHTML(Unzer.imagesProvider.backIcon);
        backArrow.addEventListener('click', () => {
            Unzer.components.PageHeader.updateEnvironment(Unzer.config.store.mode !== 'live', false);
            window.history.back()
        });

        let iconWrapper = generator.createElement('div', 'unzer-title-back', '', {}, [
            backArrow
        ])

        titleDiv.prepend(iconWrapper);
    }

    return generator.createElement('div', cssClass.join(' '), '', null, [
        generator.createElement('div', '', '', null, [
            titleDiv,
            generator.createElement('p', 'unzer-description', description)
        ]),
        button ? button : ''
    ]);
};

Unzer.components.PageHeading = {
    create: PageHeadingComponent
};
