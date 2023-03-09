import axios from 'axios';
import { SnackbarProgrammatic as Snackbar } from 'buefy';
import { ModalProgrammatic as Modal } from 'buefy';
import CustomDialog from '../components/other/custom-dialog.vue'

// Simpler version of the i18n plugin to translate error feedback messages
const i18nGet = function (key) {
    let string = tainacan_plugin.i18n[key];
    return (string !== undefined && string !== null && string !== '' ) ? string : "ERROR: Invalid i18n key!";
};

export const tainacanErrorHandler = function(error) {
    if (error.response && error.response.status) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        
        if (error.response.status) {
            let errorMessage = '';
            let errorMessageDetail = '';
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
            Snackbar.open({
                message: errorMessage,
                type: 'is-danger',
                duration: duration,
                actionText: errorMessageDetail != '' ? i18nGet('label_know_more') : null,
                onAction: () => {
                    Modal.open({
                        component: CustomDialog,
                        props: {
                            title: i18nGet('label_error') + ' ' + error.response.status + '!',
                            message: errorMessageDetail,
                            hideCancel: true
                        },
                        ariaRole: 'alertdialog',
                        ariaModal: true,
                        customClass: 'tainacan-modal',
                        closeButtonAriaLabel: i18nGet('close')
                    });
                }
            });
        } else {
            console.log('Tainacan Error Handler: ', error.response);
        }

    } else if ('Tainacan Error Handler: ', error.request) {
        // The request was made but no response was received
        // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
        // http.ClientRequest in node.js
        console.error('Tainacan Error Handler: ', error.request);
    } else {
        // Something happened in setting up the request that triggered an Error
        console.error('Tainacan Error Handler: ', error.message);
    }
    return Promise.reject(error);
}

// Tainacan API Axios
export const tainacan = axios.create({
    baseURL: tainacan_plugin.tainacan_api_url
});
if (tainacan_plugin.nonce) {
    tainacan.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;
}
if (tainacan_plugin.admin_request_options) {
    Object.keys(tainacan_plugin.admin_request_options).forEach(requestOption => {
        tainacan.defaults.headers[requestOption] = tainacan_plugin.admin_request_options[requestOption];
    });
}
tainacan.interceptors.response.use(
    (response) => response,
    (error) => tainacanErrorHandler(error)
);

// WordPress JSON API axios
export const wp = axios.create({
    baseURL: tainacan_plugin.wp_api_url
});
if (tainacan_plugin.nonce) {
    wp.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;
}
wp.interceptors.response.use(
    (response) => response,
    (error) => tainacanErrorHandler(error)
);

export const CancelToken = axios.CancelToken;
export const isCancel = axios.isCancel;
export const all = axios.all;

export default { tainacan, wp, CancelToken, isCancel, all, tainacanErrorHandler};