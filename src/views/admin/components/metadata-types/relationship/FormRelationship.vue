<template>
    <section>
        <b-field
                :addons="false"
                :listen="setError"
                :type="collectionType"
                :message="collectionMessage">
            <label class="label is-inline">
                {{ $i18n.get('label_collection_related') }}<span :class="collectionType">&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-relationship', 'collection_id')"
                        :message="$i18n.getHelperMessage('tainacan-relationship', 'collection_id')" />
            </label>
            <div
                    v-if="isResolvingCollection"
                    class="control is-loading selected-collection" />
            <div
                    v-else-if="!selectedCollection || isReplacingCollection"
                    class="collection-picker">
                <b-autocomplete
                        v-model="collectionSearch"
                        v-a11y-autocomplete="{ appendToBody: true }"
                        name="metadata_type_relationship[collection_id]"
                        :placeholder="$i18n.get('instruction_select_collection_fetch_items')"
                        :data="collections"
                        field="name"
                        :loading="loading"
                        :append-to-body="true"
                        open-on-focus
                        expanded
                        check-infinite-scroll
                        @select="onSelectCollection"
                        @update:model-value="fetchCollections"
                        @focus="clear()"
                        @infinite-scroll="fetchMoreCollections">
                    <template #empty>
                        {{ $i18n.get('info_no_options_found') }}
                    </template>
                </b-autocomplete>
                <button
                        v-if="isReplacingCollection && selectedCollection"
                        type="button"
                        class="button is-white"
                        :aria-label="$i18n.get('close')"
                        @click.prevent="cancelReplacingCollection">
                    <span class="icon is-small">
                        <i class="tainacan-icon tainacan-icon-close" />
                    </span>
                </button>
            </div>
            <div
                    v-else
                    class="control selected-collection">
                <span>{{ selectedCollection.name }}</span>
                <button
                        type="button"
                        class="button is-white"
                        :aria-label="$i18n.get('edit')"
                        @click.prevent="startReplacingCollection">
                    <span class="icon is-small">
                        <i class="tainacan-icon tainacan-icon-edit" />
                    </span>
                </button>
            </div>
            <p 
                    v-if="$userCaps.hasCapability('tnc_rep_edit_collections')"
                    class="help" 
                    v-html="$i18n.getWithVariables( 'info_manage_collections', [ $routerHelper.getAbsoluteAdminPath() + $routerHelper.getCollectionsPath() ] )" />
        </b-field>

        <transition name="fade">
            <div
                    v-if="loadingMetadata"
                    class="loading-spinner" />
        </transition>
        <transition name="filter-item">
            <b-field
                    v-if="hasMetadata"
                    :addons="false">
                <label class="label">
                    {{ $i18n.get('label_metadata_for_search') }}
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-relationship', 'search')"
                            :message="$i18n.getHelperMessage('tainacan-relationship', 'search')" />
                </label>
                <b-select
                        v-model="modelSearch"
                        name="metadata_type_relationship[search]"
                        expanded>
                    <option
                            v-for="(option, index) in metadata.filter(metadatum => metadatum.metadata_type_object.component !== 'tainacan-compound')"
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
                            :message="$i18n.getHelperMessage('tainacan-relationship', 'display_related_item_metadata')" />
                </label>
                <div :class="'displayed-metadata-options' + (metadata.length > 5 ? ' has-more-than-5-metadata' : '')">
                    <b-checkbox
                            v-model="displayRelatedItemMetadata"
                            native-value="thumbnail"
                            name="metadata_type_relationship[display_related_item_metadata]"
                            @update:model-value="emitValues()">
                        {{ $i18n.get('label_thumbnail') }}
                    </b-checkbox>
                    <b-checkbox
                            v-for="(metadatumOption, index) in metadata"
                            :key="index"
                            v-model="displayRelatedItemMetadata"
                            :native-value="metadatumOption.id"
                            name="metadata_type_relationship[display_related_item_metadata]"
                            :disabled="metadatumOption.id == modelSearch"
                            @update:model-value="emitValues()">
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
                    v-model="modelDisplayInRelatedItems" 
                    size="is-small"
                    true-value="yes"
                    false-value="no"
                    @update:model-value="emitValues()" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-relationship', 'display_in_related_items')"
                    :message="$i18n.getHelperMessage('tainacan-relationship', 'display_in_related_items')" />
        </b-field>

        <b-field
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-relationship', 'accept_draft_items')">
                &nbsp;
            <b-switch
                    v-model="modelAcceptDraftItems" 
                    size="is-small"
                    true-value="yes"
                    false-value="no"
                    @update:model-value="emitValues()" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-relationship', 'accept_draft_items')"
                    :message="$i18n.getHelperMessage('tainacan-relationship', 'accept_draft_items')" />
        </b-field>

        <b-field
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-relationship', 'accept_only_items_authored_by_current_user')">
                &nbsp;
            <b-switch
                    v-model="modelAcceptOnlyItemsAuthoredByCurrentUser" 
                    size="is-small"
                    true-value="yes"
                    false-value="no"
                    @update:model-value="emitValues()" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-relationship', 'accept_only_items_authored_by_current_user')"
                    :message="$i18n.getHelperMessage('tainacan-relationship', 'accept_only_items_authored_by_current_user')" />
        </b-field>

    </section>
</template>

<script>
    import { tainacanApi } from '../../../js/axios';

    export default {
        props: {
            search: [ String ],
            collectionId: [ Number ],
            value: [ String, Object, Array ],
            metadatum: [ String, Object ],
            errors: [ String, Object, Array ]
        },
        emits: ['update:value'],
        data(){
            return {
                icon: '',
                collections:[],
                selectedCollection: null,
                isResolvingCollection: false,
                isReplacingCollection: false,
                collectionSearch: '',
                collectionSearchQuery: '',
                collectionsPage: 1,
                totalCollections: 0,
                collectionsRequestId: 0,
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
                modelAcceptDraftItems: 'no',
                modelAcceptOnlyItemsAuthoredByCurrentUser: 'no',
                isMetaqueryRelationshipEnabled: tainacan_plugin && tainacan_plugin.tainacan_enable_relationship_metaquery == true ? tainacan_plugin.tainacan_enable_relationship_metaquery : false
            }
        },
        computed: {
            setError(){
                if ( this.errors && this.errors.collection_id !== '' )
                    this.setErrorsAttributes( 'is-danger', this.errors.collection_id );
                else
                    this.setErrorsAttributes( '', '' );
                return true;
            }
        },
        watch:{
            collection( value ) {
                this.collection = value;
                if ( value && value !== '' ) {
                    this.fetchMetadataFromCollection(value);
                } else {
                    this.metadata = [];
                    this.hasMetadata = false;
                    this.modelSearch = '';
                    this.modelDisplayInRelatedItems = 'no';
                    this.modelAcceptDraftItems = 'no';
                    this.modelAcceptOnlyItemsAuthoredByCurrentUser = 'no';
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
            const initialCollectionId = ( this.collectionId && this.collectionId !== '' )
                ? this.collectionId
                : ( this.value && this.value.collection_id ? this.value.collection_id : '' );

            if (initialCollectionId) {
                this.collection = initialCollectionId;
                this.fetchSelectedCollection(initialCollectionId);
            } else {
                this.loading = false;
                this.fetchCollections('');
            }

            this.displayRelatedItemMetadata = this.value && this.value.display_related_item_metadata && Array.isArray(this.value.display_related_item_metadata) ? this.value.display_related_item_metadata : [];
            this.modelDisplayInRelatedItems = this.value && this.value.display_in_related_items ? this.value.display_in_related_items : 'no';
            this.modelAcceptDraftItems = this.value && this.value.accept_draft_items ? this.value.accept_draft_items : 'no';
            this.modelAcceptOnlyItemsAuthoredByCurrentUser = this.value && this.value.accept_only_items_authored_by_current_user ? this.value.accept_only_items_authored_by_current_user : 'no';
        },
        methods: {
            setErrorsAttributes( type, message ){
                this.collectionType = type;
                this.collectionType = message;
            },
            fetchSelectedCollection(id) {
                this.isResolvingCollection = true;
                this.loading = true;

                return tainacanApi.get('/collections/' + id + '?fetch_only=name,id')
                    .then(res => {
                        this.selectedCollection = res.data ? res.data : { id: id, name: String(id) };
                        this.loading = false;
                        this.isResolvingCollection = false;
                    })
                    .catch(error => {
                        this.$console.log(error);
                        this.selectedCollection = { id: id, name: String(id) };
                        this.loading = false;
                        this.isResolvingCollection = false;
                    });
            },
            fetchCollections: _.debounce(function(search) {
                const query = search || '';

                if (query !== this.collectionSearchQuery) {
                    this.collectionSearchQuery = query;
                    this.collections = [];
                    this.collectionsPage = 1;
                    this.totalCollections = 0;
                }

                if (this.collectionsPage > 1 && this.collections.length >= Number(this.totalCollections))
                    return;

                const requestId = ++this.collectionsRequestId;
                this.loading = true;

                let endpoint = '/collections?paged=' + this.collectionsPage + '&perpage=12&status=any&order=asc&orderby=title';
                if (query)
                    endpoint += '&search=' + encodeURIComponent(query);

                return tainacanApi.get(endpoint)
                    .then(res => {
                        if (requestId !== this.collectionsRequestId)
                            return;

                        const pageCollections = res.data ? res.data : [];
                        for (let collection of pageCollections)
                            this.collections.push(collection);

                        this.totalCollections = res.headers['x-wp-total'] ? Number(res.headers['x-wp-total']) : this.collections.length;
                        this.collectionsPage++;
                        this.loading = false;
                    })
                    .catch(error => {
                        if (requestId !== this.collectionsRequestId)
                            return;

                        this.$console.log(error);
                        this.loading = false;
                    });
            }, 500),
            fetchMoreCollections: _.debounce(function() {
                this.fetchCollections(this.collectionSearchQuery);
            }, 250),
            onSelectCollection(collection) {
                if (!collection || !collection.id)
                    return;

                this.selectedCollection = collection;
                this.isReplacingCollection = false;
                this.collectionSearch = '';
                if (this.collection != collection.id)
                    this.collection = collection.id;
            },
            startReplacingCollection() {
                this.collectionsRequestId++;
                this.isReplacingCollection = true;
                this.collectionSearch = '';
                this.collectionSearchQuery = '';
                this.collections = [];
                this.collectionsPage = 1;
                this.totalCollections = 0;
                this.fetchCollections('');
            },
            cancelReplacingCollection() {
                this.collectionsRequestId++;
                this.isReplacingCollection = false;
                this.collectionSearch = '';
            },
            fetchMetadataFromCollection(value) {
                this.loadingMetadata = true;
                this.hasMetadata = false;

                tainacanApi.get('/collection/' + value + '/metadata/?nopaging=1')
                    .then((res) => {
                        this.loadingMetadata = false;
                        let metadata = res.data;

                        if (metadata.length > 0 ){
                            this.metadata = [];

                            for (let metadatum of metadata) {
                               if ( (metadatum.metadata_type_object.component !== 'tainacan-relationship' || this.isMetaqueryRelationshipEnabled) ) {
                                   this.metadata.push( metadatum );
                                   this.hasMetadata = true;
                                   this.checkSearchMetadatum();
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
            checkSearchMetadatum() {
                if ( this.value && this.value.search ) {
                    this.modelSearch = this.value.search;
                } else {
                    const titleMetadatumIndex = this.metadata.findIndex(metadatum => metadatum.metadata_type == 'Tainacan\\Metadata_Types\\Core_Title');
                    if (titleMetadatumIndex >= 0)
                        this.modelSearch = this.metadata[titleMetadatumIndex].id;
                    else {
                        const nonCompountMetadatumIndex = this.metadata.findIndex(metadatum => metadatum.metadata_type_object.component !== 'tainacan-compound');
                        if (nonCompountMetadatumIndex >= 0) 
                            this.modelSearch = this.metadata[nonCompountMetadatumIndex].id;
                    }
                }
            },
            clear(){
                this.collectionType = '';
                this.collectionMessage = '';
            },
            emitValues(){
                this.$emit('update:value',{
                    collection_id: this.collection,
                    search: this.modelSearch,
                    display_in_related_items: this.modelDisplayInRelatedItems,
                    display_related_item_metadata: this.displayRelatedItemMetadata,
                    accept_draft_items: this.modelAcceptDraftItems,
                    accept_only_items_authored_by_current_user: this.modelAcceptOnlyItemsAuthoredByCurrentUser
                });
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
    .collection-picker {
        display: flex;
        align-items: center;
        gap: 0.25em;

        .autocomplete {
            flex: 1;
        }

        button {
            border-radius: 100em !important;
        }
    }
    .selected-collection {
        border: 1px solid var(--tainacan-gray2);
        padding: calc(0.57em - 1px) 8px;
        font-size: 0.875em;
        min-height: 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;

        button {
            border-radius: 100em !important;
        }

        &.is-loading {
            min-height: 2.5em;
        }
    }
    .displayed-metadata-options.has-more-than-5-metadata {
        max-height: 125px;
        overflow-y: auto;
        border: 1px solid var(--tainacan-gray2);
        border-radius: var(--tainacan-dropdownmenu-border-radius, 0px);
        overflow-x: hidden;
        padding: 6px 12px;
    }
</style>
