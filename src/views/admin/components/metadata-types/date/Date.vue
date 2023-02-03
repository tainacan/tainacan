<template>
    <div>
        <b-input
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                :disabled="disabled"
                :custom-class="{ 'is-danger': isInvalidDate && dateValue }"
                type="text"
                v-mask="dateMask"
                v-model="dateValue"
                @input.native="onInput"
                @blur="onBlur"
                @focus="onMobileSpecialFocus"
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : dateFormat.toLowerCase()" />
        <p
                v-if="isInvalidDate && dateValue"
                style="font-size: 0.75em;"
                class="has-text-danger is-italic">{{ $i18n.get('info_error_invalid_date') }}</p>
        <!--<b-collapse-->
                <!--position="is-bottom-right">-->
            <!--<span class="icon"-->
                    <!--icon="calendar-today"-->
                    <!--size="is-small"-->
                    <!--slot="trigger" />-->

            <!--<div class="field">-->
                <!--<b-datepicker-->
                        <!--v-model="dateValue"-->
                        <!--:readonly="false"-->
                        <!--inline-->
                        <!--@input="onInput($event)"-->
                        <!--:placeholder="datePlaceHolder"/>-->
            <!--</div>-->
        <!--</b-collapse>-->
    </div>
</template>

<script>
    import { dateInter } from "../../../js/mixins";
    import moment from 'moment';

    export default {
        mixins: [ dateInter ],
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false,
        },
        data() {
            return {
                dateValue: '',
                isInvalidDate: false,
            }
        },
        computed: {
            isOnItemSubmissionForm() {
                return !this.itemMetadatum.item || !this.itemMetadatum.item.id;
            },
        },
        created() {
            if (this.value)
                this.dateValue = this.parseDateToNavigatorLanguage(this.value);
        },
        methods: {
            onInput: _.debounce(function ($event) {
                // Empty dates don't need to be validated, they remove the metadata
                if ($event.target.value != '') {
                    let dateISO = '';
                    
                    if ($event && $event instanceof Date)
                        dateISO = moment(this.dateValue, this.dateFormat).toISOString(true) ? moment(this.dateValue, this.dateFormat).toISOString(true).split('T')[0] : false;
                    else if ($event.target.value && $event.target.value.length === this.dateMask.length)
                        dateISO = moment($event.target.value, this.dateFormat).toISOString(true) ? moment($event.target.value,  this.dateFormat).toISOString(true).split('T')[0] : false;
                    
                    if (dateISO == false) {
                        this.isInvalidDate = true;
                        
                        if (!this.isOnItemSubmissionForm)
                            this.$emit('input', false);
                        else
                            this.$emit('input', this.dateValue) // On item submission form we keep the error here to allow the server to return the correct format.
                            
                    } else {
                        this.isInvalidDate = false;
                        this.$emit('input', dateISO);
                    }

                    
                } else  {
                   this.$emit('input', ''); 
                }
            }, 300),
            onBlur() {
                this.$emit('blur');
            },
            onMobileSpecialFocus() {
                this.$emit('mobileSpecialFocus');
            }
        }
    }
</script>