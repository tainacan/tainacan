import { wpAjax } from './axios';
import qs from 'qs';
import moment from 'moment';

/* Used by Collection and Taxonomy forms to generate slug from title */
export const permalinkGetter = {
    methods: {
        getSamplePermalink(id, newTitle, newSlug) {
            return wpAjax.post('', qs.stringify({
                action: 'tainacan-sample-permalink',
                post_id: id,
                new_title: newTitle,
                new_slug: newSlug,
            }));
        }
    }
};

/* Used by date metadata and filter fields */
export const dateInter = {
    created() {
        let locale = navigator.language;

        moment.locale(locale);

        let localeData = moment.localeData();
        this.dateFormat = localeData.longDateFormat('L') ? localeData.longDateFormat('L') : (localeData._abbr == 'pt_BR' ? 'DD/MM/YYYY' : 'YYYY-MM-DD');
        this.dateMask = this.dateFormat.replace(/[\w]/g, '0');
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
        }
    }
};

// Used for filling extra form data on hooks
// Any form component that uses this should have a 'entityName' and a 'form' in their data atts
export const formHooks = {
    data() {
        return { 
            formHooks: JSON.parse(JSON.stringify(tainacan_plugin['form_hooks'])), 
            formHookEventName: ''
        }
    },
    computed: {
        hasBeginLeftForm() {
            return this.formHooks && this.formHooks[this.entityName] && this.formHooks[this.entityName]['begin-left'] && this.formHooks[this.entityName]['begin-left'].length > 0;
        },
        hasBeginRightForm() {
            return this.formHooks && this.formHooks[this.entityName] && this.formHooks[this.entityName]['begin-right'] && this.formHooks[this.entityName]['begin-right'].length > 0;
        },
        hasEndLeftForm() {
            return this.formHooks && this.formHooks[this.entityName] && this.formHooks[this.entityName]['end-left'] && this.formHooks[this.entityName]['end-left'].length > 0;
        },
        hasEndRightForm() {
            return this.formHooks && this.formHooks[this.entityName] && this.formHooks[this.entityName]['end-right'] && this.formHooks[this.entityName]['end-right'].length > 0;
        },
        getBeginLeftForm() {
            return this.formHooks[this.entityName]['begin-left'].map(aForm => this.checkFormConditionals(aForm)).join('');
        },
        getBeginRightForm() {
            return this.formHooks[this.entityName]['begin-right'].map(aForm => this.checkFormConditionals(aForm)).join('');
        },
        getEndLeftForm() {
            return this.formHooks[this.entityName]['end-left'].map(aForm => this.checkFormConditionals(aForm)).join('');
        },
        getEndRightForm() {
            return this.formHooks[this.entityName]['end-right'].map(aForm => this.checkFormConditionals(aForm)).join('');
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
                if (
                    this.formHooks[this.entityName] && 
                    this.formHooks[this.entityName][position] &&
                    this.formHooks[this.entityName][position] != undefined 
                ) {
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
                if (
                    this.formHooks[this.entityName] &&
                    this.formHooks[this.entityName][position] &&
                    this.formHooks[this.entityName][position] != undefined
                ) {
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
        },
        checkFormConditionals(aForm) {
            if (aForm['form']) {
                if (aForm['conditional'] && aForm['conditional']['attribute'] && aForm['conditional']['value'])
                    return (this.form && this.form[aForm['conditional']['attribute']] === aForm['conditional']['value'] ) ? aForm['form'] : '';
                else
                    return aForm['form'];
            }
            return '';
        }
    }
};

// Reports common chart mixin
export const reportsChartMixin = {
    props: {
        chartData: {},
        isFetchingData: false
    },
    data () {
        return {
           isBuildingChart: false,
           chartSeries: [],
           chartOptions: {}
        }
    }
};
