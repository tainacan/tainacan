<template>
    <div class="repository-level-page page-container">
        <tainacan-title
                :bread-crumb-items="[{ path: '', label: $i18n.get('exporters') }]" />

        <h3>{{ $i18n.get('label_available_exporters') }}</h3>
        <p>{{ $i18n.get('instruction_select_an_exporter_type') }}</p>
        <div class="exporter-types-container">
            <div
                    class="exporter-type"
                    v-for="exporterType in availableExporters"
                    :key="exporterType.slug"
                    @click="onSelectExporter(exporterType)">
                <h4>{{ exporterType.name }}</h4>
                <p>{{ exporterType.description }}</p>
            </div>

        </div>

        <b-loading
                :active.sync="isLoading"
                :can-cancel="false"/>
    </div>
</template>

<script>
    import { mapActions } from 'vuex';

    export default {
        name: 'AvailableExportersPage',
        data(){
            return {
                availableExporters: [],
                isLoading: false
            }
        },
        created() {
            this.isLoading = true;
            this.fetchAvailableExporters()
                .then((res) => {
                    this.availableExporters = res;
                    this.isLoading = false;
                }).catch((error) => {
                    this.$console.log(error);
                    this.isLoading = false;
            });
        },
        methods: {
            ...mapActions('exporter', [
                'fetchAvailableExporters'
            ]),
            onSelectExporter(exporterType) {
                this.$router.push(this.$routerHelper.getExporterEditionPath(exporterType.slug));
            }
        }
    }
</script>

<style lang="scss" scoped>

    .exporter-types-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin: 20px;

        .exporter-type {
            border: 1px solid var(--tainacan-gray2);
            padding: 15px;
            min-height: 100px;
            cursor: pointer;
            background-color: var(--tainacan-item-background-color);
            transition: border 0.3s ease, background-color 0.15s ease;

            &:hover {
                background-color: var(--tainacan-item-hover-background-color);
                border: 1px solid var(--tainacan-gray3);
            }
        }
    }

</style>


