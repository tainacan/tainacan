<template>
    <div>
        <b-select
                expanded
                :disabled="disabled"
                :id = "id"
                :placeholder="$i18n.get('label_selectbox_init')"
                :value="value"
                :class="{'is-empty': value == undefined || value == ''}"
                @blur="$emit('blur')"
                @input="onChecked($event)">
            <option
                    v-for="(option, index) in getOptions"
                    :key="index"
                    :label="option"
                    :value="option"
                    border>{{ option }}</option>
        </b-select>
    </div>
</template>

<script>

    export default {
        props: {
            metadatum: {
                type: Object
            },
            options: {
                type: String
            },
            value: [String, Number, Array],
            id: '',
            disabled: false,
        },
        computed: {
            getOptions(){
                if ( this.options && this.options !== '' ){
                    return this.options.split("\n");
                }
                else if ( this.metadatum && this.metadatum.metadatum.metadata_type_options.options ) {
                    const metadata = this.metadatum.metadatum.metadata_type_options.options;
                    return ( metadata ) ? metadata.split("\n") : [];
                }
                return [];
            }
        },
        methods: {
            onChecked(value) {
                this.$emit('input', value);
            },
        }
    }
</script>