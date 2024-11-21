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
    logoImageUrl: null,
    logoFile: null,
    backgroundImageUrl: null,
    backgroundFile: null,
    font: null,
    brandColor: '#FFFFFF',
    headerColor: '#FFFFFF',
    textColor: '#0C1332',
    linkColor: '#1B6AD7',
    backgroundColor: '#F1F1F3',
    footerColor: '#FFFFFF',
    cornerRadius: 6,
    shadows: false,
    hideUnzerLogo: false,
    hideBasket: false,
  };

  let current_name = Unzer.config.store.storeName;

  const fonts = [
    { label: 'Arial', value: 'ArialMT' },
    { label: 'Times New Roman', value: 'TimesNR' },
    { label: 'Courier New', value: 'CourierNewPS' },
    { label: 'Georgia', value: 'GeorgiaMT' },
    { label: 'Verdana', value: 'VerdanaLT' },
    { label: 'Helvetica', value: 'HelveticaNeue'}
  ];

  let headerColor,
      brandColor,
      textColor,
      linkColor,
      backgroundColor,
      footerColor;

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


            selectedValues.logoImageUrl = result.logoImageUrl || selectedValues.logoImageUrl;
            selectedValues.backgroundImageUrl = result.backgroundImageUrl || selectedValues.backgroundImageUrl;
            selectedValues.headerColor = result.headerColor || selectedValues.headerColor;
            selectedValues.brandColor = result.brandColor || selectedValues.brandColor;
            selectedValues.textColor = result.textColor || selectedValues.textColor;
            selectedValues.linkColor = result.linkColor || selectedValues.linkColor;
            selectedValues.backgroundColor = result.backgroundColor || selectedValues.backgroundColor;
            selectedValues.footerColor = result.footerColor || selectedValues.footerColor;
            selectedValues.font = result.font || selectedValues.font;
            selectedValues.shadows = result.shadows || selectedValues.shadows;
            selectedValues.hideUnzerLogo = result.hideUnzerLogo || selectedValues.hideUnzerLogo;
            selectedValues.hideBasket = result.hideBasket || selectedValues.hideBasket;
            selectedValues.cornerRadius = result.cornerRadius || selectedValues.cornerRadius;
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
                if (validateColorFields() && (!selectedValues.logoImageUrl || validateUrlFields(urlField,selectedValues.logoImageUrl)) &&
                    (!selectedValues.backgroundImageUrl || validateUrlFields(backgroundImageField,selectedValues.backgroundImageUrl))) {
                  createPreviewPage();
                }
              }
            },
            {
              label: "design.heading.saveLabel",
              type: "secondary",
              onClick: () => {
                if (validateColorFields() && (!selectedValues.logoImageUrl || validateUrlFields(urlField,selectedValues.logoImageUrl)) &&
                    (!selectedValues.backgroundImageUrl || validateUrlFields(backgroundImageField,selectedValues.backgroundImageUrl))) {
                  saveChanges();
                }
              }
            },
          ])
        }));

    const urlField = Unzer.components.FileUploadComponent.create({
      label: "paymentPageSettings.logoImageUrl",
      description: "paymentPageSettings.logoImageUrlDescription",
      value: selectedValues.logoImageUrl || "",
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

    const backgroundImageField = Unzer.components.FileUploadComponent.create({
      label: "paymentPageSettings.backgroundImageUrl",
      description: "paymentPageSettings.backgroundUrlDescription",
      value: selectedValues.backgroundImageUrl || "",
      onFileSelect: (file) => {
        if (file instanceof File) {
          selectedValues.backgroundFile = file;
          selectedValues.backgroundImageUrl = null;
        }
      },
      onChange: (value) => {
        selectedValues.backgroundImageUrl = value;
        selectedValues.backgroundFile = null;
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
                    title: "paymentPageSettings.shopName",
                    subtitle: "paymentPageSettings.shopNameDescription",
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
              Unzer.components.DropdownField.create({
                title: 'paymentPageSettings.fonts',
                description: "paymentPageSettings.fontsDescription",
                options: fonts,
                value: selectedValues.font,
                onChange: (value) => {
                  selectedValues.font = value;
                }
              }),
              backgroundImageField
            ]),
        Unzer.components.PageHeading.create({
          title: "design.translations.title",
          className: "unzer-page-heading-padding-top",
        })
    );

    headerColor = Unzer.components.ColorPickerComponent.create({
      label: "paymentPageSettings.headerColor",
      description: "paymentPageSettings.headerColorDescription",
      defaultColor: selectedValues.headerColor,
      onColorChange: (color) => {
        selectedValues.headerColor = color;
      }
    });

    brandColor = Unzer.components.ColorPickerComponent.create({
      label: "paymentPageSettings.brandColor",
      description: "paymentPageSettings.brandColorDescription",
      defaultColor: selectedValues.brandColor,
      onColorChange: (color) => {
        selectedValues.brandColor = color;
      }
    });

    textColor = Unzer.components.ColorPickerComponent.create({
      label: "paymentPageSettings.textColor",
      description: "paymentPageSettings.textColorDescription",
      defaultColor: selectedValues.textColor,
      onColorChange: (color) => {
        selectedValues.textColor = color;
      }
    });

    linkColor = Unzer.components.ColorPickerComponent.create({
      label: "paymentPageSettings.linkColor",
      description: "paymentPageSettings.linkColorDescription",
      defaultColor: selectedValues.linkColor,
      onColorChange: (color) => {
        selectedValues.linkColor = color;
      }
    });

    backgroundColor = Unzer.components.ColorPickerComponent.create({
      label: "paymentPageSettings.backgroundColor",
      description: "paymentPageSettings.backgroundColorDescription",
      defaultColor: selectedValues.backgroundColor,
      onColorChange: (color) => {
        selectedValues.backgroundColor = color;
      }
    });

    footerColor = Unzer.components.ColorPickerComponent.create({
      label: "paymentPageSettings.footerColor",
      description: "paymentPageSettings.footerColorDescription",
      defaultColor: selectedValues.footerColor,
      onColorChange: (color) => {
        selectedValues.footerColor = color;
      }
    });


    page.append(
        Unzer.components.TwoColumnLayout.create([
          headerColor,
          brandColor,
          textColor,
        ], [
          linkColor,
          backgroundColor,
          footerColor,
        ]));

    colorFields = [
      { name: 'headerColor', component: headerColor },
      { name: 'brandColor', component: brandColor },
      { name: 'textColor', component: textColor },
      { name: 'linkColor', component: linkColor },
      { name: 'backgroundColor', component: backgroundColor },
      { name: 'footerColor', component: footerColor }
    ];

    const hideBasketToggle = Unzer.components.ToggleField.create({
      label: "paymentPageSettings.basketData",
      description: "paymentPageSettings.basketDataDescription",
      value: selectedValues.hideBasket ?? false,
      onChange: (value) => {
        selectedValues.hideBasket = value;
      }
    });

    const hideUnzerLogoToggle = Unzer.components.ToggleField.create({
      label: "paymentPageSettings.hideUnzerLogo",
      description: "paymentPageSettings.hideUnzerLogo",
      value: selectedValues.hideUnzerLogo ?? false,
      onChange: (value) => {
        selectedValues.hideUnzerLogo = value;
      }
    });

    const shadowsToggle = Unzer.components.ToggleField.create({
      label: "paymentPageSettings.shadows",
      description: "paymentPageSettings.shadowsDescription",
      value: selectedValues.shadows ?? false,
      onChange: (value) => {
        selectedValues.shadows = value;
      }
    });

    const handleArrowClick = (step) => {
      let cornerRadius = cornerRadiusField.querySelector(`[name=${'corner-radius'}]`);
      let currentValue = parseFloat(cornerRadius.value) || 0;

      let input = Math.max(0, currentValue + step);
      cornerRadiusField.querySelector(`[name=${'corner-radius'}]`).value = JSON.stringify(input);
      handleChange(input);
    }

    const handleChange = (value) => {
      const numericValue = String(value).replace(/[^0-9.]/g, '');

      cornerRadiusField.querySelector(`[name=${'corner-radius'}]`).value = numericValue;

      selectedValues.cornerRadius = numericValue;
    };

    const cornerRadiusField = Unzer.components.TextField.create({
      title: 'paymentPageSettings.cornerRadius',
      type: 'text',
      class: 'adl-text-input',
      name: 'corner-radius',
      min: 0,
      value: selectedValues.cornerRadius,
      maxWidth: false,
      onChange: handleChange
    });

    const { elementGenerator: generator } = Unzer;

    const descriptionSpan = generator.createElement(
        'span',
        'description',
        'paymentPageSettings.cornerRadiusDescription',
        [],
        []
    )

    const cornerRadiusWrapper = generator.createElement('div', 'surcharge-wrapper', '', null, [
      cornerRadiusField,
      descriptionSpan,
      generator.createElement('button', 'arrow-up', '', {
        type: 'button',
        onclick: () => {
          handleArrowClick(1);
        }
      }),
      generator.createElement('button', 'arrow-down', '', {
        type: 'button',
        onclick: () => {
          handleArrowClick(-1);
        }
      })
    ]);

    page.append(
        Unzer.components.TwoColumnLayout.create([
          cornerRadiusWrapper
        ], [
          hideBasketToggle,
          hideUnzerLogoToggle,
          shadowsToggle
        ]));
  }

  /**
   * @returns {boolean}
   */
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
   * @returns {boolean}
   */
  const validateUrlFields = (field, value) => {
    return Unzer.validationService.validateUrl(
        field,
        value,
        "validation.invalidUrl",
        '.unzer-file-upload-input',
        '.unzer-dropdown-description'
    );
  }

  /**
   * Save changes of payment page
   */

  function saveChanges() {
    Unzer.utilities.showLoader();

    const formData = new FormData();

    const nameArray = convertToLocaleArray(selectedValues.name);


    for (const key in selectedValues) {
      if (key !== 'name') {
        formData.append(key, selectedValues[key]);
      }
    }

    formData.append('name', JSON.stringify(nameArray));

    Unzer.DesignService.saveDesign(formData)
        .then((result) => {
          if (result.statusCode === 400) {
            Unzer.utilities.createToasterMessage("design.invalidUrl", true);

            return
          }

          selectedValues.logoImageUrl = result.logoImageUrl || selectedValues.logoImageUrl;
          selectedValues.backgroundImageUrl = result.backgroundImageUrl || selectedValues.backgroundImageUrl;
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
      if (key !== 'name') {
        formData.append(key, selectedValues[key]);
      }
    }

    formData.append('name', JSON.stringify([["default", current_name]]));

    Unzer.DesignService.createPreviewPage(formData)
        .then((response) => {

          if (response.statusCode === 400) {
            Unzer.utilities.createToasterMessage("design.invalidUrl", true);

            return
          }

          if (typeof response.paypageId === 'undefined') {
            Unzer.utilities.createToasterMessage("general.errors.general.unhandled", true);
            return;
          }


          const unzerContainer = document.getElementById("unzer-container");
          unzerContainer.innerHTML = `
            <unzer-payment publicKey="${Unzer.config.store.publicKey}">
                <unzer-pay-page
                    id="checkout"
                    payPageId="${response.paypageId}"
                ></unzer-pay-page>
            </unzer-payment>
        `;
          const checkout = document.getElementById("checkout");
          checkout.abort(function (data) {
            console.log("checkout -> aborted");
          });

          // Subscribe to the success event
          checkout.success(function (data) {
            console.log("checkout -> success", data);
            window.location.href = "ReturnController.php";
          });

          // Subscribe to the error event
          checkout.error(function (error) {
            console.log("checkout -> error", error);
          });


          checkout.open();
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
