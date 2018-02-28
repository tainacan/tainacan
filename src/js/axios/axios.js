import axios from 'axios';

const instance = axios.create({
    baseURL: tainacan_plugin.root
});

instance.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;

export default instance;