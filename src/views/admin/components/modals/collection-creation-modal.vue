<template>
    <div 
            ref="collectionCreationModal"
            aria-labelledby="collection-creation-title"
            autofocus
            role="dialog"
            tabindex="-1"
            aria-modal 
            class="tainacan-modal-content tainacan-repository-level-colors"
            style="width: auto">
        <header class="tainacan-modal-title">
            <h2 
                    v-if="selectedEstrategy == 'mappers'"
                    id="collection-creation-title">
                {{ $i18n.get('label_create_collection_from_mapper') }}
            </h2>
            <h2 
                    v-else-if="selectedEstrategy == 'presets'"
                    id="collection-creation-title">
                {{ $i18n.get('label_create_collection_from_preset') }}
            </h2>
            <h2 
                    v-else
                    id="collection-creation-title">
                {{ $i18n.get('label_create_collection') }}
            </h2>
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
            <div 
                    v-if="selectedEstrategy == undefined"
                    class="collection-creation-options-container tainacan-clickable-cards"
                    role="list">
                
                <button
                        class="collection-creation-option tainacan-clickable-card"
                        role="listitem"
                        type="button"
                        @click="selectedEstrategy = 'mappers'"
                        @keydown.enter="selectedEstrategy = 'mappers'"
                        @keydown.space="selectedEstrategy = 'mappers'">
                    <dl class="collection-creation-option-definition">
                        <dt class="collection-creation-option-name">
                            {{ $i18n.get('label_from_a_mapper') }}
                        </dt>
                        <dd class="collection-creation-option-description">
                            {{ $i18n.get('info_create_collection_from_mapper') }}
                        </dd>
                    </dl>
                </button>

                <button
                        class="collection-creation-option tainacan-clickable-card"
                        role="listitem"
                        @click="selectedEstrategy = 'presets'">
                    <dl class="collection-creation-option-definition">
                        <dt class="collection-creation-option-name">
                            {{ $i18n.get('label_using_a_preset') }}
                        </dt>
                        <dd class="collection-creation-option-description">
                            {{ $i18n.get('info_create_collection_from_preset') }}
                        </dd>
                    </dl>
                </button>

            </div>
            
            <div 
                    v-if="selectedEstrategy == 'mappers'"
                    class="collection-creation-options-container tainacan-clickable-cards"
                    :role="visibleMappersCount > 1 ? 'list' : undefined">
                <template 
                        v-for="metadatumMapper in metadatumMappers"
                        :key="metadatumMapper.slug">
                    <button
                            v-if="metadatumMapper.metadata != false"
                            class="collection-creation-option tainacan-clickable-card"
                            :role="visibleMappersCount > 1 ? 'listitem' : undefined"
                            type="button"
                            @click="$router.push($routerHelper.getNewMappedCollectionPath(metadatumMapper.slug)); closeModal();"
                            @keydown.enter="$router.push($routerHelper.getNewMappedCollectionPath(metadatumMapper.slug)); closeModal();"
                            @keydown.space="$router.push($routerHelper.getNewMappedCollectionPath(metadatumMapper.slug)); closeModal();">
                        <dl class="collection-creation-option-definition">
                            <dt class="collection-creation-option-name">
                                {{ metadatumMapper.name }}
                            </dt>
                            <dd 
                                    v-if="metadatumMapper.description"
                                    class="collection-creation-option-description">
                                {{ metadatumMapper.description }}
                            </dd>
                        </dl>
                    </button>
                </template>
            </div>

            <div 
                    v-if="selectedEstrategy == 'presets'"
                    class="collection-creation-options-container tainacan-clickable-cards"
                    :role="presetsCount > 1 ? 'list' : undefined">
                <button
                        v-for="collectionPreset in getPresetsHook"
                        :key="collectionPreset.slug"
                        class="collection-creation-option tainacan-clickable-card"
                        :role="presetsCount > 1 ? 'listitem' : undefined"
                        type="button"
                        @click="onNewCollectionPreset(collectionPreset)"
                        @keydown.enter="onNewCollectionPreset(collectionPreset)"
                        @keydown.space="onNewCollectionPreset(collectionPreset)">
                    <dl class="collection-creation-option-definition">
                        <dt class="collection-creation-option-name">
                            {{ collectionPreset.name }}
                        </dt>
                        <dd
                                v-if="collectionPreset.description"
                                class="collection-creation-option-description">
                            {{ collectionPreset.description }}
                        </dd>
                    </dl>
                </button>
            </div>

            <b-loading 
                    v-model="isLoadingMetadatumMappers"
                    :is-full-page="false" 
                    :can-cancel="false" />

            <b-loading 
                    v-model="isCreatingCollectionPreset"
                    :is-full-page="false" 
                    :can-cancel="false" />

            <footer class="field is-grouped form-submit">
                <div class="control">
                    <button 
                            class="button is-outlined" 
                            type="button" 
                            @click="closeModal()">
                        {{ $i18n.get('close') }}
                    </button>
                </div>
            </footer>
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
// We use axios directly instead of tainacanApi because the base url is given from the preset settings
import axios from 'axios';
import { tainacanErrorHandler } from '../../js/axios';

export default {
    name: 'CollectionCreationModal',
    emits: [
        'close',
        'beforeClose'
    ],
    data(){
        return {
            selectedEstrategy: 'mappers',
            isLoadingMetadatumMappers: true,
            collectionPresets: [],
            isCreatingCollectionPreset: false
        }
    },
    computed: {
        ...mapGetters('metadata', {
            'metadatumMappers': 'getMetadatumMappers'
        }),
        visibleMappersCount() {
            return (this.metadatumMappers || []).filter(m => m.metadata != false).length;
        },
        presetsCount() {
            const presets = this.getPresetsHook;
            return Array.isArray(presets) ? presets.length : 0;
        },
        hasPresetsHook() {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.hasFilter(`tainacan_collections_presets`);

            return false;
        },
        getPresetsHook() {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.applyFilters(`tainacan_collections_presets`, this.collectionPresets);

            return this.collectionPresets;
        },
    },
    watch: {
        hasPresetsHook: {
            handler() {
                this.selectedEstrategy = this.hasPresetsHook ? undefined : 'mappers';
            },
            immediate: true
        }
    },
    mounted() {
        this.isLoadingMetadatumTypes = true;

        this.fetchMetadatumMappers()
            .then(() => {
                this.isLoadingMetadatumMappers = false;
            })
            .catch(() => {
                this.isLoadingMetadatumMappers = false;
            });

        if (this.$refs.collectionCreationModal)
            this.$refs.collectionCreationModal.focus()
    },
    methods: {
        ...mapActions('metadata', [
            'fetchMetadatumMappers'
        ]),
        closeModal() {
            this.$emit('beforeClose');
            this.$emit('close');
        },
        onNewCollectionPreset(collectionPreset) {
            this.isCreatingCollectionPreset = true;
            axios.post(collectionPreset.endpoint)
                .then(() => {
                    const successMessage = typeof collectionPreset.onSuccess === 'function' ? collectionPreset.onSuccess() : this.$i18n.get('label_preset_success');

                    this.$buefy.snackbar.open({
                        message: successMessage,
                        type: 'is-success',
                        position: 'is-bottom-right',
                        pauseOnHover: true,
                        duration: 3500,
                        queue: false
                    });
                    this.isCreatingCollectionPreset = false;
                    this.$router.push(this.$routerHelper.getCollectionsPath());
                    this.closeModal();
                })
                .catch((error) =>{
                    if (typeof collectionPreset.onError === 'function') {
                        const errorMessage = collectionPreset.onError();
                        
                        this.$buefy.snackbar.open({
                            message: errorMessage,
                            type: 'is-danger',
                            position: 'is-bottom-right',
                            pauseOnHover: true,
                            duration: 3500,
                            queue: false
                        });
                    } else {
                        tainacanErrorHandler(error);
                    }
                    this.isCreatingCollectionPreset = false;
                });
        }
    }
}
</script>

<style lang="scss" scoped>

    @use '../../scss/_cards.scss';

</style>
