<template>
    <div class="component">
        <p>{{ name }}</p>
        <select
                class="form-control"
                :disabled="!getOptions"
                v-model="manageValue">
            <option
                    v-for="option in getOptions"
                    :selected="option == ''">
                {{ option }}
            </option>
        </select>
    </div>
</template>

<script>
    import store from '../../../js/store/store';

    export default {
        store,
        data(){
            return {
                selected:''
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
                const values = ( this.options ) ? this.options.split("\n") : false;
                return values;
            },
            manageValue : {
                get(){
                    let metadata = this.$store.getters['item/getMetadata'].find(metadata => metadata.metadata_id === this.metadata_id );
                    if( metadata ){
                        return  metadata.values;
                    }else if( this.value ){
                        return JSON.parse(  this.value );
                    }
                },
                set( value ){
                    this.$store.dispatch('item/sendMetadata', { item_id: this.item_id, metadata_id: this.metadata_id, values: value });
                }
            }
        },
        methods: {
            setInitValueOnStore(){
                if ( this.value ){
                    this.$store.dispatch('item/updateMetadata', { item_id: this.item_id, metadata_id: this.metadata_id, values: JSON.parse(  this.value ) });
                }
            }
        }
    }
</script>

<style scoped>
</style>