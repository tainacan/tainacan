<template>
    <div>     
        <b-field grouped group-multiline>
            <button v-if="selectedCollections.length > 0" class="button field is-danger" @click="deleteSelectedCollections()"><span>{{$i18n.get('instruction_delete_selected_collections')}} </span><b-icon icon="delete"></b-icon></button>
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
                backend-sorting>
            <template slot-scope="props">
                
                <b-table-column tabindex="0" label="Imagem" :aria-label="$i18n.get('label_image')" field="featured_image" width="55">
                    <router-link class="clickable-row" tag="span" :to="{path: $routerHelper.getCollectionPath(props.row.id)}">
                    <template v-if="props.row.featured_image" slot-scope="scope">
                        <img class="table-thumb" :src="`${props.row.featured_image}`"/>
                    </template>
                    </router-link>
                </b-table-column>

                <b-table-column tabindex="0" :label="$i18n.get('label_name')" :aria-label="$i18n.get('label_name')" field="props.row.name">
                    <router-link class="clickable-row" tag="span" :to="{path: $routerHelper.getCollectionPath(props.row.id)}">
                    {{ props.row.name }}
                    </router-link>
                </b-table-column>

                <b-table-column tabindex="0" :aria-label="$i18n.get('label_description')" :label="$i18n.get('label_description')" property="description" show-overflow-tooltip field="props.row.description">
                    <router-link class="clickable-row" tag="span" :to="{path: $routerHelper.getCollectionPath(props.row.id)}">
                    {{ props.row.description }}
                    </router-link>
                </b-table-column>

                <b-table-column class="row-creation" tabindex="0" :aria-label="$i18n.get('label_creation') + ': ' + props.row.creation" :label="$i18n.get('label_creation')" property="creation" show-overflow-tooltip field="props.row.creation">
                    <router-link class="clickable-row" v-html="props.row.creation" tag="span" :to="{path: $routerHelper.getCollectionPath(props.row.id)}">
                    </router-link>
                </b-table-column>

                <b-table-column tabindex="0" :label="$i18n.get('label_actions')" width="78" :aria-label="$i18n.get('label_actions')">
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
                        <p>{{$i18n.get('info_no_collection_created')}}</p>
                        <router-link tag="button" class="button is-primary"
                                    :to="{ path: $routerHelper.getNewCollectionPath() }">
                            {{ $i18n.get('new') + ' ' + $i18n.get('collection') }}
                        </router-link>
                    </div>
                </section>
            </template>
        </b-table>

    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'CollectionsList',
    data(){
        return {
            selectedCollections: []
        }
    },
    props: {
        isLoading: false,
        totalCollections: 0,
        page: 1,
        collectionsPerPage: 12,
        collections: []
    },
    methods: {
        ...mapActions('collection', [
            'deleteCollection'
        ]),
        deleteOneCollection(collectionId) {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_collection_delete'),
                onConfirm: () => {
                    this.deleteCollection(collectionId).then(() => {
                        this.loadCollections();
                        this.$toast.open({
                            duration: 3000,
                            message: this.$i18n.get('info_collection_deleted'),
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
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .table-thumb {
        max-height: 38px !important;
        vertical-align: middle !important;
    }

    .row-creation {
        color: $gray-light;
        font-size: 0.75em;
        line-height: 1.5
    }

    .clickable-row{ cursor: pointer !important; }

</style>


