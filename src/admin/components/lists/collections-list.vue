<template>
    <div>     
        <b-field 
                grouped 
                group-multiline>
            <button 
                    v-if="selectedCollections.length > 0 && collections.length > 0 && collections[0].current_user_can_edit" 
                    class="button field is-danger" 
                    @click="deleteSelectedCollections()">
                <span>{{ $i18n.get('instruction_delete_selected_collections') }} </span>
                <b-icon icon="delete"/>
            </button>
        </b-field>
        <b-table
                v-if="totalCollections > 0"
                ref="collectionTable"
                :data="collections"
                :checked-rows.sync="selectedCollections"
                checkable
                :loading="isLoading"
                hoverable 
                selectable
                backend-sorting>
            <template slot-scope="props">
                
                <b-table-column 
                        tabindex="0" 
                        :label="$i18n.get('label_thumbnail')" 
                        :aria-label="$i18n.get('label_thumbnail')" 
                        field="thumbnail"
                        width="55">
                    <template 
                            v-if="props.row.thumbnail"
                            slot-scope="scope">
                        <router-link 
                                tag="img" 
                                :to="{path: $routerHelper.getCollectionPath(props.row.id)}" 
                                class="table-thumb clickable-row" 
                                :src="`${props.row.thumbnail}`"/>
                    </template>
                </b-table-column>

                <b-table-column 
                        tabindex="0" 
                        :label="$i18n.get('label_name')" 
                        :aria-label="$i18n.get('label_name')" 
                        field="props.row.name">
                    <router-link 
                            class="clickable-row" 
                            tag="span" 
                            :to="{path: $routerHelper.getCollectionPath(props.row.id)}">
                    {{ props.row.name }}
                    </router-link>
                </b-table-column>

                <b-table-column 
                        tabindex="0" 
                        :aria-label="$i18n.get('label_description')" 
                        :label="$i18n.get('label_description')" 
                        property="description" 
                        show-overflow-tooltip 
                        field="props.row.description">
                    <router-link 
                            class="clickable-row" 
                            tag="span" 
                            :to="{path: $routerHelper.getCollectionPath(props.row.id)}">
                    {{ props.row.description }}
                    </router-link>
                </b-table-column>

                <b-table-column 
                        class="row-creation" 
                        tabindex="0" 
                        :aria-label="$i18n.get('label_creation') + ': ' + props.row.creation" 
                        :label="$i18n.get('label_creation')" 
                        property="creation" 
                        show-overflow-tooltip 
                        field="props.row.creation">
                    <router-link 
                            class="clickable-row" 
                            v-html="props.row.creation" 
                            tag="span" 
                            :to="{path: $routerHelper.getCollectionPath(props.row.id)}"/>
                </b-table-column>

                <b-table-column 
                        tabindex="0" 
                        v-if="props.row.current_user_can_edit"
                        :label="$i18n.get('label_actions')" 
                        width="78" 
                        :aria-label="$i18n.get('label_actions')">
                    <!-- <a id="button-view" :aria-label="$i18n.get('label_button_view')" @click.prevent.stop="goToCollectionPage(props.row.id)"><b-icon icon="eye"></a> -->
                    <a 
                            id="button-edit" 
                            :aria-label="$i18n.getFrom('collections','edit_item')" 
                            @click.prevent.stop="goToCollectionEditPage(props.row.id)"><b-icon 
                            type="is-gray" 
                            icon="pencil"/></a>
                    <a 
                            id="button-delete" 
                            :aria-label="$i18n.get('label_button_delete')" 
                            @click.prevent.stop="deleteOneCollection(props.row.id)"><b-icon 
                            type="is-gray" 
                            icon="delete"/></a>
                </b-table-column>
            </template>
        </b-table>

        <!-- Empty state image -->
        <div v-if="!totalCollections || totalCollections <= 0">
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <b-icon
                                icon="inbox"
                                size="is-large"/>
                    </p>
                    <p>{{ $i18n.get('info_no_collection_created') }}</p>
                    <router-link
                            id="button-create-collection"
                            tag="button"
                            class="button is-primary"
                            :to="{ path: $routerHelper.getNewCollectionPath() }">
                        {{ $i18n.getFrom('collections', 'new_item') }}
                    </router-link>
                </div>
            </section>
        </div>

    </div>
</template>

<script>
import { mapActions } from 'vuex'

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
        collections: Array
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
                        this.$toast.open({
                            duration: 3000,
                            message: this.$i18n.get('info_collection_deleted'),
                            position: 'is-bottom',
                            type: 'is-secondary',
                            queue: true
                        });
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
                message: this.$i18n.get('info_warning_selected_collections_delete'),
                onConfirm: () => {

                    for (let collection of this.selectedCollections) {
                        this.deleteCollection(collection.id)
                        .then(() => {
                            this.loadCollections();
                            this.$toast.open({
                                duration: 3000,
                                message: this.$i18n.get('info_collection_deleted'),
                                position: 'is-bottom',
                                type: 'is-secondary',
                                queue: false
                            })                            
                        }).catch(() => { 
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
        max-height: 55px !important;
        vertical-align: middle !important;
    }

    .row-creation span {
        color: $gray-light;
        font-size: 0.75em;
        line-height: 1.5
    }

    .clickable-row{ cursor: pointer !important; }

</style>


