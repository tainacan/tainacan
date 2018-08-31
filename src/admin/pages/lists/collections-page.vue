<template>
    <div class="repository-level-page page-container">
        <b-loading :active.sync="isLoadingMetadatumMappers"/>
        <tainacan-title />
        <div
                class="sub-header"
                v-if="$userCaps.hasCapability('edit_tainacan-collections')">
            <div class="header-item">
                <b-dropdown id="collection-creation-options-dropdown">
                    <button
                            class="button is-secondary"
                            slot="trigger">
                        <div>{{ $i18n.getFrom('collections', 'new_item') }}</div>
                        <b-icon icon="menu-down"/>
                    </button>
                    <b-dropdown-item>
                        <router-link
                                id="a-create-collection"
                                tag="div"
                                :to="{ path: $routerHelper.getNewCollectionPath() }">
                            {{ $i18n.get('new_blank_collection') }}
                            <br>
                            <small class="is-small">{{ $i18n.get('info_choose_your_metadata') }}</small>
                        </router-link>
                    </b-dropdown-item>
                    <b-dropdown-item
                            :key="metadatum_mapper.slug"
                            v-for="metadatum_mapper in metadatum_mappers"
                            v-if="metadatum_mapper.metadata != false">
                        <router-link
                                :id="'a-create-collection-' + metadatum_mapper.slug"
                                tag="div"
                                :to="{ path: $routerHelper.getNewMappedCollectionPath(metadatum_mapper.slug) }">
                            {{ $i18n.get(metadatum_mapper.name) }}
                        </router-link>
                    </b-dropdown-item>
                    <b-dropdown-item>
                        <div
                                id="a-import-collection"
                                tag="div"
                                @click="onOpenImportersModal">
                            {{ $i18n.get('import') }}
                            <br>
                            <small class="is-small">{{ $i18n.get('info_import_collection') }}</small>
                        </div>
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>

        <div class="above-subheader">
            <div class="tabs">
                <ul>
                    <li 
                            @click="onChangeTab('')"
                            :class="{ 'is-active': status == undefined || status == ''}"><a>{{ $i18n.get('label_all_items') }}</a></li>
                    <li 
                            @click="onChangeTab('draft')"
                            :class="{ 'is-active': status == 'draft'}"><a>{{ $i18n.get('label_draft_items') }}</a></li>
                    <li 
                            @click="onChangeTab('trash')"
                            :class="{ 'is-active': status == 'trash'}"><a>{{ $i18n.get('label_trash_items') }}</a></li>
                </ul>
            </div>
            <div>
                <collections-list
                        :is-loading="isLoading"
                        :total-collections="totalCollections"
                        :page="page"
                        :collections-per-page="collectionsPerPage"
                        :collections="collections"
                        :is-on-trash="status == 'trash'"/> 

                <!-- Empty state image -->
                <div v-if="collections.length <= 0 && !isLoading">
                    <section class="section">
                        <div class="content has-text-grey has-text-centered">
                            <p>
                                <b-icon
                                        icon="inbox"
                                        size="is-large"/>
                            </p>
                            <p v-if="status == undefined || status == ''">{{ $i18n.get('info_no_collection_created') }}</p>
                            <p v-if="status == 'draft'">{{ $i18n.get('info_no_collection_draft') }}</p>
                            <p v-if="status == 'trash'">{{ $i18n.get('info_no_collection_trash') }}</p>
                            <router-link
                                    v-if="status == undefined || status == ''"
                                    id="button-create-collection"
                                    tag="button"
                                    class="button is-secondary"
                                    :to="{ path: $routerHelper.getNewCollectionPath() }">
                                {{ $i18n.getFrom('collections', 'new_item') }}
                            </router-link>
                        </div>
                    </section>
                </div>  
                
                <!-- Footer -->
                <div
                        class="pagination-area"
                        v-if="collections.length > 0">
                    <div class="shown-items"> 
                        {{ 
                            $i18n.get('info_showing_collections') + 
                            (collectionsPerPage*(page - 1) + 1) + 
                            $i18n.get('info_to') + 
                            getLastCollectionNumber() + 
                            $i18n.get('info_of') + totalCollections + '.'
                        }} 
                    </div> 
                    <div class="items-per-page">
                        <b-field 
                                horizontal 
                                :label="$i18n.get('label_collections_per_page')"> 
                            <b-select 
                                    :value="collectionsPerPage"
                                    @input="onChangeCollectionsPerPage" 
                                    :disabled="collections.length <= 0">
                                <option value="12">12</option>
                                <option value="24">24</option>
                                <option value="48">48</option>
                                <option value="96">96</option>
                            </b-select>
                        </b-field>
                    </div>
                    <div class="pagination"> 
                        <b-pagination
                                @change="onPageChange"
                                :total="totalCollections"
                                :current.sync="page"
                                order="is-centered"
                                size="is-small"
                                :per-page="collectionsPerPage"/> 
                    </div>
                </div>    
            </div> 
        </div>
    </div>   
</template>

<script>
import CollectionsList from '../../components/lists/collections-list.vue';
import AvailableImportersModal from '../../components/other/available-importers-modal.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'CollectionsPage',
    data(){
        return {
            isLoading: false,
            totalCollections: 0,
            page: 1,
            collectionsPerPage: 12,
            isLoadingMetadatumMappers: true,
            status: ''
        }
    },
    components: {
        CollectionsList
    },
    computed: {
        metadatum_mappers: {
            get() {
                return this.getMetadatumMappers();
            }
        },
        collections() {
            return this.getCollections(); 
        }
    },
    methods: {
         ...mapActions('collection', [
            'fetchCollections',
        ]),
        ...mapActions('metadata', [
            'fetchMetadatumMappers'
        ]),
        ...mapGetters('collection', [
            'getCollections'
        ]),
        ...mapGetters('metadata', [
            'getMetadatumMappers'
        ]),
        onChangeTab(status) {
            this.status = status;
            this.loadCollections();
        },
        onChangeCollectionsPerPage(value) {
            
            if (value != this.collectionsPerPage) {
                this.$userPrefs.set('collections_per_page', value)
                    .then((newValue) => {
                        this.collectionsPerPage = newValue;
                    })
                    .catch(() => {
                        this.$console.log("Error settings user prefs for collection per page")
                    });
            }
            this.collectionsPerPage = value;
            this.loadCollections();
        },
        onPageChange(page) {
            this.page = page;
            this.loadCollections();
        },
        loadCollections() {    
            this.isLoading = true;
            this.fetchCollections({ 'page': this.page, 'collectionsPerPage': this.collectionsPerPage, 'status': this.status })
            .then((res) => {
                this.isLoading = false;
                this.totalCollections = res.total;
            }) 
            .catch(() => {
                this.isLoading = false;
            });
        },
        getLastCollectionNumber() {
            let last = (Number(this.collectionsPerPage*(this.page - 1)) + Number(this.collectionsPerPage));
            
            return last > this.totalCollections ? this.totalCollections : last;
        },
        onOpenImportersModal() {
            this.$modal.open({
                parent: this,
                component: AvailableImportersModal,
                hasModalCard: true
            });
        }
    },
    created() {
        this.collectionsPerPage = this.$userPrefs.get('collections_per_page');
        this.isLoadingMetadatumTypes = true;
        this.fetchMetadatumMappers()
            .then(() => {
                this.isLoadingMetadatumMappers = false;
            })
            .catch(() => {
                this.isLoadingMetadatumMappers = false;
            });
    },
    mounted(){
        if (this.collectionsPerPage != this.$userPrefs.get('collections_per_page'))
            this.collectionsPerPage = this.$userPrefs.get('collections_per_page');

        if (!this.collectionsPerPage) {
            this.collectionsPerPage = 12;
            this.$userPrefs.set('collections_per_page', 12);
        }

        this.loadCollections();
    }
}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .sub-header {
        min-height: $subheader-height;
        height: $header-height;
        margin-left: -$page-side-padding;
        margin-right: -$page-side-padding;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        border-bottom: 1px solid #ddd;

        .header-item {
            display: inline-block;
            padding-right: 8em;
        }

        @media screen and (max-width: 769px) {
            height: 60px;
            margin-top: -0.5em;
            padding-top: 0.9em;
            
            .header-item {
                padding-right: 0.5em;
            }
        }
    }
    .tabs {
        padding-top: 20px;
        margin-bottom: 20px;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
    }
    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        height: auto;
    }

</style>


