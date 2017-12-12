<template>
    <div class="component">
        <p>{{ name }}</p>
        <input type="text" v-model.lazy="value" >
    </div>
</template>

<script>
    import store from '../../../js/store/store'
    import { mapGetters } from 'vuex';


    export default {
        store,
        props: {
            name: { type: String },
            item: { type: Number },
            metadata: { type: Number },
        },
        computed:{
            value : {
                get(){
                    let metadata = this.$store.getters['item/getMetadata'].find(metadata => metadata.metadata_id === this.metadata );
                    if( metadata ){
                        return  metadata.values;
                    }
                },
                set( value ){
                    let metadata = this.$store.getters['item/getMetadata'].find(metadata => metadata.metadata_id === this.metadata );
                    if( ! metadata ){
                        this.$store.dispatch('item/addMetadata', { item_id: this.item, metadata_id: this.metadata, values: value });
                    }else{
                        this.$store.dispatch('item/updateMetadata', { item_id: this.item, metadata_id: this.metadata, values: value });
                    }
                }
            }
        }
    }
</script>

<style scoped>
    input[type="text"] {
        display: block;
        margin: 0;
        width: 100%;
        border-radius: 6px;
        font-family: sans-serif;
        font-size: 18px;
        appearance: none;
        box-shadow: none;
        color:green;
    }
    input[type="text"]:focus {
        outline: none;
    }
</style>