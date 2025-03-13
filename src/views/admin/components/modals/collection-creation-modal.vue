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
                    @click="$emit('close')">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-close tainacan-icon-1-125em" />
                </span>
            </button>
        </header>
        <section class="tainacan-form">
            <div 
                    v-if="selectedEstrategy == undefined"
                    class="collection-creation-options-container tainacan-clickable-cards">
                
                <button
                        class="collection-creation-option tainacan-clickable-card"
                        role="listitem"
                        @click="selectedEstrategy = 'mappers'">
                    <h3>{{ $i18n.get('label_from_a_mapper') }}</h3>
                    <p>{{ $i18n.get('info_create_collection_from_mapper') }}</p>
                </button>

                <button
                        class="collection-creation-option tainacan-clickable-card"
                        role="listitem"
                        @click="selectedEstrategy = 'presets'">
                    <h3>{{ $i18n.get('label_using_a_preset') }}</h3>
                    <p>{{ $i18n.get('info_create_collection_from_preset') }}</p>
                </button>

            </div>
            
            <div 
                    v-if="selectedEstrategy == 'mappers'"
                    class="collection-creation-options-container tainacan-clickable-cards"
                    role="list">
                <template 
                        v-for="metadatumMapper in metadatumMappers"
                        :key="metadatumMapper.slug">
                    <button
                            v-if="metadatumMapper.metadata != false"
                            class="collection-creation-option tainacan-clickable-card"
                            role="listitem"
                            @click="$router.push($routerHelper.getNewMappedCollectionPath(metadatumMapper.slug)); $emit('close');">
                        <h3>{{ metadatumMapper.name }}</h3>
                        <p v-if="metadatumMapper.description">
                            {{ metadatumMapper.description }}
                        </p>
                    </button>
                </template>
            </div>

            <div 
                    v-if="selectedEstrategy == 'presets'"
                    class="collection-creation-options-container tainacan-clickable-cards"
                    role="list">
                <button
                        v-for="collectionPreset in getPresetsHook"
                        :key="collectionPreset.slug"
                        class="collection-creation-option tainacan-clickable-card"
                        role="listitem"
                        @click="onNewCollectionPreset(collectionPreset)">
                    <h3>{{ collectionPreset.name }}</h3>
                    <p v-if="collectionPreset.description">
                        {{ collectionPreset.description }}
                    </p>
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
                            @click="$emit('close')">{{ $i18n.get('close') }}</button>
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
        'close'
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
                    this.$emit('close');
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

    @import '../../scss/_cards.scss';

</style>
