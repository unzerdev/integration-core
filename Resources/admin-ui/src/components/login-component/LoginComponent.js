/**
 * @typedef LoginComponentModel
 *
 * @property {function} onLogin
 */

/**
 * Creates page heading component.
 *
 * @param {LoginComponentModel} props
 *
 * @constructor
 */

const LoginComponent = ({ onLogin }) => {
    const generator = Unzer.elementGenerator;
    let link = Unzer.config.store.mode === 'live' ? "https://insights.unzer.com" : "https://sbx-insights.unzer.com";
    const values = {
        environment: 'live',
        privateKey: '',
        publicKey: ''
    };
    const envField = Unzer.components.RadioButtonGroupField.create({
        label: 'login.environment.title',
        value: 'live',
        onChange: (value) => {
            values.environment = value;
            let link = value === 'live' ? "https://insights.unzer.com" : "https://sbx-insights.unzer.com";
            let descriptionTitleLink = document.getElementById('unzer-merchant-link');
            descriptionTitleLink.href = link;
            let descriptionLink = document.getElementById('unzer-merchant-link-description');
            descriptionLink.href = link;

            Unzer.components.PageHeader.updateEnvironment(value === 'sandbox');

        },
        options: [
            { label: 'login.environment.live', value: 'live' },
            { label: 'login.environment.sandbox', value: 'sandbox' }
        ]
    });
    const publicKey = Unzer.components.TextField.create({
        label: 'login.credentials.title',
        title: 'login.credentials.public',
        description: `login.credentials.description|${link}`,
        onChange: (value) => {
            values.publicKey = value;
        }
    });

    const privateKey = Unzer.components.TextField.create({
        title: 'login.credentials.private',
        type: 'password',
        onChange: (value) => {
            values.privateKey = value;
        }
    });


    const left = generator.createElement(
        'div',
        'unzer-login-left',
        '',
        [],
        [
            Unzer.components.PageHeading.create({
                title: 'login.heading.title',
                description: `login.heading.subtitle|${link}`
            }),
            envField,
            publicKey,
            privateKey,
            Unzer.components.Button.create({
                label: 'Continue',
                type: 'secondary',
                className: 'unzer-max-width',
                onClick: () => {
                    let isValid = true;
                    isValid &= Unzer.validationService.validateField(
                        publicKey,
                        values.publicKey.length === 0,
                        'validation.requiredField'
                    );
                    isValid &= Unzer.validationService.validateField(
                        privateKey,
                        values.privateKey.length === 0,
                        'validation.requiredField'
                    );

                    if (isValid) {
                        onLogin(values);
                    }
                }
            })
        ]
    );

    const needHelp = generator.createElement(
        'div',
        'unzer-footer-support',
        '',
        [],
        [
            generator.createElementFromHTML(Unzer.imagesProvider.supportIcon),
            generator.createElement('span', '', 'footer.support', [], [])
        ]
    );

    needHelp.addEventListener('click', () => {
        const supportUrl = Unzer.translationService.translate('footer.supportLink');
        window.open(supportUrl, '_blank');
    })

    const leftWrapper = generator.createElement(
        'div',
        'unzer-login-left-wrapper',
        '',
        [],
        [
            left,
            generator.createElement(
                'div',
                'unzer-footer-support-wrapper',
                '',
                [],
                [
                    needHelp
                ]
            )
        ]
    );

    const right = generator.createElement(
        'div',
        'unzer-login-right',
        '',
        [],
        [
            Unzer.components.PageHeading.create({
                title: 'login.description.title',
                description: 'login.description.subtitle',
                className: 'unzer-page-heading-bigger'
            }),
            generator.createElementFromHTML(Unzer.imagesProvider.loginImage)
        ]
    );

    return generator.createElement('div', 'unzer-login', '', [], [leftWrapper, right]);
};

Unzer.components.LoginComponent = {
    create: LoginComponent
};
