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
    window.location.assign(addOrUpdateQueryParam(to, 'store', Unzer.config.store.storeId));
  } else {
    window.history.pushState(additionalConfig, null, addOrUpdateQueryParam(to, 'store', Unzer.config.store.storeId));
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
      Unzer.components.FooterComponent.render({
        version: Unzer.config.version.installed,
        hasNewVersion: Unzer.config.version.latest !== '' && Unzer.config.version.latest != Unzer.config.version.installed
      });
    } else {
      Unzer.components.FooterComponent.clear();
    }
  };

  const handlePageNavigation = (value) => {
    const stores = Unzer.config.stores.filter(x => x.storeId === value.storeId)
    Unzer.config.store = stores[0];
    
    const currentURL = window.location.href;
    const url = new URL(currentURL);
    url.searchParams.delete('store');
    url.searchParams.set('store', value.storeId);
    window.history.pushState(null, null, url.href);
    window.location.reload(true);
  };

  const options = Unzer.config.stores.map((store) => ({ label: store.storeName, value: store.storeId }));
  Unzer.components.PageHeader.render(options, handlePageNavigation);
  Unzer.components.PageHeader.updateEnvironment(Unzer.config.store.mode == 'sandbox');
  renderFooter();

  window.addEventListener('popstate', goToPage, false);
  goToPage();
  utilities.hideLoader();
};

function addOrUpdateQueryParam(url, paramKey, paramValue) {
  const queryStringStart = url.indexOf("?");

  if (queryStringStart === -1) {
    url += `?${paramKey}=${paramValue}`;
  } else {
    const baseUrl = url.substring(0, queryStringStart);
    const queryString = url.substring(queryStringStart + 1);

    const queryParams = new URLSearchParams(queryString);

    if (queryParams.has(paramKey)) {
      queryParams.set(paramKey, paramValue);
    } else {
      queryParams.append(paramKey, paramValue);
    }

    url = `${baseUrl}?${queryParams.toString()}`;
  }

  return url;
}


Unzer.stateController = {
  start,
  navigate
};
