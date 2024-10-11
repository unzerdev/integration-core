/**
 * @typedef StateParamsModel
 * @property {string?} page
 * @property {string?} stateParam
 * @property {any?} config
 */
/**
 * @typedef Merchant
 * @property {string} merchantId
 * @property {string} merchantName
 */
const { utilities, pageService } = Unzer;

let currentState = '';
let previousState = '';
const baseUrl = window.Unzer.config.appPath;

/**
 * Starts the appropriate controller based on the URL path.
 *
 * @param {{state: Record<string, any>}?} params
 */
const goToPage = (params) => {
  const path = window.location.pathname.substring(baseUrl.length);
  activateController(path, params?.state);

  previousState = currentState;
  currentState = path;
};

/**
 * Instantiates page controller and calls the controller's `display` method.
 *
 * @param {string} path The URL path
 * @param {Record<string, any>?} config
 * @returns {Record<string, any> | null}
 */
const activateController = (path, config) => {
  const [controllerName, page, stateParam] = path.split('/');

  let name = controllerName.split('-').reduce((result, part) => {
    return result + part.charAt(0).toUpperCase() + part.slice(1);
  }, '');

  name = name.replace('.html', '') + 'Controller';

  const controller = Unzer[name] ? new Unzer[name]() : null;

  if (controller) {
    controller.display({ page, stateParam, config });
  }
};

/**
 * Navigates to a page.
 *
 * @param {string} to
 * @param {any?} additionalConfig
 * @param {boolean?} [reload=false]
 */
const navigate = (to, additionalConfig = {}, reload = false) => {
  if (reload) {
    window.location.assign(to);
  } else {
    window.history.pushState(additionalConfig, null, to);
    dispatchEvent(new PopStateEvent('popstate', { state: additionalConfig }));
  }
};

/**
 * Main entry point for the application.
 * Determines the current page and runs the start controller.
 */
const start = () => {
  utilities.showLoader();
  pageService.clearContent();

  const renderFooter = () => {
    if (Unzer.config.store?.isLoggedIn) {
      Unzer.components.FooterComponent.render({ version: '1.0.0', hasNewVersion: false });
    } else {
      Unzer.components.FooterComponent.clear();
    }
  };

  const handlePageNavigation = (value) => {
    const currentURL = window.location.href;
    const url = new URL(currentURL);
    Unzer.components.PageHeader.updateCredentials();
    window.history.pushState(null, null, url.href);
    goToPage();
    renderFooter();
  };

  const options = Unzer.config.stores.map((store) => ({ label: store.storeName, value: store.storeId }));
  Unzer.components.PageHeader.render(options, handlePageNavigation);
  Unzer.components.PageHeader.updateEnvironment(Unzer.config.store.mode == 'sandbox');
  renderFooter();

  window.addEventListener('popstate', goToPage, false);
  goToPage();
  utilities.hideLoader();
};


Unzer.stateController = {
  start,
  navigate
};
