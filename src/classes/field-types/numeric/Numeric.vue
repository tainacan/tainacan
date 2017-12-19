<template>
    <div class="component">
        <p>{{ name }}</p>
        <input type="text" @blur="changeValue($event)" :value="getValue()">
        <p  v-for="error in getErrors()">
            {{ error }}
        </p>
    </div>
</template>

<script>
    export default {
        props: {
            name: { type: String },
            item_id: { type: Number },
            metadata_id: { type: Number },
            value: { type: [ String,Number ]  },
            errorsMsg: { type: [ String,Number ] },
        },
        methods: {
            changeValue( event ){
                this.$emit('changeValue', { item_id: this.item_id, metadata_id: this.metadata_id, values: event.target.value } );
            },
            getValue(){
                try{
                    return JSON.parse( this.value );
                }catch(e){
                    console.log('invalid json value');
                }
                return this.value;
            },
            getErrors(){
                try{
                    return JSON.parse( this.errorsMsg );
                }catch(e){
                    console.log('invalid json error msg');
                }
                return this.errorsMsg;
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