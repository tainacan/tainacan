<template>
    <div class="primary-page page-container">
        <tainacan-title />
        <div
                class="sub-header"
                v-if="totalCollections > 0">
            <div class="header-item">
                <router-link 
                        id="button-create-collection"
                        tag="button" 
                        class="button is-secondary"
                        :to="{ path: $routerHelper.getNewCollectionPath() }">
                    {{ $i18n.getFrom('collections', 'new_item') }}
                </router-link>
            </div>
        </div>
        <div class="above-subheader">
            <div>
                <collections-list
                        :is-loading="isLoading"
                        :total-collections="totalCollections"
                        :page="page"
                        :collections-per-page="collectionsPerPage"
                        :collections="collections"/>  
                <!-- Footer -->
                <div
                        class="pagination-area"
                        v-if="totalCollections > 0">
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
            .catch(() => {
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
            let collectionsList = this.getCollections(); 
            for (let collection of collectionsList) 
                collection['creation'] = this.$i18n.get('info_created_by') + collection['author_name'] + '<br>' + this.$i18n.get('info_date') + collection['creation_date'];
            return collectionsList;
        }
    },
    created() {
        this.$userPrefs.get('collections_per_page')
            .then((value) => {
                this.collectionsPerPage = value;
            })
            .catch(() => {
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
        min-height: $subheader-height;
        height: $subheader-height;
        margin-left: -$page-side-padding;
        margin-right: -$page-side-padding;
        padding-top: $page-small-top-padding;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        border-bottom: 0.5px solid #ddd;

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

    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        height: auto;
    }

</style>


