<template>
    <div 
            ref="collectionCreationModal"
            aria-labelledby="collection-creation-title"
            autofocus
            role="dialog"
            tabindex="-1"
            aria-modal 
            class="tainacan-modal-content"
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
            <a 
                    v-if="(hasPresetsHook && selectedEstrategy != undefined) || (!hasPresetsHook && selectedEstrategy == 'mappers')"
                    class="back-link"
                    @click="selectedEstrategy = hasPresetsHook ? undefined : 'mappers'">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </header>
        <section class="tainacan-form">
            <div 
                    v-if="selectedEstrategy == undefined"
                    class="collection-creation-options-container">
                
                <button
                        class="collection-creation-option"
                        aria-role="listitem"
                        @click="selectedEstrategy = 'mappers'">
                    <h3>{{ $i18n.get('label_from_a_mapper') }}</h3>
                    <p>{{ $i18n.get('info_create_collection_from_mapper') }}</p>
                </button>

                <button
                        class="collection-creation-option"
                        aria-role="listitem"
                        @click="selectedEstrategy = 'presets'">
                    <h3>{{ $i18n.get('label_using_a_preset') }}</h3>
                    <p>{{ $i18n.get('info_create_collection_from_preset') }}</p>
                </button>

            </div>
            
            <div 
                    v-if="selectedEstrategy == 'mappers'"
                    class="collection-creation-options-container"
                    role="list">
                <template 
                        v-for="metadatumMapper in metadatumMappers"
                        :key="metadatumMapper.slug">
                    <button
                            v-if="metadatumMapper.metadata != false"
                            class="collection-creation-option"
                            aria-role="listitem"
                            @click="$router.push($routerHelper.getNewMappedCollectionPath(metadatumMapper.slug)); $emit('close');">
                        <h3>{{ metadatumMapper.name }}</h3>
                        <p>{{ metadatumMapper.description }}</p>
                    </button>
                </template>
            </div>

            <div 
                    v-if="selectedEstrategy == 'presets'"
                    class="collection-creation-options-container"
                    role="list">
                <button
                        v-for="collectionPreset in getPresetsHook"
                        :key="collectionPreset.slug"
                        class="collection-creation-option"
                        aria-role="listitem"
                        @click="onNewCollectionPreset(collectionPreset)">
                    <h3>{{ collectionPreset.name }}</h3>
                    <p>{{ collectionPreset.description }}</p>
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
        metadatumMappers() {
            return this.getMetadatumMappers();
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
        ...mapGetters('metadata', [
            'getMetadatumMappers'
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

    .tainacan-modal-title {
        margin-bottom: 24px;

        h2 {
            margin-bottom: 0;
        }
        .back-link {
            color: var(--tainacan-secondary);
            cursor: pointer;
        }
    }

    .collection-creation-options-container {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        
        p {
            font-size: 1em;
            color: var(--tainacan-gray5);
            padding: 0em 1.25em;
            margin-top: 0.75em;
            margin-bottom: 0;
        }

        
        .collection-creation-option {
            border: 1px solid var(--tainacan-input-border-color);
            background-color: var(--tainacan-background-color);
            text-align: left;
            padding: 15px;
            cursor: pointer;
            flex-basis: calc(50% - 12px);
            flex-grow: 1;
            font-size: 1em;
            transition: border 0.3s ease;

            @media screen and (max-width: 768px) {
                max-width: 100%;
                margin: 12px;
            }

            h3 {
                color: var(--tainacan-heading-color);
                font-size: 1em !important;
                font-weight: 500;
                padding: 0em 0.5em;
                margin: 0;
            }
            p {
                font-size: 0.75em;
                color: var(--tainacan-gray5);
                padding: 0em 0.5em;
                margin-bottom: 0;
            }

            &:hover {
                border: 1px solid var(--tainacan-gray5);
            }
        }
    }
    
</style>


 
