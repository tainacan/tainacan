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
            return this.axiosWPAjax.post('', qs.stringify({
                    action: 'tainacan-sample-permalink',
                    post_id: id,
                    new_title: newTitle,
                    new_slug: newSlug,
                    nonce: tainacan_plugin.nonce,
                }));
        },
        getDatei18n(dateString){
            return this.axiosWPAjax.post('', qs.stringify({
                action: 'tainacan-date-i18n',
                date_string: dateString,
                nonce: tainacan_plugin.nonce,
            }));
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