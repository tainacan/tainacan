import axios from 'axios';

const instance = axios.create({
    baseURL: wpApiSettings.api_root
});

//instance.defaults.headers.common['something'] = 'none';

export default instance;