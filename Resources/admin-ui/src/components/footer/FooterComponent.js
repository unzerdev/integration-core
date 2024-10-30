/**
 * @typedef FileUploadComponentModel
 *
 * @property {string?} version
 * @property {boolean?} hasNewVersion
 */

/**
 * Creates a file upload component that can also handle URLs.
 *
 * @param {FileUploadComponentModel} props
 *
 * @constructor
 */
const FooterComponent = ({ version, hasNewVersion = false}) => {
    const { elementGenerator: generator } = Unzer;

    const container = document.querySelector('#unzer-footer');
    Unzer.pageService.clearComponent(container);

    const support = generator.createElement('div', 'unzer-footer-support', '', [], [
        generator.createElementFromHTML(Unzer.imagesProvider.supportIcon),
        generator.createElement('span', '', 'footer.support', [], [])
    ]);

  support.addEventListener('click', function() {
    const supportUrl = Unzer.translationService.translate('footer.supportLink');
    window.open(supportUrl, '_blank');
  });

    const versionWrapper = generator.createElement('div', 'unzer-footer-version-wrapper', '', [], [
        generator.createElement('span', 'unzer-footer-version-version', `footer.version|${version}`)
    ]);

    if (hasNewVersion) {
        versionWrapper.append(generator.createElement('span', 'unzer-footer-version-new', 'footer.update', [], []));
    }

    const footerWrapper = generator.createElement('div', 'unzer-footer', '', [], [ versionWrapper, support]);
    container.append(footerWrapper)
}

const ClearFooter = () => {
    const container = document.querySelector('#unzer-footer');
    Unzer.pageService.clearComponent(container);
}

Unzer.components.FooterComponent = {
    render: FooterComponent,
    clear: ClearFooter
};
