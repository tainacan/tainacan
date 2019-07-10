<template>
    <div>
        <div
                :class="{'has-content': dateValue !== undefined && dateValue !== ''}"
                class="control is-inline">
            <input
                    :disabled="disabled"
                    class="input"
                    :class="{'is-danger': isInvalidDate && dateValue}"
                    type="text"
                    v-mask="dateMask"
                    v-model="dateValue"
                    @blur="onBlur"
                    @input="onInput"
                    :placeholder="dateFormat.toLowerCase()">
            <p
                    v-if="isInvalidDate && dateValue"
                    class="has-text-danger is-italic is-size-7">{{ $i18n.get('info_error_invalid_date') }}</p>
            <!--<b-collapse-->
                    <!--position="is-bottom-right">-->
                <!--<span class="icon"-->
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
            if (this.value)
                this.dateValue = this.parseDateToNavigatorLanguage(this.value);
        },
        data() {
            return {
                dateValue: '',
                isInvalidDate: false,
            }
        },
        props: {
            id: '',
            metadatum: {
                type: Object
            },
            value: [String, Number, Array],
            disabled: false,
        },
        methods: {
            onBlur() {
                this.$emit('blur');
            },
            onInput: _.debounce(function ($event) {
                // Emty dates don't need to be validated, they remove the metadata
                if ($event.target.value != '') {
                    let dateISO = '';
                    
                    if ($event && $event instanceof Date) {
                        dateISO = moment(this.dateValue, this.dateFormat).toISOString() ? moment(this.dateValue, this.dateFormat).toISOString().split('T')[0] : false;
                    } else if($event.target.value && $event.target.value.length === this.dateMask.length) {
                        dateISO = moment(this.dateValue, this.dateFormat).toISOString() ? moment($event.target.value,  this.dateFormat).toISOString().split('T')[0] : false;
                    }

                    if(dateISO == false){
                        this.isInvalidDate = true;
                        return;
                    } else {
                        this.isInvalidDate = false;
                    }

                    this.$emit('input', dateISO);
                } else  {
                   this.$emit('input', [null]); 
                }
                this.$emit('blur');
            }, 300)
        }
    }
</script>
