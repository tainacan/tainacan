<template>
    <div>
        <div class="page-container repository-level-page">
            <tainacan-title 
                    :bread-crumb-items="[
                        { path: $routerHelper.getTaxonomiesPath(), label: $i18n.get('taxonomies') },
                        { path: '', label: (taxonomy!= null && taxonomy.name != undefined) ? taxonomy.name : $i18n.get('taxonomy') }
                    ]"/>
            <b-tabs 
                    @change="onChangeTab($event)"
                    v-model="tabIndex">    
                <b-tab-item :label="$i18n.get('taxonomy')">
                    <form 
                            v-if="taxonomy != null && taxonomy != undefined" 
                            class="tainacan-form" 
                            label-width="120px">
                        <div class="columns">
                            <div class="column">
                                <!-- Name -------------------------------- -->
                                <b-field
                                        :addons="false"
                                        :label="$i18n.get('label_name')"
                                        :type="editFormErrors['name'] != undefined ? 'is-danger' : ''"
                                        :message="editFormErrors['name'] != undefined ? editFormErrors['name'] : ''">
                                    <help-button 
                                            :title="$i18n.getHelperTitle('taxonomies', 'name')" 
                                            :message="$i18n.getHelperMessage('taxonomies', 'name')"/>
                                    <b-input
                                            id="tainacan-text-name"
                                            v-model="form.name"
                                            @focus="clearErrors('name')"
                                            @blur="updateSlug()"/>
                                </b-field>

                                <!-- Hook for extra Form options -->
                                <template 
                                        v-if="formHooks != undefined && 
                                            formHooks['taxonomy'] != undefined &&
                                            formHooks['taxonomy']['begin-left'] != undefined">  
                                    <form 
                                        id="form-taxonomy-begin-left"
                                        class="form-hook-region"
                                        v-html="formHooks['taxonomy']['begin-left'].join('')"/>
                                </template>

                                <!-- Description -------------------------------- -->
                                <b-field
                                        :addons="false"
                                        :label="$i18n.get('label_description')"
                                        :type="editFormErrors['description'] != undefined ? 'is-danger' : ''"
                                        :message="editFormErrors['description'] != undefined ? editFormErrors['description'] : ''">
                                    <help-button 
                                            :title="$i18n.getHelperTitle('taxonomies', 'description')" 
                                            :message="$i18n.getHelperMessage('taxonomies', 'description')"/>
                                    <b-input
                                            id="tainacan-text-description"
                                            type="textarea"
                                            v-model="form.description"
                                            @focus="clearErrors('description')"/>
                                </b-field>

                                <!-- Allow Insert -->
                                <b-field :addons="false">
                                    <label class="label is-inline">
                                            {{ $i18n.get('label_taxonomy_allow_new_terms') }}
                                            <b-switch
                                                    id="tainacan-checkbox-allow-insert" 
                                                    size="is-small"
                                                    v-model="form.allowInsert"
                                                    true-value="yes"
                                                    false-value="no" />
                                            <help-button 
                                                :title="$i18n.getHelperTitle('taxonomies', 'allow_insert')" 
                                                :message="$i18n.getHelperMessage('taxonomies', 'allow_insert')"/>
                                        </label>
                                </b-field>
                            </div>

                            <div class="column">

                                <!-- Status -------------------------------- --> 
                                <b-field
                                        :addons="false" 
                                        :label="$i18n.get('label_status')"
                                        :type="editFormErrors['status'] != undefined ? 'is-danger' : ''" 
                                        :message="editFormErrors['status'] != undefined ? editFormErrors['status'] : ''">
                                    <help-button 
                                            :title="$i18n.getHelperTitle('taxonomies', 'status')" 
                                            :message="$i18n.getHelperMessage('taxonomies', 'status')"/>
                                    <div class="status-radios">
                                        <b-radio
                                                v-model="form.status"
                                                v-for="(statusOption, index) of $statusHelper.getStatuses()"
                                                :key="index"
                                                :native-value="statusOption.slug">
                                            <span class="icon has-text-gray">
                                                <i 
                                                    class="tainacan-icon tainacan-icon-18px"
                                                    :class="$statusHelper.getIcon(statusOption.slug)"/>
                                            </span>
                                            {{ statusOption.name }}
                                        </b-radio>
                                    </div>
                                </b-field>
                                
                                <!-- Slug -------------------------------- -->
                                <b-field
                                        :addons="false"
                                        :label="$i18n.get('label_slug')"
                                        :type="editFormErrors['slug'] != undefined ? 'is-danger' : ''"
                                        :message="editFormErrors['slug'] != undefined ? editFormErrors['slug'] : ''">
                                    <help-button 
                                            :title="$i18n.getHelperTitle('taxonomies', 'slug')" 
                                            :message="$i18n.getHelperMessage('taxonomies', 'slug')"/>
                                    <b-input
                                            @input="updateSlug()"
                                            id="tainacan-text-slug"
                                            v-model="form.slug"
                                            @focus="clearErrors('slug')"
                                            :disabled="isUpdatingSlug"/>
                                </b-field>

                                <!-- Activate for other post types -->
                                <b-field
                                        :addons="false"
                                        :label="$i18n.getHelperTitle('taxonomies', 'enabled_post_types')"
                                        :type="editFormErrors['enabled_post_types'] != undefined ? 'is-danger' : ''"
                                        :message="editFormErrors['enabled_post_types'] != undefined ? editFormErrors['enabled_post_types'] : ''">
                                    <help-button 
                                        :title="$i18n.getHelperTitle('taxonomies', 'enabled_post_types')" 
                                        :message="$i18n.getHelperMessage('taxonomies', 'enabled_post_types')"/>

                                    <div 
                                            v-for="wpPostType in wpPostTypes"
                                            :key="wpPostType.slug"
                                            class="field">
                                        <b-checkbox
                                            :native-value="wpPostType.slug"
                                            :true-value="wpPostType.slug"
                                            false-value=""
                                            v-model="form.enabledPostTypes"
                                            name="enabled_post_types" >
                                            {{ wpPostType.label }}  
                                        </b-checkbox>
                                    </div>    
                                </b-field>

                                <!-- Hook for extra Form options -->
                                <template 
                                        v-if="formHooks != undefined && 
                                            formHooks['taxonomy'] != undefined &&
                                            formHooks['taxonomy']['end-left'] != undefined">  
                                    <form 
                                        id="form-taxonomy-end-left"
                                        class="form-hook-region"
                                        v-html="formHooks['taxonomy']['end-left'].join('')"/>
                                </template>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="field is-grouped form-submit">
                            <div class="control">
                                <button
                                        id="button-cancel-taxonomy-creation"
                                        class="button is-outlined"
                                        type="button"
                                        @click="cancelBack">{{ $i18n.get('cancel') }}</button>
                            </div>
                            <p 
                                    v-if="updatedAt != undefined"
                                    class="updated-at">
                                {{ ($i18n.get('info_updated_at') + ' ' + updatedAt) }}
                            </p>
                            <div class="control">
                                <button
                                        :class="{ 'is-loading': isLoadingTaxonomy }"
                                        id="button-submit-taxonomy-creation"
                                        @click.prevent="onSubmit"
                                        class="button is-success">{{ $i18n.get('save') }}</button>
                            </div>
                        </div>
                        <p class="help is-danger">{{ formErrorMessage }}</p>
                    </form>
                </b-tab-item>
                
                <b-tab-item :label="$i18n.get('terms')">
                    <!-- Terms List -->    
                    <terms-list 
                            @isEditingTermUpdate="isEditingTermUpdate"
                            :taxonomy-id="taxonomyId"/>
                </b-tab-item>

                <b-loading 
                        :active.sync="isLoadingTaxonomy" 
                        :can-cancel="false"/>
            </b-tabs>
        </div>
    </div>
</template>

<script>
    import { wpAjax, formHooks } from "../../js/mixins";
    import { mapActions, mapGetters } from 'vuex';
    import TermsList from '../lists/terms-list.vue';
    import CustomDialog from '../other/custom-dialog.vue';

    export default {
        name: 'TaxonomyEditionForm',
        mixins: [ wpAjax, formHooks ],
        data(){
            return {
                taxonomyId: String,
                tabIndex: 0,
                taxonomy: null,
                isLoadingTaxonomy: false,
                isUpdatingSlug: false,
                isEditinTerm: false,
                form: {
                    name: String,
                    status: String,
                    description: String,
                    slug: String,
                    allowInsert: String,
                    enabledPostTypes: Array
                },
                wpPostTypes: tainacan_plugin.wp_post_types,
                editFormErrors: {},
                formErrorMessage: '',
                entityName: 'taxonomy',
                updatedAt: undefined
            }
        },
        components: {
            TermsList
        },
        beforeRouteLeave( to, from, next ) {
            let formNotSaved = false;

            if (this.taxonomy.name != this.form.name)
                formNotSaved = true;
            if (this.taxonomy.description != this.form.description)
                formNotSaved = true;
            if (this.taxonomy.slug != this.form.slug)
                formNotSaved = true;
            if (this.taxonomy.allow_insert != this.form.allowInsert)
                formNotSaved = true;
            if (this.taxonomy.status != this.form.status)
                formNotSaved = true;
            if (this.taxonomy.enabled_post_types != this.form.enabledPostTypes)
                formNotSaved = true;

            if (formNotSaved) {
                this.$modal.open({
                    parent: this,
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_taxonomy_not_saved'),
                        onConfirm: () => {
                            next();
                        }
                    }
                });  
            } else if (this.isEditinTerm) {
                this.$modal.open({
                    parent: this,
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_terms_not_saved'),
                        onConfirm: () => {
                            next();
                        }
                    }
                });  
            } else {
                next();
            }  
        },
        methods: {
            ...mapActions('taxonomy', [
                'createTaxonomy',
                'updateTaxonomy',
                'fetchTaxonomy',
                'fetchOnlySlug'
            ]),
            ...mapGetters('taxonomy',[
                'getTaxonomy',
            ]),
            onChangeTab(tab) {
                this.tabIndex = tab;
                if (this.tabIndex == 1) {
                    this.$router.push({query: {tab: 'terms'}});
                } else {
                    this.$router.push({query: {}});
                }
            },
            onSubmit() {

                this.isLoadingTaxonomy = true;

                let data = {
                    taxonomyId: this.taxonomyId,
                    name: this.form.name,
                    description: this.form.description,
                    slug: this.form.slug ? this.form.slug : '',
                    status: this.form.status,
                    allow_insert: this.form.allowInsert,
                    enabled_post_types: this.form.enabledPostTypes
                };
                this.fillExtraFormData(data);
                this.updateTaxonomy(data)
                    .then(updatedTaxonomy => {

                        this.taxonomy = updatedTaxonomy;

                        // Fills hook forms with it's real values 
                        this.updateExtraFormData(this.taxonomy);

                        // Fill this.form data with current data.
                        this.form.name = this.taxonomy.name;
                        this.form.slug = this.taxonomy.slug;
                        this.form.description = this.taxonomy.description;
                        this.form.status = this.taxonomy.status;
                        this.form.allowInsert = this.taxonomy.allow_insert;
                        this.form.enabledPostTypes = this.taxonomy.enabled_post_types;

                        this.isLoadingTaxonomy = false;
                        this.formErrorMessage = '';
                        this.editFormErrors = {};

                        // Updates saved at message
                        let now = new Date();
                        this.updatedAt = now.toLocaleString();
                    })
                    .catch((errors) => {
                        for (let error of errors.errors) {
                            for (let attribute of Object.keys(error)) {
                                this.editFormErrors[attribute] = error[attribute];
                            }
                        }
                        this.formErrorMessage = errors.error_message;

                        this.isLoadingTaxonomy = false;
                    });
            },
            updateSlug: _.debounce(function(){
                if(!this.form.name || this.form.name.length <= 0){
                    return;
                }

                this.isUpdatingSlug = true;

                this.getSamplePermalink(this.taxonomyId, this.form.name, this.form.slug)
                    .then((res) => {
                        this.form.slug = res.data.slug;

                        this.isUpdatingSlug = false;
                        this.formErrorMessage = '';
                        this.editFormErrors = {};
                    })
                    .catch(errors => {
                        this.$console.error(errors);

                        this.isUpdatingSlug = false;
                    });

            }, 500),
            createNewTaxonomy() {
                // Puts loading on Draft Taxonomy creation
                this.isLoadingTaxonomy = true;

                // Creates draft Taxonomy
                let data = {
                    name: '',
                    description: '',
                    status: 'auto-draft',
                    slug: '',
                    allow_insert: '',
                };
                this.fillExtraFormData(data);
                this.createTaxonomy(data)
                    .then(res => {

                        this.taxonomyId = res.id;
                        this.taxonomy = res;

                        // Fill this.form data with current data.
                        this.form.name = this.taxonomy.name;
                        this.form.description = this.taxonomy.description;
                        this.form.slug = this.taxonomy.slug;
                        this.form.allowInsert = this.taxonomy.allow_insert;

                        // Pre-fill status with publish to incentivate it
                        this.form.status = 'publish';

                        this.isLoadingTaxonomy = false;

                    })
                    .catch(error => this.$console.error(error));
            },
            clearErrors(attribute) {
                this.editFormErrors[attribute] = undefined;
            },
            cancelBack(){
                this.$router.go(-1);
            },
            labelNewTerms(){
                return ( this.form.allowInsert === 'yes' ) ? this.$i18n.get('label_yes') : this.$i18n.get('label_no');
            },
            isEditingTermUpdate (value) {
                this.isEditinTerm = value;
            }
        },
        mounted(){
  
            if (this.$route.query.tab == 'terms')
                this.tabIndex = 1;

            if (this.$route.path.split("/").pop() === "new") {
                this.createNewTaxonomy();
            } else if (this.$route.path.split("/").pop() === "edit") {

                this.isLoadingTaxonomy = true;

                // Obtains current taxonomy ID from URL
                this.pathArray = this.$route.fullPath.split("/").reverse();
                this.taxonomyId = this.pathArray[1];

                this.fetchTaxonomy(this.taxonomyId).then(res => {
                    this.taxonomy = res.taxonomy;

                    // Fills hook forms with it's real values 
                    this.$nextTick()
                        .then(() => {
                            this.updateExtraFormData(this.taxonomy);
                        });

                    // Fill this.form data with current data.
                    this.form.name = this.taxonomy.name;
                    this.form.description = this.taxonomy.description;
                    this.form.slug = this.taxonomy.slug;
                    this.form.status = this.taxonomy.status;
                    this.form.allowInsert = this.taxonomy.allow_insert;
                    this.form.enabledPostTypes = this.taxonomy.enabled_post_types;

                    this.isLoadingTaxonomy = false;
                });
            }
        }
    }
</script>
<style>
    .tab-content {
        overflow: visible !important;
    }
    .status-radios {
        display: flex;
    }
    .status-radios .control-lable {
        display: flex;
        align-items: center;
    }
    .tainacan-form>.columns {
        margin-bottom: 76px;
    }
    .tainacan-form .column {
        padding: 1rem 4.1666667%;
    }
    .tainacan-form .column:last-of-type {
        padding-left: 0;
    }
    .form-submit {
        align-items: center;
    }
    .updated-at {
        margin: 0 1rem 0 auto;
        color: #555758;
        font-style: italic;
    }
</style>

