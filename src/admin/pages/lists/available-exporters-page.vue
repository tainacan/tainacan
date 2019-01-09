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
        methods: {
            ...mapActions('exporter', [
                'fetchAvailableExporters'
            ]),
            onSelectExporter(exporterType) {
                this.$router.push(this.$routerHelper.getExporterEditionPath(exporterType.slug));
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
        }

    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .exporter-types-container {
        display: flex;
        flex-wrap: wrap;

        .exporter-type {
            border: 1px solid $gray2;
            padding: 15px;
            margin: 20px;
            cursor: pointer;
        }
    }

</style>


