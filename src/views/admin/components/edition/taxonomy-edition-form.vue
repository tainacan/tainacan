<template>
    <div>
        <div class="page-container tainacan-repository-level-colors">
            <tainacan-title>
                <h1>
                    <template v-if="$route.name == 'TaxonomyCreationForm'">
                        {{ $i18n.get('title_create_taxonomy_page') }}
                    </template>
                    <template v-else-if="$route.name == 'TaxonomyEditionForm'">
                        {{ $i18n.get('title_taxonomy_edit_page') }} <span class="is-italic has-text-weight-semibold">{{ taxonomy ? taxonomy.name : '' }}</span>
                    </template>
                </h1>
            </tainacan-title>

            <form 
                    v-if="taxonomy != null && taxonomy != undefined && (($route.name == 'TaxonomyCreationForm' && $userCaps.hasCapability('tnc_rep_edit_taxonomies')) || ($route.name == 'TaxonomyEditionForm' && taxonomy.current_user_can_edit))"
                    class="tainacan-form" 
                    label-width="120px">
                <div class="columns">
                    <div class="column is-3">

                        <!-- Name -------------------------------- -->
                        <b-field
                                :addons="false"
                                :label="$i18n.get('label_name')"
                                :type="editFormErrors['name'] != undefined ? 'is-danger' : ''"
                                :message="isUpdatingSlug ? $i18n.get('info_validating_slug') : (editFormErrors['name'] != undefined ? editFormErrors['name'] : '')">
                            <span class="required-metadatum-asterisk">*</span>
                            <help-button 
                                    :title="$i18n.getHelperTitle('taxonomies', 'name')" 
                                    :message="$i18n.getHelperMessage('taxonomies', 'name')"
                                    extra-classes="tainacan-repository-tooltip" />
                            <b-input
                                    id="tainacan-text-name"
                                    v-model="form.name"
                                    :disabled="isUpdatingSlug"
                                    :loading="isUpdatingSlug"
                                    @focus="clearErrors('name')"
                                    @blur="updateSlug()" />
                        </b-field>

                        <!-- Hook for extra Form options -->
                        <template v-if="hasBeginLeftForm">  
                            <form 
                                    id="form-taxonomy-begin-left"
                                    class="form-hook-region"
                                    v-html="getBeginLeftForm" />
                        </template>

                        <!-- Description -------------------------------- -->
                        <b-field
                                :addons="false"
                                :label="$i18n.get('label_description')"
                                :type="editFormErrors['description'] != undefined ? 'is-danger' : ''"
                                :message="editFormErrors['description'] != undefined ? editFormErrors['description'] : ''">
                            <help-button 
                                    :title="$i18n.getHelperTitle('taxonomies', 'description')" 
                                    :message="$i18n.getHelperMessage('taxonomies', 'description')"
                                    extra-classes="tainacan-repository-tooltip" />
                            <b-input
                                    id="tainacan-text-description"
                                    v-model="form.description"
                                    type="textarea"
                                    rows="3"
                                    @focus="clearErrors('description')" />
                        </b-field>

                         <!-- Slug -------------------------------- -->
                         <b-field
                                :addons="false"
                                :label="$i18n.get('label_slug')"
                                :type="editFormErrors['slug'] != undefined ? 'is-danger' : ''"
                                :message="editFormErrors['slug'] != undefined ? editFormErrors['slug'] : ''">
                            <help-button 
                                    :title="$i18n.getHelperTitle('taxonomies', 'slug')" 
                                    :message="$i18n.getHelperMessage('taxonomies', 'slug')"
                                    extra-classes="tainacan-repository-tooltip" />
                            <b-input
                                    id="tainacan-text-slug"
                                    v-model="form.slug"
                                    :disabled="isUpdatingSlug"
                                    @update:model-value="updateSlug()"
                                    @focus="clearErrors('slug')" />
                        </b-field>

                            <!-- Status -------------------------------- --> 
                        <b-field
                                :addons="false" 
                                :label="$i18n.get('label_status')"
                                :type="editFormErrors['status'] != undefined ? 'is-danger' : ''" 
                                :message="editFormErrors['status'] != undefined ? editFormErrors['status'] : ''">
                            <help-button
                                    :title="$i18n.getHelperTitle('taxonomies', 'status')"
                                    :message="$i18n.getHelperMessage('taxonomies', 'status')" />
                            <b-dropdown
                                    ref="item-edition-status-dropdown"
                                    aria-role="list"
                                    class="item-edition-status-dropdown"
                                    :triggers="[ 'click' ]"
                                    :disabled="taxonomy.status === 'auto-draft' || ( editFormErrors['status'] && (form.status == 'publish' || form.status == 'private' || form.status == 'pending' ) )"
                                    max-height="300px">
                                <template #trigger>
                                    <button 
                                            :disabled="taxonomy.status === 'auto-draft' || ( editFormErrors['status'] && (form.status == 'publish' || form.status == 'private' || form.status == 'pending' ) )"
                                            type="button"
                                            class="button is-outlined"
                                            :class="{ 'disabled': taxonomy.status === 'auto-draft' || ( editFormErrors['status'] && (form.status == 'publish' || form.status == 'private' || form.status == 'pending' ) ) }"
                                            style="width: auto">
                                        <span class="icon has-text-gray">
                                            <i 
                                                    class="tainacan-icon tainacan-icon-18px"
                                                    :class="$statusHelper.getIcon(form.status)" />
                                        </span>
                                        <template v-if="form.status !== 'auto-draft' && $statusHelper.getStatuses().find(aStatusObject => aStatusObject.slug == form.status)">
                                            {{ $statusHelper.getStatuses().find(aStatusObject => aStatusObject.slug == form.status).name }}
                                        </template>
                                        <template v-else-if="form.status === 'auto-draft'">
                                            {{ $i18n.get('status_auto-draft') }}
                                        </template>
                                        <span 
                                                style="margin-left: 0.5em;"
                                                class="icon is-small">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                                        </span>
                                    </button>
                                </template>
                                <b-dropdown-item 
                                        v-for="(statusOption, index) of $statusHelper.getStatuses().filter((status) => status.slug != 'draft')"
                                        :key="index"
                                        aria-role="listitem"
                                        @click="form.status = statusOption.slug">
                                    <span class="icon has-text-gray">
                                        <i 
                                                class="tainacan-icon tainacan-icon-18px"
                                                :class="$statusHelper.getIcon(statusOption.slug)" />
                                    </span>
                                    {{ statusOption.name }}
                                    <br>
                                    <small 
                                            v-if="$statusHelper.hasDescription(statusOption.slug)"
                                            class="is-small"
                                            style="margin-left: 2px;">
                                        {{ $statusHelper.getDescription(statusOption.slug) }}
                                    </small>
                                </b-dropdown-item>
                            </b-dropdown>
                        </b-field>

                        <!-- Allow Insert -->
                        <b-field :addons="false">
                            <label class="label is-inline">
                                {{ $i18n.get('label_taxonomy_allow_new_terms') }}
                                <b-switch
                                        id="tainacan-checkbox-allow-insert" 
                                        v-model="form.allowInsert"
                                        size="is-small"
                                        true-value="yes"
                                        false-value="no" />
                                <help-button 
                                        :title="$i18n.getHelperTitle('taxonomies', 'allow_insert')" 
                                        :message="$i18n.getHelperMessage('taxonomies', 'allow_insert')"
                                        extra-classes="tainacan-repository-tooltip" />
                            </label>
                        </b-field>

                        <!-- Allow Insert -->
                        <b-field :addons="false">
                            <label class="label is-inline">
                                {{ $i18n.getHelperTitle('taxonomies', 'hierarchical') }}
                                <b-switch
                                        id="tainacan-checkbox-allow-insert" 
                                        v-model="form.hierarchical"
                                        size="is-small"
                                        true-value="yes"
                                        false-value="no" />
                                <help-button 
                                        :title="$i18n.getHelperTitle('taxonomies', 'hierarchical')" 
                                        :message="$i18n.getHelperMessage('taxonomies', 'hierarchical')"
                                        extra-classes="tainacan-repository-tooltip" />
                            </label>
                        </b-field>

                        <!-- Activate for other post types -->
                        <b-field
                                :addons="false"
                                :label="$i18n.getHelperTitle('taxonomies', 'enabled_post_types')"
                                :type="editFormErrors['enabled_post_types'] != undefined ? 'is-danger' : ''"
                                :message="editFormErrors['enabled_post_types'] != undefined ? editFormErrors['enabled_post_types'] : ''">
                            <help-button 
                                    :title="$i18n.getHelperTitle('taxonomies', 'enabled_post_types')" 
                                    :message="$i18n.getHelperMessage('taxonomies', 'enabled_post_types')"
                                    extra-classes="tainacan-repository-tooltip" />

                            <div class="two-columns-fields">
                                <div 
                                        v-for="wpPostType in wpPostTypes"
                                        :key="wpPostType.slug"
                                        class="field">
                                    <b-checkbox
                                            v-model="form.enabledPostTypes"
                                            :native-value="wpPostType.slug"
                                            :true-value="wpPostType.slug"
                                            false-value=""
                                            name="enabled_post_types">
                                        {{ wpPostType.label }}  
                                    </b-checkbox>
                                </div>    
                            </div>
                        </b-field>

                    </div>

                    <div class="column is-9">

                        <!-- Terms List -->                        
                        <b-field
                                :addons="false"
                                :label="$i18n.get('terms')">
                            <help-button 
                                    :title="$i18n.get('terms')" 
                                    :message="$i18n.get('info_taxonomy_terms_list')"
                                    extra-classes="tainacan-repository-tooltip" />
                            <terms-list
                                    :key="shouldReloadTermsList ? 'termslistreloaded' : 'termslist'"
                                    :is-hierarchical="form.hierarchical !== 'no'"
                                    :taxonomy-id="taxonomyId"
                                    :current-user-can-edit-taxonomy="taxonomy ? taxonomy.current_user_can_edit : false" />
                        </b-field>

                        <!-- Hook for extra Form options -->
                        <template v-if="hasEndLeftForm">  
                            <form 
                                    id="form-taxonomy-end-left"
                                    class="form-hook-region"
                                    v-html="getEndLeftForm" />
                        </template>
                    </div>
                </div>

                <!-- Submit -->
                <footer class="footer field is-grouped form-submit">
                    <div
                            v-if="$route.query.recent"
                            class="control">
                        <button
                                id="button-another-taxonomy-creation"
                                class="button is-secondary"
                                @click.prevent="goToCreateAnotherTaxonomy()">{{ $i18n.get('label_create_another_taxonomy') }}</button>
                    </div>
                    <div    
                            v-if="!$route.query.recent"
                            style="margin-right: auto;"
                            class="control">
                        <button
                                id="button-cancel-taxonomy-creation"
                                class="button is-outlined"
                                type="button"
                                @click="cancelBack">{{ $i18n.get('cancel') }}</button>
                    </div>
                    <p 
                            style="margin: 0 12px;"
                            class="help is-danger">
                        {{ formErrorMessage }}
                    </p>
                    <p 
                            v-if="updatedAt != undefined"
                            class="updated-at">
                        {{ ($i18n.get('info_updated_at') + ' ' + updatedAt) }}
                    </p>
                    <div class="control">
                        <a
                                target="_blank"
                                class="button link-button"
                                :href="themeTaxonomiesURL + taxonomy.slug">
                            <span class="icon is-large">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-openurl" />
                            </span>
                            <span>{{ $i18n.get('label_taxonomy_page_on_website') }}</span>
                        </a>
                        <button
                                id="button-submit-taxonomy-creation"
                                :class="{ 'is-loading': isLoadingTaxonomy, 'is-success': !isLoadingTaxonomy }"
                                class="button"
                                @click.prevent="onSubmit">{{ $i18n.get('save') }}</button>
                    </div>
                </footer>
            </form>

            <div v-if="!isLoadingTaxonomy && (($route.name == 'TaxonomyCreationForm' && !$userCaps.hasCapability('tnc_rep_edit_taxonomies')) || ($route.name == 'TaxonomyEditionForm' && taxonomy && taxonomy.current_user_can_edit != undefined && !taxonomy.current_user_can_edit))">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-taxonomies" />
                            </span>
                        </p>
                        <p>{{ $i18n.get('info_can_not_edit_taxonomy') }}</p>
                    </div>
                </section>
            </div>

            <b-loading 
                    v-model="isLoadingTaxonomy" 
                    :can-cancel="false" />

        </div>
    </div>
</template>

<script>
    import { nextTick } from 'vue';
    import { permalinkGetter, formHooks } from "../../js/mixins";
    import { mapActions } from 'vuex';
    import TermsList from '../lists/terms-list.vue';
    import CustomDialog from '../other/custom-dialog.vue';

    export default {
        name: 'TaxonomyEditionForm',
        components: {
           TermsList
        },
        mixins: [ permalinkGetter, formHooks ],
        beforeRouteLeave( to, from, next ) {
            let formNotSaved = false;

            if (this.taxonomy) {
                if (this.taxonomy.name != this.form.name)
                    formNotSaved = true;
                if (this.taxonomy.description != this.form.description)
                    formNotSaved = true;
                if (this.taxonomy.slug != this.form.slug)
                    formNotSaved = true;
                if (this.taxonomy.allow_insert != this.form.allowInsert)
                    formNotSaved = true;
                if (this.taxonomy.hierarchical != this.form.hierarchical)
                    formNotSaved = true;
                if (this.taxonomy.status != this.form.status)
                    formNotSaved = true;
                if (this.taxonomy.enabled_post_types != this.form.enabledPostTypes)
                    formNotSaved = true;
            }

            if (formNotSaved && this.taxonomy) {
                this.$buefy.modal.open({
                    parent: this,
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_taxonomy_not_saved'),
                        onConfirm: () => {
                            next();
                        }
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    canCancel: ['escape', 'outside']
                });   
            } else {
                next();
            }  
        },
        data(){
            return {
                taxonomyId: String,
                tabIndex: 0,
                taxonomy: null,
                isLoadingTaxonomy: false,
                isUpdatingSlug: false,
                form: {
                    name: String,
                    status: String,
                    description: String,
                    slug: String,
                    allowInsert: String,
                    hierarchical: String,
                    enabledPostTypes: Array
                },
                wpPostTypes: tainacan_plugin.wp_post_types,
                editFormErrors: {},
                formErrorMessage: '',
                entityName: 'taxonomy',
                updatedAt: undefined,
                shouldReloadTermsList: false,
                themeTaxonomiesURL: tainacan_plugin.theme_taxonomy_list_url
            }
        },
        mounted(){
  
            if (this.$route.query.tab == 'terms')
                this.tabIndex = 1;

            if (this.$route.name == "TaxonomyCreationForm") {
                this.createNewTaxonomy();
            } else if (this.$route.name == "TaxonomyEditionForm") {

                this.isLoadingTaxonomy = true;

                // Obtains current taxonomy ID from URL
                this.pathArray = this.$route.fullPath.split("/").reverse();
                this.taxonomyId = this.pathArray[1];

                this.fetchTaxonomy({ taxonomyId: this.taxonomyId, isContextEdit: true })
                    .then(res => {
                        this.taxonomy = JSON.parse(JSON.stringify(res.taxonomy));

                        this.$routerHelper.appendToPageTitle(this.taxonomy.name);

                        wp.hooks.doAction(
                            'tainacan_navigation_path_updated', 
                            { 
                                currentRoute: this.$route,
                                adminOptions: this.$adminOptions,
                                parentEntity: {
                                    rootLink: 'taxonomies',
                                    name: this.taxonomy.name,
                                    defaultLink: `taxonomies/${this.taxonomyId}/edit`,
                                    label: this.$i18n.get('taxonomies')
                                }
                            }
                        );

                        // Fills hook forms with it's real values 
                        nextTick()
                            .then(() => {
                                this.updateExtraFormData(this.taxonomy);
                            });

                        // Fill this.form data with current data.
                        this.form.name = this.taxonomy.name;
                        this.form.description = this.taxonomy.description;
                        this.form.slug = this.taxonomy.slug;
                        this.form.status = this.taxonomy.status;
                        this.form.allowInsert = this.taxonomy.allow_insert;
                        this.form.hierarchical = this.taxonomy.hierarchical;
                        this.form.enabledPostTypes = this.taxonomy.enabled_post_types;

                        this.isLoadingTaxonomy = false;
                    })
                    .catch(() => this.isLoadingTaxonomy = false);
            }
        },
        methods: {
            ...mapActions('taxonomy', [
                'createTaxonomy',
                'updateTaxonomy',
                'fetchTaxonomy',
                'fetchOnlySlug'
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
                    hierarchical: this.form.hierarchical,
                    enabled_post_types: this.form.enabledPostTypes,
                    context: 'edit'
                };
                this.fillExtraFormData(data);
                this.updateTaxonomy(data)
                    .then(updatedTaxonomy => {

                        this.taxonomy = JSON.parse(JSON.stringify(updatedTaxonomy));

                        wp.hooks.doAction(
                            'tainacan_navigation_path_updated', 
                            { 
                                currentRoute: this.$route,
                                adminOptions: this.$adminOptions,
                                parentEntity: {
                                    rootLink: 'taxonomies',
                                    name: this.taxonomy.name,
                                    defaultLink: `taxonomies/${this.taxonomyId}/edit`,
                                    label: this.$i18n.get('taxonomies')
                                }
                            }
                        );

                        // Fills hook forms with it's real values 
                        this.updateExtraFormData(this.taxonomy);

                        // Fill this.form data with current data.
                        this.form.name = this.taxonomy.name;
                        this.form.slug = this.taxonomy.slug;
                        this.form.description = this.taxonomy.description;
                        this.form.status = this.taxonomy.status;
                        this.form.allowInsert = this.taxonomy.allow_insert;
                        this.form.hierarchical = this.taxonomy.hierarchical;
                        this.form.enabledPostTypes = this.taxonomy.enabled_post_types;

                        this.isLoadingTaxonomy = false;
                        this.formErrorMessage = '';
                        this.editFormErrors = {};
                        // Updates saved at message
                        let now = new Date();
                        this.updatedAt = now.toLocaleString();

                        if (this.$route.name == 'TaxonomyCreationForm') {                    
                            this.$router.push(this.$routerHelper.getTaxonomyEditPath(this.taxonomyId, true));
                        }
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
                    allow_insert: 'yes',
                    hierarchical: 'yes'
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
                        this.form.hierarchical = this.taxonomy.hierarchical;

                        // Pre-fill status with publish to incentivate it
                        this.form.status = 'publish';

                        this.isLoadingTaxonomy = false;
                        this.shouldReloadTermsList = false;

                    })
                    .catch((error) => {
                        this.$console.error(error)
                        this.isLoadingTaxonomy = false;
                    });
            },
            clearErrors(attribute) {
                this.editFormErrors[attribute] = undefined;
            },
            cancelBack(){
                this.$router.go(-1);
            },
            goToCreateAnotherTaxonomy() {
                this.$router.push(this.$routerHelper.getNewTaxonomyPath());                            
                
                this.taxonomyId = undefined;
                this.taxonomy = null;
                this.isUpdatingSlug = false;
                this.form = {
                    name: String,
                    status: String,
                    description: String,
                    slug: String,
                    allowInsert: String,
                    hierarchical: String,
                    enabledPostTypes: Array
                };
                this.editFormErrors = {};
                this.formErrorMessage = '';
                this.updatedAt = undefined;

                // Forces terms list to reload
                this.shouldReloadTermsList = true;

                this.createNewTaxonomy();
            }
        }
    }
</script>
<style lang="scss" scoped>

    .tab-content {
        overflow: visible !important;
    }
    .status-radios {
        display: flex;
    }
    .status-radios .control-label {
        display: flex;
        align-items: center;
    }
    .tainacan-form>.columns {
        margin-bottom: 48px;
    }
    .tainacan-form .column:last-of-type {
        padding-left: var(--tainacan-one-column) !important;
    }
    .two-columns-fields {
        column-width: 180px;

        .field {
            margin-bottom: 0px;
        }
    }
    .form-submit {
        align-items: center;
    }
    .updated-at {
        margin: 0 1em 0 auto;
        color: var(--tainacan-info-color);
        font-style: italic;
    }
    .two-thirds-layout-options {
        display: flex;
        column-gap: 1em !important;

        & > .field:first-child {
            flex-basis: 75%;
        }
        & > .field:last-child {
            flex-basis: 25%;
        }

        .dropdown-trigger>.button {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media screen and (max-width: 782px) {
            flex-wrap: wrap;
            margin-bottom: 1em;

            & > .field {
                flex-basis: 100% !important;
            }
            .dropdown-trigger>.button {
                min-height: 40px;
            }
        }
    }
    .footer {
        padding: 10px var(--tainacan-one-column);
        position: absolute;
        bottom: 0;
        right: 0;
        z-index: 9999;
        background-color: var(--tainacan-gray1);
        width: 100%;
        height: 52px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        transition: bottom 0.5s ease, width 0.2s linear;
        box-shadow: 0px 0px 12px -8px var(--tainacan-black);

        &::after,
        &::before {
            height: 18px;
            width: 18px;
            background: transparent;
            display: block;
            content: '';
            position: absolute;
        }
        &::before {
            left: 0;
            top: -18px;
            border-bottom-left-radius: 9px;
            box-shadow: -9px 0px 0 0 var(--tainacan-gray1), inset 2px -2px 5px -3px var(--tainacan-gray2)
        }
        &::after {
            right: 0;
            top: -18px;
            border-bottom-right-radius: 9px;
            box-shadow: 9px 0px 0 0 var(--tainacan-gray1), inset -2px -2px 5px -3px var(--tainacan-gray2)
        }

        .footer-message {
            display: flex;
            align-items: center;
        }

        .update-info-section {
            color: var(--tainacan-info-color);
            margin-right: auto;
            display: flex;
            flex-wrap: nowrap;
        }

        .help {
            display: inline-flex;
            font-size: 1.0em;
            margin-top: 0;
            margin-left: 24px;

            .tainacan-help-tooltip-trigger {
                margin-left: 0.25em;
            }
        }

        .link-button {
            background-color: transparent;
            border: none;
        }

        @media screen and (max-width: 769px) {
            padding: 13px 0.5em;
            width: 100%;
            flex-wrap: wrap;
            height: auto;
            position: fixed;

            .update-info-section {
                margin-left: auto;margin-bottom: 0.75em;
                margin-top: -0.25em;
            }
        }
    }
</style>

