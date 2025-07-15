<template>
    <div class="page-container">
        
        <b-loading 
                v-model="isLoading"
                :is-full-page="false" />
        
        <tainacan-external-link 
                :link-label="$i18n.get('label_view_collections_on_website')"
                :link-url="repositoryCollectionsURL" />

        <tainacan-title :is-sticky="true">
            <h1>
                {{ $route.meta.title }}
            </h1>

            <!-- New Collection button -->
            <div 
                    v-if="!$adminOptions.hideCollectionsListCreationDropdown && $userCaps.hasCapability('tnc_rep_edit_collections')"
                    class="header-item">
                <b-dropdown
                        id="collection-creation-options-dropdown"
                        aria-role="list"
                        :mobile-modal="true"
                        trap-focus
                        append-to-body>
                    <template #trigger>
                        <button class="button is-secondary">
                            <div>{{ $i18n.getFrom('collections', 'new_item') }}</div>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                    </template>   
                    <b-dropdown-item aria-role="listitem">
                        <router-link
                                v-slot="{ navigate }"
                                :to="{ path: $routerHelper.getNewCollectionPath() }"
                                custom>
                            <div 
                                    id="a-create-collection"
                                    @click="navigate()">
                                {{ $i18n.get('new_blank_collection') }}
                                <br>
                                <small class="is-small">{{ $i18n.get('info_choose_your_metadata') }}</small>
                            </div>
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
                    <b-dropdown-item 
                            v-if="$userCaps.hasCapability('manage_tainacan')"
                            aria-role="listitem">
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
        </tainacan-title>

        <div class="sub-header tainacan-sub-header--sticky">
          
            <!-- Textual Search -------------->
            <b-field 
                    id="collection-page-search"
                    class="header-item">
                <b-input 
                        v-model="searchQuery"
                        :placeholder="$i18n.get('instruction_search')"
                        type="search"
                        size="is-small"
                        :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('collections')"
                        autocomplete="on"
                        icon-right="magnify"
                        icon-right-clickable
                        @keyup.enter="searchCollections()"
                        @icon-right-click="searchCollections()" />
            </b-field>

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
                        <template #trigger>
                            <button
                                    :aria-label="collectionTaxonomy['name']"
                                    class="button is-white">
                                <span>{{ collectionTaxonomy['name'] }}</span>
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                                </span>
                            </button>
                        </template>
                        <div class="metadata-options-container">
                            <b-dropdown-item
                                    v-for="(collectionTaxonomyTerm, index) in collectionTaxonomy['terms']"
                                    :key="index"
                                    class="control"
                                    custom
                                    aria-role="listitem"
                                    :class="{ 'is-active': collectionTaxonomyTerm.enabled }">
                                <b-checkbox
                                        v-model="collectionTaxonomyTerm.enabled"
                                        :native-value="collectionTaxonomyTerm.enabled">
                                    {{ collectionTaxonomyTerm.name }}
                                </b-checkbox>
                            </b-dropdown-item>   
                        </div>
                        <div class="dropdown-item-apply">
                            <button 
                                    aria-controls="collections-list-results"
                                    class="button is-success"
                                    @click="onChangeCollectionTaxonomyTerms(taxonomyValue)">
                                {{ $i18n.get('label_apply_changes') }}
                            </button>
                        </div>  
                    </b-dropdown>
                </b-field>
            </template>

            <!-- Author filtering options ----  -->
            <b-field   
                    id="collections-page-author-filter"
                    class="header-item">
                <label 
                        id="author-filter-switch-label"
                        v-tooltip="{
                            content: $i18n.get('label_show_only_created_by_me'),
                            autoHide: true,
                            placement: 'auto',
                            popperClass: ['tainacan-tooltip', 'tooltip']
                        }"
                        class="label">{{ $i18n.get('label_authored_by_me') }}&nbsp;</label>
                <b-switch
                        v-model="authorFilter"
                        size="is-small"
                        class="author-filter-switch"
                        aria-labelledby="author-filter-switch-label"
                        :disabled="collections.length <= 0 && isLoading"
                        :true-value="'current-author'"
                        :false-value="''"
                        :label="$i18n.get('label_authored_by_me')"
                        @update:model-value="onChangeAuthorFilter" />
                
            </b-field>

            <!-- Sorting options ----  -->
            <b-field 
                    id="collections-page-sorting-options"
                    class="header-item">
                <b-dropdown
                        ref="sortingDropdown" 
                        :mobile-modal="true"
                        :multiple="false"
                        class="show sorting-options-dropdown"
                        aria-role="list"
                        trap-focus
                        position="is-bottom-left"
                        :close-on-click="false"
                        :disabled="collections.length <= 0 || isLoading"
                        @active-change="() => { newOrder = order; newOrderBy = orderBy; }">
                    <template #trigger>
                        <button
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: $i18n.getWithVariables('info_sorting_%s_by_%s', [order == 'asc' ? $i18n.get('label_ascending') : $i18n.get('label_descending'), orderByName]),
                                    autoHide: false,
                                    html: true,
                                    placement: 'auto-start',
                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']
                                }"
                                :aria-label="$i18n.get('label_sorting')"
                                class="button is-white">
                            <span class="is-small gray-icon">
                                <i 
                                        :class="order == 'desc' ? 'tainacan-icon-sortdescending' : 'tainacan-icon-sortascending'"
                                        class="tainacan-icon" />
                            </span>
                            &nbsp;
                            <span class="is-hidden-touch is-hidden-desktop-only">{{ $i18n.get('label_sorting') }}</span>
                            <span class="is-hidden-widescreen">{{ $i18n.get('label_sort') }}</span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                    </template>
                    <div class="sorting-options-container">
                        <div class="sorting-options-container-direction">
                            <b-dropdown-item
                                    aria-controls="collections-list-results"
                                    role="button"
                                    :class="{ 'is-active': newOrder == 'desc' }"
                                    :value="'desc'"
                                    aria-role="listitem"
                                    @click="newOrder = 'desc'">
                                <span class="icon gray-icon">
                                    <i class="tainacan-icon tainacan-icon-sortdescending" />
                                </span>
                                <span>{{ $i18n.get('label_descending') }}</span>
                            </b-dropdown-item>
                            <b-dropdown-item
                                    aria-controls="collections-list-results"
                                    role="button"
                                    :class="{ 'is-active': newOrder == 'asc' }"
                                    :value="'asc'"
                                    aria-role="listitem"
                                    @click="newOrder = 'asc'">
                                <span class="icon gray-icon">
                                    <i class="tainacan-icon tainacan-icon-sortascending" />
                                </span>
                                <span>{{ $i18n.get('label_ascending') }}</span>
                            </b-dropdown-item>
                        </div>
                        <div class="sorting-options-container-orderby">
                            <template 
                                    v-for="option in sortingOptions"
                                    :key="option.value">
                                <b-dropdown-item
                                        aria-controls="collections-list-results"
                                        role="button"
                                        :class="{ 'is-active': newOrderBy == option.value }"
                                        :value="option.value"
                                        aria-role="listitem"
                                        @click="newOrderBy = option.value">
                                    {{ option.label }}
                                </b-dropdown-item>
                            </template>
                        </div>
                    </div>
                    <div class="dropdown-item-apply">
                        <button 
                                aria-controls="items-list-results"
                                class="button is-success"
                                @click="onChangeOrderAndOrderBy(newOrder, newOrderBy)">
                            {{ $i18n.get('label_apply_changes') }}
                        </button>
                    </div>  
                </b-dropdown>
            </b-field>

        </div>

        <div class="above-subheader">
            <div class="tabs">
                <ul>
                    <li 
                            v-tooltip="{
                                content: $i18n.get('info_collections_tab_all'),
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']
                            }"
                            :class="{ 'is-active': status == undefined || status == '' || status == 'publish,private,pending,draft' }"
                            @click="onChangeTab('')">
                        <a style="font-weight: bold;">
                            {{ `${$i18n.get('label_all_collections')}` }}
                            <span class="has-text-gray">&nbsp;{{ `${` ${repositoryTotalCollections ? `(${Number(repositoryTotalCollections.pending) + Number(repositoryTotalCollections.private) + Number(repositoryTotalCollections.publish)})` : '' }`}` }}</span>
                        </a>
                    </li>
                    <li 
                            v-for="(statusOption, index) of statusOptionsForCollections"
                            :key="index"
                            v-tooltip="{
                                content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('collections')]),
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']
                            }"
                            :class="{ 'is-active': status == statusOption.slug}"
                            :style="{ marginRight: statusOption.slug == 'pending' ? 'auto' : '', marginLeft: statusOption.slug == 'trash' ? 'auto' : '' }"
                            @click="onChangeTab(statusOption.slug)">
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
                            <p v-if="status == undefined || status == ''">
                                {{ $i18n.get('info_no_collection_created') }}
                            </p>
                            <p v-else>
                                {{ $i18n.get('info_no_collections_' + status) }}
                            </p>
                            <p v-if="searchQuery">
                                {{ $i18n.get('info_try_empting_the_textual_search') }}
                            </p>
                            <p v-if="authorFilter !== '' && !searchQuery">
                                {{ $i18n.get('info_try_selecting_all_collections_in_filter') }}
                            </p>
                            <div v-if="!$adminOptions.hideCollectionsListCreationDropdown && $userCaps.hasCapability('tnc_rep_edit_collections') && status == undefined || status == ''">
                                <b-dropdown 
                                        id="collection-creation-options-dropdown"
                                        aria-role="list"
                                        trap-focus>
                                    <template #trigger>
                                        <button class="button is-secondary">
                                            <div>{{ $i18n.getFrom('collections', 'new_item') }}</div>
                                            <span class="icon">
                                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                                            </span>
                                        </button>
                                    </template>
                                    <b-dropdown-item aria-role="listitem">
                                        <router-link
                                                v-slot="{ navigate }"
                                                :to="{ path: $routerHelper.getNewCollectionPath() }"
                                                custom>
                                            <div 
                                                    id="a-create-collection"
                                                    @click="navigate()">
                                                {{ $i18n.get('new_blank_collection') }}
                                                <br>
                                                <small class="is-small">{{ $i18n.get('info_choose_your_metadata') }}</small>
                                            </div>
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
                        v-if="collections.length > 0"
                        class="pagination-area">
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
                                    :aria-label="$i18n.get('label_collections_per_page')"
                                    :model-value="collectionsPerPage"
                                    :disabled="collections.length <= 0 || collectionsPerPageOptions.length <= 1" 
                                    @update:model-value="onChangeCollectionsPerPage">
                                <option 
                                        v-for="perPageOption of collectionsPerPageOptions"
                                        :key="perPageOption"
                                        :value="perPageOption">
                                    {{ perPageOption }}
                                </option>
                            </b-select>
                        </b-field>
                    </div>
                    <div class="pagination"> 
                        <b-pagination
                                v-model="page"
                                :total="totalCollections"
                                order="is-centered"
                                size="is-small"
                                :per-page="collectionsPerPage"
                                :aria-next-label="$i18n.get('label_next_page')"
                                :aria-previous-label="$i18n.get('label_previous_page')"
                                :aria-page-label="$i18n.get('label_page')"
                                :aria-current-label="$i18n.get('label_current_page')"
                                @change="onPageChange" /> 
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
            newOrder: 'desc',
            newOrderBy: 'date',
            searchQuery: '',
            authorFilter: '',
            sortingOptions: [
                { label: this.$i18n.get('label_title'), value: 'title' },
                { label: this.$i18n.get('label_creation_date'), value: 'date' },
                { label: this.$i18n.get('label_modification_date'), value: 'modified' }
            ],
            maxCollectionsPerPage: tainacan_plugin.api_max_items_per_page ? Number(tainacan_plugin.api_max_items_per_page) : 96,
            repositoryCollectionsURL: tainacan_plugin.theme_collection_list_url
        }
    },
    computed: {
        ...mapGetters('collection', {
            'collections': 'getCollections',
            'repositoryTotalCollections': 'getRepositoryTotalCollections'
        }),
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
        },
        statusOptionsForCollections() {
            return this.$statusHelper.getStatuses().filter((status) => status.slug != 'draft' && (status.slug != 'private' || (status.slug == 'private' && this.$userCaps.hasCapability('tnc_rep_read_private_collections'))));
        },
        orderByName() {
            const selectedOrderOption = this.sortingOptions.find( aOption => aOption.value == this.orderBy);
            if ( selectedOrderOption )
                return selectedOrderOption.label;

            return this.orderBy;
        },
        collectionsPerPageOptions() {
            const defaultCollectionsPerPageOptions = [];
            
            if ( 12 < this.maxCollectionsPerPage )
                defaultCollectionsPerPageOptions.push(12);
            
            if ( 24 < this.maxCollectionsPerPage )
                defaultCollectionsPerPageOptions.push(24);
            
            if ( 48 < this.maxCollectionsPerPage )
                defaultCollectionsPerPageOptions.push(48);
            
            defaultCollectionsPerPageOptions.push(this.maxCollectionsPerPage);

            if (!isNaN(this.collectionsPerPage) && !defaultCollectionsPerPageOptions.includes(this.collectionsPerPage))
                defaultCollectionsPerPageOptions.push(Number(this.collectionsPerPage));
            
            return defaultCollectionsPerPageOptions.sort((a,b) => a - b);
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

        if (this.authorFilter != this.$userPrefs.get('collections_author_filter'))
            this.authorFilter = this.$userPrefs.get('collections_author_filter');
        if (this.authorFilter === undefined) {
            this.authorFilter = '';
            this.$userPrefs.set('collections_author_filter', '');
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
            'getCollectionTaxonomies'
        ]),
        onChangeTab(status) {
            if ( status != this.status ) {
                this.page = 1;
                this.status = status;
                this.loadCollections();
            }
        },
        onChangeOrderAndOrderBy(newOrder, newOrderBy) {
            this.$userPrefs.set('collections_order', newOrder)
                .then((newOrder) => {
                    this.order = newOrder;
                })
                .catch(() => {
                    this.$console.log("Error settings user prefs for collections order")
                });
            this.$userPrefs.set('collections_order_by', newOrderBy)
                .then((newOrderBy) => {
                    this.orderBy = newOrderBy;
                })
                .catch(() => {
                    this.$console.log("Error settings user prefs for collections orderby")
                });
            this.page = 1;
            this.order = newOrder;
            this.orderBy = newOrderBy;
            this.$refs['sortingDropdown'].toggle();
            this.loadCollections();
        },
        onChangeAuthorFilter(newAuthorFilter) {
            this.$userPrefs.set('collections_author_filter', newAuthorFilter)
                .then((newAuthorFilter) => {
                    this.authorFilter = newAuthorFilter;
                })
                .catch(() => {
                    this.$console.log("Error settings user prefs for collections author filter")
                });
            this.page = 1;
            this.authorFilter = newAuthorFilter;
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
                this.page = 1;
                this.collectionsPerPage = value;
                this.loadCollections();
            }
        },
        onPageChange(page) {
            if ( page != this.page ) {
                this.page = page;
                this.loadCollections();
            }
        },
        onChangeCollectionTaxonomyTerms(taxonomyValue) {
            this.page = 1;
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
                status: this.status ? this.status : 'publish,private,pending,draft',
                contextEdit: true, 
                order: this.order,
                orderby: this.orderBy,
                search: this.searchQuery,
                collectionTaxonomies: this.collectionTaxonomies,
                authorid: this.authorFilter === 'current-author' && tainacan_user.data && tainacan_user.data.ID ? tainacan_user.data.ID : ''
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
                component: AvailableImportersModal,
                hasModalCard: true,
                trapFocus: true,
                customClass: 'tainacan-modal',
                canCancel: ['escape', 'outside']
            });
        },
        onOpenCollectionCreationModal() {
            this.$buefy.modal.open({
                component: CollectionCreationModal,
                hasModalCard: true,
                trapFocus: true,
                customClass: 'tainacan-modal',
                canCancel: ['escape', 'outside']
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

    .sub-header {
        flex-wrap: wrap;
        gap: 8px;

        .header-item {
            margin-bottom: 0 !important;
            min-height: 1.875em;

            &:first-child {
                margin-right: auto;
            }
            &:not(:last-child) {
                padding-right: 0.875em;
            }

            .label {
                font-size: 0.875em;
                font-weight: normal;
                margin-top: 2px;
                margin-bottom: 2px;
                cursor: default;
                display: flex;
                align-items: center;
            }
            
            .field {
                align-items: center;
            }

            .gray-icon .icon i::before, 
            .gray-icon i::before {
                font-size: 1.3125em !important;
                color: var(--tainacan-info-color) !important;
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

                    .sorting-options-container,
                    .metadata-options-container {
                        max-height: 288px;
                        overflow: auto;
                    }
                    .sorting-options-container-direction {
                        position: sticky;
                        top: 0;
                        display: flex;
                        background-color: var(--tainacan-background-color);
                        border-bottom: 1px solid var(--tainacan-input-border-color);
                        padding: 8px 12px;
                        z-index: 1;

                        & > .dropdown-item {
                            border: 1px solid var(--tainacan-primary);

                            &:first-child {
                                border-top-left-radius: var(--tainacan-button-border-radius);
                                border-bottom-left-radius: var(--tainacan-button-border-radius);
                            }
                            &:last-child {
                                border-top-right-radius: var(--tainacan-button-border-radius);
                                border-bottom-right-radius: var(--tainacan-button-border-radius);
                            }
                        }
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
                        padding: 8px;
                        text-align: right;
                    }
                    .dropdown-item-apply .button {
                        width: 100%;
                    }
                }
            }
        }

        #collections-page-author-filter {
            display: flex;
            align-items: center;
        }

        @media screen and (max-width: 768px) {
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
    @media screen and (max-width: 768px) {
        .table-container {
            padding-left: 0;
            padding-right: 0;
        }
    }

</style>


