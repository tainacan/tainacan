<template>
    <b-field
            :addons="false"
            :message="getErrorMessage"
            :type="metadatumTypeMessage">
        <span   
                class="collapse-handle"
                @click="$emit('changeCollapse', metadatumTypeMessage != 'is-danger' ? !isCollapsed : true)">
            <b-icon 
                    type="is-secondary"
                    :icon="isCollapsed || metadatumTypeMessage == 'is-danger' ? 'menu-down' : 'menu-right'" />
            <label class="label">{{ metadatum.metadatum.name }}</label>
            <span
                    v-if="metadatum.metadatum.required == 'yes'"
                    class="required-metadatum-asterisk"
                    :class="metadatumTypeMessage">*</span>
            <span class="metadata-type">({{ $i18n.get(metadatum.metadatum.metadata_type_object.component) }})</span>
            <help-button 
                    :title="metadatum.metadatum.name"
                    :message="metadatum.metadatum.description"/>
        </span>
        <div   
                v-show="isCollapsed || metadatumTypeMessage == 'is-danger'"
                v-if="isTextInputComponent( metadatum.metadatum.metadata_type_object.component )">
            <component 
                    :id="metadatum.metadatum.metadata_type_object.component + '-' + metadatum.metadatum.slug"
                    :is="metadatum.metadatum.metadata_type_object.component"
                    v-model="inputs[0]" 
                    :metadatum="metadatum"
                    @blur="changeValue()"/>
            <div v-if="metadatum.metadatum.multiple == 'yes'">
                <div 
                        v-if="index > 0" 
                        v-for="(input, index) in inputsList " 
                        :key="index" 
                        class="multiple-inputs">
                    <component 
                            :id="metadatum.metadatum.metadata_type_object.component + '-' + metadatum.metadatum.slug"
                            :is="metadatum.metadatum.metadata_type_object.component"
                            v-model="inputs[index]" 
                            :metadatum="metadatum"
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
                    :id="metadatum.metadatum.metadata_type_object.component + '-' + metadatum.metadatum.slug"
                    :is="metadatum.metadatum.metadata_type_object.component"
                    v-model="inputs"
                    :metadatum="metadatum"
                    @blur="changeValue()"/>
        </div>
    </b-field>
</template>

<script>
    import { eventBus } from '../../js/event-bus-web-components'

    export default {
        name: 'TainacanFormItem',
        props: {
            metadatum: {
                type: Object
            },
            isCollapsed: true // Metadatum Collapses
        },
        data(){
            return {
                inputs: [],
                metadatumTypeMessage:''
            }
        },
        computed: {
            inputsList() {
                return this.inputs;
            },
            getErrorMessage() {
                
                let msg = '';
                let errors = eventBus.getErrors(this.metadatum.metadatum.id);

                if ( errors) {
                    this.setMetadatumTypeMessage('is-danger');
                    for (let error of errors) { 
                        for (let index of Object.keys(error)) {
                            // this.$console.log(index);
                            msg += error[index] + '\n';
                        }
                    }
                } else {
                    this.setMetadatumTypeMessage('');
                }

                return msg;
            }
        },
        created(){
            this.getValue();
        },
        methods: {
            changeValue(){
                eventBus.$emit('input', { item_id: this.metadatum.item.id, metadatum_id: this.metadatum.metadatum.id, values: this.inputs } );
            },
            getValue(){           
                if (this.metadatum.value instanceof Array) {
                    this.inputs = this.metadatum.value;
                    if (this.inputs.length === 0)
                        this.inputs.push('');
                } else {
                    this.metadatum.value == null || this.metadatum.value == undefined ? this.inputs.push('') : this.inputs.push(this.metadatum.value);
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
                let array = ['tainacan-relationship','tainacan-taxonomy'];
                return !( array.indexOf( component ) >= 0 );
            },
            setMetadatumTypeMessage( message ){
                this.metadatumTypeMessage = message;
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
        .metadata-type {
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
