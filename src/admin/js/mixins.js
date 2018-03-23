import axios from 'axios';
import qs from  'qs';

export const wpAjax = {
    methods: {
        getSamplePermalink(id, newTitle, newSlug){
            let axiosWPAjax = axios.create({
                baseURL: tainacan_plugin.wp_ajax_url,
            });

            return new Promise((resolve, reject) => {
                axiosWPAjax.post('', qs.stringify({
                    action: 'sample-permalink',
                    post_id: id,
                    new_title: newTitle,
                    new_slug: newSlug,
                    samplepermalinknonce: tainacan_plugin.sample_permalink_nonce,
                }))
                   .then(res => {
                       resolve(res.data);
                   })
                   .catch(error => {
                       reject(error)
                   })
            });
        }
    }
};