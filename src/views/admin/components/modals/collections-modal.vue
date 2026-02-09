<template>
    <form 
            ref="collectionsModal"
            action=""
            autofocus
            role="dialog"
            class="tainacan-modal-content"
            tabindex="-1"
            aria-modal>
        <header class="tainacan-modal-title">
            <h2>{{ $i18n.get('collections') }}</h2>
            <button         
                    class="button is-medium is-white is-align-self-flex-start"
                    :aria-label="$i18n.get('close')"
                    @click="closeModal()">
                <span 
                        aria-hidden="true"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-close tainacan-icon-1-125em" />
                </span>
            </button>
        </header>
        <section class="tainacan-form">
            <p>{{ $i18n.get('instruction_select_a_target_collection') }}</p>
            <div 
                    v-if="!isLoading" 
                    class="collection-types-container tainacan-clickable-cards"
                    :role="editableCollectionsCount > 1 ? 'list' : undefined">
                <template
                        v-for="(collection, index) in collections"
                        :key="index">
                    <router-link
                            v-if="collection && collection.current_user_can_edit_items"
                            :role="editableCollectionsCount > 1 ? 'listitem' : undefined"
                            class="collection-type tainacan-clickable-card"
                            :to="$routerHelper.getNewItemPath(collection.id)"
                            @click="closeModal()">
                        <dl class="collection-type-definition">
                            <dt class="collection-type-name">
                                {{ collection.name }}
                            </dt>
                            <dd>
                                {{ collection.description.length > 200 ? (collection.description.substring(0,197) + '...') : collection.description }}
                            </dd>            
                        </dl>
                    </router-link>
                </template>
                <div 
                        v-if="collections.length <= 0"
                        class="block">
                    <p class="has-text-dark">
                        {{ $i18n.get('info_no_collection_created') }}
                    </p>
                </div>
            </div>
            <b-loading 
                    v-model="isLoading"
                    :is-full-page="false" 
                    :can-cancel="false" />
            
            <footer class="field is-grouped form-submit">
                <div class="control">
                    <button 
                            class="button is-outlined" 
                            type="button" 
                            @click="closeModal()">Close</button>
                </div>
            </footer>
        </section>
    </form>     
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'CollectionsModal',
    emits: [
        'close',
        'beforeClose'
    ],
    data() {
        return {
            collections: [],
            isLoading: false,
            maxCollectionsPerPage: tainacan_plugin.api_max_items_per_page ? Number(tainacan_plugin.api_max_items_per_page) : 96
        }
    },
    computed: {
        editableCollectionsCount() {
            return (this.collections || []).filter(c => c && c.current_user_can_edit_items).length;
        }
    },
    mounted() {
        this.isLoading = true;
        this.fetchCollections({ 
                page: 1, 
                collectionsPerPage: this.maxCollectionsPerPage, 
                contextEdit: true
            })
            .then((res) => {
                this.collections = res.collections;
                this.isLoading = false;
            }).catch((error) => {
                this.$console.log(error);
                this.isLoading = false;
            });

        if (this.$refs.collectionsModal)
            this.$refs.collectionsModal.focus();
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollections'
        ]),
        closeModal() {
            this.$emit('beforeClose');
            this.$emit('close');
        }
    }
}
</script>

<style lang="scss" scoped>

    @use '../../scss/_cards.scss';

</style>


 
