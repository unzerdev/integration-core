Unzer.DesignController = function () {

  /**
   *
   * @type {HTMLElement}
   */
  const page = Unzer.pageService.getContentPage();

  const languages = Unzer.config.locales;

  const dropdownOptions = languages.map(language => {
    const imageUrl = language.flag !== "default"
        ? `${Unzer.config.flagsUrl}/${language.flag}.svg`
        : `${Unzer.config.flagsUrl}/country-xx.svg`;

    return {
      label: `<img src="${imageUrl}" alt="${language.code}" style="width: 30px; height: 15px; margin-right: 5px;"/>`,
      value: language.code
    };
  });

  let selectedValues = {
    shopName: "",
    shopNameCode: "",
    logoImageUrl: "",
    shopTagline: "",
    shopTaglineCode: "",
    headerColor: "",
    shopTaglineBackgroundColor: "",
    shopNameColor: "",
    headerFontColor: "",
    shopTaglineColor: "",
    shopNameBackground: "",
  };


  /**
   * renders credentials page
   * @param {StateParamsModel} params
   */
  this.display = (params) => {
    if (!Unzer.config.store.isLoggedIn) {
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
              onClick: saveChanges
            },
          ])
        }),
        Unzer.components.TwoColumnLayout.create(
            [
              Unzer.components.TextDropdownComponent.create({
                isIcon: true,
                value: "default",
                options: [{ label: Unzer.imagesProvider.languageIcon, value: "default" },
                  ...dropdownOptions
                ],
                onChange: (value) => {
                  selectedValues.shopNameCode = value;
                }
              }, {
                maxWidth: false,
                title: "design.translations.shopName",
                subtitle: "design.translations.shopNameDescription",
                onChange: (value) => {
                  selectedValues.shopName = value;
                }
              }, 'unzer-text-dropdown-padding-bottom'),
              Unzer.components.FileUploadComponent.create({
                label: "design.translations.logoImageUrl",
                description: "design.translations.logoImageUrlDescription",
                onFileSelect: (file) => {
                  selectedValues.logoImageUrl = file;
                },
                onChange: (file) => {
                  selectedValues.logoImageUrl = file;
                }
              }),

            ], [
              Unzer.components.TextDropdownComponent.create({
                isIcon: true,
                value: "default",
                options: [{ label: Unzer.imagesProvider.languageIcon, value: "default" },
                    ...dropdownOptions
                ],
                onChange: (value) => {
                  selectedValues.shopTaglineCode = value;
                }
              }, {
                maxWidth: false,
                title: "design.translations.shopTagline",
                subtitle: "design.translations.shopTaglineDescription",
                onChange: (value) => {
                  selectedValues.shopTagline = value;
                }
              }),
            ]),
        Unzer.components.PageHeading.create({
          title: "design.translations.title",
          className: "unzer-page-heading-padding-top",
        }),

        Unzer.components.TwoColumnLayout.create([
          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.headerColor",
            description: "design.translations.headerColorDescription",
            onColorChange: (color) => {
              selectedValues.headerColor = color;
            }
          }),
          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.shopTaglineBackgroundColor",
            description: "design.translations.shopTaglineBackgroundColorDescription",
            onColorChange: (color) => {
              selectedValues.shopTaglineBackgroundColor = color;
            }
          }),
          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.shopNameColor",
            description: "design.translations.shopNameColorDescription",
            onColorChange: (color) => {
              selectedValues.shopNameColor = color;
            }
          }),

        ], [

          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.headerFontColor",
            description: "design.translations.headerFontColorDescription",
            onColorChange: (color) => {
              selectedValues.headerFontColor = color;
            }
          }),
          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.shopTaglineColor",
            description: "design.translations.shopTaglineColor",
            onColorChange: (color) => {
              selectedValues.shopTaglineColor = color;
            }
          }),
          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.shopNameBackground",
            description: "design.translations.shopNameBackgroundDescription",
            onColorChange: (color) => {
              selectedValues.shopNameBackground = color;
            }
          })
        ])
    )
  }

  function saveChanges() {
    Unzer.utilities.showLoader();
    console.log(selectedValues);

    Unzer.DesignService.saveDesign(selectedValues)
        .then(() => {
          console.log(selectedValues);
        })
        .catch((ex) => {

        })
        .finally(Unzer.utilities.hideLoader);
  }
}
