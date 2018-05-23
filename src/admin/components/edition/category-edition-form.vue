<template>
    <div>
        <div class="page-container primary-page">
            <tainacan-title />
            <b-tabs v-model="activeTab">    
                <b-tab-item :label="$i18n.get('taxonomy')">
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
                                    :class="{'has-content': form.name != undefined && form.name != ''}"
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
                                    :class="{'has-content': form.description != undefined && form.description != ''}"
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
                                    :class="{'has-content': form.slug != undefined && form.slug != ''}"
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
                </b-tab-item>
                
                <b-tab-item :label="$i18n.get('terms')">
                    <!-- Terms List -->    
                    <terms-list :category-id="categoryId"/>       
                </b-tab-item>

                <b-loading 
                        :active.sync="isLoadingCategory" 
                        :can-cancel="false"/>
            </b-tabs>
        </div>
    </div>
</template>

<script>
    import { wpAjax } from "../../js/mixins";
    import { mapActions, mapGetters } from 'vuex';
    import TermsList from '../lists/terms-list.vue'
    import htmlToJSON from 'html-to-json';

    export default {
        name: 'CategoryEditionForm',
        mixins: [ wpAjax ],
        data(){
            return {
                categoryId: String,
                activeTab: 0,
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
                // baseUrl: tainacan_plugin.base_url,
            }
        },
        components: {
            TermsList
        },
        beforeRouteLeave( to, from, next ) {
            let formNotSaved = false;

            if (this.category.name != this.form.name)
                formNotSaved = true;
            if (this.category.description != this.form.description)
                formNotSaved = true;
            if (this.category.slug != this.form.slug)
                formNotSaved = true;
            if (this.category.allow_insert != this.form.allowInsert)
                formNotSaved = true;
            if (this.category.status != this.form.status)
                formNotSaved = true;

            if (formNotSaved) {
                this.$dialog.confirm({
                    message: this.$i18n.get('info_warning_category_not_saved'),
                        onConfirm: () => {
                            next();
                        },
                        cancelText: this.$i18n.get('cancel'),
                        confirmText: this.$i18n.get('continue'),
                        type: 'is-secondary'
                    });  
            } else {
                next()
            }  
        },
        methods: {
            ...mapActions('category', [
                'createCategory',
                'updateCategory',
                'fetchCategory',
                'fetchOnlySlug'
            ]),
            ...mapGetters('category',[
                'getCategory',
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
                        this.form.allowInsert = this.category.allow_insert;

                        this.isLoadingCategory = false;
                        this.formErrorMessage = '';
                        this.editFormErrors = {};

                        this.$router.push(this.$routerHelper.getCategoriesPath());
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
                            //this.$console.info(this.form.slug);
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

                    })
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
            }
        },
        created(){

            if (this.$route.fullPath.split("/").pop() === "new") {
                this.createNewCategory();
            } else if (this.$route.fullPath.split("/").pop() === "edit" || this.$route.fullPath.split("/").pop() === "terms") {

                this.isLoadingCategory = true;

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

                if (this.$route.fullPath.split("/").pop() === "terms") 
                    this.activeTab = 1;
            }
        }
    }
</script>

<style lang="scss" scoped>

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

</style>


