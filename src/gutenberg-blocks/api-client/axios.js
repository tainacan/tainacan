import axios from 'axios';

const tainacan = axios.create({
    baseURL: tainacan_plugin.tainacan_api_url
});

tainacan.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;

export default tainacan;