<template>
    <div>
        <el-form-item :label="name" :prop="validateObject()">
            <el-input v-model="inputs[0]" @blur="changeValue"></el-input>
            <div v-if="multiple == 'yes'">
                <div v-if="index > 0" v-for="(input, index) in inputsList " v-bind:key="index" class="multiple-inputs">
                    <el-input v-model="inputs[index]" @blur="changeValue()"></el-input><el-button v-if="index > 0" @click="removeInput(index)">-</el-button>
                </div> 
                <el-button @click="addInput">+</el-button>
            </div>
        </el-form-item>
    </div>
</template>

<script>
    import { eventBus } from '../../../js/event-bus-web-components'

    export default {
        props: {
            name: { type: String },
            required: { type: Boolean },
            item_id: { type: Number },
            field_id: { type: Number },
            value: { },
            message: { type: [ String,Number ] },
            multiple: { type: String }
        },
        data(){
            return {
                inputs: []
            }
        },
        computed: {
            inputsList() {
                return this.inputs;
            }
        },
        created(){
            this.getValue();
        },
        methods: {
            changeValue(){
                this.$emit('changeValues', { item_id: this.item_id, field_id: this.field_id, values: this.inputs } );
                eventBus.$emit('input', { item_id: this.item_id, field_id: this.field_id, values: this.inputs, instance: this } );
            },
            getValue(){            
                if (this.value instanceof Array) {
                    this.inputs = this.value;
                    if (this.inputs.length == 0)
                        this.inputs.push('');
                } else {
                    this.value == null || this.value == undefined ? this.inputs.push('') : this.inputs.push(this.value);
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
            },
            addInput(){
                this.inputs.push('');
                this.changeValue();
            },
            removeInput(index) {
                this.inputs.splice(index, 1);
                this.changeValue();
            }
        }
    }
</script>

<style scoped>
    .multiple-inputs {
        display: flex;
    }
</style>
