<template>
    <section>
        <b-field
                :addons="false"
                :listen="setError"
                :type="collectionType"
                :message="collectionMessage">
            <label class="label is-inline">
                {{ $i18n.get('label_collection_related') }}<span :class="collectionType" >&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-relationship', 'collection_id')"
                        :message="$i18n.getHelperMessage('tainacan-relationship', 'collection_id')"/>
            </label>
            <b-select
                    name="metadata_type_relationship[collection_id]"
                    :placeholder="$i18n.get('instruction_select_collection_fetch_items' )"
                    v-model="collection"
                    @change.native="emitValues()"
                    @focus="clear()"
                    :loading="loading"
                    expanded>
                <option
                        v-for="option in collections"
                        :value="option.id"
                        :key="option.id">
                    {{ option.name }}
                </option>
            </b-select>
        </b-field>

        <transition name="fade">
            <div
                v-if="loadingMetadata"
                class="loading-spinner"/>
        </transition>
        <transition name="filter-item">
            <b-field
                    v-if="hasMetadata"
                    :addons="false">
                <label class="label">
                    {{ $i18n.get('label_metadata_for_search') }}
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-relationship', 'search')"
                            :message="$i18n.getHelperMessage('tainacan-relationship', 'search')"/>
                </label>
                <b-select
                        name="metadata_type_relationship[search]"
                        v-model="modelSearch"
                        expanded>
                    <option
                            v-for="(option, index) in metadata"
                            :key="index"
                            :value="option.id"
                            class="field">
                        {{ option.name }}
                    </option>
                </b-select>
            </b-field>
        </transition>
        <transition name="filter-item">
            <b-field 
                    v-if="hasMetadata"
                    :addons="false">
                <label class="label">
                    {{ $i18n.getHelperTitle('tainacan-relationship', 'display_related_item_metadata') }}
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-relationship', 'display_related_item_metadata')"
                            :message="$i18n.getHelperMessage('tainacan-relationship', 'display_related_item_metadata')"/>
                </label>
                <div :class="'displayed-metadata-options' + (metadata.length > 5 ? ' has-more-than-5-metadata' : '')">
                    <b-checkbox
                            native-value="thumbnail"
                            name="metadata_type_relationship[display_related_item_metadata]"
                            @input="emitValues()"
                            v-model="displayRelatedItemMetadata">
                        {{ $i18n.get('label_thumbnail') }}
                    </b-checkbox>
                    <b-checkbox
                            v-for="(metadatumOption, index) in metadata"
                            :key="index"
                            :native-value="metadatumOption.id"
                            name="metadata_type_relationship[display_related_item_metadata]"
                            @input="emitValues()"
                            v-model="displayRelatedItemMetadata"
                            :disabled="metadatumOption.id == modelSearch">
                        {{ metadatumOption.name }}
                    </b-checkbox>
                </div>
            </b-field>
        </transition>

        <b-field
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-relationship', 'display_in_related_items')">
                &nbsp;
            <b-switch
                    size="is-small" 
                    v-model="modelDisplayInRelatedItems"
                    @input="emitValues()"
                    true-value="yes"
                    false-value="no" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-relationship', 'display_in_related_items')"
                    :message="$i18n.getHelperMessage('tainacan-relationship', 'display_in_related_items')"/>
        </b-field>

    </section>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios';

    export default {
        props: {
            search: [ String ],
            collection_id: [ Number ],
            value: [ String, Object, Array ],
            metadatum: [ String, Object ],
            errors: [ String, Object, Array ]
        },
        data(){
            return {
                icon: '',
                collections:[],
                metadata: [],
                loading: true,
                collection: '',
                hasMetadata: false,
                loadingMetadata: false,
                modelDisplayInRelatedItems: 'no',
                modelSearch:'',
                collectionType: '',
                collectionMessage: '',
                displayRelatedItemMetadata: [],
                isMetaqueryRelationshipEnabled: tainacan_plugin && tainacan_plugin.tainacan_enable_relationship_metaquery == true ? tainacan_plugin.tainacan_enable_relationship_metaquery : false
            }
        },
        computed: {
            setError(){
                if( this.errors && this.errors.collection_id !== '' ){
                    this.setErrorsAttributes( 'is-danger', this.errors.collection_id );
                } else {
                    this.setErrorsAttributes( '', '' );
                }
                return true;
            }
        },
        watch:{
            collection( value ){
                this.collection = value;
                if( value && value !== '' ) {
                    this.fetchMetadataFromCollection(value);
                } else {
                    this.metadata = [];
                    this.hasMetadata = false;
                    this.modelSearch = '';
                    this.modelDisplayInRelatedItems = 'no';
                    this.emitValues();
                }
            },
            modelSearch( value ){
                if ( !this.displayRelatedItemMetadata.includes(value) )
                    this.displayRelatedItemMetadata.push(value);
                this.emitValues();
            }
        },
        created(){
            this.fetchCollections().then(() => {
                if( this.collection_id && this.collection_id !== '' ){
                    this.collection = this.collection_id;
                } else if ( this.value ) {
                    this.collection = this.value.collection_id;
                }
            });

            this.displayRelatedItemMetadata = this.value && this.value.display_related_item_metadata && Array.isArray(this.value.display_related_item_metadata) ? this.value.display_related_item_metadata : [];
            this.modelDisplayInRelatedItems = this.value && this.value.display_in_related_items ? this.value.display_in_related_items : 'no';
        },
        methods: {
            setErrorsAttributes( type, message ){
                this.collectionType = type;
                this.collectionType = message;
            },
            async fetchCollections(){
                return await axios.get('/collections?nopaging=1')
                    .then(res => {
                        const collections = res.data;

                        this.loading = false;
                        this.collections = collections ? collections : [];
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            },
            fetchMetadataFromCollection(value) {
                this.loadingMetadata = true;
                this.hasMetadata = false;

                axios.get('/collection/' + value + '/metadata/?nopaging=1')
                    .then((res) => {
                        this.loadingMetadata = false;
                        let metadata = res.data;

                        if (metadata.length > 0 ){
                            this.metadata = [];

                            for (let metadatum of metadata) {
                               if ( (metadatum.metadata_type_object.component !== 'tainacan-relationship' || this.isMetaqueryRelationshipEnabled) && metadatum.metadata_type_object.component !== 'tainacan-compound' ) {
                                   this.metadata.push( metadatum );
                                   this.hasMetadata = true;
                                   this.checkMetadata();
                               }
                            }
                    
                            if (this.metadata.length <= 0) {
                                this.$buefy.toast.open({
                                    duration: 4000,
                                    message: this.$i18n.get('info_warning_no_metadata_found'),
                                    position: 'is-bottom',
                                    type: 'is-danger'
                                })
                            }

                        } else {
                            this.metadata = [];
                            this.hasMetadata = false;
                            this.$buefy.toast.open({
                                duration: 4000,
                                message: this.$i18n.get('info_warning_no_metadata_found'),
                                position: 'is-bottom',
                                type: 'is-danger'
                            })
                        }

                        this.emitValues();
                    })
                    .catch(() => {
                        this.hasMetadata = false;
                        this.emitValues();
                    });

            },
            checkMetadata(){
                if( this.value && this.value.search ){
                    this.modelSearch = this.value.search;
                } else {
                    try {
                        const json = JSON.parse( this.search );
                        this.modelSearch = json;
                    } catch(e){
                        this.modelSearch = '';
                    }
                }
            },
            clear(){
                this.collectionType = '';
                this.collectionMessage = '';
            },
            emitValues(){
                this.$emit('input',{
                    collection_id: this.collection,
                    search: this.modelSearch,
                    display_in_related_items: this.modelDisplayInRelatedItems,
                    display_related_item_metadata: this.displayRelatedItemMetadata
                });
            }
        }
    }
</script>

<style scoped>
    .help-wrapper {
        font-size: 1em;
    }
    .switch.is-small {
        margin-top: -0.5em;
    }
    .displayed-metadata-options.has-more-than-5-metadata {
        max-height: 125px;
        overflow-y: auto;
        border: 1px solid var(--tainacan-gray2);
        overflow-x: hidden;
        padding: 6px 12px;
    }
</style>
