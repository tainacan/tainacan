import axios from 'axios';
import { guardRestRequest } from '../../admin/js/rest-request-guard';

const tainacanApi = axios.create({
    baseURL: tainacan_blocks.root
});

if (tainacan_blocks.nonce) {
    tainacanApi.defaults.headers.common['X-WP-Nonce'] = tainacan_blocks.nonce;
}
tainacanApi.interceptors.request.use((config) => {
    const tunnelingMethods = ['put', 'patch', 'delete'];
  
    if (config.method && tunnelingMethods.includes(config.method.toLowerCase())) {
      const originalMethod = config.method.toUpperCase();
  
      // Force request to POST
      config.method = 'post';
      config.tainacanTunneledMethod = originalMethod;
  
      // Add _method param to URL
      const url = new URL(config.url, window.location.origin);
      url.searchParams.set('_method', originalMethod);
      config.url = url.pathname + url.search;
    }
  
    return config;
});
// The guard accepts _method only when tainacanTunneledMethod was set above.
tainacanApi.interceptors.request.use(guardRestRequest);

export default tainacanApi;