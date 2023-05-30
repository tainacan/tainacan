<template>
    <div class="repository-level-page page-container">
        <b-loading :active.sync="isLoading"/>
        <tainacan-title 
                :bread-crumb-items="[{ path: '', label: $i18n.get('collections') }]"/>
        <div class="sub-header">
            
            <!-- New Collection button -->
            <div 
                    v-if="!$adminOptions.hideCollectionsListCreationDropdown && $userCaps.hasCapability('tnc_rep_edit_collections')"
                    class="header-item">
                <b-dropdown
                        aria-role="list"
                        id="collection-creation-options-dropdown"
                        trap-focus>
                    <button
                            class="button is-secondary"
                            slot="trigger">
                        <div>{{ $i18n.getFrom('collections', 'new_item') }}</div>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                        </span>
                    </button>
                    <b-dropdown-item aria-role="listitem">
                        <router-link
                                id="a-create-collection"
                                tag="div"
                                :to="{ path: $routerHelper.getNewCollectionPath() }">
                            {{ $i18n.get('new_blank_collection') }}
                            <br>
                            <small class="is-small">{{ $i18n.get('info_choose_your_metadata') }}</small>
                        </router-link>
                    </b-dropdown-item>
                    <b-dropdown-item aria-role="listitem">
                        <div
                                id="a-preset-collection"
                                tag="div"
                                @click="onOpenCollectionCreationModal">
                            {{ $i18n.get('label_preset_collections') }}
                            <br>
                            <small class="is-small">{{ $i18n.get('info_preset_collections') }}</small>
                        </div>
                    </b-dropdown-item>
                    <b-dropdown-item aria-role="listitem">
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

            <!-- Collection Taxonomies, if available -->
            <template v-if="!isLoadingCollectionTaxonomies && Object.values(collectionTaxonomies) && Object.values(collectionTaxonomies).length >= 0">
                <b-field 
                        v-for="(collectionTaxonomy, taxonomyValue) in collectionTaxonomies"
                        :key="taxonomyValue"
                        class="header-item">
                    <b-dropdown
                            :ref="'collectionTaxonomyFilterDropdown-' + taxonomyValue"
                            :mobile-modal="true"
                            :disabled="(totalCollections && totalCollections.length && totalCollections.length <= 0) || isLoading"
                            class="show metadata-options-dropdown"
                            aria-role="list"
                            trap-focus>
                        <button
                                :aria-label="collectionTaxonomy['name']"
                                class="button is-white"
                                slot="trigger">
                            <span>{{ collectionTaxonomy['name'] }}</span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                        <div class="metadata-options-container">
                            <b-dropdown-item
                                    v-for="(collectionTaxonomyTerm, index) in collectionTaxonomy['terms']"
                                    :key="index"
                                    class="control"
                                    custom
                                    aria-role="listitem">
                                <b-checkbox
                                        v-model="collectionTaxonomyTerm.enabled"
                                        :native-value="collectionTaxonomyTerm.enabled">
                                    {{ collectionTaxonomyTerm.name }}
                                </b-checkbox>
                            </b-dropdown-item>   
                        </div>
                        <div class="dropdown-item-apply">
                            <button 
                                    aria-controls="items-list-results"
                                    @click="onChangeCollectionTaxonomyTerms(taxonomyValue)"
                                    class="button is-success">
                                {{ $i18n.get('label_apply_changes') }}
                            </button>
                        </div>  
                    </b-dropdown>
                </b-field>
            </template>

            <!-- Sorting options ----  -->
            <b-field class="header-item">
                <label class="label">{{ $i18n.get('label_sort') }}&nbsp;</label>
                <b-dropdown
                        :mobile-modal="true"
                        :disabled="collections.length <= 0 || isLoading"
                        @input="onChangeOrder"
                        aria-role="list"
                        trap-focus>
                    <button
                            :aria-label="$i18n.get('label_sorting_direction')"
                            class="button is-white"
                            slot="trigger">
                        <span 
                                style="margin-top: -2px;"
                                class="icon is-small gray-icon">
                            <i 
                                    :class="order == 'desc' ? 'tainacan-icon-sortdescending' : 'tainacan-icon-sortascending'"
                                    class="tainacan-icon tainacan-icon-1-125em"/>
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                        </span>
                    </button>
                    <b-dropdown-item
                            aria-controls="items-list-results"
                            role="button"
                            :class="{ 'is-active': order == 'desc' }"
                            :value="'desc'"
                            aria-role="listitem"
                            style="padding-bottom: 0.45em">
                        <span class="icon is-small gray-icon">
                            <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-sortdescending"/>
                        </span>
                        {{ $i18n.get('label_descending') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            aria-controls="items-list-results"
                            role="button"
                            :class="{ 'is-active': order == 'asc' }"
                            :value="'asc'"
                            aria-role="listitem"
                            style="padding-bottom: 0.45em">
                        <span class="icon is-small gray-icon">
                            <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-sortascending"/>
                        </span>
                        {{ $i18n.get('label_ascending') }}
                    </b-dropdown-item>
                </b-dropdown>
                <span
                        class="label"
                        style="padding-left: 0.65em;">
                    {{ $i18n.get('info_by_inner') }}
                </span>
                <b-select
                        class="sorting-select"
                        :disabled="collections.length <= 0"
                        @input="onChangeOrderBy($event)"
                        :value="orderBy"
                        :label="$i18n.get('label_sorting')">
                    <option
                            v-for="(option, index) in sortingOptions"
                            :value="option.value"
                            :key="index">
                        {{ option.label }}
                    </option>
                </b-select>
            </b-field>

            <!-- Textual Search -------------->
            <b-field class="header-item">
                <b-input 
                        :placeholder="$i18n.get('instruction_search')"
                        type="search"
                        size="is-small"
                        :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('collections')"
                        autocomplete="on"
                        v-model="searchQuery"
                        @keyup.enter.native="searchCollections()"
                        icon-right="magnify"
                        icon-right-clickable
                        @icon-right-click="searchCollections()" />
            </b-field>
        </div>

        <div class="above-subheader">
            <div class="tabs">
                <ul>
                    <li 
                            @click="onChangeTab('')"
                            :class="{ 'is-active': status == undefined || status == ''}"
                            v-tooltip="{
                                content: $i18n.get('info_collections_tab_all'),
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']
                            }">
                        <a :style="{ fontWeight: 'bold', color: 'var(--tainacan-gray5) !important' }">
                            {{ `${$i18n.get('label_all_collections')}` }}
                            <span class="has-text-gray">&nbsp;{{ `${` ${repositoryTotalCollections ? `(${Number(repositoryTotalCollections.private) + Number(repositoryTotalCollections.publish)})` : '' }`}` }}</span>
                        </a>
                    </li>
                    <li 
                            v-for="(statusOption, index) of $statusHelper.getStatuses().filter((status) => status.slug != 'draft')"
                            :key="index"
                            v-if="statusOption.slug != 'private' || (statusOption.slug == 'private' && $userCaps.hasCapability('tnc_rep_read_private_collections'))"
                            @click="onChangeTab(statusOption.slug)"
                            :class="{ 'is-active': status == statusOption.slug}"
                            :style="{ marginRight: statusOption.slug == 'private' ? 'auto' : '', marginLeft: statusOption.slug == 'trash' ? 'auto' : '' }"
                            v-tooltip="{
                                content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('collections')]),
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']
                            }">
                        <a>
                            <span 
                                    v-if="$statusHelper.hasIcon(statusOption.slug)"
                                    class="icon has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-1-125em"
                                        :class="$statusHelper.getIcon(statusOption.slug)"
                                        />
                            </span>
                            {{ statusOption.name }}
                            <span class="has-text-gray">&nbsp;{{ `${` ${repositoryTotalCollections ? `(${repositoryTotalCollections[statusOption.slug]})` : '' }`}` }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div>
                <collections-list
                        :is-loading="isLoading"
                        :total-collections="totalCollections"
                        :page="page"
                        :collections-per-page="collectionsPerPage"
                        :collections="collections"
                        :status="status" /> 

                <!-- Empty state image -->
                <div v-if="collections.length <= 0 && !isLoading">
                    <section class="section">
                        <div class="content has-text-grey has-text-centered">
                            <p>
                                <span class="icon is-large">
                                    <i class="tainacan-icon tainacan-icon-30px tainacan-icon-collections" />
                                </span>
                            </p>
                            <p v-if="status == undefined || status == ''">{{ $i18n.get('info_no_collection_created') }}</p>
                            <p
                                    v-for="(statusOption, index) of $statusHelper.getStatuses()"
                                    :key="index"
                                    v-if="status == statusOption.slug">
                                {{ $i18n.get('info_no_collections_' + statusOption.slug) }}
                            </p>

                            <div v-if="!$adminOptions.hideCollectionsListCreationDropdown && $userCaps.hasCapability('tnc_rep_edit_collections') && status == undefined || status == ''">
                                <b-dropdown 
                                        id="collection-creation-options-dropdown"
                                        aria-role="list"
                                        trap-focus>
                                    <button
                                            class="button is-secondary"
                                            slot="trigger">
                                        <div>{{ $i18n.getFrom('collections', 'new_item') }}</div>
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                                        </span>
                                    </button>
                                    <b-dropdown-item aria-role="listitem">
                                        <router-link
                                                id="a-create-collection"
                                                tag="div"
                                                :to="{ path: $routerHelper.getNewCollectionPath() }">
                                            {{ $i18n.get('new_blank_collection') }}
                                            <br>
                                            <small class="is-small">{{ $i18n.get('info_choose_your_metadata') }}</small>
                                        </router-link>
                                    </b-dropdown-item>
                                    <b-dropdown-item aria-role="listitem">
                                        <div
                                                id="a-preset-collection"
                                                tag="div"
                                                @click="onOpenCollectionCreationModal">
                                            {{ $i18n.get('label_preset_collections') }}
                                            <br>
                                            <small class="is-small">{{ $i18n.get('info_preset_collections') }}</small>
                                        </div>
                                    </b-dropdown-item>
                                    <b-dropdown-item aria-role="listitem">
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
                                <option :value="maxCollectionsPerPage">{{ maxCollectionsPerPage }}</option>
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
                                :per-page="collectionsPerPage"
                                :aria-next-label="$i18n.get('label_next_page')"
                                :aria-previous-label="$i18n.get('label_previous_page')"
                                :aria-page-label="$i18n.get('label_page')"
                                :aria-current-label="$i18n.get('label_current_page')"/> 
                    </div>
                </div>    
            </div> 
        </div>
    </div>   
</template>

<script>
import CollectionsList from '../../components/lists/collections-list.vue';
import AvailableImportersModal from '../../components/modals/available-importers-modal.vue';
import CollectionCreationModal from '../../components/modals/collection-creation-modal.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'CollectionsPage',
    components: {
        CollectionsList
    },
    data(){
        return {
            isLoading: false,
            totalCollections: 0,
            page: 1,
            collectionsPerPage: 12,
            isLoadingMetadatumMappers: true,
            isLoadingCollectionTaxonomies: false,
            status: '',
            order: 'desc',
            orderBy: 'date',
            searchQuery: '',
            sortingOptions: [
                { label: this.$i18n.get('label_title'), value: 'title' },
                { label: this.$i18n.get('label_creation_date'), value: 'date' },
                { label: this.$i18n.get('label_modification_date'), value: 'modified' }
            ],
            maxCollectionsPerPage: tainacan_plugin.api_max_items_per_page ? Number(tainacan_plugin.api_max_items_per_page) : 96
        }
    },
    computed: {
        collections() {
            return this.getCollections(); 
        },
        repositoryTotalCollections(){
            return this.getRepositoryTotalCollections();
        },
        collectionTaxonomies() {
            let collectionTaxonomies = this.getCollectionTaxonomies();

            // Adds the 'enable' property to our local version of terms
            if ( Object.values(collectionTaxonomies).length ) {
                Object.values(collectionTaxonomies).forEach(collectionTaxonomy => {
                    collectionTaxonomy.terms.forEach(aTerm => aTerm.enabled = false);
                });
                return collectionTaxonomies;
            }

            return {};
        }
    },
    created() {
        this.collectionsPerPage = this.$userPrefs.get('collections_per_page');

        this.isLoadingCollectionTaxonomies = true;
        this.fetchCollectionTaxonomies()
            .then(() => {
                this.isLoadingCollectionTaxonomies = false;
            })
            .catch(() => {
                this.isLoadingCollectionTaxonomies= false;
            });
    }, 
    mounted() {

        if (this.collectionsPerPage != this.$userPrefs.get('collections_per_page'))
            this.collectionsPerPage = this.$userPrefs.get('collections_per_page');
        if (!this.collectionsPerPage) {
            this.collectionsPerPage = 12;
            this.$userPrefs.set('collections_per_page', 12);
        }

        if (this.order != this.$userPrefs.get('collections_order'))
            this.order = this.$userPrefs.get('collections_order');
        if (!this.order) {
            this.order = 'asc';
            this.$userPrefs.set('collections_order', 'asc');
        }

        if (this.orderBy != this.$userPrefs.get('collections_order_by'))
            this.orderBy = this.$userPrefs.get('collections_order_by');
        if (!this.orderBy) {
            this.orderBy = 'date';
            this.$userPrefs.set('collections_order_by', 'date');
        }

        this.loadCollections();
    },
    methods: {
         ...mapActions('collection', [
            'fetchCollections',
            'cleanCollections',
            'fetchCollectionTaxonomies'
        ]),
        ...mapActions('metadata', [
            'fetchMetadatumMappers'
        ]),
        ...mapGetters('collection', [
            'getCollections',
            'getRepositoryTotalCollections',
            'getCollectionTaxonomies'
        ]),
        onChangeTab(status) {
            this.page = 1;
            this.status = status;
            this.loadCollections();
        },
        onChangeOrder(newOrder) {
            if (newOrder != this.order) { 
                this.$userPrefs.set('collections_order', newOrder)
                    .catch(() => {
                        this.$console.log("Error settings user prefs for collections order")
                    });
                this.order = newOrder;
                this.loadCollections();
            }
        },
        onChangeOrderBy(newOrderBy) {
            if (newOrderBy != this.orderBy) { 
                this.$userPrefs.set('collections_order_by', newOrderBy)
                    .then((newOrderBy) => {
                        this.orderBy = newOrderBy;
                    })
                    .catch(() => {
                        this.$console.log("Error settings user prefs for collections orderby")
                    });
            }
            this.orderBy = newOrderBy;
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
        onChangeCollectionTaxonomyTerms(taxonomyValue) {

            this.loadCollections();

            // Closes dropdown
            if (this.$refs['collectionTaxonomyFilterDropdown-' + taxonomyValue] && this.$refs['collectionTaxonomyFilterDropdown-' + taxonomyValue][0])
                this.$refs['collectionTaxonomyFilterDropdown-' + taxonomyValue][0].toggle();
        },
        loadCollections() {
            this.cleanCollections();
            this.isLoading = true;
            this.fetchCollections({ 
                page: this.page, 
                collectionsPerPage: this.collectionsPerPage,
                status: this.status,
                contextEdit: true, 
                order: this.order,
                orderby: this.orderBy,
                search: this.searchQuery,
                collectionTaxonomies: this.collectionTaxonomies,
            })
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
            this.$buefy.modal.open({
                parent: this,
                component: AvailableImportersModal,
                hasModalCard: true,
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        },
        onOpenCollectionCreationModal() {
            this.$buefy.modal.open({
                parent: this,
                component: CollectionCreationModal,
                hasModalCard: true,
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        },
        searchCollections() {
            this.page = 1;
            this.loadCollections();
        },
    }
}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .sub-header {
        min-height: $header-height;
        height: auto;
        padding-left: 0;
        padding-right: 0;
        border-bottom: 1px solid #ddd;
        display: inline-flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        width: 100%;

        .header-item {
            margin-bottom: 0 !important;
            min-height: 2em;

            &:first-child {
                margin-right: auto;
            }
            &:not(:last-child) {
                padding-right: 0.875em;
            }

            .label {
                font-size: 0.875em;
                font-weight: normal;
                margin-top: 5px;
                margin-bottom: 2px;
                cursor: default;
            }

            &:not(:first-child) {
                .button {
                    display: flex;
                    align-items: center;
                    height: 1.95em !important;
                }
            }
            
            .field {
                align-items: center;
            }

            .gray-icon,
            .gray-icon .icon {
                color: var(--tainacan-info-color) !important;
                padding-right: 10px;
                height: 1.125em !important;
            }
            .gray-icon .icon i::before, 
            .gray-icon i::before {
                max-width: 1.25em;
            }

            .icon {
                pointer-events: all;
                cursor: pointer;
                color: var(--tainacan-blue5);
                height: 27px;
                font-size: 1.125em !important;
                height: 1.75em
            }

            .dropdown-menu {
                display: block;

                div.dropdown-content {
                    padding: 0;

                    .metadata-options-container {
                        max-height: 288px;
                        overflow: auto;
                    }
                    .dropdown-item {
                        padding: 0.25em 1.0em 0.25em 0.75em; 
                    }
                    .dropdown-item span{
                        vertical-align: middle;
                    }      
                    .dropdown-item-apply {
                        width: 100%;
                        border-top: 1px solid var(--tainacan-skeleton-color);
                        padding: 8px 12px;
                        text-align: right;
                    }
                    .dropdown-item-apply .button {
                        width: 100%;
                    }
                }
            }
        }

        @media screen and (max-width: 769px) {
            height: 120px;
            margin-top: -0.5em;
            padding-top: 0.9em;

            .header-item:not(:last-child) {
                padding-right: 0.2em;
            }
        }
    }
    .tabs {
        padding-top: 20px;
        margin-bottom: 20px;
        padding-left: var(--tainacan-one-column);
        padding-right: var(--tainacan-one-column);
    }
    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        height: auto;
    }
    @media screen and (max-width: 769px) {
        .table-container {
            padding-left: 0;
            padding-right: 0;
        }
    }

</style>


