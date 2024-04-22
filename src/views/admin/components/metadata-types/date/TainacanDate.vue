<template>
    <div class="control is-clearfix">
        <input
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                v-imask="{ mask: dateMask, skipInvalid: false }"
                :value="dateValue"
                :disabled="disabled"
                class="input"
                :class="isInvalidDate ? 'is-danger' : ''"
                type="text"
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : dateFormat.toLowerCase()"
                @input="onInput"
                @blur="onBlur"
                @focus="onMobileSpecialFocus">
        <p
                v-if="isInvalidDate"
                style="font-size: 0.75em;"
                class="has-text-danger is-italic">{{ $i18n.get('info_error_invalid_date') }}</p>
    </div>
</template>

<script>
    import { dateInter } from "../../../js/mixins";
    import moment from 'moment';
    import { IMaskDirective } from 'vue-imask';

    export default {
        directives: {
            imask: IMaskDirective
        },
        mixins: [ dateInter ],
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false,
        },
        emits: [
            'update:value',
            'blur',
            'mobile-special-focus'
        ],
        data() {
            return {
                dateValue: '',
                isInvalidDate: false,
            }
        },
        computed: {
            isOnItemSubmissionForm() {
                return !this.itemMetadatum.item || !this.itemMetadatum.item.id;
            }
        },
        watch: {
            value(newValue) {
                this.dateValue = newValue ? this.parseDateToNavigatorLanguage(newValue) : '';
            }
        },
        created() {
            this.dateValue = this.value ? this.parseDateToNavigatorLanguage(this.value) : '';
        },
        methods: {
            onInput: _.debounce(function ($event) {

                // Empty dates don't need to be validated, they remove the metadata
                if ($event.target.value != '') {
                    let dateISO = '';
                    
                    if ($event && $event instanceof Date)
                        dateISO = moment(this.dateValue, this.dateFormat).toISOString(true) ? moment(this.dateValue, this.dateFormat).toISOString(true).split('T')[0] : false;
                    else if ($event.target.value && $event.target.value.length === this.dateFormat.length)
                        dateISO = moment($event.target.value, this.dateFormat).toISOString(true) ? moment($event.target.value,  this.dateFormat).toISOString(true).split('T')[0] : false;
                    
                    if (dateISO == false) {
                        
                        if ( !this.isOnItemSubmissionForm )
                            this.dateValue = $event.target.value; // Keep wrong version in the input so user can fix it
                        else
                            this.$emit('update:value', this.dateValue) // On item submission we send the errored version here to allow the server to return the correct format.
                            
                        this.isInvalidDate = true;
                        
                        return;
                        
                    } else {
                        this.$emit('update:value', dateISO);
                    }
                    
                } else  {
                    this.$emit('update:value', ''); 
                }

                this.isInvalidDate = false;

            }, 750),
            onBlur() {
                this.$emit('blur');
            },
            onMobileSpecialFocus() {
                this.$emit('mobile-special-focus');
            }
        }
    }
</script>

<style scoped>
    .input:placeholder-shown.is-danger {
        background-color: var(--tainacan-input-background-color);
        border: 1px solid var(--tainacan-input-border-color);
    }
</style>