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

// Used for filling extra form data on hooks
export const formHooks = {
    data() {
        return { 
            formHooks: JSON.parse(JSON.stringify(tainacan_plugin['form_hooks'])) 
        }
    },
    methods: {
        fillExtraFormData(data, entity) {
            let positions  =  [
                'begin-left', 
                'begin-right',
                'end-left',
                'end-right'
            ];
            // Gets data from existing extra form hooks
            for (let position of positions) {
                if (this.formHooks[entity][position] && this.formHooks[entity][position] != undefined) {
                    let formElement = document.getElementById('form-' + entity + '-' + position);
                    
                    if (formElement) {  
                        for (let element of formElement.elements) {
                            if (element.type == "checkbox" || (element.type == "select" && element.multiple != undefined && element.multiple == true)) {
                                if (element.checked && element.name != undefined && element.name != '') {
                                    if (!Array.isArray(data[element.name]))
                                        data[element.name] = [];
                                    data[element.name].push(element.value);
                                }
                            } else if (element.type == "radio") {
                                if (element.checked && element.name != undefined && element.name != '')
                                    data[element.name] = element.value;
                            } else {
                                data[element.name] = element.value;
                            }
                        }
                    }
                }
            }
        },
        updateExtraFormData(entity, entityObject) {
            let positions  =  [
                'begin-left', 
                'begin-right',
                'end-left',
                'end-right'
            ];
            // Gets data from existing extra form hooks
            for (let position of positions) {
                if (this.formHooks[entity][position] && this.formHooks[entity][position] != undefined) {
                    let formElement = document.getElementById('form-' + entity + '-' + position);
                    
                    if (formElement) {   
                        for (let element of formElement.elements) {
                            for (let key of Object.keys(entityObject)) {
                                if (element['name'] == key)  {
                                    if (Array.isArray(entityObject[key])) {
                                        let obj = entityObject[key].find((value) => { return value == element['value'] });
                                        element['checked'] = obj != undefined ? true : false;
                                    } else {
                                        if (entityObject[key] != null && entityObject[key] != undefined && entityObject[key] != ''){
                                            if (element.type == "radio")
                                                element['checked'] = entityObject[key] == element['value'] ? true : false;
                                            else 
                                                element['value'] = entityObject[key];
                                        }
                                            
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
};