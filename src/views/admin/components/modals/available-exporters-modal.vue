<template>
    <form 
            action=""
            autofocus
            role="dialog"
            class="tainacan-modal-content"
            tabindex="-1"
            aria-modal
            ref="availableExportersModal">
        <div
                class="tainacan-modal-content"
                style="width: auto">
            <header class="tainacan-modal-title">
                <h2>{{ this.$i18n.get('exporters') }}</h2>
                <hr>
            </header>
            
            <section class="tainacan-form">
                <p>{{ $i18n.get('instruction_select_an_exporter_type') }}</p>
                <div 
                        role="list"
                        class="exporter-types-container">
                    <div
                            role="listitem"
                            class="exporter-type"
                            v-for="exporterType in availableExporters"
                            :key="exporterType.slug"
                            v-if="!(hideWhenManualCollection && !exporterType.manual_collection)"
                            @click="onSelectExporter(exporterType)">
                        <h4>{{ exporterType.name }}</h4>
                        <p>{{ exporterType.description }}</p>
                    </div>
                </div>
                <footer class="field is-grouped form-submit">
                    <div class="control">
                        <button
                                id="button-cancel-exporter-selection"
                                class="button is-outlined"
                                type="button"
                                @click="$parent.close();">
                            {{ $i18n.get('cancel') }}</button>
                    </div>
                </footer>

                <b-loading
                        :active.sync="isLoading"
                        :can-cancel="false"/>
            </section>
        </div>
    </form>
</template>

<script>
    import { mapActions } from 'vuex';

    export default {
        name: 'AvailableExportersModal',
        props: {
            sourceCollection: String,
            hideWhenManualCollection: false
        },
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
            ]),
            onSelectExporter(exporterType) {
                this.$router.push({ path: this.$routerHelper.getExporterEditionPath(exporterType.slug), query: { sourceCollection: this.sourceCollection } });
                this.$parent.close();
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .exporter-types-container {

        .exporter-type {
            border-bottom: 1px solid var(--tainacan-gray2);
            padding: 15px 8.3333333%;
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