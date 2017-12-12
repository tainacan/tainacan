<template>
    <div class="component">
        <input :placeholder="name" type="text" v-model.lazy="manageValue" >
    </div>
</template>

<script>
    import store from '../../../js/store/store';

    export default {
        store,
        props: {
            name: { type: String },
            item_id: { type: Number },
            metadata_id: { type: Number },
            value: { type: [ String,Number ]  },
        },
        created(){
            this.setInitValueOnStore();
        },
        computed:{
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
                    this.$store.dispatch('item/setSingleMetadata', { item_id: this.item_id, metadata_id: this.metadata_id, values: JSON.parse(  this.value ) });
                }
            }
        }
    }
</script>

<style scoped>
    input[type="text"] {
        background: transparent;
        font-size:18px;
        padding:10px 10px 10px 5px;
        display:block;
        width:300px;
        border-top-color:transparent;
        border-bottom:1px solid #757575;
    }
    input[type="text"]:focus {
        outline: none;
    }
</style>