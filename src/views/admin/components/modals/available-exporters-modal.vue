<template>
    <form 
            ref="availableExportersModal"
            action=""
            autofocus
            role="dialog"
            class="tainacan-modal-content"
            :class="{ 'tainacan-repository-level-colors': isNaN(sourceCollection) || !sourceCollection }"
            tabindex="-1"
            aria-modal>
        <div style="width: auto">
            <header class="tainacan-modal-title">
                <h2>{{ $i18n.get('exporters') }}</h2>
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
                <p>{{ $i18n.get('instruction_select_an_exporter_type') }}</p>
                <div 
                        role="list"
                        class="exporter-types-container tainacan-clickable-cards">
                    <template 
                            v-for="exporterType in availableExporters"
                            :key="exporterType.slug">
                        <router-link
                                v-if="!(hideWhenManualCollection && !exporterType.manual_collection)"
                                role="listitem"
                                class="exporter-type tainacan-clickable-card"
                                :to="{ path: $routerHelper.getExporterEditionPath(exporterType.slug), query: { sourceCollection: sourceCollection } }"
                                @click="$emit('close')">
                            <h4>{{ exporterType.name }}</h4>
                            <p>{{ exporterType.description }}</p>
                        </router-link>
                    </template>
                </div>
                <footer class="field is-grouped form-submit">
                    <div class="control">
                        <button
                                id="button-cancel-exporter-selection"
                                class="button is-outlined"
                                type="button"
                                @click="$emit('close');">
                            {{ $i18n.get('cancel') }}</button>
                    </div>
                </footer>

                <b-loading
                        v-model="isLoading"
                        :can-cancel="false" />
            </section>
        </div>
    </form>
</template>

<script>
    import { mapActions } from 'vuex';

    export default {
        name: 'AvailableExportersModal',
        props: {
            sourceCollection: [Number,String],
            hideWhenManualCollection: false
        },
        emits: [
            'close'
        ],   
        data(){
            return {
                availableExporters: [],
                isLoading: false
            }
        },
        mounted() {
            this.isLoading = true;
            this.fetchAvailableExporters()
                .then((data) => {
                    this.availableExporters = data;
                    this.isLoading = false;
                }).catch((error) => {
                    this.$console.log(error);
                    this.isLoading = false;
                });

            if (this.$refs.availableExportersModal)
                this.$refs.availableExportersModal.focus();
        },
        methods: {
            ...mapActions('exporter', [
                'fetchAvailableExporters'
            ])
        }
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_cards.scss';

</style>
