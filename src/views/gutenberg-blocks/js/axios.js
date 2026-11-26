import axios from 'axios';
import { guardInterceptor } from '../../admin/js/axios/guard-interceptor';
import { tunnelInterceptor } from '../../admin/js/axios/tunnel-interceptor';

const tainacanApi = axios.create({
    baseURL: tainacan_blocks.root
});

if (tainacan_blocks.nonce) {
    tainacanApi.defaults.headers.common['X-WP-Nonce'] = tainacan_blocks.nonce;
}
// Axios runs request interceptors in reverse registration order.
// The tunnel is registered last so it strips _method before the guard runs.
tainacanApi.interceptors.request.use(guardInterceptor);
tainacanApi.interceptors.request.use(tunnelInterceptor);

export default tainacanApi;