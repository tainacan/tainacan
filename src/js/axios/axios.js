import axios from 'axios';

const instance = axios.create({
    baseURL: wpApiSettings.root
});

//instance.defaults.headers.common['something'] = 'none';

export default instance;