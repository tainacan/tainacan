<template>
    <form 
            ref="availableImportersModal"
            action=""
            autofocus
            role="dialog"
            class="tainacan-modal-content"
            tabindex="-1"
            aria-modal>
        <div style="width: auto">
            <header class="tainacan-modal-title">
                <h2>{{ $i18n.get('importers') }}</h2>
                <hr>
            </header>
            <section class="tainacan-form">
                <p>{{ $i18n.get('instruction_select_an_importer_type') }}</p>
                <div 
                        role="list"
                        class="importer-types-container">
                    <template 
                            v-for="importerType in availableImporters"
                            :key="importerType.slug">
                        <div
                                v-if="!(hideWhenManualCollection && !importerType.manual_collection)"
                                role="listitem"
                                class="importer-type"
                                @click="onSelectImporter(importerType)">
                            <h4>{{ importerType.name }}</h4>
                            <p>{{ importerType.description }}</p>            
                        </div>
                    </template>

                    <b-loading 
                            v-model="isLoading"
                            :is-full-page="false" 
                            :can-cancel="false" />
                </div>
                
                <footer class="field is-grouped form-submit">
                    <div class="control">
                        <button 
                                class="button is-outlined" 
                                type="button" 
                                @click="$emit('close')">Close</button>
                    </div>
                    <!-- <div class="control">
                        <button class="button is-success">Confirm</button>
                    </div> -->
                </footer>
            </section>
        </div>
    </form>     
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'AvailableImportersModal',
    props: {
        targetCollection: Number,
        hideWhenManualCollection: false
    },
    emits: [
        'close'
    ],
    data(){
        return {
            availableImporters: [],
            isLoading: false
        }
    },
    mounted() {
        this.isLoading = true;
        this.fetchAvailableImporters()
            .then((res) => {
                this.availableImporters = res;
                this.isLoading = false;
            }).catch((error) => {
                this.$console.log(error);
                this.isLoading = false;
            });

        if (this.$refs.availableImportersModal)
            this.$refs.availableImportersModal.focus();
    },
    methods: {
        ...mapActions('importer', [
            'fetchAvailableImporters'
        ]),
        onSelectImporter(importerType) {
            this.$router.push({ path: this.$routerHelper.getImporterEditionPath(importerType.slug), query: { targetCollection: this.targetCollection } });
            this.$emit('close');
        }
    }
}
</script>

<style lang="scss" scoped>

    .importer-types-container {
        position: relative;

        .importer-type {
            border-bottom: 1px solid var(--tainacan-gray2);
            padding: 15px calc(2 * var(--tainacan-one-column));
            cursor: pointer;
            transition: background-color 0.3s ease;
        
            &:first-child {
                margin-top: 15px;
            }
            &:last-child {
                border-bottom: none;
            }
            &:hover {
                background-color: var(--tainacan-gray2);
            }
        }
    }

</style>


 
