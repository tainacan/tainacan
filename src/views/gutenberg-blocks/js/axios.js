import axios from 'axios';
import { guardInterceptor } from '../../admin/js/axios/guard-interceptor';
import { tunnelInterceptor } from '../../admin/js/axios/tunnel-interceptor';

const tainacanApi = axios.create({
    baseURL: tainacan_blocks.root
});

if (tainacan_blocks.nonce) {
    tainacanApi.defaults.headers.common['X-WP-Nonce'] = tainacan_blocks.nonce;
}
// PUT, PATCH and DELETE are sent as POST with X-HTTP-Method-Override.
tainacanApi.interceptors.request.use(tunnelInterceptor);
tainacanApi.interceptors.request.use(guardInterceptor);

export default tainacanApi;