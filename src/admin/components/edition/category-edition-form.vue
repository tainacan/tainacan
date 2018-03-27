<template>
    <div>
        <div class="page-container primary-page">
            <b-tag
                    v-if="category != null && category != undefined" 
                    :type="'is-' + getStatusColor(category.status)" 
                    v-text="category.status"/>
            <form 
                    v-if="category != null && category != undefined" 
                    class="tainacan-form" 
                    label-width="120px">

                <!-- Name -------------------------------- -->
                <b-field
                        :addons="false"
                        :label="$i18n.get('label_name')"
                        :type="editFormErrors['name'] != undefined ? 'is-danger' : ''"
                        :message="editFormErrors['name'] != undefined ? editFormErrors['name'] : ''">
                    <help-button 
                            :title="$i18n.getHelperTitle('categories', 'name')" 
                            :message="$i18n.getHelperMessage('categories', 'name')"/>
                    <b-input
                            id="tainacan-text-name"
                            v-model="form.name"
                            @focus="clearErrors('name')"
                            @blur="updateSlug()"/>
                </b-field>

                <!-- Description -------------------------------- -->
                <b-field
                        :addons="false"
                        :label="$i18n.get('label_description')"
                        :type="editFormErrors['description'] != undefined ? 'is-danger' : ''"
                        :message="editFormErrors['description'] != undefined ? editFormErrors['description'] : ''">
                    <help-button 
                            :title="$i18n.getHelperTitle('categories', 'description')" 
                            :message="$i18n.getHelperMessage('categories', 'description')"/>
                    <b-input
                            id="tainacan-text-description"
                            type="textarea"
                            v-model="form.description"
                            @focus="clearErrors('description')"/>
                </b-field>

                <!-- Status -------------------------------- -->
                <b-field
                        :addons="false"
                        :label="$i18n.get('label_status')"
                        :type="editFormErrors['status'] != undefined ? 'is-danger' : ''"
                        :message="editFormErrors['status'] != undefined ? editFormErrors['status'] : ''">
                    <help-button 
                            :title="$i18n.getHelperTitle('categories', 'status')" 
                            :message="$i18n.getHelperMessage('categories', 'status')"/>
                    <b-select
                            id="tainacan-select-status"
                            v-model="form.status"
                            @focus="clearErrors('status')"
                            :placeholder="$i18n.get('instruction_select_a_status')">
                        <option
                                v-for="statusOption in statusOptions"
                                :key="statusOption.value"
                                :value="statusOption.value"
                                :disabled="statusOption.disabled">{{ statusOption.label }}
                        </option>
                    </b-select>
                </b-field>

                <!-- Slug -------------------------------- -->
                <b-field
                        :addons="false"
                        :label="$i18n.get('label_slug')"
                        :type="editFormErrors['slug'] != undefined ? 'is-danger' : ''"
                        :message="editFormErrors['slug'] != undefined ? editFormErrors['slug'] : ''">
                    <help-button 
                            :title="$i18n.getHelperTitle('categories', 'slug')" 
                            :message="$i18n.getHelperMessage('categories', 'slug')"/>
                    <b-icon :class="{'is-loading': isUpdatingSlug}"/>
                    <b-input
                            @input="updateSlug()"
                            id="tainacan-text-slug"
                            v-model="form.slug"
                            @focus="clearErrors('slug')"
                            :disabled="isUpdatingSlug"/>
                </b-field>

                <!-- Allow Insert -->
                <b-field 
                        :addons="false"
                        :label="$i18n.get('label_category_allow_new_terms')">
                    <help-button 
                        :title="$i18n.getHelperTitle('categories', 'allow_insert')" 
                        :message="$i18n.getHelperMessage('categories', 'allow_insert')"/>
                    <div class="block" >
                        <b-checkbox
                                v-model="form.allowInsert"
                                true-value="yes"
                                false-value="no">
                            {{ labelNewTerms() }}
                        </b-checkbox>
                    </div>
                </b-field>

                <!-- Terms List -->
                <b-field
                        :addons="false"
                        :label="$i18n.get('label_category_terms')">
                    <button
                            class="button is-secondary is-small"
                            type="button"
                            @click="addNewTerm()">
                        {{ $i18n.get('label_new_term') }}
                    </button>    
                    <!-- Term item -->
                    <div    
                            class="term-item"
                            :class="{
                                'not-sortable-item': term.term_id == 'new' || term.term_id == undefined || openedTermId != '' , 
                                'not-focusable-item': openedTermId == term.term_id
                            }" 
                            v-for="(term, index) in termsList" 
                            :key="index">
                        <span 
                                class="term-name" 
                                :class="{'is-danger': formWithErrors == term.term_id }">
                            {{ term.name }}
                        </span>
                        <span   
                                v-if="term.term_id != undefined"
                                class="label-details">
                            <span 
                                    class="not-saved" 
                                    v-if="(editForms[term.term_id] != undefined && editForms[term.term_id].saved != true) || term.term_id == 'new'"> 
                                {{ $i18n.get('info_not_saved') }}
                            </span>
                        </span>
                        <span 
                                class="loading-spinner" 
                                v-if="term.term_id == undefined"/>
                        <span 
                                class="controls" 
                                v-if="term.term_id !== undefined || term.term_id !== 'new'">
    
                            <a @click.prevent="editTerm(term)">
                                <b-icon 
                                        type="is-gray" 
                                        icon="pencil"/>
                            </a>
                            <a @click.prevent="removeTerm(term)">
                                <b-icon 
                                        type="is-gray" 
                                        icon="delete"/>
                            </a>
                        </span>
                        <div v-if="openedTermId == term.term_id">
                            <term-edition-form   
                                    :category-id="categoryId"
                                    @onEditionFinished="onTermEditionFinished()"
                                    @onEditionCanceled="onTermEditionCanceled()"
                                    @onErrorFound="formWithErrors = term.term_id"
                                    :index="index"
                                    :original-term="term"
                                    :edited-term="editForms[term.term_id]"/>

                        </div>
                    </div>

                </b-field>

                <!-- Submit -->
                <div class="field is-grouped form-submit">
                    <div class="control">
                        <button
                                id="button-cancel-category-creation"
                                class="button is-outlined"
                                type="button"
                                @click="cancelBack">{{ $i18n.get('cancel') }}</button>
                    </div>
                    <div class="control">
                        <button
                                id="button-submit-category-creation"
                                @click.prevent="onSubmit"
                                class="button is-success">{{ $i18n.get('save') }}</button>
                    </div>
                </div>
                <p class="help is-danger">{{ formErrorMessage }}</p>
            </form>

            <b-loading 
                    :active.sync="isLoading" 
                    :can-cancel="false"/>
        </div>
    </div>
</template>

<script>
    import { wpAjax } from "../../js/mixins";
    import { mapActions, mapGetters } from 'vuex';
    import TermEditionForm from './term-edition-form.vue'
    import htmlToJSON from 'html-to-json';

    export default {
        name: 'CategoryEditionForm',
        mixins: [ wpAjax ],
        data(){
            return {
                categoryId: Number,
                category: null,
                isLoadingCategory: false,
                isUpdatingSlug: false,
                form: {
                    name: String,
                    status: String,
                    description: String,
                    slug: String,
                    allowInsert: String
                },
                statusOptions: [{
                    value: 'publish',
                    label: this.$i18n.get('publish')
                }, {
                    value: 'draft',
                    label: this.$i18n.get('draft')
                }, {
                    value: 'private',
                    label: this.$i18n.get('private')
                }, {
                    value: 'trash',
                    label: this.$i18n.get('trash')
                }],
                editFormErrors: {},
                formErrorMessage: '',
                // Terms related
                isLoadingTerms: false,
                formWithErrors: '',
                openedTermId: '',
                editForms: [],
                orderedTermsList: []
            }
        },
        computed: {
            termsList() {
                //this.orderedTermsList = new Array();
                //this.buildOrderedTermsList(0, 0); 
                return this.getTerms();
            }
        },
        components: {
            TermEditionForm
        },
        methods: {
            ...mapActions('category', [
                'createCategory',
                'updateCategory',
                'fetchCategory',
                'fetchOnlySlug',
                'fetchTerms',
                'updateTerm',
                'deleteTerm'
            ]),
            ...mapGetters('category',[
                'getCategory',
                'getTerms'
            ]),
            onSubmit() {

                this.isLoadingCategory = true;

                let data = {
                    categoryId: this.categoryId,
                    name: this.form.name,
                    description: this.form.description,
                    slug: this.form.slug,
                    status: this.form.status,
                    allowInsert: this.form.allowInsert
                };

                this.updateCategory(data)
                    .then(updatedCategory => {

                        this.category = updatedCategory;

                        // Fill this.form data with current data.
                        this.form.name = this.category.name;
                        this.form.slug = this.category.slug;
                        this.form.description = this.category.description;
                        this.form.status = this.category.status;
                        this.allowInsert = this.category.allow_insert;

                        this.isLoadingCategory = false;
                        this.formErrorMessage = '';
                        this.editFormErrors = {};

                        this.$router.push(this.$routerHelper.getCategoryPath(this.categoryId));
                    })
                    .catch((errors) => {
                        for (let error of errors.errors) {
                            for (let attribute of Object.keys(error)) {
                                this.editFormErrors[attribute] = error[attribute];
                            }
                        }
                        this.formErrorMessage = errors.error_message;

                        this.isLoadingCategory = false;
                    });
            },
            updateSlug(){
                if(!this.form.name || this.form.name.length <= 0){
                    return;
                }

                this.isUpdatingSlug = true;

                this.getSamplePermalink(this.categoryId, this.form.name, this.form.slug)
                    .then(samplePermalink => {

                        let promise = htmlToJSON.parse(samplePermalink, {
                            permalink($doc) {
                                return $doc.find('#editable-post-name-full').text();
                            }
                        });

                        promise.done((result) => {
                            this.form.slug = result.permalink;
                            this.$console.info(this.form.slug);
                        });

                        this.isUpdatingSlug = false;
                        this.formErrorMessage = '';
                        this.editFormErrors = {};
                    })
                    .catch(errors => {
                        this.$console.error(errors);

                        this.isUpdatingSlug = false;
                    });

            },
            getStatusColor(status) {
                switch(status) {
                    case 'publish':
                        return 'success';
                    case 'draft':
                        return 'info';
                    case 'private':
                        return 'warning';
                    case 'trash':
                        return 'danger';
                    default:
                        return 'info';
                }
            },
            createNewCategory() {
                // Puts loading on Draft Category creation
                this.isLoadingCategory = true;

                // Creates draft Category
                let data = {
                    name: '',
                    description: '',
                    status: 'auto-draft',
                    slug: '',
                    allowInsert: '',
                };

                this.createCategory(data)
                    .then(res => {

                        this.categoryId = res.id;
                        this.category = res;

                        // Fill this.form data with current data.
                        this.form.name = this.category.name;
                        this.form.description = this.category.description;
                        this.form.slug = this.category.slug;
                        this.form.allowInsert = this.category.allow_insert;

                        // Pre-fill status with publish to incentivate it
                        this.form.status = 'publish';

                        this.isLoadingCategory = false;

                    }
                )
                    .catch(error => this.$console.error(error));
            },
            clearErrors(attribute) {
                this.editFormErrors[attribute] = undefined;
            },
            cancelBack(){
                this.$router.push(this.$routerHelper.getCategoriesPath());
            },
            labelNewTerms(){
                return ( this.form.allowInsert === 'yes' ) ? this.$i18n.get('label_yes') : this.$i18n.get('label_no');
            },
            addNewTerm() {
                let newTerm = {
                    categoryId: this.categoryId,
                    name: '',
                    description: '',
                    parent: 0,
                    term_id: 'new'
                }
                this.termsList.push(newTerm);
                this.editTerm(newTerm);
            },
            editTerm(term) {

                // Closing collapse
                if (this.openedTermId == term.term_id) {    
                    this.openedTermId = '';

                // Opening collapse
                } else {

                    this.openedTermId = term.term_id;
                    // First time opening
                    if (this.editForms[this.openedTermId] == undefined) {
                        this.editForms[this.openedTermId] = JSON.parse(JSON.stringify(term));
                        this.editForms[this.openedTermId].saved = true;
                        
                        if (term.term_id == 'new')
                            this.editForms[this.openedTermId].saved = false;
                    }     
                }
            },
            removeTerm(term) {
                this.$console.log(term);
                
                this.deleteTerm({categoryId: this.categoryId, termId: term.term_id})
                .then(() => {

                })
                .catch((error) => {
                    this.$console.log(error);
                });
            },
            onTermEditionFinished() {
                this.formWithErrors = '';
                delete this.editForms[this.openedTermId];
                this.openedTermId = '';
            },
            onTermEditionCanceled() {
                this.formWithErrors = '';
                delete this.editForms[this.openedTermId];
                this.openedTermId = '';
            },
            buildOrderedTermsList(parentId, termDepth) {
    
                for (let term of this.termsList) {

                    if (term['parent'] != parentId ) {    
                        continue;
                    } 
                    
                    term.depth = termDepth;
                    this.orderedTermsList.push(term);

                    this.buildOrderedTermsList(term.term_id, termDepth + 1);
                }
            },
        },
        created(){

            if (this.$route.fullPath.split("/").pop() === "new") {
                this.createNewCategory();
            } else if (this.$route.fullPath.split("/").pop() === "edit") {

                this.isLoadingCategory = true;
                this.isLoadingTerms = true;

                // Obtains current category ID from URL
                this.pathArray = this.$route.fullPath.split("/").reverse();
                this.categoryId = this.pathArray[1];

                this.fetchCategory(this.categoryId).then(res => {
                    this.category = res.category;

                    // Fill this.form data with current data.
                    this.form.name = this.category.name;
                    this.form.description = this.category.description;
                    this.form.slug = this.category.slug;
                    this.form.status = this.category.status;
                    this.form.allowInsert = this.category.allow_insert;

                    this.isLoadingCategory = false;
                });

                this.fetchTerms(this.categoryId)
                .then(() => {
                    // Fill this.form data with current data.
                    this.isLoadingCategory = false;
                })
                .catch((error) => {
                    this.$console.log(error);
                });
            }
        },
        beforeRouteLeave ( to, from, next ) {
            let hasUnsavedForms = false;
            for (let editForm in this.editForms) {
                if (!this.editForms[editForm].saved) 
                    hasUnsavedForms = true;
            }
            if ((this.openedTermId != '' && this.openedTermId != undefined) || hasUnsavedForms ) {
                this.$dialog.confirm({
                    message: this.$i18n.get('info_warning_terms_not_saved'),
                        onConfirm: () => {
                            this.onEditionCanceled();
                            next();
                        },
                        cancelText: this.$i18n.get('cancel'),
                        confirmText: this.$i18n.get('continue'),
                        type: 'is-secondary'
                    });  
            } else {
                next()
            }  
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .thumbnail-field {
        width: 128px;
        height: 128px;
        max-width: 128px;
        max-height: 128px;

        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            bottom: 0;
            position: absolute;
        }

        .thumbnail-buttons-row {
            display: none;
        }
        &:hover {
            .thumbnail-buttons-row {
                display: inline-block;
                position: relative;
                bottom: 31px;
                background-color: rgba(255,255,255,0.8);
                padding: 2px 8px;
                border-radius: 0px 4px 0px 0px;
            }
        }
    }

    .loading-spinner {
        animation: spinAround 500ms infinite linear;
        border: 2px solid #dbdbdb;
        border-radius: 290486px;
        border-right-color: transparent;
        border-top-color: transparent;
        content: "";
        display: inline-block;
        height: 1em; 
        width: 1em;
    }

    .term-item {
        background-color: white;
        padding: 0.7em 0.9em;
        margin: 4px;
        min-height: 40px;
        display: block; 
        position: relative;
        cursor: grab;
        
        .handle {
            padding-right: 6em;
        }
        .grip-icon { 
            fill: $gray; 
            top: 2px;
            position: relative;
        }
        .term-name {
            text-overflow: ellipsis;
            overflow-x: hidden;
            white-space: nowrap;
            font-weight: bold;
            margin-left: 0.4em;
            margin-right: 0.4em;

            &.is-danger {
                color: $danger !important;
            }
        }
        .label-details {
            font-weight: normal;
            color: $gray;
        }
        .not-saved {
            font-style: italic;
            font-weight: bold;
            color: $danger;
        }
        .controls { 
            position: absolute;
            right: 5px;
            top: 10px;

            .icon {
                bottom: 1px;   
                position: relative;
                i, i:before { font-size: 20px; }
            }
        }

        &.not-sortable-item, &.not-sortable-item:hover {
            cursor: default;
            background-color: white !important;

            .handle .label-details, .handle .icon {
                color: $gray !important;
            }
        } 
        &.not-focusable-item, &.not-focusable-item:hover {
            cursor: default;
            
            .term-name {
                color: $primary;
            }
            .handle .label-details, .handle .icon {
                color: $gray !important;
            }
        }
  
    }
    .term-item:hover:not(.not-sortable-item) {
        background-color: $secondary;
        border-color: $secondary;
        color: white !important;

        .label-details, .icon, .not-saved {
            color: white !important;
        }

        .grip-icon { 
            fill: white; 
        }
    }

</style>


