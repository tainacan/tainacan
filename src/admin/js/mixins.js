import axios from 'axios';
import qs from  'qs';

export const wpAjax = {
    data(){
      return {
          axiosWPAjax: {},
      }
    },
    created(){
        this.axiosWPAjax = axios.create({
            baseURL: tainacan_plugin.wp_ajax_url,
        });
    },
    methods: {
        getSamplePermalink(id, newTitle, newSlug){
            return new Promise((resolve, reject) => {
                this.axiosWPAjax.post('', qs.stringify({
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