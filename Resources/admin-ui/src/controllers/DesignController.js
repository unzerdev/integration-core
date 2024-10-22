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
        locale: "defaul",
        value: ""
      }
    ],
    tagline: [
      {
        locale: "default",
        value: ""
      }
    ],
    logoImageUrl: "",
    logoFile: "",
    headerColor: "",
    shopTaglineBackgroundColor: "",
    shopNameColor: "",
    headerFontColor: "",
    shopTaglineColor: "",
    shopNameBackground: "",
  };

  /**
   * display design page
   */

  this.display = (params) => {
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
            console.log(result);
            selectedValues.name = result?.shopName?.map(x => ({
              locale: x.locale,
              value: x.value
            })) || [{ locale: 'default', value: '' }];
            selectedValues.tagline = result?.shopTagline?.map(x => ({
              locale: x.locale,
              value: x.value
            })) || [{ locale: 'default', value: '' }];


            console.log("Logo Image URL:", result.logoImageUrl);
            selectedValues.logoImageUrl = result.logoImageUrl || '';
            selectedValues.headerColor = result.headerBackgroundColor || '';
            selectedValues.headerFontColor = result.headerFontColor || '';
            selectedValues.shopNameColor = result.shopNameFontColor || '';
            selectedValues.shopTaglineColor = result.shopTaglineFontColor || '';
            selectedValues.shopTaglineBackgroundColor = result.shopTaglineBackgroundColor || '';
            selectedValues.shopNameBackground = result.shopNameBackgroundColor || '';

            console.log(selectedValues);
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
                    options: languages?.map(x => ({ value: x.code, label: x.flag })),
                  }, {
                    maxWidth: false,
                    title: "design.translations.shopName",
                    subtitle: "design.translations.shopNameDescription",
                    value: selectedValues?.name?.find(x => x.locale == 'default')?.value ?? '',
                  },
                  selectedValues?.name?.map(x => ({ locale: x.locale, value: x.value })),
                  (value) => {
                    selectedValues.name = value;
                  },
                  'unzer-text-dropdown-max-width',
                  selectedValues?.name?.find(x => x.locale === 'default') ?? {
                    locale: 'default',
                    value: ''
                  }
              ),
              Unzer.components.FileUploadComponent.create({
                label: "design.translations.logoImageUrl",
                description: "design.translations.logoImageUrlDescription",
                value: selectedValues.logoImageUrl,
                onFileSelect: (file) => {
                  if (file instanceof File) {
                    selectedValues.logoFile = file;
                    selectedValues.logoImageUrl = '';
                  }
                },
                onChange: (value) => {
                  selectedValues.logoImageUrl = value;
                  selectedValues.logoFile = null;
                }
              }),

            ], [
              Unzer.components.TextDropdownComponent.create({
                    isIcon: true,
                    value: "default",
                    options: languages?.map(x => ({ value: x.code, label: x.flag })),
                  }, {
                    maxWidth: false,
                    title: "design.translations.shopTagline",
                    subtitle: "design.translations.shopTaglineDescription",
                    value: selectedValues?.tagline?.find(x => x.locale == 'default')?.value ?? '',
                  },
                  selectedValues?.tagline?.map(x => ({ locale: x.locale, value: x.value })),
                  (value) => {
                    selectedValues.tagline = value;
                  },
                  'unzer-text-dropdown-max-width',
                  selectedValues?.tagline?.find(x => x.locale === 'default') ?? {
                    locale: 'default',
                    value: ''
                  }
              ),
            ]),
        Unzer.components.PageHeading.create({
          title: "design.translations.title",
          className: "unzer-page-heading-padding-top",
        }),

        Unzer.components.TwoColumnLayout.create([
          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.headerColor",
            description: "design.translations.headerColorDescription",
            defaultColor: selectedValues.headerColor,
            onColorChange: (color) => {
              selectedValues.headerColor = color;
            }
          }),
          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.shopTaglineBackgroundColor",
            description: "design.translations.shopTaglineBackgroundColorDescription",
            defaultColor: selectedValues.shopTaglineBackgroundColor,
            onColorChange: (color) => {
              selectedValues.shopTaglineBackgroundColor = color;
            }
          }),
          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.shopNameColor",
            description: "design.translations.shopNameColorDescription",
            defaultColor: selectedValues.shopNameColor,
            onColorChange: (color) => {
              selectedValues.shopNameColor = color;
            }
          }),

        ], [

          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.headerFontColor",
            description: "design.translations.headerFontColorDescription",
            defaultColor: selectedValues.headerFontColor,
            onColorChange: (color) => {
              selectedValues.headerFontColor = color;
            }
          }),
          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.shopTaglineColor",
            description: "design.translations.shopTaglineColor",
            defaultColor: selectedValues.shopTaglineColor,
            onColorChange: (color) => {
              selectedValues.shopTaglineColor = color;
            }
          }),
          Unzer.components.ColorPickerComponent.create({
            label: "design.translations.shopNameBackground",
            description: "design.translations.shopNameBackgroundDescription",
            defaultColor: selectedValues.shopNameBackground,
            onColorChange: (color) => {
              selectedValues.shopNameBackground = color;
            }
          })
        ])
    )
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

    // Dodajte nameArray i taglineArray u formData
    formData.append('name', JSON.stringify(nameArray));
    formData.append('tagline', JSON.stringify(taglineArray));

    Unzer.DesignService.saveDesign(formData)
        .then((response) => {
          console.log(response);
        })
        .catch((ex) => {
          Unzer.utilities.createToasterMessage(ex.errorMessage, true);
        })
        .finally(Unzer.utilities.hideLoader);
  }

  /**
   * Converts into array
   * @param array
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
