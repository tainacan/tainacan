<template>
    <section
            v-if="isReady"
            :listen="setError">
        <b-field 
                :addons="false"
                :type="taxonomyType"
                :message="taxonomyMessage">
            <label class="label is-inline">
                {{ $i18n.get('label_select_taxonomy') }}<span :class="taxonomyType" >&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-taxonomy', 'taxonomy_id')"
                        :message="$i18n.getHelperMessage('tainacan-taxonomy', 'taxonomy_id')"/>
            </label>
            <b-select
                    name="field_type_options[taxonomy_id]"
                    placeholder="Select the taxonomy"
                    v-model="taxonomy_id"
                    @input="emitValues()"
                    @focus="clear"
                    :loading="loading"
                    expanded>
                <option value="">{{ $i18n.get('label_selectbox_init') }}...</option>
                <option
                        v-for="option in taxonomies"
                        :value="option.id"
                        :key="option.id">
                    {{ option.name }}
                </option>
            </b-select>
        </b-field>

        <b-field :addons="false">
            <label class="label">
                {{ $i18n.get('label_select_taxonomy_input_type') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-taxonomy', 'input_type')"
                        :message="$i18n.getHelperMessage('tainacan-taxonomy', 'input_type')"/>
            </label>
            <b-select
                    v-if="listInputType"
                    name="metadata_type_options[component_type]"
                    placeholder="Select the input type for the taxonomy metadatum"
                    @input="emitValues()"
                    v-model="input_type"
                    expanded>
                <option
                        v-for="(option, index) in single_types"
                        :value="index"
                        :key="index">
                    {{ option }}
                </option>
            </b-select>

            <b-select
                    name="metadata_type_options[input_type]"
                    placeholder="Select the input type for the taxonomy metadatum"
                    v-model="input_type"
                    @input="emitValues()"
                    v-else
                    expanded>
                <option
                        v-for="(option, index) in multiple_types"
                        :value="index"
                        :key="index">
                    {{ option }}
                </option>
            </b-select>

        </b-field>
        <b-field
                v-if="taxonomy_id && taxonomies.length && (input_type == 'tainacan-taxonomy-checkbox' || input_type == 'tainacan-taxonomy-radio')" 
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-taxonomy', 'visible_options_list')">
                &nbsp;
            <b-switch
                    size="is-small" 
                    v-model="visible_options_list"
                    @input="emitValues()" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-taxonomy', 'visible_options_list')"
                    :message="$i18n.getHelperMessage('tainacan-taxonomy', 'visible_options_list')"/>
        </b-field>
        <b-field
                v-if="taxonomy_id && taxonomies.length && isTermCreationAllowedOnCurrentTaxonomy" 
                :addons="false"
                :label="$i18n.get('label_taxonomy_allow_new_terms')">
                &nbsp;
            <b-switch
                    size="is-small" 
                    v-model="allow_new_terms"
                    @input="emitValues()"
                    true-value="yes"
                    false-value="no" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-taxonomy', 'allow_new_terms')"
                    :message="$i18n.getHelperMessage('tainacan-taxonomy', 'allow_new_terms')"/>
        </b-field>
        <b-field
                v-if="taxonomy_id && taxonomies.length" 
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-taxonomy', 'do_not_dispaly_term_as_link')">
                &nbsp;
            <b-switch
                    size="is-small" 
                    v-model="do_not_dispaly_term_as_link"
                    @input="emitValues()"
                    true-value="yes"
                    false-value="no" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-taxonomy', 'do_not_dispaly_term_as_link')"
                    :message="$i18n.getHelperMessage('tainacan-taxonomy', 'do_not_dispaly_term_as_link')"/>
        </b-field>
        <b-field :addons="false">
            <label class="label">
                {{ $i18n.getHelperTitle('tainacan-taxonomy', 'link_filtered_by_collections') }}
                <help-button
                    :title="$i18n.getHelperTitle('tainacan-taxonomy', 'link_filtered_by_collections')"
                    :message="$i18n.getHelperMessage('tainacan-taxonomy', 'link_filtered_by_collections')"/>
            </label>
            <b-taginput
                    :value="getSelectedTaxonomyCollections()"
                    autocomplete
                    :open-on-focus="true"
                    :data="collections.filter((collection) => !link_filtered_by_collections.includes(collection.id) && (collectionSearchString ? (collection.name.toLowerCase().indexOf(collectionSearchString.toLowerCase()) >= 0) : true) )"
                    field="name"
                    @input="updateSelectedCollections"
                    @focus="clear()"
                    attached
                    :disabled="do_not_dispaly_term_as_link == 'yes'"
                    :remove-on-keys="[]"
                    :aria-close-label="$i18n.get('remove_value')"
                    :class="{'has-selected': link_filtered_by_collections != undefined && link_filtered_by_collections != []}"
                    :placeholder="$i18n.get('instruction_select_one_or_more_collections')"
                    @typing="filterCollections"
                    :loading="loadingCollections">
                <template slot-scope="props">
                    <div class="media">
                        <div
                                v-if="props.option.thumbnail && props.option.thumbnail['tainacan-small'] && props.option.thumbnail['tainacan-small']"
                                class="media-left">
                            <img 
                                    width="24"
                                    :alt="$i18n.get('label_thumbnail')"
                                    :src="$thumbHelper.getSrc(props.option['thumbnail'], 'tainacan-small')" >
                        </div>
                        <div class="media-content">
                            {{ props.option.name }}
                        </div>
                    </div>
                </template>
                <template slot="empty">
                    {{ $i18n.get('info_no_options_found') }}
                </template>
            </b-taginput>
        </b-field>
        <b-field
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-taxonomy', 'hide_hierarchy_path')">
                &nbsp;
            <b-switch
                    size="is-small" 
                    v-model="hide_hierarchy_path"
                    @input="emitValues()"
                    true-value="yes"
                    false-value="no" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-taxonomy', 'hide_hierarchy_path')"
                    :message="$i18n.getHelperMessage('tainacan-taxonomy', 'hide_hierarchy_path')"/>
        </b-field>

    </section>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios';

    export default {
        props: {
            value: [ String, Object, Array ],
            metadatum: [ String, Object ],
            errors: [ String, Object, Array ]
        },
        data(){
            return {
                isReady: false,
                taxonomies: [],
                taxonomy_id: '',
                taxonomy: '',
                loading: false,
                allow_new_terms: 'yes',
                hide_hierarchy_path: 'no',
                do_not_dispaly_term_as_link: 'no',
                link_filtered_by_collections: [],
                visible_options_list: false, 
                input_type: 'tainacan-taxonomy-radio',
                multiple_types: {},
                single_types: {},
                taxonomyType:'',
                taxonomyMessage: '',
                collections: [],
                collectionSearchString: '',
                loadingCollections: false
            }
        },
        computed: {
            listInputType() {
                if ( this.metadatum && this.metadatum.multiple === 'no' ) {
                    let types = Object.keys( this.single_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    
                    if (hasValue)
                        this.setInputType(this.value.input_type)
                    else {
                        this.setInputType('tainacan-taxonomy-radio');
                        this.emitValues();
                    }

                    return true;
                } else {
                    let types = Object.keys( this.multiple_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    if (hasValue)
                        this.setInputType(this.value.input_type)
                    else
                        this.setInputType('tainacan-taxonomy-checkbox');
                        
                    return false;
                }
            },
            setError(){
                if (this.errors && this.errors.taxonomy_id !== '') {
                    this.setErrorsAttributes( 'is-danger', this.errors.taxonomy_id );
                } else {
                    this.setErrorsAttributes( '', '' );
                }
                return true;
            },
            isTermCreationAllowedOnCurrentTaxonomy() {
                const currentTaxonomy = this.taxonomies.find((taxonomy) => taxonomy.id == this.taxonomy_id);
                return currentTaxonomy ? currentTaxonomy.allow_insert == 'yes' : false;
            }
        },
        watch: {
            input_type:{
                handler(val, oldValue) {
                    if (val != oldValue) {
                        this.emitValues();
                    }
                }
            }
        },
        created() {
            this.fetchTaxonomies();
            this.fetchCollections();

            this.single_types['tainacan-taxonomy-radio'] = 'Radio';
            this.multiple_types['tainacan-taxonomy-tag-input'] = 'Tag Input';
            this.multiple_types['tainacan-taxonomy-checkbox'] = 'Checkbox';

            if (this.value) {

                this.taxonomy_id = this.value.taxonomy_id;
                this.allow_new_terms = ( this.value.allow_new_terms ) ? this.value.allow_new_terms : 'no';
                this.hide_hierarchy_path = ( this.value.hide_hierarchy_path ) ? this.value.hide_hierarchy_path : 'no';
                this.do_not_dispaly_term_as_link = ( this.value.do_not_dispaly_term_as_link ) ? this.value.do_not_dispaly_term_as_link : 'no';
                
                if (this.metadatum && this.metadatum.multiple === 'no') {
                    let types = Object.keys( this.single_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    this.setInputType( ( hasValue ) ? this.value.input_type : 'tainacan-taxonomy-radio' );
                } else {
                    let types = Object.keys( this.multiple_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    this.setInputType( ( hasValue ) ? this.value.input_type : 'tainacan-taxonomy-checkbox' );
                }

                this.visible_options_list = ( this.value.visible_options_list ) ? this.value.visible_options_list : false;
                this.link_filtered_by_collections = ( this.value.link_filtered_by_collections ) ? this.value.link_filtered_by_collections : [];
                this.taxonomy = this.value.taxonomy ? this.value.taxonomy : '';
            }

            this.isReady = true;
        },
        methods: {
            setInputType( input ) {
                this.input_type = input;
            },
            setErrorsAttributes( type, message ) {
                this.taxonomyType = type;
                this.taxonomyMessage = message;
            },
            fetchCollections() {
                this.loadingCollections = true;

                return axios.get('/collections?nopaging=1&context=edit&nopaging=1&fetch_only=name,id,thumbnail')
                    .then(res => {
                        this.collections = res.data ? res.data : [];
                        this.loadingCollections = false;
                    })
                    .catch(error => {
                        this.$console.log(error);
                        this.loadingCollections = false;
                    });
            },
            fetchTaxonomies(){
                this.loading = true;

                return axios.get('/taxonomies?nopaging=1&order=asc&orderby=title')
                    .then(res => {
                        this.taxonomies = res.data ? res.data : [];
                        this.loading = false;
                    })
                    .catch(error => {
                        this.$console.log(error);
                        this.loading = false;
                    });
            },
            labelNewTerms(){
                return ( this.allow_new_terms === 'yes' ) ? this.$i18n.get('label_yes') : this.$i18n.get('label_no');
            },
            clear(){
                this.taxonomyType = '';
                this.taxonomyMessage = '';
            },
            emitValues(){
                this.$emit('input',{
                    taxonomy_id: this.taxonomy_id,
                    input_type: this.input_type,
                    allow_new_terms: this.allow_new_terms,
                    visible_options_list: this.visible_options_list,
                    link_filtered_by_collections: this.link_filtered_by_collections,
                    hide_hierarchy_path: this.hide_hierarchy_path,
                    do_not_dispaly_term_as_link: this.do_not_dispaly_term_as_link,
                    taxonomy: this.taxonomy
                })
            },
            updateSelectedCollections(selectedCollections) {
               this.link_filtered_by_collections = selectedCollections.map(collection => collection.id);
               this.emitValues();
            },
            getSelectedTaxonomyCollections() {
                if ( this.link_filtered_by_collections && this.link_filtered_by_collections.length )
                    return this.collections.filter((collection) => this.link_filtered_by_collections.includes(collection.id));
                return [];
            },
            filterCollections(searchString) {
                this.collectionSearchString = searchString;
            }
        }
    }
</script>

<style scoped>
    .tainacan-help-tooltip-trigger {
        font-size: 1em;
    }
    .switch.is-small {
        margin-top: -0.5em;
    }
</style>