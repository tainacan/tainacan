<template>
    <div class="repository-level-page page-container">
        <tainacan-title
                :bread-crumb-items="[
                    { path: $routerHelper.getAvailableExportersPath(), label: $i18n.get('exporters') },
                    { path: '', label: exporterType != undefined ? exporterType : $i18n.get('title_exporter_page') }
                ]"/>

        <form class="tainacan-form">
            <div class="columns">
                <div class="column">
                    <div v-html="exporterSession.options_form" />
                </div>
                <div class="column">
                    <b-field :label="$i18n.get('label_origin_collection')">
                        <b-select
                                :loading="isFetchingCollections"
                                :placeholder="$i18n.get('instruction_select_a_collection')">
                            <option
                                    v-for="collection in collections"
                                    :value="collection.id"
                                    :key="collection.id">
                                {{ collection.name }}
                            </option>
                        </b-select>
                    </b-field>
                </div>
            </div>
        </form>
    </div>
</template>

<script>

    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: "ExporterEditionForm",
        data(){
            return {
                exporterType: '',
                collections: [],
                isFetchingCollections: false,
            }
        },
        methods: {
            ...mapActions('exporter', [
                'createExporterSession'
            ]),
            ...mapGetters('exporter', [
                'getExporterSession'
            ]),
            ...mapActions('collection', [
                'fetchCollectionsForParent'
            ]),
        },
        computed: {
            exporterSession(){
                let ex = this.getExporterSession();
                console.log(ex);
                return ex;
            },
        },
        created(){
            this.exporterType = this.$route.params.exporterSlug;
            this.createExporterSession(this.exporterType);

            this.isFetchingCollections = true;

            this.fetchCollectionsForParent()
                .then(collections => {
                    this.collections = collections;
                    this.isFetchingCollections = false;
                })
                .catch(error => {
                    this.isFetchingCollections = false;
                    console.error(error);
                });
        }
    }
</script>

<style scoped>

</style>