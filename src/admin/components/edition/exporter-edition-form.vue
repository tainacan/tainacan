<template>
    <div class="repository-level-page page-container">
        <tainacan-title
                :bread-crumb-items="[
                    { path: $routerHelper.getAvailableExportersPath(), label: $i18n.get('exporters') },
                    { path: '', label: exporterType != undefined ? exporterType : $i18n.get('title_exporter_page') }
                ]"/>

        <form class="tainacan-form">
            <div class="columns">
                <div class="column">C1</div>
                <div class="column">C2</div>
            </div>
        </form>
    </div>
</template>

<script>

    import { mapActions } from 'vuex';

    export default {
        name: "ExporterEditionForm",
        data(){
            return {
                exporterType: '',
                exporterSession: {}
            }
        },
        methods: {
            ...mapActions('exporter', [
                'createExporterSession'
            ]),
        },
        created(){
            this.exporterType = this.$route.params.exporterSlug;
            this.exporterSession = this.createExporterSession();
        }
    }
</script>

<style scoped>

</style>