import axios from 'axios';

const instance = axios.create({
    baseURL: wp_settings.root
});

//instance.defaults.headers.common['something'] = 'none';

export default instance;