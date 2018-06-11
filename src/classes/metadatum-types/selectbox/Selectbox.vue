<template>
    <div>
        <b-select 
            :id = "id"
            :placeholder="$i18n.get('label_selectbox_init')"
            v-model="selected" 
            :class="{'is-empty': selected == undefined || selected == ''}"
            @input="onChecked()">
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
        created(){
            if( this.value && this.value ){
                this.selected = this.value
            }
        },
        data(){
            return {
                selected: undefined
            }
        },
        props: {
            metadatum: {
                type: Object
            },
            options: {
                type: String
            },
            value: [String, Number, Array],
            id: ''
        },
        computed: {
            getOptions(){
                if ( this.options && this.options !== '' ){
                    return this.options.split("\n");
                }
                else if ( this.metadatum && this.metadatum.metadatum.metadatum_type_options.options ) {
                    const metadata = this.metadatum.metadatum.metadatum_type_options.options;
                    return ( metadata ) ? metadata.split("\n") : [];
                }
                return [];
            }
        },
        methods: {
            onChecked() {
                this.$emit('blur');
                if (this.selected != undefined)
                    this.onInput(this.selected)
            },
            onInput($event) {
                this.$emit('input', $event);
            }
        }
    }
</script>