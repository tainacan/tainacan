<template>
    <div>
        <el-form-item :label="name" :prop="validateObject()">
            <component :is="customComponentInput" :value="inputs[0]" @blur="changeValue"></component>
            <div v-if="multiple == 'yes'">
                <div v-if="index > 0" v-for="(input, index) in inputsList " v-bind:key="index" class="multiple-inputs">
                    <component :is="customComponentInput" :value="inputs[index]" @blur="changeValue()"></component><el-button v-if="index > 0" @click="removeInput(index)">-</el-button>
                </div> 
                <el-button @click="addInput">+</el-button>
            </div>
        </el-form-item>
    </div>
</template>

<script>
    import { eventBus } from '../../js/event-bus-web-components'

    export default {
        name: 'TainacanFormItem',
        props: {
            customComponentInput: String,
            name: { type: String },
            required: { type: Boolean },
            item_id: { type: Number },
            metadata_id: { type: Number },
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
                console.log(this.inputsList);
                this.$emit('changeValues', { item_id: this.item_id, metadata_id: this.metadata_id, values: this.inputs } );
                eventBus.$emit('input', { item_id: this.item_id, metadata_id: this.metadata_id, values: this.inputs, instance: this } );
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
