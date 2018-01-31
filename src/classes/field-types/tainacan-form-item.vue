<template>
    <div>
        <el-form-item :label="field.field.name" :prop="validateObject()">
            <component :is="extractFieldType(field.field.field_type)" v-model="inputs[0]" :field="field" @blur="changeValue()"></component>
            <div v-if="field.field.multiple == 'yes'">
                <div v-if="index > 0" v-for="(input, index) in inputsList " v-bind:key="index" class="multiple-inputs">
                    <component :is="extractFieldType(field.field.field_type)" v-model="inputs[index]" :field="field" @blur="changeValue()"></component><el-button v-if="index > 0" @click="removeInput(index)">-</el-button>
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
            field: {}
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
                console.log(this.inputs);
                eventBus.$emit('input', { item_id: this.field.item.id, field_id: this.field.field.id, values: this.inputs, instance: this } );
            },
            getValue(){            
                if (this.field.value instanceof Array) {
                    this.inputs = this.field.value;
                    if (this.inputs.length == 0)
                        this.inputs.push('');
                } else {
                    this.field.value == null || this.field.value == undefined ? this.inputs.push('') : this.inputs.push(this.field.value);
                }
            },
            extractFieldType(field_type) {
                let parts = field_type.split('\\');
                return 'tainacan-' + parts.pop().toLowerCase();
            },
            validateObject () {
                return
                [
                    { required: this.field.field.required, message: this.message, trigger: 'blur' }
                ]
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
