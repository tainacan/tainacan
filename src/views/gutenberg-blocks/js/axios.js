import axios from 'axios';

const tainacanApi = axios.create({
    baseURL: tainacan_blocks.root
});

if (tainacan_blocks.nonce) {
    tainacanApi.defaults.headers.common['X-WP-Nonce'] = tainacan_blocks.nonce;
}

export default tainacanApi;