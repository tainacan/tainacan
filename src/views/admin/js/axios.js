import axios from 'axios';

// Simpler version of the i18n plugin to translate error feedback messages
const i18nGet = function (key) {
    let string = tainacan_plugin.i18n[key];
    return (string !== undefined && string !== null && string !== '' ) ? string : 'ERROR: Invalid i18n key!';
};
export const tainacanErrorHandler = function(error) {

    let errorMessage;
    let errorMessageDetail;

    if (error.response && error.response.status) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        
        if (error.response.status) {
            let duration = 5000;
            switch(error.response.status) {
                case 400:
                case 401:
                case 403:
                case 404:    
                case 408:
                case 413:
                case 500:
                case 502:
                case 503:
                case 504:
                case 511:
                    errorMessage = i18nGet('error_' + error.response.status);
                    errorMessageDetail = i18nGet('error_' + error.response.status + '_detail') + (error.response.config && error.response.config.url ? (' <br><br><strong>' + i18nGet('label_request_details') + ':</strong> <code>' + error.response.config.url + '</code>') : '');
                    break;
                default:
                    errorMessage = i18nGet('error_other');
                    break;
            }
        } else {
            console.log('Tainacan Error Handler: ', error.response);
        }

    } else if ( error.request ) {
        // The request was made but no response was received
        // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
        // http.ClientRequest in node.js
        console.error('Tainacan Error Handler: ', error.request);
    } else if ( error.message ) {
        // Something happened in setting up the request that triggered an Error
        console.error('Tainacan Error Handler: ', error.message);
    }
    return Promise.reject({ error, errorMessage, errorMessageDetail });
}

// Tainacan API Axios
export const tainacanApi = axios.create({
    baseURL: tainacan_plugin.tainacan_api_url
});
if (tainacan_user.nonce) {
    tainacanApi.defaults.headers.common['X-WP-Nonce'] = tainacan_user.nonce;
}
if (tainacan_plugin.admin_request_options) {
    Object.keys(tainacan_plugin.admin_request_options).forEach(requestOption => {
        tainacanApi.defaults.headers[requestOption] = tainacan_plugin.admin_request_options[requestOption];
    });
}
tainacanApi.interceptors.response.use(
    (response) => response,
    (error) => tainacanErrorHandler(error)
);
// Converts PUT, PATCH and DELETE requests to POST requests with _method param
// This is necessary in some environments such as the WordPress Playground
// See https://developer.wordpress.org/rest-api/using-the-rest-api/global-parameters/#method
tainacanApi.interceptors.request.use((config) => {
    const tunnelingMethods = ['put', 'patch', 'delete'];
  
    if (config.method && tunnelingMethods.includes(config.method.toLowerCase())) {
      const originalMethod = config.method.toUpperCase();
  
      // Force request to POST
      config.method = 'post';
  
      // Add _method param to URL
      const url = new URL(config.url, window.location.origin);
      url.searchParams.set('_method', originalMethod);
      config.url = url.pathname + url.search;
    }
  
    return config;
});

// WordPress JSON API axios
export const wpApi= axios.create({
    baseURL: tainacan_plugin.wp_api_url
});
if (tainacan_user.nonce) {
    wpApi.defaults.headers.common['X-WP-Nonce'] = tainacan_user.nonce;
}
wpApi.interceptors.response.use(
    (response) => response,
    (error) => tainacanErrorHandler(error)
);
// Converts PUT, PATCH and DELETE requests to POST requests with _method param
// This is necessary in some environments such as the WordPress Playground
// See https://developer.wordpress.org/rest-api/using-the-rest-api/global-parameters/#method
wpApi.interceptors.request.use((config) => {
    const tunnelingMethods = ['put', 'patch', 'delete'];
  
    if (config.method && tunnelingMethods.includes(config.method.toLowerCase())) {
      const originalMethod = config.method.toUpperCase();
  
      // Force request to POST
      config.method = 'post';
  
      // Add _method param to URL
      const url = new URL(config.url, window.location.origin);
      url.searchParams.set('_method', originalMethod);
      config.url = url.pathname + url.search;
    }
  
    return config;
});

// WordPress AJAX axios
export const wpAjax = axios.create({
    baseURL: tainacan_plugin.wp_ajax_url
});
if (tainacan_user.nonce) {
    wpAjax.defaults.headers.common['X-WP-Nonce'] = tainacan_user.nonce;
}
wpAjax.interceptors.response.use(
    (response) => response,
    (error) => tainacanErrorHandler(error)
);


// WordPress Abilities API (wp-abilities/v1) — separate namespace from wp/v2
export const wpAbilitiesApi = axios.create({
    baseURL: typeof tainacan_plugin !== 'undefined' && tainacan_plugin.wp_abilities_api_url ? tainacan_plugin.wp_abilities_api_url : ''
});
if (tainacan_user.nonce) {
    wpAbilitiesApi.defaults.headers.common['X-WP-Nonce'] = tainacan_user.nonce;
}
wpAbilitiesApi.interceptors.response.use(
    (response) => response,
    (error) => tainacanErrorHandler(error)
);

export const CancelToken = axios.CancelToken;
export const isCancel = axios.isCancel;
export const all = axios.all;
export default { tainacanApi, wpApi, wpAbilitiesApi, wpAjax, CancelToken, isCancel, all, tainacanErrorHandler };