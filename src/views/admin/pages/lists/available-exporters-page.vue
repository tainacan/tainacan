<template>
    <div class="tainacan-repository-level-colors page-container">
        <tainacan-title :is-sticky="true" />

        <h2>{{ $i18n.get('label_available_exporters') }}</h2>
        <p>{{ $i18n.get('instruction_select_an_exporter_type') }}</p>
        <div
                :role="Object.keys(availableExporters).length > 1 ? 'list' : undefined"
                class="exporter-types-container tainacan-clickable-cards">
            <template 
                    v-for="exporterType in availableExporters"
                    :key="exporterType.slug">
                <router-link
                        class="exporter-type tainacan-clickable-card"
                        :to="$routerHelper.getExporterEditionPath(exporterType.slug) + ( selectedCollection ? ('?sourceCollection=' + selectedCollection) : '' )"
                        :role="Object.keys(availableExporters).length > 1 ? 'listitem' : undefined">
                    <dl class="exporter-type-definition">
                        <dt class="exporter-type-name">
                            {{ exporterType.name }}
                        </dt>
                        <dd class="exporter-type-description">
                            {{ exporterType.description }}
                        </dd>
                    </dl>
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
                availableExporters: {},
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

    @use '../../scss/_cards.scss';

</style>
