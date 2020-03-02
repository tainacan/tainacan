import axios from 'axios';

export const tainacan = axios.create({
    baseURL: tainacan_plugin.tainacan_api_url
});

if (tainacan_plugin.nonce) {
    tainacan.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;
}

export const wp = axios.create({
    baseURL: tainacan_plugin.wp_api_url
});

if (tainacan_plugin.nonce) {
    wp.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;
}

export const CancelToken = axios.CancelToken;
export const isCancel = axios.isCancel;
export const all = axios.all;

export default { tainacan, wp, CancelToken, isCancel, all };