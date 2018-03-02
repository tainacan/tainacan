<template>
    <div>
        <section class="section">
            <b-field grouped group-multiline>
                <button v-if="selectedCollections.length > 0" class="button field is-danger" @click="deleteSelectedCollections()"><span>{{$i18n.get('instruction_delete_selected_collections')}} </span><b-icon icon="delete"></b-icon></button>
                <b-select 
                        :label="$i18n.get('label_collections_per_page')"
                        v-model="collectionsPerPage" 
                        @input="onChangeCollectionsPerPage" 
                        :disabled="collections.length <= 0">
                    <option value="2">2 {{ $i18n.get('label_per_page') }}</option>
                    <option value="10">10 {{ $i18n.get('label_per_page') }}</option>
                    <option value="15">15 {{ $i18n.get('label_per_page') }}</option>
                    <option value="20">20 {{ $i18n.get('label_per_page') }}</option>
                </b-select>

            </b-field>
            <b-table
                    ref="collectionTable"
                    :data="collections"
                    @selection-change="handleSelectionChange"
                    :checked-rows.sync="selectedCollections"
                    checkable
                    :loading="isLoading"
                    hoverable 
                    striped
                    selectable
                    paginated
                    backend-pagination
                    :total="totalCollections"
                    :per-page="collectionsPerPage"
                    @page-change="onPageChange">
                <template slot-scope="props">
                    
                    <b-table-column tabindex="0" label="Imagem" :aria-label="$i18n.get('label_image')" field="featured_image" width="55">
                        <router-link class="clickable-row" tag="span" :to="{path: $routerHelper.getCollectionPath(props.row.id)}">
                        <template v-if="props.row.featured_image" slot-scope="scope">
                            <img class="table-thumb" :src="`${props.row.featured_image}`"/>
                        </template>
                        </router-link>
                    </b-table-column>

                    <b-table-column tabindex="0" label="Nome" :aria-label="$i18n.get('label_name') + ': ' + props.row.name" field="props.row.name">
                        <router-link class="clickable-row" tag="span" :to="{path: $routerHelper.getCollectionPath(props.row.id)}">
                        {{ props.row.name }}
                        </router-link>
                    </b-table-column>

                    <b-table-column tabindex="0" :aria-label="$i18n.get('label_description') + ': ' + props.row.description" property="description" label="Descrição" show-overflow-tooltip field="props.row.description">
                        <router-link class="clickable-row" tag="span" :to="{path: $routerHelper.getCollectionPath(props.row.id)}">
                        {{ props.row.description }}
                        </router-link>
                    </b-table-column>

                    <b-table-column tabindex="0" label="Ações" width="110" :aria-label="$i18n.get('label_ações')">
                        <!-- <a id="button-view" :aria-label="$i18n.get('label_button_view')" @click.prevent.stop="goToCollectionPage(props.row.id)"><b-icon icon="eye"></a> -->
                        <a id="button-edit" :aria-label="$i18n.get('label_button_edit')" @click.prevent.stop="goToCollectionEditPage(props.row.id)"><b-icon icon="pencil"></a>
                        <a id="button-delete" :aria-label="$i18n.get('label_button_delete')" @click.prevent.stop="deleteOneCollection(props.row.id)"><b-icon icon="delete"></a>
                    </b-table-column>
                </template>

                <!-- Empty state image -->
                <template slot="empty">
                    <section class="section">
                        <div class="content has-text-grey has-text-centered">
                            <p>
                                <b-icon
                                    icon="inbox"
                                    size="is-large">
                                </b-icon>
                            </p>
                            <p></p>
                            <router-link tag="button" class="button is-primary"
                                        :to="{ path: $routerHelper.getNewCollectionPath() }">
                                {{ $i18n.get('new') + ' ' + $i18n.get('collection') }}
                            </router-link>
                        </div>
                    </section>
                </template>
            </b-table>
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'CollectionsList',
    data(){
        return {
            selectedCollections: [],
            tableFields: [],
            isLoading: false,
            totalCollections: 0,
            page: 1,
            collectionsPerPage: 2
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollections',
            'deleteCollection'
        ]),
        ...mapGetters('collection', [
            'getCollections'
        ]),
        deleteOneCollection(collectionId) {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_collection_deleted'),
                onConfirm: () => {
                    this.deleteCollection(collectionId).then(() => {
                        this.loadCollections();
                        this.$toast.open({
                            duration: 3000,
                            message: this.$i18n.get('info_collection_delete'),
                            position: 'is-bottom',
                            type: 'is-secondary',
                            queue: true
                        })
                        for (let i = 0; i < this.selectedCollections.length; i++) {
                            if (this.selectedCollections[i].id == this.collectionId)
                                this.selectedCollections.splice(i, 1);
                        }
                    }).catch(() =>
                        this.$toast.open({
                            duration: 3000,
                            message: this.$i18n.get('info_error_deleting_collection'),
                            position: 'is-bottom',
                            type: 'is-danger',
                            queue: true
                        })
                    );
                }
            });
        },
        deleteSelectedCollections() {
            this.$dialog.confirm({
                message: this.$i18n.get('info_selected_collections_delete'),
                onConfirm: () => {

                    for (let collection of this.selectedCollections) {
                        this.deleteCollection(collection.id)
                        .then((res) => {
                            this.loadCollections();
                            this.$toast.open({
                                duration: 3000,
                                message: this.$i18n.get('info_collection_deleted'),
                                position: 'is-bottom',
                                type: 'is-secondary',
                                queue: false
                            })                            
                        }).catch((err) => { 
                            this.$toast.open({
                                duration: 3000,
                                message: this.$i18n.get('info_error_deleting_collection'),
                                position: 'is-bottom',
                                type: 'is-danger',
                                queue: false
                            });
                        });
                    }
                    this.selectedCollections =  [];
                }
            });
        },
        handleSelectionChange(value) {
        },
        goToCollectionPage(collectionId) {
            this.$router.push(this.$routerHelper.getCollectionPath(collectionId));
        },
        goToCollectionEditPage(collectionId) {
            this.$router.push(this.$routerHelper.getCollectionEditPath(collectionId));
        },
        onChangeCollectionsPerPage(value) {
            this.collectionsPerPage = value;
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
        }
    },
    computed: {
        collections(){
            return this.getCollections();
        }
    },
    mounted(){
        this.loadCollections();
    }

}
</script>

<style scoped>

    .table-thumb {
        max-height: 38px !important;
        vertical-align: middle !important;
    }

    .clickable-row{ cursor: pointer !important; }

</style>


