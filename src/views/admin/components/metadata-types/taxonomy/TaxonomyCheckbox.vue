<template>
    <div>
        <p 
                v-if="value instanceof Array ? value.length > 0 : (value != undefined && value != '')"
                class="has-text-gray">
            {{ $i18n.get('label_selected_terms') + ':' }}
        </p>
        <b-field
                v-if="value instanceof Array ? value.length > 0 : (value != undefined && value != '')"
                grouped
                group-multiline
                class="selected-tags">
            <div
                    v-for="(term, index) in (value instanceof Array ? value : [value])"
                    :key="index">
                <b-tag
                        attached
                        closable
                        @close="value.splice(index, 1)">
                    {{ selectedTagsName[term] }}
                </b-tag>
            </div>
            <div 
                    v-if="isSelectedTermsLoading" 
                    class="control has-icons-right is-loading is-clearfix" />
        </b-field>
        <p 
                style="margin-top: 10px;"
                class="has-text-gray">
            {{ (isShowingAllTerms ? $i18n.get('label_available_terms') : $i18n.get('label_some_available_terms')) + ':' }}
        </p>
        <div 
                class="metadata-taxonomy-list"
                :id="metadatum.metadata_type_object.component + '-' + metadatum.slug">
            <template v-for="(option, index) in options">
                <b-checkbox
                        :key="index"
                        :disabled="disabled"
                        :style="{ paddingLeft: (option.level * 30) + 'px' }"
                        v-model="checked"
                        @input="onChecked(option)"
                        :native-value="option.id"
                        border>
                    {{ option.name }}
                </b-checkbox>
                <br :key="index">
            </template>
        </div>
        <div 
                v-if="!isShowingAllTerms"
                class="view-all">
            <span>
                {{
                    $i18n.get('info_showing_terms') + 1 +
                    $i18n.get('info_to') + options.length +
                    $i18n.get('info_of') + totalTerms + '. '
                }}
            </span>
            <a @click="openCheckboxModal()">
                {{ $i18n.get('label_view_all') + ' ' + totalTerms + '.' }}
            </a>
        </div>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios';
    import qs from 'qs';
    import CheckboxRadioModal from '../../modals/checkbox-radio-modal.vue';

    export default {
        
        props: {
            value: [ Number, String, Array ],
            disabled: false,
            taxonomyId: Number,
            metadatum: Object
        },
        data() {
            return {
                checked: [],
                selectedTagsName: {},
                isSelectedTermsLoading: false,
                options: [],
                terms: [],
                termsNumber: 12,
                offset: 0,
                totalTerms: 0
            }
        },
        computed: {
            isShowingAllTerms() {
                return this.terms.length >= this.totalTerms;
            }
        },
        watch: {
            value(val){
                this.checked = val;
                this.fetchSelectedLabels();
            }
        },
        created() {
            if (this.value && this.value.length > 0)
                this.checked = this.value;

            this.getTermsFromTaxonomy();
            this.$parent.$on('update-taxonomy-inputs', ($event) => {
                if ($event.taxonomyId == this.taxonomyId && $event.metadatumId == this.metadatum.id) {
                    this.offset = 0;
                    this.getTermsFromTaxonomy();
                }
            });
        },
        mounted() {
            this.fetchSelectedLabels();
        },
        methods: {
            onChecked() {
                this.onInput(this.checked);
            },
            onInput($event) {
                this.value = $event;
                this.$emit('input', this.value);
            },
            fetchSelectedLabels() {

                if (this.value != null && this.value != undefined) {

                    const selected = this.value instanceof Array ? this.value : [this.value];

                    if (this.taxonomyId) {
                        this.isSelectedTermsLoading = true;

                        axios.get(`/taxonomy/${this.taxonomyId}/terms/?${qs.stringify({ hideempty: 0, include: selected })}`)
                            .then((res) => {
                                let terms = res.data;

                                for (let term of terms) {
                                    if (!this.selectedTagsName[term.id])
                                        this.$set(this.selectedTagsName, term.id, term.name);
                                }

                                this.isSelectedTermsLoading = false;
                            })
                            .catch((error) => {
                                this.$console.log(error);
                                this.isSelectedTermsLoading = false;
                            });
                    }
                }
            },
            getTermsFromTaxonomy() {
                this.terms = [];

                const endpoint = '/taxonomy/' + this.taxonomyId + '/terms?hideempty=0&order=asc&number=' + this.termsNumber + '&offset=' + this.offset; 

                axios.get(endpoint)
                    .then( res => {
                        this.totalTerms = Number(res.headers['x-wp-total']);
                        this.offset += this.termsNumber;
                        
                        for (let item of res.data)
                            this.terms.push( item );

                        this.options = this.getOptions(0);
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            },
            getOptions(parent, level = 0) { // retrieve only ids
                let result = [];
                if (this.terms) {
                    for (let term of this.terms){
                        if (term.parent == parent){
                            term['level'] = level;
                            result.push(term);
                            const levelTerm = level + 1;
                            const children = this.getOptions( term.id, levelTerm);
                            result = result.concat(children);
                        }
                    }
                }
                return result;
            },
            openCheckboxModal() {
                this.$buefy.modal.open({
                    parent: this,
                    component: CheckboxRadioModal,
                    props: {
                        isFilter: false,
                        parent: 0,
                        taxonomy_id: this.taxonomyId,
                        selected: !this.value ? [] : this.value,
                        metadatumId: this.metadatum.id,
                        taxonomy: this.taxonomy,
                        collectionId: this.metadatum.collection_id,
                        isTaxonomy: true,
                        query: '',
                        metadatum: this.metadatum,
                        isCheckbox: true
                    },
                    events: {
                        input: (selected) => {
                            this.value = selected;
                            this.$emit('input', this.value);
                        }
                    },
                    width: 'calc(100% - 8.333333333%)',
                    trapFocus: true
                });
            }
        }
    }
</script>

<style scoped lang="scss">
    .selected-tags {
        margin-top: 0.75rem;
        font-size: 0.75rem;
        position: relative;
    }
    .selected-tags .is-loading {
        margin-left: 2rem;
        margin-top: -0.4rem;
    }
    .selected-tags .is-loading::after {
        border: 2px solid #555758 !important;
        border-right-color: #dbdbdb !important;
        border-top-color: #dbdbdb !important;
    } 
    .metadata-taxonomy-list {
        column-count: 2;
        margin: 10px;

        label {
            break-inside: avoid;
            padding-right: 10px;
        }
    }
    .view-all {
        color: #898d8f;
        margin-bottom: 20px;
        font-size: 0.75rem;
    }
</style>
