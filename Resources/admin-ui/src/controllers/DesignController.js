Unzer.DesignController = function () {

  /**
   * @type {HTMLElement}
   */
  const page = Unzer.pageService.getContentPage();

  /**
   * @type {undefined}
   */
  const languages = Unzer.config.locales;

  /**
   * @type {{shopNameColor: string, shopTaglineColor: string, headerColor: string, shopTaglineBackgroundColor: string,
   *     logoFile: string, shopNameBackground: string, name: [{locale: string, value: string}], tagline: [{locale:
   *     string, value: string}], logoImageUrl: string, headerFontColor: string}}
   */
  let selectedValues = {
    name: [
      {
        locale: "default",
        value: Unzer.config.store.storeName
      }
    ],
    tagline: [
      {
        locale: "default",
        value: ""
      }
    ],
    logoImageUrl: null,
    logoFile: null,
    headerColor: '#ffffff',
    shopTaglineBackgroundColor: '#ffffff',
    shopNameColor: '#000000',
    headerFontColor: '#ffffff',
    shopTaglineColor: '#000000',
    shopNameBackground: '#ffffff',
  };

  let current_name = Unzer.config.store.storeName;
  let current_tagline = "";

  let headerColor,
      shopTaglineBackgroundColor,
      shopNameColor,
      headerFontColor,
      shopTaglineColor,
      shopNameBackground;

  let colorFields;
  /**
   * display design page
   */

  this.display = () => {
    if (!Unzer.config.store.isLoggedIn) {
      Unzer.stateController.navigate('login');

      return;
    }

    getData();
  };

  /**
   * Gets payment page settings data
   */
  const getData = () => {
    Unzer.utilities.showLoader();

    Unzer.DesignService
        .getDesign()
        .then((result) => {

          if (result) {
            selectedValues.name = result?.shopName?.map(x => ({
              locale: x.locale,
              value: x.value,
            })) || [{ locale: 'default', value: Unzer.config.store.storeName }];

            if (selectedValues.name.length == 0) {
              selectedValues.name = [{ locale: 'default', value: Unzer.config.store.storeName }];
            }

            current_name = selectedValues?.name?.find(x => x.locale == 'default')?.value ?? Unzer.config.store.storeName;


            selectedValues.tagline = result?.shopTagline?.map(x => ({
              locale: x.locale,
              value: x.value,
            })) || [{ locale: 'default', value: '' }];

            current_tagline = selectedValues?.tagline?.find(x => x.locale == 'default')?.value ?? '';

            selectedValues.logoImageUrl = result.logoImageUrl || selectedValues.logoImageUrl;
            selectedValues.headerColor = result.headerBackgroundColor || selectedValues.headerColor;
            selectedValues.headerFontColor = result.headerFontColor || selectedValues.headerFontColor
            selectedValues.shopNameColor = result.shopNameFontColor || selectedValues.shopNameColor;
            selectedValues.shopTaglineColor = result.shopTaglineFontColor || selectedValues.shopTaglineColor;
            selectedValues.shopTaglineBackgroundColor = result.shopTaglineBackgroundColor || selectedValues.shopTaglineBackgroundColor;
            selectedValues.shopNameBackground = result.shopNameBackgroundColor || selectedValues.shopNameBackground;
          }

          render();

        })
        .catch((ex) => {
          Unzer.utilities.createToasterMessage(ex.errorMessage, true);
        })
        .finally(Unzer.utilities.hideLoader);
  }


  /**
   * render design page
   */
  const render = () => {
    Unzer.pageService.clearComponent(page);

    page.append(
        Unzer.components.PageHeading.create({
          title: "design.heading.title",
          description: "design.heading.description",
          backIcon: true,
          button: Unzer.components.Button.createList([
            {
              label: "design.heading.previewLabel",
              type: "primary",
              className: "adlt--export",
              onClick: () => {
                if (validateColorFields() && (!selectedValues.logoImageUrl || Unzer.validationService.validateUrl(
                    urlField,
                    selectedValues.logoImageUrl,
                    "validation.invalidUrl",
                    '.unzer-file-upload-input',
                    '.unzer-dropdown-description'
                ))) {
                  createPreviewPage();
                }
              }
            },
            {
              label: "design.heading.saveLabel",
              type: "secondary",
              onClick: () => {
                if (validateColorFields()  && (!selectedValues.logoImageUrl || Unzer.validationService.validateUrl(
                    urlField,
                    selectedValues.logoImageUrl,
                    "validation.invalidUrl",
                    '.unzer-file-upload-input',
                    '.unzer-dropdown-description'
                ))) {
                  saveChanges();
                }
              }
            },
          ])
        }));

    const urlField = Unzer.components.FileUploadComponent.create({
      label: "design.translations.logoImageUrl",
      description: "design.translations.logoImageUrlDescription",
      value: selectedValues.logoImageUrl,
      onFileSelect: (file) => {
        if (file instanceof File) {
          selectedValues.logoFile = file;
          selectedValues.logoImageUrl = null;
        }
      },
      onChange: (value) => {
        selectedValues.logoImageUrl = value;
        selectedValues.logoFile = null;
      }
    });

    page.append(
        Unzer.components.TwoColumnLayout.create(
            [
              Unzer.components.TextDropdownComponent.create({
                    isIcon: true,
                    value: "default",
                    options: languages?.map(x => ({ value: x.code, label: x.flag, title: x.name })),
                  }, {
                    maxWidth: false,
                    title: "design.translations.shopName",
                    subtitle: "design.translations.shopNameDescription",
                    value: selectedValues?.name?.find(x => x.locale == 'default')?.value ?? Unzer.config.store.storeName,
                  },
                  selectedValues?.name?.map(x => ({ locale: x.locale, value: x.value })),
                  (value) => {
                    selectedValues.name = value;
                    current_name = selectedValues?.name?.find(x => x.locale == 'default')?.value ?? Unzer.config.store.storeName;
                  },
                  'unzer-text-dropdown-max-width',
                  selectedValues?.name?.find(x => x.locale === 'default') ?? {
                    locale: 'default',
                    value: Unzer.config.store.storeName
                  }
              ),
              urlField

            ], [
              Unzer.components.TextDropdownComponent.create({
                    isIcon: true,
                    value: "default",
                    options: languages?.map(x => ({ value: x.code, label: x.flag, title: x.name })),
                  }, {
                    maxWidth: false,
                    title: "design.translations.shopTagline",
                    subtitle: "design.translations.shopTaglineDescription",
                    value: selectedValues?.tagline?.find(x => x.locale == 'default')?.value ?? '',
                  },
                  selectedValues?.tagline?.map(x => ({ locale: x.locale, value: x.value })),
                  (value) => {
                    selectedValues.tagline = value;
                    current_tagline = selectedValues?.tagline?.find(x => x.locale == 'default')?.value ?? '';
                  },
                  'unzer-text-dropdown-max-width',
                  selectedValues?.tagline?.find(x => x.locale === 'default') ?? {
                    locale: 'default',
                    value: '',
                  }
              ),
            ]),
        Unzer.components.PageHeading.create({
          title: "design.translations.title",
          className: "unzer-page-heading-padding-top",
        })
    );

    headerColor = Unzer.components.ColorPickerComponent.create({
      label: "design.translations.headerColor",
      description: "design.translations.headerColorDescription",
      defaultColor: selectedValues.headerColor,
      onColorChange: (color) => {
        selectedValues.headerColor = color;
      }
    });

    shopTaglineBackgroundColor = Unzer.components.ColorPickerComponent.create({
      label: "design.translations.shopTaglineBackgroundColor",
      description: "design.translations.shopTaglineBackgroundColorDescription",
      defaultColor: selectedValues.shopTaglineBackgroundColor,
      onColorChange: (color) => {
        selectedValues.shopTaglineBackgroundColor = color;
      }
    });

    shopNameColor = Unzer.components.ColorPickerComponent.create({
      label: "design.translations.shopNameColor",
      description: "design.translations.shopNameColorDescription",
      defaultColor: selectedValues.shopNameColor,
      onColorChange: (color) => {
        selectedValues.shopNameColor = color;
      }
    });

    headerFontColor = Unzer.components.ColorPickerComponent.create({
      label: "design.translations.headerFontColor",
      description: "design.translations.headerFontColorDescription",
      defaultColor: selectedValues.headerFontColor,
      onColorChange: (color) => {
        selectedValues.headerFontColor = color;
      }
    });

    shopTaglineColor = Unzer.components.ColorPickerComponent.create({
      label: "design.translations.shopTaglineColor",
      description: "design.translations.shopTaglineColor",
      defaultColor: selectedValues.shopTaglineColor,
      onColorChange: (color) => {
        selectedValues.shopTaglineColor = color;
      }
    });

    shopNameBackground = Unzer.components.ColorPickerComponent.create({
      label: "design.translations.shopNameBackground",
      description: "design.translations.shopNameBackgroundDescription",
      defaultColor: selectedValues.shopNameBackground,
      onColorChange: (color) => {
        selectedValues.shopNameBackground = color;
      }
    });


    page.append(
        Unzer.components.TwoColumnLayout.create([
          headerColor,
          shopTaglineBackgroundColor,
          shopNameColor,
        ], [
          headerFontColor,
          shopTaglineColor,
          shopNameBackground,
        ]));
    colorFields = [
      { name: 'headerColor', component: headerColor },
      { name: 'shopTaglineBackgroundColor', component: shopTaglineBackgroundColor },
      { name: 'shopNameColor', component: shopNameColor },
      { name: 'headerFontColor', component: headerFontColor },
      { name: 'shopTaglineColor', component: shopTaglineColor },
      { name: 'shopNameBackground', component: shopNameBackground }
    ];

  }

  const validateColorFields = () => {
    let isValid = true;

    colorFields.forEach(field => {
      const fieldName = field.name;
      const isFieldValid = Unzer.validationService.validateColorComponent(
          field.component,
          selectedValues[fieldName],
          "validation.invalidColorFormat",
          '.unzer-color-picker',
          '.unzer-color-description'
      );

      if (!isFieldValid) {
        isValid = false;
      }
    });

    return isValid;
  }

  /**
   * Save changes of payment page
   */

  function saveChanges() {
    Unzer.utilities.showLoader();

    const formData = new FormData();

    const nameArray = convertToLocaleArray(selectedValues.name);
    const taglineArray = convertToLocaleArray(selectedValues.tagline);


    for (const key in selectedValues) {
      if (key !== 'name' && key !== 'tagline') {
        formData.append(key, selectedValues[key]);
      }
    }

    formData.append('name', JSON.stringify(nameArray));
    formData.append('tagline', JSON.stringify(taglineArray));

    Unzer.DesignService.saveDesign(formData)
        .then((result) => {
          if (result.statusCode === 400) {
            Unzer.utilities.createToasterMessage("design.invalidUrl", true);

            return
          }

          selectedValues.logoImageUrl = result.logoImageUrl || selectedValues.logoImageUrl;
          Unzer.utilities.createToasterMessage("general.changesSaved", false);
          render();
        })
        .catch((ex) => {
          Unzer.utilities.createToasterMessage(ex.errorMessage, true);
        })
        .finally(Unzer.utilities.hideLoader);
  }

  /**
   * Save changes of payment page
   */
  function createPreviewPage() {
    Unzer.utilities.showLoader();

    const formData = new FormData();

    for (const key in selectedValues) {
      if (key !== 'name' && key !== 'tagline') {
        formData.append(key, selectedValues[key]);
      }
    }

    formData.append('name', JSON.stringify([["default", current_name]]));
    formData.append('tagline', JSON.stringify([["default", current_tagline]]));

    Unzer.DesignService.createPreviewPage(formData)
        .then((response) => {

          if (response.statusCode === 400) {
            Unzer.utilities.createToasterMessage("design.invalidUrl", true);

            return
          }

          if (typeof response.id === 'undefined') {
            Unzer.utilities.createToasterMessage("general.errors.general.unhandled", true);
            return;
          }
          var checkout = new window.checkout(response.id);
          checkout.init()
              .then(() => {
                checkout.open();
              })
              .catch((ex) => {
                Unzer.utilities.createToasterMessage(ex.errorMessage, true);
              });
        })
        .catch((ex) => {
          Unzer.utilities.createToasterMessage(ex.errorMessage, true);
        })
        .finally(Unzer.utilities.hideLoader);
  }

  /**
   * Converts into array
   * @param objects
   * @returns {*[]}
   */

  function convertToLocaleArray(objects) {
    let result = [];

    if (objects && Array.isArray(objects)) {
      objects.forEach(item => {
        result.push([item.locale, item.value]);
      });
    }

    return result;
  }
}
