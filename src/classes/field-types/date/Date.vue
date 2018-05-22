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
                    :placeholder="datePlaceHolder">
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
            if( this.value ){
                this.dateValue = new Date(this.value.replace(/-/g, '/')).toLocaleDateString();
            }
        },
        data() {
            return {
                dateValue: '',
                datePlaceHolder: new Date().toLocaleDateString(),
                dateMask: this.getDateLocaleFormat(),
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

                let localeData = moment.localeData();
                let format = localeData.longDateFormat('L');

                if($event && $event instanceof Date) {
                    dateISO = moment(this.dateValue, format).toISOString().split('T')[0];
                } else if($event.target.value && $event.target.value.length === this.dateMask.length) {
                    dateISO = moment($event.target.value, format).toISOString().split('T')[0];
                }

                this.$emit('input', dateISO);
                this.$emit('blur');
            }
        }
    }
</script>
