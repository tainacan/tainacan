<template>
        <b-field :label="field.field.name"
                 :message="getErrorMessage"
                 :type="fieldTypeMessage">
            <div>
                <component :id="field.field.field_type_object.component + '-' + field.field.slug" :is="field.field.field_type_object.component" v-model="inputs[0]" :field="field" @blur="changeValue()"></component>
                <div v-if="field.field.multiple == 'yes'">
                    <div v-if="index > 0" v-for="(input, index) in inputsList " v-bind:key="index" class="multiple-inputs">
                        <component :id="field.field.field_type_object.component + '-' + field.field.slug" :is="field.field.field_type_object.component" v-model="inputs[index]" :field="field" @blur="changeValue()"></component><a class="button" v-if="index > 0" @click="removeInput(index)">-</a>
                    </div>
                    <a class="button" @click="addInput">+</a>
                </div>
            </div>
        </b-field>
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
                inputs: [],
                fieldTypeMessage:''
            }
        },
        computed: {
            inputsList() {
                return this.inputs;
            },
            getErrorMessage() {
                let msg = '';
                let errors = eventBus.getErrors(this.field.field.id);
                if ( errors) {
                    this.fieldTypeMessage = 'is-danger';
                    for (let index in errors) {
                      msg += errors[index] + '\n';
                    }
                } else {
                    this.fieldTypeMessage = '';
                }
                return msg;
            }
        },
        created(){
            this.getValue();
        },
        methods: {
            changeValue(){
                eventBus.$emit('input', { item_id: this.field.item.id, field_id: this.field.field.id, values: this.inputs } );
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
