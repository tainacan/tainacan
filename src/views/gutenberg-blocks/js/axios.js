import axios from 'axios';

const tainacan = axios.create({
    baseURL: tainacan_blocks.root
});

if (tainacan_blocks.nonce) {
    tainacan.defaults.headers.common['X-WP-Nonce'] = tainacan_blocks.nonce;
}

export default tainacan;