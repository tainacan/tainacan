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
        }
    }
};

export const dateInter = {
    created() {
        let locale = navigator.language;

        moment.locale(locale);

        let localeData = moment.localeData();
        this.dateFormat = localeData.longDateFormat('L');
        this.dateMask = this.dateFormat.replace(/[\w]/g, '#');
    },
    data() {
        return {
            dateFormat: '',
            dateMask: ''
        }
    },
    methods: {
        parseDateToNavigatorLanguage(date) {
            date = new Date(date.replace(/-/g, '/'));
            return moment(date, moment.ISO_8601).format(this.dateFormat);  
        },
    }
};

// Used for filling extra form data on hooks
export const formHooks = {
    data() {
        return { 
            formHooks: JSON.parse(JSON.stringify(tainacan_plugin['form_hooks'])), 
            formHookEventName: ''
        }
    },
    created() {
        this.formHookEventName = 'tainacan-' + this.entityName + '-hook-reload';
        this.formHookEvent = new Event(this.formHookEventName);
    },
    updated() {
        // Emits event on every Vue update to allow javascript plugins on hooks to re-render 
        if (this.formHooks[this.entityName])
            document.dispatchEvent(this.formHookEvent);
    },
    methods: {
        fillExtraFormData(data) {
            let positions  =  [
                'begin-left', 
                'begin-right',
                'end-left',
                'end-right'
            ];
            // Gets data from existing extra form hooks
            for (let position of positions) {
                if (this.formHooks[this.entityName] && this.formHooks[this.entityName][position] && this.formHooks[this.entityName][position] != undefined) {
                    let formElement = document.getElementById('form-' + this.entityName + '-' + position);
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
        updateExtraFormData(entityObject) {
            let positions  =  [
                'begin-left', 
                'begin-right',
                'end-left',
                'end-right'
            ];
            // Gets data from existing extra form hooks
            for (let position of positions) {
                if (this.formHooks[this.entityName] && this.formHooks[this.entityName][position] && this.formHooks[this.entityName][position] != undefined) {
                    let formElement = document.getElementById('form-' + this.entityName + '-' + position);
                    
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