import axios from 'axios';

// Simpler version of the i18n plugin to translate error feedback messages
const i18nGet = function (key) {
    let string = tainacan_plugin.i18n[key];
    return (string !== undefined && string !== null && string !== '' ) ? string : "ERROR: Invalid i18n key!";
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
if (tainacan_plugin.nonce) {
    tainacanApi.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;
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

// WordPress JSON API axios
export const wpApi= axios.create({
    baseURL: tainacan_plugin.wp_api_url
});
if (tainacan_plugin.nonce) {
    wpApi.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;
}
wpApi.interceptors.response.use(
    (response) => response,
    (error) => tainacanErrorHandler(error)
);

// WordPress AJAX axios
export const wpAjax = axios.create({
    baseURL: tainacan_plugin.wp_ajax_url
});
if (tainacan_plugin.nonce) {
    wpAjax.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;
}
wpAjax.interceptors.response.use(
    (response) => response,
    (error) => tainacanErrorHandler(error)
);

export const CancelToken = axios.CancelToken;
export const isCancel = axios.isCancel;
export const all = axios.all;
export default { tainacanApi, wpApi, wpAjax, CancelToken, isCancel, all, tainacanErrorHandler };