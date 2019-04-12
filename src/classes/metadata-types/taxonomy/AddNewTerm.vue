<template>
    <div>
        <span v-if="!showForm">
            <a
                    @click="toggleForm()"
                    class="is-inline add-link">
                <span class="icon is-small">
                    <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                </span>
                &nbsp;{{ $i18n.get('label_new_term') }}
            </a>
        </span>
        <transition name="appear">
            <section
                    v-if="showForm"
                    style="padding-left: 0px; margin-top: 12px; margin-bottom: -12px;">
                <b-field 
                        :addons="false"
                        :type="((formErrors.name !== '' || formErrors.repeated !== '') && (formErrors.name !== undefined || formErrors.repeated !== undefined )) ? 'is-danger' : ''"
                        :message="formErrors.name != undefined? formErrors : formErrors.repeated">
                    <label class="label is-inline">
                        {{ $i18n.get('label_name') }}
                        <span class="required-term-asterisk">*</span>
                        <help-button
                                :title="$i18n.get('label_name')"
                                :message="$i18n.get('info_help_term_name')"/>
                    </label>
                    <b-input
                            :class="{'has-content': name != undefined && name != ''}" 
                            v-model="name"
                            @focus="clearErrors({ name: 'name', repeated: 'repeated' })"/>
                </b-field>

                <!-- <b-field :label="$i18n.get('label_parent_term')">
                    <b-select
                            v-model="parent">
                        <option 
                                :value="0" 
                                selected> ---{{ $i18n.get('label_parent_term') }}--- </option>
                        <option
                                v-for="(option,index) in options"
                                :key="index"
                                :value="option.id"
                                v-html="setSpaces( option.level ) + option.name"/>
                    </b-select>
                </b-field> -->
                
                <!-- Parent -------------- -->
                <b-field
                        :addons="false"
                        :type="((formErrors.parent !== '' || formErrors.repeated !== '') && (formErrors.parent !== undefined || formErrors.repeated !== undefined )) ? 'is-danger' : ''"
                        :message="formErrors.parent ? formErrors : formErrors.repeated">
                    <label class="label is-inline">
                        {{ $i18n.get('label_parent_term') }}
                        <b-switch
                                @input="onToggleSwitch()"
                                id="tainacan-checkbox-has-parent" 
                                size="is-small"
                                v-model="hasParent" />
                        <help-button
                                :title="$i18n.get('label_parent_term')"
                                :message="$i18n.get('info_help_parent_term')"/>
                    </label>
                    <b-autocomplete
                            id="tainacan-text-cover-page"
                            :placeholder="$i18n.get('instruction_parent_term')"
                            :data="parentTerms"
                            field="name"
                            v-model="parentTermName"
                            @select="onSelectParentTerm($event)"
                            :loading="isFetchingParentTerms"
                            @input="fecthParentTerms($event)"
                            @focus="clearErrors('parent');"
                            :disabled="!hasParent">
                        <template slot-scope="props">
                            {{ props.option.name }}
                        </template>
                        <template slot="empty">{{ $i18n.get('info_no_parent_term_found') }}</template>
                    </b-autocomplete>
                    <transition name="fade">
                        <p
                                class="checkboxes-warning"
                                v-show="showCheckboxesWarning == true">
                            {{ $i18n.get('info_warning_changing_parent_term') }}
                        </p>
                    </transition>
                </b-field>

                <button
                        :class="{ 'is-loading': isAddingNewTerm }"
                        class="button is-outlined"
                        @click="toggleForm()"
                        type="button">
                    {{ $i18n.get('cancel') }}
                </button>

                <button
                        :class="{ 'is-loading': isAddingNewTerm }"
                        class="button is-secondary"
                        @click="save"
                        type="button">
                    {{ $i18n.get('save') }}
                </button>
            </section>

        </transition>
    </div>
</template>
<script>
    import { tainacan as axios } from '../../../js/axios/axios'
    import { mapActions } from 'vuex';

    export default {
        data(){
            return {
                name: '',
                parent: 0,
                hasParent: false,
                showForm: false,
                parentTerms: [],
                search: '',
                parentTermName: '',
                isAddingNewTerm: false,
                isFetchingParentTerms: false,
                metadatum_id: this.metadatum.metadatum.id,
                formErrors: {}
            }
        },
        props: {
            id: String,
            item_id: [Number,String],
            metadatum: [Number,String],
            taxonomy_id: [Number,String],
            value:[ Array, Boolean, Number ],
            options: {
                type: Array
            },
            componentType: ''
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetchPossibleParentTerms'
            ]),
            toggleForm() {
                this.name = '';
                this.parent = 0;
                this.hasParent = false;
                this.parentTerms = [];
                this.search = '';
                this.parentTermName = '';
                this.isFetchingParentTerms = false;
                this.isAddingNewTerm = false;
                this.formErrors = {};
                this.showForm = !this.showForm;
            },
            setSpaces( level ){
                let result = '';
                let space =  '&nbsp;&nbsp;'

                for(let i = 0;i < level; i++)
                    result += space;

                return result;
            },
            fecthParentTerms(search) {
                this.isFetchingParentTerms = true;
                
                this.fetchPossibleParentTerms({
                        taxonomyId: this.taxonomy_id, 
                        termId: 'new', 
                        search: search })
                    .then((parentTerms) => {
                        this.parentTerms = parentTerms;
                        this.isFetchingParentTerms = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingParentTerms = false;
                    });
            },
            onToggleSwitch() {
                this.clearErrors('parent');
            },
            onSelectParentTerm(selectedParentTerm) {
                this.parent = selectedParentTerm.id;
                this.selectedParentTerm = selectedParentTerm;
                this.parentTermName = selectedParentTerm.name;
            },
            clearErrors(attributes) {
                if(attributes instanceof Object){
                    for(let attribute in attributes){
                        this.formErrors[attribute] = undefined;
                    }
                } else {
                    this.formErrors[attributes] = undefined;
                }
            },
            save(){
                if( this.name.trim() === ''){
                    this.$toast.open({
                        duration: 2000,
                        message: this.$i18n.get('info_name_is_required'),
                        position: 'is-bottom',
                        type: 'is-danger'
                    })
                } else {
                    this.isAddingNewTerm = true;

                    axios.post(`/taxonomy/${this.taxonomy_id}/terms?hideempty=0&order=asc`, {
                        name: this.name,
                        parent: this.parent
                    })
                    .then( res => {

                        this.isAddingNewTerm = false;

                        if( res.data && res.data.id || res.id ){
                            let id = ( res.id ) ? res.id : res.data.id;
                            let val = this.value;

                            if( !Array.isArray( val ) && this.metadatum.metadatum.multiple === 'no' ){
                                axios.patch(`/item/${this.item_id}/metadata/${this.metadatum_id}`, {
                                    values: id,
                                }).then(() => {
                                    this.$emit('newTerm', { values: id, taxonomyId: this.taxonomy_id, metadatumId: this.metadatum_id });
                                    this.toggleForm();
                                })
                            } else {
                                val = ( val ) ? val : [];
                                val.push( this.componentType == ('tainacan-taxonomy-checkbox' || 'tainacan-taxonomy-radio') ? id : {'label': this.name, 'value': id} );
                                axios.patch(`/item/${this.item_id}/metadata/${this.metadatum_id}`, {
                                    values: val,
                                }).then(() => {
                                    this.$emit('newTerm', { values: val, taxonomyId: this.taxonomy_id, metadatumId: this.metadatum_id });
                                    this.toggleForm();
                                })
                            }
                        }
                    })
                    .catch((error) => {
                        let errors = { error_message: error['response']['data'].error_message, errors: error['response']['data'].errors };
                        for (let error of errors.errors) {
                            for (let metadatum of Object.keys(error)) {
                                this.$set(this.formErrors, metadatum, (this.formErrors[metadatum] !== undefined ? this.formErrors[metadatum] : '') + error[metadatum] + '\n');
                            }
                        }

                        this.isAddingNewTerm = false;
                    });
                }

            }
        },
        mounted() {
            this.hasParent = this.parent != undefined && this.parent > 0;
        }
    }
</script>
<style scoped>
</style>