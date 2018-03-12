<template>
    <div class="primary-page page-container-small">
        <div class="sub-header">
            <div class="header-item">
                <router-link tag="button" class="button is-secondary"
                            :to="{ path: $routerHelper.getNewCollectionPath() }">
                    {{ $i18n.get('new') + ' ' + $i18n.get('collection') }}
                </router-link>
            </div>
        </div>
        <div class="columns">
            <aside class="column filters-menu">
                <h3>{{ $i18n.get('filters') }}</h3>
            </aside>
            <div class="column table-container">
                <collections-list
                    :isLoading="isLoading"
                    :totalCollections="totalCollections"
                    :page="page"
                    :collectionsPerPage="collectionsPerPage"
                    :collections="collections">
                </collections-list>  
                <!-- Footer -->
                <div class="table-footer">
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
                        <b-field horizontal :label="$i18n.get('label_collections_per_page')"> 
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
                            :per-page="collectionsPerPage">
                        </b-pagination> 
                    </div>
                </div>    
            </div> 
        </div>
    </div>   
</template>

<script>
import CollectionsList from '../../components/lists/collections-list.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'CollectionsPage',
    data(){
        return {
            isLoading: false,
            totalCollections: 0,
            page: 1,
            collectionsPerPage: 12
        }
    },
    components: {
        CollectionsList
    },
    methods: {
         ...mapActions('collection', [
            'fetchCollections',
        ]),
        ...mapGetters('collection', [
            'getCollections'
        ]),
        onChangeCollectionsPerPage(value) {
            let prevValue = this.collectionsPerPage;
            this.collectionsPerPage = value;
            this.$userPrefs.set('collections_per_page', value,  prevValue);
            this.loadCollections();
        },
        onPageChange(page) {
            this.page = page;
            this.loadCollections();
        },
        loadCollections() {    
            this.isLoading = true;
            this.fetchCollections({ 'page': this.page, 'collectionsPerPage': this.collectionsPerPage })
            .then((res) => {
                this.isLoading = false;
                this.totalCollections = res.total;
            }) 
            .catch((error) => {
                this.isLoading = false;
            });
        },
        getLastCollectionNumber() {
            let last = (Number(this.collectionsPerPage*(this.page - 1)) + Number(this.collectionsPerPage));
            
            return last > this.totalCollections ? this.totalCollections : last;
        }
    },
    computed: {
        collections(){
            return this.getCollections();
        }
    },
    created() {
        this.$userPrefs.get('collections_per_page')
            .then((value) => {
                this.collectionsPerPage = value;
            })
            .catch((error) => {
                this.$userPrefs.set('collections_per_page', 12, null);
            }); 
    },
    mounted(){
        this.loadCollections();
    }
}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .sub-header {
        max-height: $header-height;
        height: $header-height;
        margin-left: -$page-small-side-padding;
        margin-right: -$page-small-side-padding;
        margin-top: -$page-small-top-padding;
        padding-top: $page-small-top-padding;
        padding-left: $page-small-side-padding;
        padding-right: $page-small-side-padding;
        border-bottom: 0.5px solid #ddd;

        .header-item {
            display: inline-block;
            padding-right: 8em;
        }
    }

    .columns {
        margin-bottom: 0;
        margin-top: 0;
        min-height: calc(100% - $header-height - $header-height);

        .filters-menu {
            min-width: $side-menu-width;
            max-width: $side-menu-width;
            height: 100%;
            background-color: $primary-lighter;
            margin-left: -$page-small-side-padding;
            padding-left: $page-small-side-padding
        }

        .table-container {
            margin-right: -$page-small-side-padding;
            padding: 3em 2.5em;
        }
    }

</style>


