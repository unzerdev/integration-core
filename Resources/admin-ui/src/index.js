const translation = require('./lang/en.json');

window.Unzer = {
    ...(window.Unzer || {}),
    ...{
        ...window.Unzer?.config || {},
        components: {},
        models: {},
        translations: {
            default: translation,
            current: translation
        }
    }
};
require('./services/AjaxService');
require('./services/ResponseService');
require('./services/PageService');
require('./services/TranslationService');
require('./services/UtilityService');
require('./services/ValidationService');
require('./services/LoginService');
require('./services/StoresService');
require('./services/CheckoutService');
require('./services/PaymentMethodService');
require('./services/DesignService');
require('./services/CountriesService');

require('./components/ImagesProvider');
require('./components/ElementGenerator');
require('./components/data-table/DataTableComponent');
require('./components/dropdown/DropdownComponent');
require('./components/multiselect-dropdown/MultiselectDropdownComponent');
require('./components/modal/ModalComponent');
require('./components/page-header/PageHeaderComponent');
require('./components/table-filter/TableFilterComponent');
require('./components/button/ButtonComponent');
require('./components/page-heading/PageHeadingComponent');
require('./components/form-fields/index.js');
require('./components/two-column-layout/TwoColumnLayoutComponent');
require('./components/two-column-row-layout/TwoColumnRowLayoutComponent');
require('./components/search-box/SearchBoxComponent');
require('./components/payment-method-component/PaymentMethodComponent');
require('./components/login-component/LoginComponent');
require('./components/text-dropdown-field/TextDropdownComponent');
require('./components/color-picker-component/ColorPickerComponent');
require('./components/file-upload-component/FileUploadComponent');
require('./components/footer/FooterComponent');

require('./controllers/StateController');
require('./controllers/CheckoutController');
require('./controllers/LoginController');
require('./controllers/CredentialsController');
require('./controllers/DesignController');

