<template>
    <div class="tainacan-repository-level-colors page-container">
        <tainacan-title :is-sticky="true" />

        <h2>{{ $i18n.get('label_available_exporters') }}</h2>
        <p>{{ $i18n.get('instruction_select_an_exporter_type') }}</p>
        <div
                role="list"
                class="exporter-types-container tainacan-clickable-cards">
            <template v-for="exporterType in availableExporters">
                <router-link
                        v-if="exporterType.manual_collection"
                        :key="exporterType.slug"
                        class="exporter-type tainacan-clickable-card"
                        :to="$routerHelper.getExporterEditionPath(exporterType.slug) + ( selectedCollection ? ('?sourceCollection=' + selectedCollection) : '' )"
                        role="listitem">
                    <h4>{{ exporterType.name }}</h4>
                    <p>{{ exporterType.description }}</p>
                </router-link>
            </template>
        </div>

        <b-loading
                v-model="isLoading"
                :can-cancel="false" />
    </div>
</template>

<script>
    import { mapActions } from 'vuex';

    export default {
        name: 'AvailableExportersPage',
        data(){
            return {
                availableExporters: [],
                isLoading: false,
                selectedCollection: false
            }
        },
        created() {
            this.isLoading = true;

            this.selectedCollection = this.$route.query.sourceCollection;

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
        }
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_cards.scss';

</style>
