Unzer.DesignController = function () {

    /**
     *
     * @type {HTMLElement}
     */
    const page = Unzer.pageService.getContentPage();


    /**
     * renders credentials page
     * @param {StateParamsModel} params
     */
    this.display = (params) => {
        if (!Unzer.config.store.isConnected) {
            Unzer.stateController.navigate("login");

            return;
        }

        Unzer.pageService.clearComponent(page);

        page.append(
            Unzer.components.PageHeading.create({
                title: "design.heading.title",
                description: "design.heading.description",
                button: Unzer.components.Button.createList([
                    {
                        label: "design.heading.previewLabel",
                        type: "primary",
                        className: "adlt--export",
                        onClick: () => {

                        }
                    },
                    {
                        label: "design.heading.saveLabel",
                        type: "secondary",
                        onClick: () => {

                        }
                    },
                ])
            }),
            Unzer.components.TwoColumnLayout.create(
                [
                    Unzer.components.TextDropdownComponent.create({
                        isIcon: true,
                        value: "default",
                        options: [{ label: Unzer.imagesProvider.languageIcon, value: "default" }]
                    }, {
                        maxWidth: false,
                        title: "design.translations.shopName",
                        subtitle: "design.translations.shopNameDescription"
                    }, 'unzer-text-dropdown-padding-bottom'),
                    Unzer.components.FileUploadComponent.create({
                        label: "design.translations.logoImageUrl",
                        description: "design.translations.logoImageUrlDescription",
                        onFileSelect: (file) => {

                        }
                    }),

                ], [
                    Unzer.components.TextDropdownComponent.create({
                        isIcon: true,
                        value: "default",
                        options: [{ label: Unzer.imagesProvider.languageIcon, value: "default" }]
                    }, {
                        maxWidth: false,
                        title: "design.translations.shopTagline",
                        subtitle: "design.translations.shopTaglineDescription"
                    }),
                ]),
            Unzer.components.PageHeading.create({
                title: "design.translations.title",
                className: "unzer-page-heading-padding-top",
            }),

            Unzer.components.TwoColumnLayout.create([
                Unzer.components.ColorPickerComponent.create({
                    label: "design.translations.headerColor",
                    description: "design.translations.headerColorDescription"
                }),
                Unzer.components.ColorPickerComponent.create({
                    label: "design.translations.shopTaglineBackgroundColor",
                    description: "design.translations.shopTaglineBackgroundColorDescription"
                }),
                Unzer.components.ColorPickerComponent.create({
                    label: "design.translations.shopNameColor",
                    description: "design.translations.shopNameColorDescription"
                }),

            ], [

                Unzer.components.ColorPickerComponent.create({
                    label: "design.translations.headerFontColor",
                    description: "design.translations.headerFontColorDescription"
                }),
                Unzer.components.ColorPickerComponent.create({
                    label: "design.translations.shopTaglineColor",
                    description: "design.translations.shopTaglineColor"
                }),
                Unzer.components.ColorPickerComponent.create({
                    label: "design.translations.shopNameBackground",
                    description: "design.translations.shopNameBackgroundDescription"
                })
            ])
        )
    }
}
