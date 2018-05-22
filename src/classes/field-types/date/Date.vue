<template>
    <div>
        <div
                :class="{'has-content': dateValue !== undefined && dateValue !== ''}"
                class="control is-inline">
            <input
                    class="input"
                    type="text"
                    v-mask="dateMask"
                    v-model="dateValue"
                    @blur="onBlur"
                    @input="onInput"
                    :placeholder="dateFormat">
            <!--<b-collapse-->
                    <!--position="is-bottom-right">-->
                <!--<b-icon-->
                        <!--icon="calendar-today"-->
                        <!--size="is-small"-->
                        <!--slot="trigger" />-->

                <!--<div class="field">-->
                    <!--<b-datepicker-->
                            <!--:class="{'has-content': dateValue !== undefined && dateValue !== ''}"-->
                            <!--:id="id"-->
                            <!--v-model="dateValue"-->
                            <!--@blur="onBlur"-->
                            <!--:readonly="false"-->
                            <!--inline-->
                            <!--@input="onInput($event)"-->
                            <!--:placeholder="datePlaceHolder"/>-->
                <!--</div>-->
            <!--</b-collapse>-->
        </div>
    </div>
</template>

<script>
    import { dateInter } from "../../../admin/js/mixins";
    import moment from 'moment';

    export default {
        mixins: [ dateInter ],
        created(){
            let locale = navigator.language;

            moment.locale(locale);

            let localeData = moment.localeData();
            this.dateFormat = localeData.longDateFormat('L');

            if( this.value ){
                this.dateValue = this.parseDateToNavigatorLanguage(new Date(this.value.replace(/-/g, '/')));
            }
        },
        data() {
            return {
                dateValue: '',
                dateMask: this.getDateLocaleMask(),
                dateFormat: ''
            }
        },
        props: {
            id: '',
            field: {
                type: Object
            },
            value: [String, Number, Array],
        },
        methods: {
            onBlur() {
                this.$emit('blur');
            },
            onInput($event) {
                let dateISO = '';

                if($event && $event instanceof Date) {
                    dateISO = moment(this.dateValue, this.dateFormat).toISOString().split('T')[0];
                } else if($event.target.value && $event.target.value.length === this.dateMask.length) {
                    dateISO = moment($event.target.value,  this.dateFormat).toISOString().split('T')[0];
                }

                this.$emit('input', dateISO);
                this.$emit('blur');
            },
            parseDateToNavigatorLanguage(date){
                return moment(date, moment.ISO_8601).format(this.dateFormat);
            }
        }
    }
</script>
