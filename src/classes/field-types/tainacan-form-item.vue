<template>
    <b-field
            :addons="false"
            :message="getErrorMessage"
            :type="fieldTypeMessage">
        <span   
                class="collapse-handle"
                @click="$emit('changeCollapse', !isCollapsed)">
            <b-icon 
                    type="is-secondary"
                    :icon="isCollapsed ? 'menu-down' : 'menu-right'" />
            <label class="label">{{ field.field.name }}</label>
            <span
                    v-if="field.field.required == 'yes'" 
                    class="required-field-asterisk" 
                    :class="fieldTypeMessage">*</span>
            <span class="field-type">({{ $i18n.get(field.field.field_type_object.component) }})</span>      
            <help-button 
                    :title="field.field.name" 
                    :message="field.field.description"/>
        </span>
        <div   
                v-show="isCollapsed" 
                v-if="isTextInputComponent( field.field.field_type_object.component )">
            <component 
                    :id="field.field.field_type_object.component + '-' + field.field.slug" 
                    :is="field.field.field_type_object.component" 
                    v-model="inputs[0]" 
                    :field="field" 
                    @blur="changeValue()"/>
            <div v-if="field.field.multiple == 'yes'">
                <div 
                        v-if="index > 0" 
                        v-for="(input, index) in inputsList " 
                        :key="index" 
                        class="multiple-inputs">
                    <component 
                            :id="field.field.field_type_object.component + '-' + field.field.slug" 
                            :is="field.field.field_type_object.component" 
                            v-model="inputs[index]" 
                            :field="field" 
                            @blur="changeValue()"/><a 
                            class="button" 
                            v-if="index > 0" 
                            @click="removeInput(index)">-</a>
                </div>
                <a 
                        class="button" 
                        @click="addInput">+</a>
            </div>
        </div>
        <div 
                v-show="isCollapsed"
                v-else>
            <component
                    :id="field.field.field_type_object.component + '-' + field.field.slug"
                    :is="field.field.field_type_object.component" 
                    v-model="inputs"
                    :field="field" 
                    @blur="changeValue()"/>
        </div>
    </b-field>
</template>

<script>
    import { eventBus } from '../../js/event-bus-web-components'

    export default {
        name: 'TainacanFormItem',
        props: {
            field: {
                type: Object
            },
            isCollapsed: true // Field Collapses
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
                    this.setFieldTypeMessage('is-danger');
                    for (let error of errors) { 
                        for (let index of Object.keys(error)) {
                            //this.$console.log(index);
                            msg += error[index] + '\n';
                        }
                    }
                } else {
                    this.setFieldTypeMessage('');
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
                    if (this.inputs.length === 0)
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
            },
            isTextInputComponent( component ){
                let array = ['tainacan-relationship','tainacan-category'];
                return !( array.indexOf( component ) >= 0 );
            },
            setFieldTypeMessage( message ){
                this.fieldTypeMessage = message;
                if (message != '')
                    this.$emit('changeCollapse', true);
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import '../../admin/scss/_variables.scss'; 

    .multiple-inputs {
        display: flex;
    }

    .field {
        border-bottom: 1px solid $draggable-border-color;
        padding: 10px 25px;

        .label {
            font-size: 14px;
            font-weight: 500;
            margin-left: 18px;
            margin-bottom: 0.5em;
        }
        .field-type {
            font-size: 13px;
            font-weight: 400;
            color: $gray;
            top: -0.2em;
            position: relative;
        }
        .help-wrapper {
            top: -0.2em;
        }
        .collapse-handle {
            cursor: pointer;
            position: relative;
        }
    }
</style>
