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
                    @click="$emit('close')">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-close tainacan-icon-1-125em" />
                </span>
            </button>
        </header>
        <section class="tainacan-form">
            <p>{{ $i18n.get('instruction_select_a_target_collection') }}</p>
            <div 
                    v-if="!isLoading" 
                    class="collection-types-container tainacan-clickable-cards"
                    role="list">
                <template
                        v-for="(collection, index) in collections"
                        :key="index">
                    <router-link
                            v-if="collection && collection.current_user_can_edit_items"
                            role="listitem"
                            class="collection-type tainacan-clickable-card"
                            :to="$routerHelper.getNewItemPath(collection.id)"
                            @click="$emit('close')">
                        <h4>{{ collection.name }}</h4>
                        <p>{{ collection.description.length > 200 ? (collection.description.substring(0,197) + '...') : collection.description }}</p>            
                    </router-link>
                </template>
                <div 
                        v-if="collections.length <= 0"
                        class="block">
                    <p class="has-text-gray">
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
                            @click="$emit('close')">Close</button>
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
        'close'
    ],
    data() {
        return {
            collections: [],
            isLoading: false,
            maxCollectionsPerPage: tainacan_plugin.api_max_items_per_page ? Number(tainacan_plugin.api_max_items_per_page) : 96
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
        ])
    }
}
</script>

<style lang="scss" scoped>

    @import '../../scss/_cards.scss';

</style>


 
