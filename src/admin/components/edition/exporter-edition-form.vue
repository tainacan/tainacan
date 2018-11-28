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
                    <b-field
                            v-if="exporterSession.manual_collection"
                            :label="$i18n.get('label_origin_collection')">
                        <b-select
                                @input="updateExporter('collection.id')"
                                v-model="selectedCollection"
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

                    <b-field
                            class="is-block"
                            v-if="Object.keys(exporterSession).length &&
                            Object.keys(exporterSession.mapping_accept).length &&
                             exporterSession.mapping_accept.any"
                            :label="$i18n.get('mapping')">
                        <div
                                v-for="(mapping, key) in exporterSession.mapping_list"
                                :key="key">
                            <b-checkbox
                                    @input="updateExporter('mapping_selected')"
                                    class="is-capitalized"
                                    :native-value="key"
                                    v-model="selectedMappings">
                                {{ key.replace(/-/, ' ') }}
                            </b-checkbox>
                        </div>
                    </b-field>

                    <b-field :label="$i18n.get('label_send_email')">
                        <b-input
                                ref="sendEmailREF"
                                :loading="emailLoading"
                                @input="updateEmail()"
                                type="email"
                                v-model="sendEmail"/>
                    </b-field>
                </div>
            </div>
            <div class="columns">
                <div class="column">
                    <button
                            @click.prevent="$router.go(-1)"
                            class="button is-outlined">
                        {{ $i18n.get('cancel') }}
                    </button>
                </div>
                <div class="column">
                    <button
                            :class="{'is-loading': runButtonLoading}"
                            @click.prevent="runExporter()"
                            :disabled="!formIsValid()"
                            class="button is-pulled-right is-turquoise5">
                        {{ $i18n.get('run') }}
                    </button>
                </div>
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
                collections: [],
                isFetchingCollections: false,
                selectedMappings: [],
                selectedCollection: undefined,
                sendEmail: '',
                emailLoading: false,
                runButtonLoading: false,
                exporterSession: {}
            }
        },
        methods: {
            ...mapActions('exporter', [
                'createExporterSession',
                'updateExporterSession',
                'runExporterSession'
            ]),
            ...mapActions('collection', [
                'fetchCollectionsForParent'
            ]),
            runExporter(){
                this.runButtonLoading = true;
                this.runExporterSession(this.exporterSession.id)
                    .then((bgp) => {
                        this.runButtonLoading = false;
                        this.$router.push(this.$routerHelper.getProcessesPage(bgp.bg_process_id));
                    })
                    .catch(() => {
                        this.runButtonLoading = false;
                    });
            },
            formIsValid(){
                return (
                    this.selectedCollection &&
                    this.selectedMappings.length
                );
            },
            updateEmail: _.debounce(function () {
                this.updateExporter('send_email');
            }, 500),
            updateExporter(attributeName){
                if(attributeName === 'collection.id'){
                    let exporterSessionUpdated = {
                        body: {
                            collection: {
                                id: this.selectedCollection,
                            },
                        },
                        id: this.exporterSession.id,
                    };

                    this.updateExporterSession(exporterSessionUpdated)
                        .then(exporterSessionUpdated => this.exporterSession = exporterSessionUpdated);

                } else if (attributeName === 'mapping_selected'){
                    let exporterSessionUpdate = {
                        body: {
                            mapping_selected: this.selectedMappings.length <= 1 ? this.selectedMappings.toString() : this.selectedMappings,
                        },
                        id: this.exporterSession.id,
                    };

                    this.updateExporterSession(exporterSessionUpdate)
                        .then(exporterSessionUpdated => this.exporterSession = exporterSessionUpdated);

                } else if (attributeName === 'send_email' &&
                    this.$refs.sendEmailREF &&
                    this.$refs.sendEmailREF.checkHtml5Validity()){

                    let exporterSessionUpdate = {
                        body: {
                            send_email: this.sendEmail,
                        },
                        id: this.exporterSession.id,
                    };

                    this.emailLoading = true;

                    this.updateExporterSession(exporterSessionUpdate)
                        .then(exporterSessionUpdated => {
                            this.exporterSession = exporterSessionUpdated;
                            this.emailLoading = false;
                        })
                        .catch(() => {
                            this.emailLoading = false;
                        });
                }
            }
        },
        created(){
            this.exporterType = this.$route.params.exporterSlug;

            this.createExporterSession(this.exporterType).then(exporterSession => {
                console.info(exporterSession);
                this.exporterSession = exporterSession ? exporterSession : {};
                this.selectedMappings.push(this.exporterSession.mapping_selected);
            });

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