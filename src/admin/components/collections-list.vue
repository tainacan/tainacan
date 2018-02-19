<template>
    <div>
        <section class="section">
            <b-field grouped group-multiline>
                <button v-if="selectedCollections.length > 0" class="button field is-danger" @click="deleteSelectedCollections()"><span>Deletar coleções selecionadas </span><b-icon icon="delete"></b-icon></button>

                <router-link tag="button" class="button is-primary"
                            :to="{ path: `/collections/new` }">
                    Criar Coleção
                </router-link>

                <b-select 
                        v-model="collectionsPerPage" 
                        @input="onChangeCollectionsPerPage" 
                        :disabled="collections.length <= 0">
                    <option value="2">2 coleções por página</option>
                    <option value="10">10 coleções por página</option>
                    <option value="15">15 coleções por página</option>
                    <option value="20">20 coleções por página</option>
                </b-select>

            </b-field>
            <b-table
                    ref="collectionTable"
                    :data="collections"
                    @selection-change="handleSelectionChange"
                    :checked-rows.sync="selectedCollections"
                    checkable
                    :loading="isLoading"
                    paginated
                    backend-pagination
                    :total="totalCollections"
                    :per-page="collectionsPerPage"
                    @page-change="onPageChange">
                <template slot-scope="props">

                    <b-table-column field="featured_image" width="55">
                        <template v-if="props.row.featured_image" slot-scope="scope">
                            <img class="table-thumb" :src="`${props.row.featured_image}`"/>
                        </template>
                    </b-table-column>

                    <b-table-column label="Nome" field="props.row.name">
                        <router-link :to="`/collections/${props.row.id}`" tag="a">{{ props.row.name }}</router-link>
                    </b-table-column>

                    <b-table-column property="description" label="Descrição" show-overflow-tooltip field="props.row.description">
                        {{ props.row.description }}
                    </b-table-column>


                    <b-table-column label="Ações">
                        <router-link :to="`/collections/${props.row.id}/edit`" tag="a"><b-icon icon="pencil"></router-link>
                        <a><b-icon icon="delete" @click.native="deleteOneCollection(props.row.id)"></a>
                        <a @click.native="showMoreCollection(props.row.id)"><b-icon icon="dots-vertical"></a>
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
                            <p>Nenhuma coleção ainda neste repositório.</p>
                            <router-link tag="button" class="button is-primary"
                                        :to="{ path: `/collections/new` }">
                                Criar Coleção
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
                message: 'Deseja realmente deletar esta Coleção?',
                onConfirm: () => {
                    this.deleteCollection(collectionId).then(() => {
                        this.loadCollections();
                        this.$toast.open({
                            duration: 3000,
                            message: `Coleção deletada`,
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
                            message: `Erro ao deletar coleção`,
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
                message: 'Deseja realmente todos as coleções selecionadas?',
                onConfirm: () => {

                    for (let collection of this.selectedCollections) {
                        this.deleteCollection(collection.id)
                        .then((res) => {
                            this.loadCollections();
                            this.$toast.open({
                                duration: 3000,
                                message: `Coleção deletada`,
                                position: 'is-bottom',
                                type: 'is-secondary',
                                queue: false
                            })                            
                        }).catch((err) => { 
                            this.$toast.open({
                                duration: 3000,
                                message: `Erro ao deletar coleção`,
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
        showMoreCollection(collectionId) {
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

</style>


