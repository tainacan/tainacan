<template>
    <div class="component" :class="{ invalid : hasError }">
        <input :placeholder="name" type="text" @change="setValue( $event )" :value="getValue()" >
        <p
            v-for="error in getErrors()">
            {{ error }}
        </p>
    </div>
</template>

<script>
    import store from '../../../js/store/store';

    export default {
        store,
        data(){
            return {
                hasError: false
            }
        },
        props: {
            name: { type: String },
            item_id: { type: Number },
            metadata_id: { type: Number },
            value: { type: [ String,Number ]  }
        },
        created(){
            this.setInitValueOnStore();
        },
        methods: {
            getValue(){
                let metadata = this.$store.getters['item/getMetadata'].find(metadata => metadata.metadata_id === this.metadata_id );
                if( metadata ){
                    return  metadata.values;
                }
                return '';
            },
            getErrors(){
                const messages = [];
                const metadata = this.$store.getters['item/getError'].find(error => error.metadata_id === this.metadata_id );
                if( metadata && metadata.error ){
                    const object_messages = metadata.error[0];
                    for(let error_type in object_messages){
                        messages.push( object_messages[ error_type ] );
                    }
                    this.hasError = true;
                }else{
                    this.hasError = false;
                }
                return messages;
            },
            setInitValueOnStore(){
                if ( this.value ){
                    this.$store.dispatch('item/updateMetadata', { item_id: this.item_id, metadata_id: this.metadata_id, values: JSON.parse(  this.value ) });
                }
            },
            setValue( event ){
                this.$store.dispatch('item/sendMetadata', { item_id: this.item_id, metadata_id: this.metadata_id, values: event.target.value });
            }
        }
    }
</script>

<style scoped>
    p{
        padding:10px 10px 10px 5px;
        font-style: oblique ;
    }

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

    .component.invalid input{
        background: #ff9999;
    }
</style>