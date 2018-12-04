import axios from 'axios';

export const tainacan = axios.create({
    baseURL: tainacan_plugin.tainacan_api_url
});

tainacan.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;

export const wp = axios.create({
    baseURL: tainacan_plugin.wp_api_url
});

wp.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;

export const CancelToken = axios.CancelToken;
export const isCancel = axios.isCancel;

export default { tainacan, wp, CancelToken, isCancel };