<template>
    <div>
        <div class="page-container primary-page">
            <b-tag v-if="category != null && category != undefined" :type="'is-' + getStatusColor(category.status)" v-text="category.status"></b-tag>
            <form v-if="category != null && category != undefined" class="tainacan-form" label-width="120px">

                <!-- Name -------------------------------- -->
                <b-field
                        :addons="false"
                        :label="$i18n.get('label_name')"
                        :type="editFormErrors['name'] != undefined ? 'is-danger' : ''"
                        :message="editFormErrors['name'] != undefined ? editFormErrors['name'] : ''">
                    <help-button 
                            :title="$i18n.getHelperTitle('categories', 'name')" 
                            :message="$i18n.getHelperMessage('categories', 'name')">
                    </help-button>
                    <b-input
                            id="tainacan-text-name"
                            v-model="form.name"
                            @focus="clearErrors('name')"
                            @blur="updateSlug()">
                    </b-input>
                </b-field>

                <!-- Description -------------------------------- -->
                <b-field
                        :addons="false"
                        :label="$i18n.get('label_description')"
                        :type="editFormErrors['description'] != undefined ? 'is-danger' : ''"
                        :message="editFormErrors['description'] != undefined ? editFormErrors['description'] : ''">
                    <help-button 
                            :title="$i18n.getHelperTitle('categories', 'description')" 
                            :message="$i18n.getHelperMessage('categories', 'description')">
                    </help-button>
                    <b-input
                            id="tainacan-text-description"
                            type="textarea"
                            v-model="form.description"
                            @focus="clearErrors('description')">
                    </b-input>
                </b-field>

                <!-- Status -------------------------------- -->
                <b-field
                        :addons="false"
                        :label="$i18n.get('label_status')"
                        :type="editFormErrors['status'] != undefined ? 'is-danger' : ''"
                        :message="editFormErrors['status'] != undefined ? editFormErrors['status'] : ''">
                    <help-button 
                            :title="$i18n.getHelperTitle('categories', 'status')" 
                            :message="$i18n.getHelperMessage('categories', 'status')">
                    </help-button>
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
                            :message="$i18n.getHelperMessage('categories', 'slug')">
                    </help-button>
                    <b-input
                            id="tainacan-text-slug"
                            v-model="form.slug"
                            @focus="clearErrors('slug')"
                            :disabled="isUpdatingSlug">
                    </b-input>
                </b-field>

                <!-- Allow Insert -->
                <b-field 
                        :addons="false"
                        :label="$i18n.get('label_category_allow_new_terms')">
                    <help-button 
                        :title="$i18n.getHelperTitle('categories', 'allow_insert')" 
                        :message="$i18n.getHelperMessage('categories', 'allow_insert')">
                    </help-button>
                    <div class="block" >
                        <b-checkbox
                                v-model="form.allowInsert"
                                true-value="yes"
                                false-value="no">
                            {{ labelNewTerms() }}
                        </b-checkbox>
                    </div>
                </b-field>

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
                <p class="help is-danger">{{formErrorMessage}}</p>
            </form>

            <b-loading 
                    :active.sync="isLoading" 
                    :canCancel="false"></b-loading>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        name: 'CategoryEditionForm',
        data(){
            return {
                categoryId: Number,
                category: null,
                isLoading: false,
                isUpdatingSlug: false,
                form: {
                    name: String,
                    status: String,
                    description: String,
                    slug: String,
                    allowInsert: String,
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
            }
        },
        methods: {
            ...mapActions('category', [
                'createCategory',
                'updateCategory',
                'fetchCategory',
                'fetchOnlySlug',
            ]),
            ...mapGetters('category',[
                'getCategory'
            ]),
            onSubmit() {
                this.isLoading = true;

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

                        this.isLoading = false;
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

                        this.isLoading = false;
                    });
            },
            updateSlug(){
                if(!this.form.name || this.form.name.length <= 0){
                    return;
                }

                this.isUpdatingSlug = true;

                let data = {
                    categoryId: this.categoryId,
                    name: this.form.name,
                    description: this.form.description,
                    //slug: '',
                    status: 'private',
                    allowInsert: this.form.allowInsert
                };
                console.log(data);

                this.updateCategory(data)
                    .then(updatedCategory => {
                        this.category = updatedCategory;

                        console.info(this.category);

                        // Fill this.form data with current data.
                        this.form.name = this.category.name;
                        this.form.slug = this.category.slug;
                        this.form.description = this.category.description;
                        this.form.status = this.category.status;
                        this.allowInsert = this.category.allow_insert;

                        this.isUpdatingSlug = false;
                        this.formErrorMessage = '';
                        this.editFormErrors = {};
                    })
                    .catch(errors => {
                        for (let error of errors.errors) {
                            for (let attribute of Object.keys(error)) {
                                this.editFormErrors[attribute] = error[attribute];
                            }
                        }
                        this.formErrorMessage = errors.error_message;

                        this.isLoading = false;
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
                this.isLoading = true;

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

                        this.isLoading = false;

                    }
                )
                    .catch(error => console.log(error));
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
        },
        created(){

            if (this.$route.fullPath.split("/").pop() === "new") {
                this.createNewCategory();
            } else if (this.$route.fullPath.split("/").pop() === "edit") {

                this.isLoading = true;

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

                    this.isLoading = false;
                });
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


