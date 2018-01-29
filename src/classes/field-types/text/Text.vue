<template>
        <el-form-item :label="name" :prop="validateObject()">
            <el-input v-model="valueInput" @blur="changeValue"></el-input>
        </el-form-item>
</template>

<script>
    import { eventBus } from '../../../js/event-bus-web-components'

    export default {
        props: {
            name: { type: String },
            required: { type: Boolean },
            item_id: { type: Number },
            metadata_id: { type: Number },
            value: { type: [ String,Number ]  },
            message: { type: [ String,Number ] },
        },
        data(){
            return {
                valueInput:''
            }
        },
        created(){
            this.getValue();
        },
        methods: {
            changeValue(){
               // this.$emit('changeValues', { item_id: this.item_id, metadata_id: this.metadata_id, values: this.valueInput } );
                eventBus.$emit('input', { item_id: this.item_id, metadata_id: this.metadata_id, values: this.valueInput } );
            },
            getValue(){
                try{
                    let val = JSON.parse( this.value );
                    this.valueDate = val;
                }catch(e){
                    console.log('invalid json value');
                }
            },
            validateObject () {
                return
                [
                    { required: this.required, message: this.message, trigger: 'blur' }
                ]
            },
            getErrors(){
                try{
                    return JSON.parse( this.errorsMsg );
                }catch(e){
                    console.log('invalid json error');
                }
                return this.errorsMsg;
            }
        }
    }
</script>