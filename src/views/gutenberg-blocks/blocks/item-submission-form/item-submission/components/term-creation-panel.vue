<template>
    <form
            id="termEditForm"
            autofocus
            role="dialog"
            tabindex="-1"
            aria-modal
            class="tainacan-form term-creation-panel"
            @submit.prevent="saveEdition(form)">

        <h4>{{ form & form.id && form.id != 'new' ? $i18n.get("title_term_edit") : $i18n.get("title_term_creation") }}</h4>
    
        <div>
            <b-loading
                    v-model="isLoading"
                    :is-full-page="false" />

            <!-- Name -------------- -->
            <b-field
                    :addons="false"
                    :type="((formErrors.name !== '' || formErrors.repeated !== '') && (formErrors.name !== undefined || formErrors.repeated !== undefined )) ? 'is-danger' : ''"
                    :message="formErrors.name ? formErrors.name : formErrors.repeated">
                <label class="label is-inline">
                    {{ $i18n.get('label_name') }}
                    <span class="required-term-asterisk">*</span>
                    <help-button
                            :title="$i18n.get('label_name')"
                            :message="$i18n.get('info_help_term_name')" /> 
                </label>
                <b-input
                        v-model="form.name"
                        :placeholder="$i18n.get('label_term_without_name')"
                        name="name"
                        @focus="clearErrors({ name: 'name', repeated: 'repeated' })" />
            </b-field>

            <!-- Parent -------------- -->
            <b-field
                    v-if="isHierarchical"
                    :addons="false"
                    :type="((formErrors.parent !== '' || formErrors.repeated !== '') && (formErrors.parent !== undefined || formErrors.repeated !== undefined )) ? 'is-danger' : ''"
                    :message="formErrors.parent ? formErrors : formErrors.repeated">
                <label class="label is-inline">
                    {{ $i18n.get('label_parent_term') }}
                    <b-switch
                            id="tainacan-checkbox-has-parent"
                            v-model="hasParent" 
                            size="is-small"
                            @update:model-value="onToggleSwitch()" />
                    <help-button
                            :title="$i18n.get('label_parent_term')"
                            :message="$i18n.get('info_help_parent_term')" />
                </label>
                <b-autocomplete
                        id="tainacan-add-parent-field"
                        v-model="parentTermName"
                        v-a11y-autocomplete
                        :placeholder="$i18n.get('instruction_parent_term')"
                        :data="parentTerms"
                        field="name"
                        clearable
                        icon-right="menu-down"
                        :loading="isFetchingParentTerms"
                        :disabled="!hasParent"
                        open-on-focus
                        check-infinite-scroll
                        @select="onSelectParentTerm"
                        @focus="onFocusParentTerm"
                        @active="onParentTermSuggestionsActive"
                        @typing="fetchParentTerms"
                        @infinite-scroll="fetchMoreParentTerms">
                    <template #default="props">
                        <div class="media">
                            <div 
                                    v-if="props.option.header_image"
                                    class="media-left">
                                <img 
                                        width="28"
                                        :src="props.option.thumbnail && props.option.thumbnail['tainacan-small'] && props.option.thumbnail['tainacan-small'][0] ? props.option.thumbnail['tainacan-small'][0] : props.option.header_image">
                            </div>
                            <div class="media-content">
                                {{ props.option.name }}
                            </div>
                        </div>
                    </template>
                    <template #empty>
                        {{ $i18n.get('info_no_parent_term_found') }}
                    </template>
                </b-autocomplete>
            </b-field>

            <!-- Submit buttons -------------- -->
            <div 
                    class="wp-block-buttons form-submit"
                    style="gap: 1rem;">
                <div
                        class="wp-block-button is-style-outline"
                        style="margin-inline-end: auto;">
                    <button
                            type="button"
                            class="wp-block-button__link wp-element-button"
                            @click.prevent="cancelEdition()">
                        {{ $i18n.get('cancel') }}
                    </button>
                </div>
                <div class="wp-block-button">
                    <button
                            class="wp-block-button__link wp-element-button"
                            type="submit">
                        {{ $i18n.get('label_create_and_select') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
    import { formHooks } from "../../../../../admin/js/mixins";
    import { mapActions } from 'vuex';

    export default {
        name: 'TermCreationPanel',
        mixins: [ formHooks ],
        props: {
            originalForm: Object,
            taxonomyId: '',
            isHierarchical: Boolean
        },
        emits: [
            'on-edition-finished',
            'on-edition-canceled'
        ],
        data() {
            return {
                formErrors: {},
                isFetchingParentTerms: false,
                parentTerms: [],
                parentTermName: '',
                committedParentTermName: '',
                hasParent: false,
                hasChangedParent: false,
                initialParentId: undefined,
                entityName: 'term',
                isLoading: false,
                parentTermSearchQuery: '',
                parentTermSearchOffset: 0,
                form: {}
            }
        },
        watch: {
            parentTermName(name) {
                if (!name)
                    this.onSelectParentTerm(null);
            }
        },
        created() {
            this.form = JSON.parse(JSON.stringify(this.originalForm));
        },
        mounted() {
            this.hasParent = this.form.parent != undefined && this.form.parent > 0;
            this.initialParentId = this.form.parent;

            if (this.hasParent) {
                this.isFetchingParentTerms = true;
                this.fetchParentName({ taxonomyId: this.taxonomyId, parentId: this.form.parent })
                    .then((parentName) => {
                        this.parentTermName = parentName;
                        this.committedParentTermName = parentName;
                        this.isFetchingParentTerms = false;
                        this.showCheckboxesWarning = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingParentTerms = false;
                        this.showCheckboxesWarning = false;
                    });
            }
        },
        methods: {
            ...mapActions('taxonomy', [
                'sendChildTerm',
                'updateTerm',
                'fetchParentName',
                'fetchPossibleParentTerms'
            ]),
            saveEdition(term) {

                if (term.id === 'new') {
                    this.$emit('on-edition-finished', { name: this.form.name, parent: this.form.parent });
                    this.form = {};
                    this.formErrors = {};
                    this.isLoading = false;
                }
            },
            cancelEdition() {
                this.$emit('on-edition-canceled', this.form);
            },
            clearErrors(attributes) {
                if (attributes instanceof Object) {
                    for (let attribute in attributes)
                        this.formErrors[attribute] = undefined;
                } else {
                    this.formErrors[attributes] = undefined;
                }
            },
            onFocusParentTerm() {
                this.clearErrors('parent');
                this.browseParentTerms();
            },
            onParentTermSuggestionsActive(isOpen) {
                if (isOpen)
                    return;

                if (this.form.parent && this.committedParentTermName && this.parentTermName !== this.committedParentTermName)
                    this.parentTermName = this.committedParentTermName;
            },
            browseParentTerms() {
                this.parentTermSearchQuery = '';
                this.parentTermSearchOffset = 0;
                this.totalTerms = undefined;
                this.parentTerms = [];
                this.isFetchingParentTerms = true;
                this.requestParentTerms('');
            },
            fetchParentTerms: _.debounce(function(search) {
                const query = search || '';

                if (this.committedParentTermName && query === this.committedParentTermName)
                    return;

                if (query !== this.parentTermSearchQuery) {
                    this.parentTermSearchQuery = query;
                    this.parentTerms = [];
                    this.parentTermSearchOffset = 0;
                    this.totalTerms = undefined;
                }

                if (this.parentTermSearchOffset > 0 && this.totalTerms !== undefined && this.parentTerms.length >= this.totalTerms)
                    return;

                this.isFetchingParentTerms = true;
                this.requestParentTerms(query);
            }, 500),
            requestParentTerms(query) {
                this.fetchPossibleParentTerms({
                        taxonomyId: this.taxonomyId,
                        termId: this.form.id,
                        search: query,
                        offset: this.parentTermSearchOffset })
                    .then((res) => {
                        const terms = res.parentTerms ? res.parentTerms : [];
                        if (this.parentTermSearchOffset === 0)
                            this.parentTerms = terms;
                        else {
                            for (let term of terms)
                                this.parentTerms.push(term);
                        }

                        this.parentTermSearchOffset += 12;
                        this.totalTerms = res.totalTerms;
                        this.isFetchingParentTerms = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingParentTerms = false;
                    });
            },
            fetchMoreParentTerms: _.debounce(function () {
                this.fetchParentTerms(this.parentTermSearchQuery)
            }, 250),
            onToggleSwitch() {

                if (this.form.parent == 0)
                    this.hasChangedParent = this.hasParent;
                else
                    this.hasChangedParent = !this.hasParent;
                
                this.showCheckboxesWarning = true; 
                this.clearErrors('parent');
            },
            onSelectParentTerm(selectedParentTerm) {
                if (!selectedParentTerm) {
                    if (this.parentTermName || (!this.form.parent && !this.committedParentTermName))
                        return;

                    this.form.parent = 0;
                    this.selectedParentTerm = undefined;
                    this.committedParentTermName = '';
                    this.hasChangedParent = this.initialParentId != 0;
                    this.showCheckboxesWarning = true;
                    return;
                }

                this.hasChangedParent = this.initialParentId != selectedParentTerm.id;
                this.form.parent = selectedParentTerm.id;
                this.selectedParentTerm = selectedParentTerm;
                this.committedParentTermName = selectedParentTerm.name;
                this.parentTermName = selectedParentTerm.name;
                this.showCheckboxesWarning = true;
            }
        }
    }
</script>

<style lang="scss" scoped>
.term-creation-panel {
    padding-top: 6px;

    h4 {
        font-size: 0.875em !important;
    }
    &>div {
        padding: 0 16px 16px;
        border-inline-start: 1px solid var(--tainacan-input-border-color, #dbdbdb);
        border-bottom: 1px solid var(--tainacan-input-border-color, #dbdbdb);
        column-count: 2;
    
        @media screen and (max-width: 1024px) {
            column-count: 1;
        }

        .field {
            break-inside: avoid;
            padding-inline-start: 0;
            margin-inline-start: 0;
        }
        .form-submit {
            padding-top: 1rem;
            padding-bottom: 0;
            column-span: all;
            display: flex;
        }
    }
}
</style>


