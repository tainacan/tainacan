import axios from 'axios';

const instance = axios.create({
    baseURL: wp_settings.root
});

instance.defaults.headers.common['X-WP-Nonce'] = wp_settings.nonce;

export default instance;