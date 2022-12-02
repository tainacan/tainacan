<template>
    <div>
        <b-input
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                :disabled="disabled"
                type="text"
                @input.native="onInput"
                @blur="onBlur"
                :placeholder="itemMetadatum.metadatum.placeholder || ''" />
        <p
                style="font-size: 0.75em;"
                class="has-text-danger is-italic">{{ $i18n.get('info_error_invalid_geocoordinate') }}</p>
    </div>
</template>

<script>

    export default {
        mixins: [ ],
        props: {
            itemMetadatum: Object,
            value: [String, Array],
            disabled: false,
        },
        data() {
            return {
                coordinateValue: '-14.4086569,-51.31668',
            }
        },
        created(){
            if (this.value)
                this.coordinateValue= this.value;
        },
        methods: {
            onInput: _.debounce(function ($event) {
                if ($event.target.value != '') {
                    this.$emit('input', this.coordinateValue);
                } else  {
                   this.$emit('input', ''); 
                }
            }, 300),
            onBlur() {
                this.$emit('blur');
            }
        }
    }
</script>