<template>
    <div class="component">
        <label
                v-for="option in getOptions"
                :for="option.replace(' ','-') + '-checkbox'">
            <input
                    type="checkbox"
                    :id="option.replace(' ','-') + '-checkbox'"
                    :value="option"
                    :checked="isChecked(option)"
                    @change="sendValue($event)"> {{ option }} <br>
        </label>
    </div>
</template>

<script>
    import store from '../../../js/store/store';

    export default {
        store,
        data(){
            return {
                checked:[]
            }
        },
        props: {
            name: {
                type: String
            },
            options: {
                type: String
            },
            item_id: {
                type: Number
            },
            metadata_id: {
                type: Number
            },
            value: {
                type: [ String,Number ]
            },
        },
        created(){
           this.setInitValueOnStore();
        },
        computed: {
            getOptions(){
                const values = ( this.options ) ? this.options.split("\n") : '';
                return values;
            }
        },
        methods: {
            setInitValueOnStore (){
                const array_values = JSON.parse(  this.value );
                if ( array_values.length > 0 ){
                    this.checked = array_values;
                    this.$store.dispatch('item/setSingleMetadata', { item_id: this.item_id, metadata_id: this.metadata_id, values: array_values });
                }
            },
            sendValue ( event ){
                let index  = this.checked.indexOf( event.target.value );
                if( index >= 0 ){
                    this.checked.splice(index,1);
                }else{
                    this.checked.push( event.target.value );
                }
                this.$store.dispatch('item/sendMetadata', { item_id: this.item_id, metadata_id: this.metadata_id, values: this.checked });
            },
            isChecked ( value ){
                let index  = this.checked.indexOf( value );
                return  index >= 0;
            }
        }

    }
</script>

<style scoped="">
    #postcustomstuff table input, #postcustomstuff table select, #postcustomstuff table textarea {
        width: auto;
        margin: 8px;
    }
</style>