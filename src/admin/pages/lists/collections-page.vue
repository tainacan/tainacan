<template>
    <div class="repository-level-page page-container">
        <b-loading :active.sync="isLoading"/>
        <tainacan-title 
                :bread-crumb-items="[{ path: '', label: this.$i18n.get('collections') }]"/>
        <div
                class="sub-header"
                v-if="$userCaps.hasCapability('edit_tainacan-collections')">
            
            <!-- New Collection button -->
            <div class="header-item">
                <b-dropdown 
                        aria-role="list"
                        id="collection-creation-options-dropdown">
                    <button
                            class="button is-secondary"
                            slot="trigger">
                        <div>{{ $i18n.getFrom('collections', 'new_item') }}</div>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown" />
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
                    <b-dropdown-item
                            :key="metadatum_mapper.slug"
                            v-for="metadatum_mapper in metadatum_mappers"
                            v-if="metadatum_mapper.metadata != false"
                            aria-role="listitem">
                        <router-link
                                :id="'a-create-collection-' + metadatum_mapper.slug"
                                tag="div"
                                :to="{ path: $routerHelper.getNewMappedCollectionPath(metadatum_mapper.slug) }">
                            {{ $i18n.get(metadatum_mapper.name) }}
                        </router-link>
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

            <!-- Sorting options ----  -->
            <b-field class="header-item">
                <label class="label">{{ $i18n.get('label_sort') }}</label>
                <b-dropdown
                        :mobile-modal="true"
                        :disabled="collections.length <= 0 || isLoading"
                        @input="onChangeOrder(order == 'asc' ? 'desc' : 'asc')"
                        aria-role="list">
                    <button
                            :aria-label="$i18n.get('label_sorting_direction')"
                            class="button is-white"
                            slot="trigger">
                        <span class="icon is-small gray-icon">
                            <i 
                                    :class="order == 'desc' ? 'tainacan-icon-sortdescending' : 'tainacan-icon-sortascending'"
                                    class="tainacan-icon tainacan-icon-18px"/>
                        </span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown" />
                        </span>
                    </button>
                    <b-dropdown-item
                            aria-controls="items-list-results"
                            role="button"
                            :class="{ 'is-active': order == 'desc' }"
                            :value="'desc'"
                            aria-role="listitem"
                            style="padding-bottom: 0.45rem">
                        <span class="icon is-small gray-icon">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortdescending"/>
                        </span>
                        {{ $i18n.get('label_descending') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            aria-controls="items-list-results"
                            role="button"
                            :class="{ 'is-active': order == 'asc' }"
                            :value="'asc'"
                            aria-role="listitem"
                            style="padding-bottom: 0.45rem">
                        <span class="icon is-small gray-icon">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortascending"/>
                        </span>
                        {{ $i18n.get('label_ascending') }}
                    </b-dropdown-item>
                </b-dropdown>
                <span
                        class="label"
                        style="padding-left: 0.65rem;">
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
                            }">
                        <a :style="{ fontWeight: 'bold', color: '#454647 !important', lineHeight: '1.5rem' }">
                            {{ `${$i18n.get('label_all_collections')}` }}
                            <span class="has-text-gray">&nbsp;{{ `${` ${repositoryTotalCollections ? `(${Number(repositoryTotalCollections.private) + Number(repositoryTotalCollections.publish)})` : '' }`}` }}</span>
                        </a>
                    </li>
                    <li 
                            v-for="(statusOption, index) of $statusHelper.getStatuses()"
                            :key="index"
                            @click="onChangeTab(statusOption.slug)"
                            :class="{ 'is-active': status == statusOption.slug}"
                            :style="{ marginRight: statusOption.slug == 'private' ? 'auto' : '', marginLeft: statusOption.slug == 'draft' ? 'auto' : '' }"
                            v-tooltip="{
                                content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('collections')]),
                                autoHide: true,
                                placement: 'auto',
                            }">
                        <a>
                            <span 
                                    v-if="$statusHelper.hasIcon(statusOption.slug)"
                                    class="icon has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-18px"
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
                        :is-on-trash="status == 'trash'"/> 

                <!-- Empty state image -->
                <div v-if="collections.length <= 0 && !isLoading">
                    <section class="section">
                        <div class="content has-text-grey has-text-centered">
                            <p>
                                <span class="icon is-large">
                                    <i class="tainacan-icon tainacan-icon-36px tainacan-icon-collections" />
                                </span>
                            </p>
                            <p v-if="status == undefined || status == ''">{{ $i18n.get('info_no_collection_created') }}</p>
                            <p
                                    v-for="(statusOption, index) of $statusHelper.getStatuses()"
                                    :key="index"
                                    v-if="status == statusOption.slug">
                                {{ $i18n.get('info_no_collections_' + statusOption.slug) }}
                            </p>

                            <div v-if="$userCaps.hasCapability('edit_tainacan-collections') && status == undefined || status == ''">
                                <b-dropdown 
                                        :disabled="isLoadingMetadatumMappers"
                                        id="collection-creation-options-dropdown"
                                        aria-role="list">
                                    <button
                                            class="button is-secondary"
                                            slot="trigger">
                                        <div>{{ $i18n.getFrom('collections', 'new_item') }}</div>
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown" />
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
                                    <b-dropdown-item
                                            :key="metadatum_mapper.slug"
                                            v-for="metadatum_mapper in metadatum_mappers"
                                            v-if="metadatum_mapper.metadata != false"
                                            aria-role="listitem">
                                        <router-link
                                                :id="'a-create-collection-' + metadatum_mapper.slug"
                                                tag="div"
                                                :to="{ path: $routerHelper.getNewMappedCollectionPath(metadatum_mapper.slug) }">
                                            {{ $i18n.get(metadatum_mapper.name) }}
                                        </router-link>
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
            status: '',
            order: 'desc',
            ordeBy: 'date',
            sortingOptions: [
                { label: this.$i18n.get('label_title'), value: 'title' },
                { label: this.$i18n.get('label_creation_date'), value: 'date' },
            ]
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
        },
        repositoryTotalCollections(){
            return this.getRepositoryTotalCollections();
        }
    },
    methods: {
         ...mapActions('collection', [
            'fetchCollections',
            'cleanCollections'
        ]),
        ...mapActions('metadata', [
            'fetchMetadatumMappers'
        ]),
        ...mapGetters('collection', [
            'getCollections',
            'getRepositoryTotalCollections'
        ]),
        ...mapGetters('metadata', [
            'getMetadatumMappers'
        ]),
        onChangeTab(status) {
            this.status = status;
            this.loadCollections();
        },
        onChangeOrder(newOrder) {
            if (newOrder != this.order) { 
                this.$userPrefs.set('collections_order', newOrder)
                    .then((newOrder) => {
                        this.order = newOrder;
                    })
                    .catch(() => {
                        this.$console.log("Error settings user prefs for collections order")
                    });

            }
            this.order = newOrder;
            this.loadCollections()
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
        loadCollections() {
            this.cleanCollections();    
            this.isLoading = true;
            this.fetchCollections({ 
                page: this.page, 
                collectionsPerPage: this.collectionsPerPage,
                status: this.status,
                contextEdit: true, 
                order: this.order,
                orderby: this.orderBy
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
    }
}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .sub-header {
        min-height: $subheader-height;
        height: $header-height;
        padding-left: 0;
        padding-right: 0;
        border-bottom: 1px solid #ddd;
        display: inline-flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;

        .header-item {

            &:not(:last-child) {
                padding-right: 0.5em;
            }

            .label {
                font-size: 0.875rem;
                font-weight: normal;
                margin-top: 3px;
                margin-bottom: 2px;
                cursor: default;
            }

            .button {
                display: flex;
                align-items: center;
            }
            
            .field {
                align-items: center;
            }

            .gray-icon, .gray-icon .icon {
                color: $gray4 !important;
                padding-right: 10px;
            }
            .gray-icon .icon i::before, 
            .gray-icon i::before {
                font-size: 1.3125rem !important;
                max-width: 26px;
            }
        }

        @media screen and (max-width: 769px) {
            height: 60px;
            margin-top: -0.5em;
            padding-top: 0.9em;
        
            .header-item {
                padding-right: 0.2em;
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


