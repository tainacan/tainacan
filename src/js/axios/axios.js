import axios from 'axios';

export const tainacan = axios.create({
    baseURL: tainacan_plugin.root
});

tainacan.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;

export const wp = axios.create({
    baseURL: tainacan_plugin.root_wp_api
});

wp.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;

export default { tainacan, wp };