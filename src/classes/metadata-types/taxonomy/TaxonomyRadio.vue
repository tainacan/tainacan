<template>
    <div>
        <p 
                v-if="value instanceof Array ? value.length > 0 : (value != undefined && value != '')"
                class="has-text-gray">
            {{ $i18n.get('label_selected_terms') + ' :' }}
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
                        @close="value = ''">
                    {{ selectedTagsName[value] }}
                </b-tag>
            </div>
            <div 
                    v-if="isSelectedTermsLoading" 
                    class="control has-icons-right is-loading is-clearfix" />
        </b-field>
        <b-radio
                :disabled="disabled"
                :id="metadatum.metadata_type_object.component + '-' + metadatum.slug"
                v-model="checked"
                @input="onChecked()"
                :native-value="''"
                border>
            {{ $i18n.get('clear_radio') }}
        </b-radio>
        <div
                :id="metadatum.metadata_type_object.component + '-' + metadatum.slug"
                v-for="(option, index) in options"
                :key="index">
            <b-radio
                    :disabled="disabled"
                    :style="{ paddingLeft: (option.level * 30) + 'px' }"
                    :key="index"
                    v-model="checked"
                    @input="onChecked(option)"
                    :native-value="option.id"
                    border>
                {{ option.name }}
            </b-radio>
            <br>
        </div>
        <a
                class="view-all"
                v-if="terms.length < totalTerms"
                @click="openCheckboxModal()">
            {{ $i18n.get('label_view_all') }}
        </a>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import CheckboxRadioModal from '../../../admin/components/other/checkbox-radio-modal.vue'

    export default {
        created() {
            this.getTermsFromTaxonomy();
            this.$parent.$on('update-taxonomy-inputs', ($event) => {
                if ($event.taxonomyId == this.taxonomyId && $event.metadatumId == this.metadatum.metadatum.id) {
                    this.terms = [];
                    this.offset = 0;
                    this.getTermsFromTaxonomy();
                }
            });
        },
        data() {
            return {
                checked: this.value ? this.value : '',
                selectedTagsName: {},
                isSelectedTermsLoading: false,
                options: [],
                terms: [],
                totalTerms: 0,
            }
        },
        watch: {
            value( val ){
                this.checked = val;
                this.fetchSelectedLabels();
            }
        },
        props: {
            value: [ Number, String, Array ],
            disabled: false,
            taxonomyId: Number
        },
        methods: {
            onChecked() {
                this.onInput(this.checked)
            },
            onInput($event) {
                this.value = $event;
                this.$emit('input', this.value);
            },
            fetchSelectedLabels() {

                if (this.value != null && this.value != undefined) {

                    this.isSelectedTermsLoading = true;
                    let selected = this.value instanceof Array ? this.value : [this.value];

                    if (this.taxonomyId && selected.length > 0) {
                        for (const term of selected) {

                            if (!this.isSelectedTermsLoading)
                                this.isSelectedTermsLoading = true;
                            
                            axios.get(`/taxonomy/${this.taxonomyId}/terms/${term}`)
                                .then((res) => {
                                    this.saveSelectedTagName(res.data.id, res.data.name);
                                    this.isSelectedTermsLoading = false;
                                })
                                .catch((error) => {
                                    this.$console.log(error);
                                    this.isSelectedTermsLoading = false;
                                });
                        }
                    } else {
                        this.isSelectedTermsLoading = false;
                    }
                }
            },
            saveSelectedTagName(value, label) {
                if(!this.selectedTagsName[value]) {
                    this.$set(this.selectedTagsName, `${value}`, label);
                }
            },
            getTermsFromTaxonomy() {
                let endpoint = '/taxonomy/' + this.taxonomyId + '/terms?hideempty=0&order=asc&number=' + this.termsNumber + '&offset=' + this.offset; 

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
                        metadatumId: this.metadatum.metadatum.id,
                        taxonomy: this.taxonomy,
                        collectionId: this.metadatum.collection_id,
                        isTaxonomy: true,
                        query: '',
                        metadatum: this.metadatum.metadatum,
                        isCheckbox: false
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
        },
        mounted() {
            this.fetchSelectedLabels();
        }
    }
</script>

<style scoped>
    .view-all {
        margin-top: 15px;
        margin-bottom: 30px;
        font-size: 0.75rem;
    }
</style>