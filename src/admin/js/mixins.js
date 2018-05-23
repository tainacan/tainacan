import axios from 'axios';
import qs from  'qs';
import moment from 'moment';

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
        },
        getDatei18n(dateString){
            this.axiosWPAjax.post('', qs.stringify({
                action: 'tainacan_date_i18n',
                date_string: dateString,
                nonce: tainacan_plugin.nonce,
            })).then(res => {
                return res.data
            });
        },
    }
};

export const dateInter = {
    methods: {
        getDateLocaleMask() {
            let locale = navigator.language;

            moment.locale(locale);

            let localeData = moment.localeData();
            let format = localeData.longDateFormat('L');

            return format.replace(/[\w]/g, '#');
        }
    }
};