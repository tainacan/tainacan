<template>
    <b-field
            :addons="false"
            :message="getErrorMessage"
            :type="metadatumTypeMessage">
        <span   
                class="collapse-handle"
                @click="$emit('changeCollapse', metadatumTypeMessage != 'is-danger' ? !isCollapsed : true)">
            <span class="icon">
                <i 
                        :class="{ 'tainacan-icon-arrowdown' : isCollapsed || metadatumTypeMessage == 'is-danger', 'tainacan-icon-arrowright' : !(isCollapsed || metadatumTypeMessage == 'is-danger') }"
                        class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
            </span>
            <label 
                    v-tooltip="{
                        delay: {
                            show: 500,
                            hide: 300,
                        },
                        content: metadatum.metadatum.name,
                        autoHide: false,
                        placement: 'auto-end'
                    }" 
                    class="label">
                {{ metadatum.metadatum.name }}
            </label>
            <span
                    v-if="metadatum.metadatum.required == 'yes'"
                    class="required-metadatum-asterisk"
                    :class="metadatumTypeMessage">*</span>
            <span class="metadata-type">({{ $i18n.get(metadatum.metadatum.metadata_type_object.component) }})</span>
            <help-button 
                    :title="metadatum.metadatum.name"
                    :message="metadatum.metadatum.description"/>
        </span>
        <transition name="filter-item">
            <div   
                    v-show="isCollapsed || metadatumTypeMessage == 'is-danger'"
                    v-if="isTextInputComponent( metadatum.metadatum.metadata_type_object.component )">
                <component 
                        :id="metadatum.metadatum.metadata_type_object.component + '-' + metadatum.metadatum.slug"
                        :is="metadatum.metadatum.metadata_type_object.component"
                        v-model="inputs[0]" 
                        :metadatum="metadatum"
                        @input="emitIsChangingValue()"/>
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
                                @input="emitIsChangingValue()"/>
                            <a 
                                    v-if="index > 0" 
                                    @click="removeInput(index)"
                                    class="is-inline add-link">
                                <b-icon
                                        icon="minus-circle"
                                        size="is-small"
                                        type="is-secondary"/>
                                &nbsp;{{ $i18n.get('label_remove_value') }}</a>
                    </div>

                    <a 
                            @click="addInput"
                            class="is-inline add-link">
                        <span class="icon is-small">
                            <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                        </span>
                        &nbsp;{{ $i18n.get('label_add_value') }}
                    </a>
                </div>
            </div>
        </transition>
        <transition name="filter-item">
            <div 
                    v-show="isCollapsed"
                    v-if="!isTextInputComponent( metadatum.metadatum.metadata_type_object.component )">
                <component
                        :id="metadatum.metadatum.metadata_type_object.component + '-' + metadatum.metadatum.slug"
                        :is="metadatum.metadatum.metadata_type_object.component"
                        v-model="inputs"
                        :metadatum="metadatum"
                        @input="emitIsChangingValue()"/>
            </div>
        </transition>
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
            emitIsChangingValue() {
                this.changeValue();
            },
            changeValue: _.debounce(function() {
                
                if(this.metadatum.value != this.inputs){

                    if(this.inputs.length > 0 && this.inputs[0].value){
                        let terms = [];

                        for(let term of this.inputs){
                            terms.push(term.value);
                        }

                        if(this.metadatum.value instanceof Array){
                            let eq = [];

                            for(let meta of terms){
                                let found = this.metadatum.value.find((element) => {
                                    return meta == element.id;
                                });

                                if(found){
                                    eq.push(found);
                                }
                            }

                            if(eq.length == terms.length && this.metadatum.value.length <= eq.length){
                                return;
                            }
                        }
                    } else if(this.metadatum.value.constructor.name == 'Object'){

                        if(this.metadatum.value.id == this.inputs){
                            return;
                        }
                    } else if(this.metadatum.value instanceof Array){                        
                        let eq = [];

                        for(let meta of this.inputs){
                            let found = this.metadatum.value.find((element) => {
                                return meta == element.id;
                            });

                            if(found){
                                eq.push(found);
                            }
                        }

                        if(eq.length == this.inputs.length && this.metadatum.value.length <= eq.length){
                            return;
                        }
                    } 

                    eventBus.$emit('input', { item_id: this.metadatum.item.id, metadatum_id: this.metadatum.metadatum.id, values: this.inputs } );
                }
            }, 1000),
            getValue(){ 
                if (this.metadatum.value instanceof Array) {
                    this.inputs = this.metadatum.value.slice(0);
                    
                    if (this.inputs.length === 0){
                        this.inputs.push('');
                    }
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
        margin: 0.75rem 0;
        flex-direction: column;
        justify-content: space-between;
    }

    .field {
        border-bottom: 1px solid $gray2;
        padding: 10px 25px;

        .label {
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 15px;
            margin-bottom: 0;
            margin-top: 0.15rem;
            max-width: 50%;
        }
        .metadata-type {
            font-size: 0.8125rem;
            font-weight: 400;
            color: $gray3;
            top: -0.4em;
            position: relative;
        }
        .help-wrapper {
            top: -0.2em;
        }
        .collapse-handle {
            cursor: pointer;
            position: relative;
            margin-left: -42px;
            bottom: 0.1rem;
            white-space: nowrap;
        }
    }
</style>
