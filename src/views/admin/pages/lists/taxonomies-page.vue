<template>
    <div class="tainacan-repository-level-colors page-container">

        <tainacan-external-link 
                :link-label="$i18n.get('label_view_taxonomies_on_website')"
                :link-url="repositoryTaxonomiesURL" />

        <tainacan-title :is-sticky="true">
            <h1>
                {{ $route.meta.title }}
            </h1>

            <!-- New Taxonomy Button ----  -->
            <div 
                    v-if="$userCaps.hasCapability('tnc_rep_edit_taxonomies')"
                    class="header-item">
                <router-link
                        v-slot="{ navigate }"
                        :to="{ path: $routerHelper.getNewTaxonomyPath() }"
                        custom>
                    <button 
                            id="button-create-taxonomy"
                            type="button"
                            role="link" 
                            class="button is-secondary"
                            @click="navigate()"
                            @keydown.enter="navigate()"
                            @keydown.space="navigate()">
                        {{ $i18n.getFrom('taxonomies', 'new_item') }}
                    </button>
                </router-link>
            </div>

        </tainacan-title>
        
        <div class="sub-header tainacan-sub-header--sticky">

            <!-- Textual Search -------------->
            <b-field class="header-item">
                <b-input 
                        v-model="searchQuery"
                        :placeholder="$i18n.get('instruction_search_and_press_enter')"
                        type="search"
                        size="is-small"
                        :aria-label="$i18n.get('instruction_search_and_press_enter')"
                        autocomplete="on"
                        icon-right="magnify"
                        icon-right-clickable
                        @keyup.enter="searchTaxonomies()"
                        @icon-right-click="searchTaxonomies()" />
            </b-field>

            <!-- Collection filter ----  -->
            <b-field 
                    id="taxonomies-page-collection-filter"
                    class="header-item">
                <b-autocomplete
                        v-model="collectionSearch"
                        v-a11y-autocomplete="{ appendToBody: true }"
                        :placeholder="$i18n.get('label_filter_by_collection')"
                        :aria-label="$i18n.get('label_filter_by_collection')"
                        :data="collections"
                        field="name"
                        size="is-small"
                        clearable
                        icon-right="menu-down"
                        :loading="isFetchingCollections"
                        :append-to-body="true"
                        open-on-focus
                        check-infinite-scroll
                        @select="onSelectCollection"
                        @focus="browseCollections"
                        @active="onCollectionSuggestionsActive"
                        @typing="fetchCollections"
                        @infinite-scroll="fetchMoreCollections">
                    <template #empty>
                        {{ $i18n.get('info_no_options_found') }}
                    </template>
                </b-autocomplete>
            </b-field>

            <!-- Sorting options ----  -->
            <b-field 
                    id="taxonomies-page-sorting-options"
                    class="header-item">
                <b-dropdown
                        ref="sortingDropdown" 
                        v-a11y-dropdown
                        :trigger-tabindex="-1"
                        :mobile-modal="true"
                        :multiple="false"
                        class="show sorting-options-dropdown"
                        trap-focus
                        position="is-bottom-left"
                        :close-on-click="false"
                        :disabled="taxonomies.length <= 0 || isLoading"
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
                                :disabled="taxonomies.length <= 0 || isLoading"
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
                            <span 
                                    aria-hidden="true"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                    </template>
                    <div class="sorting-options-container">
                        <div class="sorting-options-container-direction">
                            <b-dropdown-item
                                    aria-controls="taxonomies-list-results"
                                    role="button"
                                    :class="{ 'is-active': newOrder == 'desc' }"
                                    :value="'desc'"
                                    @click="newOrder = 'desc'"
                                    @keydown.enter.prevent="newOrder = 'desc'"
                                    @keydown.space.prevent="newOrder = 'desc'">
                                <span 
                                        aria-hidden="true"
                                        class="icon gray-icon">
                                    <i class="tainacan-icon tainacan-icon-sortdescending" />
                                </span>
                                <span>{{ $i18n.get('label_descending') }}</span>
                            </b-dropdown-item>
                            <b-dropdown-item
                                    aria-controls="taxonomies-list-results"
                                    role="button"
                                    :class="{ 'is-active': newOrder == 'asc' }"
                                    :value="'asc'"
                                    @click="newOrder = 'asc'"
                                    @keydown.enter.prevent="newOrder = 'asc'"
                                    @keydown.space.prevent="newOrder = 'asc'">
                                <span 
                                        aria-hidden="true"
                                        class="icon gray-icon">
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
                                        aria-controls="taxonomies-list-results"
                                        role="button"
                                        :class="{ 'is-active': newOrderBy == option.value }"
                                        :value="option.value"
                                        @click="newOrderBy = option.value"
                                        @keydown.enter.prevent="newOrderBy = option.value"
                                        @keydown.space.prevent="newOrderBy = option.value">
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
            <b-loading
                    v-model="isLoading" 
                    :is-full-page="true" 
                    :can-cancel="false" />
            <div class="tabs">
                <ul
                        v-a11y-tabs
                        role="tablist"
                        data-orientation="horizontal">
                    <li 
                            :tabindex="-1"
                            :class="{ 'is-active': status == undefined || status == ''|| status == 'publish,private,pending,draft'}">
                        <a
                                id="taxonomies-status-tab-all"
                                v-tooltip="{
                                    content: $i18n.get('info_taxonomies_tab_all'),
                                    autoHide: true,
                                    placement: 'auto',
                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']
                                }"
                                role="tab"
                                :aria-selected="status == undefined || status == '' || status == 'publish,private,pending,draft'"
                                :tabindex="(status == undefined || status == '' || status == 'publish,private,pending,draft') ? 0 : -1"
                                style="font-weight: bold;"
                                @click="onChangeTab('')">
                            {{ `${$i18n.get('label_all_taxonomies')}` }}
                            <span class="has-text-dark">&nbsp;{{ repositoryTotalTaxonomies ? `(${Number(repositoryTotalTaxonomies.private) + Number(repositoryTotalTaxonomies.publish) + Number(repositoryTotalTaxonomies.draft)})` : '' }}</span>
                        </a>
                    </li>
                    <li 
                            v-for="(statusOption, index) of statusOptionsForTaxonomies"
                            :key="index"
                            :tabindex="-1"
                            :class="{ 'is-active': status == statusOption.slug}"
                            :style="{ marginInlineEnd: statusOption.slug == 'draft' ? 'auto' : '', marginInlineStart: statusOption.slug == 'trash' ? 'auto' : '' }">
                        <a
                                :id="'taxonomies-status-tab-' + statusOption.slug"
                                v-tooltip="{
                                    content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('taxonomies')]),
                                    autoHide: true,
                                    placement: 'auto',
                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']
                                }"
                                role="tab"
                                :aria-selected="status == statusOption.slug"
                                :tabindex="status == statusOption.slug ? 0 : -1"
                                @click="onChangeTab(statusOption.slug)">
                            <span 
                                    v-if="$statusHelper.hasIcon(statusOption.slug)"
                                    class="icon has-text-dark">
                                <i 
                                        class="tainacan-icon tainacan-icon-1-125em"
                                        :class="$statusHelper.getIcon(statusOption.slug)"
                                        aria-hidden="true" />
                            </span>
                            {{ statusOption.name }}
                            <span class="has-text-dark">&nbsp;{{ repositoryTotalTaxonomies ? `(${repositoryTotalTaxonomies[statusOption.slug]})` : '' }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <taxonomies-list
                        :is-loading="isLoading"
                        :total="total"
                        :status="status"
                        :page="page"
                        :taxonomies-per-page="taxonomiesPerPage"
                        :taxonomies="taxonomies" />
                
                <!-- Empty state image -->
                <div v-if="taxonomies.length <= 0 && !isLoading">
                    <section class="section">
                        <div class="content has-text-dark has-text-centered">
                            <p>
                                <span 
                                        aria-hidden="true"
                                        class="icon is-medium">
                                    <i class="tainacan-icon tainacan-icon-30px tainacan-icon-terms" />
                                </span>
                            </p>
                            <p v-if="collectionId">
                                {{ $i18n.get('info_no_taxonomy_used_by_collection') }}
                            </p>
                            <p v-else-if="status == undefined || status == ''">
                                {{ $i18n.get('info_no_taxonomy_created') }}
                            </p>
                            <p v-else>
                                {{ $i18n.getNoEntitiesMessageForStatus('taxonomies', status) }}
                            </p>
                            <router-link
                                    v-if="(status == undefined || status == '') && !collectionId"
                                    v-slot="{ navigate }"
                                    :to="{ path: $routerHelper.getNewTaxonomyPath() }"
                                    custom>
                                <button
                                        id="button-create-taxonomy"
                                        role="link"
                                        type="button"
                                        class="button is-secondary"
                                        @click="navigate()"
                                        @keydown.enter="navigate()"
                                        @keydown.space="navigate()">
                                    {{ $i18n.getFrom('taxonomies', 'new_item') }}
                                </button>
                            </router-link>
                        </div>
                    </section>
                </div>
                <!-- Footer -->
                <div 
                        v-if="taxonomies.length > 0" 
                        class="pagination-area">
                    <div class="shown-items">
                        {{ showingTaxonomiesText }}
                    </div>
                    <div class="items-per-page">
                        <b-field 
                                horizontal 
                                :label="$i18n.get('label_taxonomies_per_page')">
                            <b-select
                                    :aria-label="$i18n.get('label_taxonomies_per_page')"
                                    :model-value="taxonomiesPerPage"
                                    :disabled="taxonomies.length <= 0 || taxonomiesPerPageOptions.length <= 1"
                                    @update:model-value="onChangePerPage">
                                <option 
                                        v-for="perPageOption of taxonomiesPerPageOptions"
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
                                :total="total"
                                order="is-centered"
                                size="is-small"
                                :aria-label="$i18n.get('label_pagination')"
                                :per-page="taxonomiesPerPage"
                                :aria-next-label="$i18n.get('label_next_page')"
                                :aria-previous-label="$i18n.get('label_previous_page')"
                                :aria-page-label="$i18n.get('label_page')"
                                @change="onPageChange" />
                    
                    </div>
                </div>
            </div>
        </div>    
    </div>
</template>

<script>
    import TaxonomiesList from "../../components/lists/taxonomies-list.vue";
    import { mapActions, mapGetters } from 'vuex';
    import { tainacanApi, CancelToken, isCancel } from '../../js/axios';

    export default {
        name: 'TaxonomyPage',
        components: {
            TaxonomiesList
        },
        data(){
            return {
                isLoading: false,
                total: 0,
                page: 1,
                taxonomiesPerPage: 12,
                status: '',
                order: 'asc',
                orderBy: 'date',
                newOrder: 'asc',
                newOrderBy: 'date',
                searchQuery: '',
                collectionId: null,
                collections: [],
                collectionSearch: '',
                committedCollectionName: '',
                collectionSearchQuery: '',
                collectionsPage: 1,
                totalCollectionPages: 0,
                isFetchingCollections: false,
                collectionSearchCancel: null,
                sortingOptions: [
                    { label: this.$i18n.get('label_title'), value: 'title' },
                    { label: this.$i18n.get('label_creation_date'), value: 'date' },
                ],
                maxTaxonomiesPerPage: tainacan_plugin.api_max_items_per_page ? Number(tainacan_plugin.api_max_items_per_page) : 96,
                repositoryTaxonomiesURL: tainacan_plugin.theme_taxonomy_list_url
            }
        },
        computed: {
            ...mapGetters('taxonomy', {
                'taxonomies': 'get',
                'repositoryTotalTaxonomies': 'getRepositoryTotalTaxonomies'
            }),
            statusOptionsForTaxonomies() {
                return this.$statusHelper.getStatuses().filter((status) => status.slug != 'draft' && (status.slug != 'private' || (status.slug == 'private' && this.$userCaps.hasCapability('tnc_rep_read_private_taxonomies'))));
            },
            orderByName() {
                const selectedOrderOption = this.sortingOptions.find( aOption => aOption.value == this.orderBy);
                if ( selectedOrderOption )
                    return selectedOrderOption.label;

                return this.orderBy;
            },
            showingTaxonomiesText() {
                const first = this.taxonomiesPerPage * (this.page - 1) + 1;
                const last = this.getLastTaxonomyNumber();
                const total = this.total;
                
                return this.$i18n.getWithVariables('info_showing_taxonomies_range', [first, last, total]);
            },
            taxonomiesPerPageOptions() {
                const defaultTaxonomiesPerPageOptions = [];
                
                if ( 12 <= this.maxTaxonomiesPerPage )
                    defaultTaxonomiesPerPageOptions.push(12);
                
                if ( 24 <= this.maxTaxonomiesPerPage )
                    defaultTaxonomiesPerPageOptions.push(24);
                
                if ( 48 <= this.maxTaxonomiesPerPage )
                    defaultTaxonomiesPerPageOptions.push(48);
                
                if ( !defaultTaxonomiesPerPageOptions.includes(this.maxTaxonomiesPerPage) )
                    defaultTaxonomiesPerPageOptions.push(this.maxTaxonomiesPerPage);

                if (!isNaN(this.taxonomiesPerPage) && !defaultTaxonomiesPerPageOptions.includes(this.taxonomiesPerPage))
                    defaultTaxonomiesPerPageOptions.push(Number(this.taxonomiesPerPage));
                
                return defaultTaxonomiesPerPageOptions.sort((a,b) => a - b);
            }
        },
        watch: {
            // An empty field, from the clear button or from erasing the text, removes the filter
            collectionSearch(newSearch) {
                if (newSearch === '' && this.collectionId) {
                    this.committedCollectionName = '';
                    this.setCollectionFilter(null);
                }
            }
        },
        created() {
            this.taxonomiesPerPage = this.$userPrefs.get('taxonomies_per_page');
        },
        mounted(){
            if (this.taxonomiesPerPage != this.$userPrefs.get('taxonomies_per_page'))
                this.taxonomiesPerPage = this.$userPrefs.get('taxonomies_per_page');
            if (!this.taxonomiesPerPage) {
                this.taxonomiesPerPage = 12;
                this.$userPrefs.set('taxonomies_per_page', 12);
            }

            if (this.order != this.$userPrefs.get('taxonomies_order'))
                this.order = this.$userPrefs.get('taxonomies_order');
            if (!this.order) {
                this.order = 'asc';
                this.$userPrefs.set('taxonomies_order', 'asc');
            }


            if (this.orderBy != this.$userPrefs.get('taxonomies_order_by'))
                this.orderBy = this.$userPrefs.get('taxonomies_order_by');
            if (!this.orderBy) {
                this.orderBy = 'title';
                this.$userPrefs.set('taxonomies_order_by', 'title');
            }

            // A collection in the URL is checked first, so the list is not requested with an unreadable one
            this.restoreCollectionFilterFromRoute()
                .then(() => this.load());
        },
        beforeUnmount() {
            this.cancelCollectionSearch();
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetch',
            ]),
            onChangeTab(status) {
                if ( status != this.status ) {
                    this.page = 1;
                    this.status = status;
                    this.load();
                }
            },
            onChangeOrderAndOrderBy(newOrder, newOrderBy) {
                this.$userPrefs.set('taxonomies_order', newOrder)
                    .then((newOrder) => {
                        this.order = newOrder;
                    })
                    .catch(() => undefined);
                this.$userPrefs.set('taxonomies_order_by', newOrderBy)
                    .then((newOrderBy) => {
                        this.orderBy = newOrderBy;
                    })
                    .catch(() => undefined);
                this.page = 1;
                this.order = newOrder;
                this.orderBy = newOrderBy;
                this.$refs['sortingDropdown'].toggle();
                this.load();
            },
            onChangePerPage(value) {
                if ( value != this.taxonomiesPerPage ) { 
                    this.$userPrefs.set('taxonomies_per_page', value)
                        .then((newValue) => {
                            this.taxonomiesPerPage = newValue;
                        })
                        .catch(() => undefined);
                    this.page = 1;
                    this.taxonomiesPerPage = value;
                    this.load();
                }
            },
            onPageChange() {
                this.load();
            },
            load() {
                this.isLoading = true;

                this.fetch({ 
                    page: this.page, 
                    taxonomiesPerPage: this.taxonomiesPerPage, 
                    status: this.status ? this.status : 'publish,private,pending',
                    order: this.order,
                    orderby: this.orderBy,
                    search: this.searchQuery,
                    collectionId: this.collectionId
                })
                    .then((res) => {
                        this.isLoading = false;
                        this.total = res.total;
                    })
                    .catch(() => {
                        this.isLoading = false;
                    });
            },
            getLastTaxonomyNumber() {
                let last = (Number(this.taxonomiesPerPage * (this.page - 1)) + Number(this.taxonomiesPerPage));
                return last > this.total ? this.total : last;
            },
            searchTaxonomies() {
                this.page = 1;
                this.load();
            },
            onSelectCollection(collection) {
                // Typing a new search unselects the option, so only a selection changes the filter here
                if (!collection || !collection.id)
                    return;

                this.committedCollectionName = collection.name;
                this.setCollectionFilter(collection.id);
            },
            setCollectionFilter(collectionId) {
                if (collectionId == this.collectionId)
                    return;

                this.collectionId = collectionId;
                this.page = 1;
                this.updateCollectionFilterInRoute();
                this.load();
            },
            updateCollectionFilterInRoute() {
                // Keeps the filter in the URL, so it survives going back from a taxonomy and can be shared
                const query = { ...this.$route.query };

                if (this.collectionId)
                    query.collection_id = String(this.collectionId);
                else
                    delete query.collection_id;

                if (query.collection_id !== this.$route.query.collection_id)
                    this.$router.replace({ query }).catch(() => undefined);
            },
            restoreCollectionFilterFromRoute() {
                const routeCollectionId = parseInt(this.$route.query.collection_id, 10);

                if (isNaN(routeCollectionId) || routeCollectionId <= 0) {
                    if (this.$route.query.collection_id !== undefined)
                        this.updateCollectionFilterInRoute();
                    return Promise.resolve();
                }

                return tainacanApi.get('/collections/' + routeCollectionId + '?context=edit&fetch_only=name,id')
                    .then(res => {
                        if (!res.data || !res.data.id)
                            throw new Error('Collection not found.');

                        // Sets the name first, so the empty search watcher does not clear the filter
                        this.committedCollectionName = res.data.name;
                        this.collectionSearch = res.data.name;
                        this.collectionId = Number(res.data.id);
                    })
                    .catch((error) => {
                        // A collection that does not exist, or that the user cannot read, does not filter the list.
                        // The URL is fixed silently, so the error is claimed before the status snackbar opens.
                        this.$claimApiError(error);
                        this.collectionId = null;
                        this.updateCollectionFilterInRoute();
                    });
            },
            onCollectionSuggestionsActive(isOpen) {
                if (isOpen)
                    return;

                // Closing the suggestions without a selection keeps showing the filtered collection
                if (this.collectionSearch !== this.committedCollectionName)
                    this.collectionSearch = this.committedCollectionName;
            },
            cancelCollectionSearch() {
                if (this.collectionSearchCancel) {
                    this.collectionSearchCancel.cancel('Collection search canceled.');
                    this.collectionSearchCancel = null;
                }
            },
            beginCollectionSearch() {
                this.cancelCollectionSearch();
                this.collectionSearchCancel = CancelToken.source();
            },
            browseCollections() {
                this.collectionSearchQuery = '';
                this.collectionsPage = 1;
                this.totalCollectionPages = 0;
                this.beginCollectionSearch();
                this.isFetchingCollections = true;
                this.loadCollectionPage('');
            },
            fetchCollections: _.debounce(function(search) {
                const query = search || '';

                if (this.committedCollectionName && query === this.committedCollectionName)
                    return;

                if (query !== this.collectionSearchQuery) {
                    this.collectionSearchQuery = query;
                    this.collectionsPage = 1;
                    this.totalCollectionPages = 0;
                }

                if (this.totalCollectionPages > 0 && this.collectionsPage > this.totalCollectionPages)
                    return;

                this.beginCollectionSearch();
                this.isFetchingCollections = true;
                this.loadCollectionPage(query);
            }, 500),
            fetchMoreCollections: _.debounce(function() {
                this.fetchCollections(this.collectionSearchQuery);
            }, 250),
            loadCollectionPage(query) {
                const source = this.collectionSearchCancel;
                let endpoint = '/collections?paged=' + this.collectionsPage + '&perpage=12&context=edit&fetch_only=name,id&order=asc&orderby=title';
                if (query)
                    endpoint += '&search=' + encodeURIComponent(query);

                return tainacanApi.get(endpoint, { cancelToken: source.token })
                    .then(res => {
                        if (this.collectionSearchCancel !== source)
                            return;

                        const pageCollections = Array.isArray(res.data) ? res.data : [];

                        if (this.collectionsPage === 1)
                            this.collections = pageCollections;
                        else
                            this.collections.push(...pageCollections);

                        this.totalCollectionPages = res.headers['x-wp-totalpages'] ? Number(res.headers['x-wp-totalpages']) : 0;
                        this.collectionsPage++;

                        this.isFetchingCollections = false;
                    })
                    .catch(error => {
                        if (isCancel(error) || this.collectionSearchCancel !== source)
                            return;

                        this.$console.error(error);
                        this.isFetchingCollections = false;
                    });
            }
        }
    }
</script>

<style lang="scss" scoped>

    .sub-header {
        gap: 8px;

        .header-item {
            margin-bottom: 0 !important;
            min-height: 1.875em;

            &:first-child {
                margin-inline-end: auto;
            }
            &:not(:last-child) {
                padding-inline-end: 0.875em;
            }

            .label {
                color: var(--tainacan-label-color);
                font-size: 0.875em;
                font-weight: normal;
                margin-top: 5px;
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
                height: 1.75em;
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
</style>


