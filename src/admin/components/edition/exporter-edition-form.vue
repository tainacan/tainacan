<template>
    <div class="repository-level-page page-container">
        <tainacan-title
                :bread-crumb-items="[
                    { path: $routerHelper.getAvailableExportersPath(), label: $i18n.get('exporters') },
                    { path: '', label: exporterType != undefined ? exporterType : $i18n.get('title_exporter_page') }
                ]"/>
        <b-loading
                :active.sync="isLoading"
                :can-cancel="false"/>
        <form
                @click="formErrorMessage = ''"
                label-width="120px"
                v-if="exporterSession"
                class="tainacan-form">
            <div class="columns">

                <div class="column is-6">
                    <form id="exporterOptionsForm">
                        <div v-html="exporterSession.options_form" />
                    </form>
                </div>
                <div
                        style="max-width: 40px;"
                        class="column"/>
                <div class="column is-5">
                    <b-field
                            v-if="exporterSession.manual_collection"
                            :addons="false"
                            :label="$i18n.get('label_source_collection')">
                        <help-button
                                :title="$i18n.get('label_source_collection')"
                                :message="$i18n.get('info_source_collection_helper')"/>
                        <br>
                        <b-select
                                expanded
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

                            <b-select
                                    expanded
                                    @input="updateExporter('mapping_selected')"
                                    v-model="selectedMapping"
                                    :placeholder="$i18n.get('instruction_select_a_mapper')">
                                <option :value="''">-</option>
                                <option
                                        v-for="(mapping, key) in exporterSession.mapping_list"
                                        :value="key"
                                        :key="key">
                                    {{ key.replace(/-/, ' ') }}
                                </option>
                            </b-select>

                    </b-field>

                    <b-field :label="$i18n.get('label_send_email')">
                        <b-input
                                ref="sendEmailREF"
                                :loading="emailLoading"
                                @input="updateEmail()"
                                type="email"
                                placeholder="my@email.com"
                                v-model="sendEmail"/>
                    </b-field>
                </div>
            </div>
            <div class="columns">
                <span class="help is-danger">{{ formErrorMessage }}</span>

                <div class="column">
                    <button
                            @click.prevent="$router.go(-1)"
                            class="button is-pulled-left is-outlined">
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
                selectedMapping: undefined,
                selectedCollection: undefined,
                sendEmail: '',
                emailLoading: false,
                runButtonLoading: false,
                exporterSession: {},
                formErrorMessage: '',
                isLoading: false,
            }
        },
        methods: {
            ...mapActions('exporter', [
                'createExporterSession',
                'updateExporterSession',
                'runExporterSession'
            ]),
            ...mapActions('collection', [
                'fetchCollections'
            ]),
            updateExporterOptions(){
                let formElement = document.getElementById('exporterOptionsForm');
                let formData = new FormData(formElement);

                let options = {};

                for (let [key, value] of formData.entries())
                    options[key] = value;

                let exporterSessionUpdated = {
                    body: {
                        options: options,
                    },
                    id: this.exporterSession.id,
                };

                return this.updateExporterSession(exporterSessionUpdated)
                    .then(exporterSessionUpdated => this.verifyError(exporterSessionUpdated));
            },
            runExporter(){
                this.runButtonLoading = true;

                this.updateExporterOptions().then(() => {

                    if(!this.formErrorMessage) {
                        this.runExporterSession(this.exporterSession.id)
                            .then((bgp) => {
                                this.runButtonLoading = false;
                                this.$router.push(this.$routerHelper.getProcessesPage(bgp.bg_process_id));
                            })
                            .catch(() => {
                                this.runButtonLoading = false;
                            });
                    }
                });
            },
            formIsValid(){
                return (
                    this.selectedCollection &&
                    this.selectedMapping &&
                    !this.formErrorMessage
                );
            },
            updateEmail: _.debounce(function () {
                this.updateExporter('send_email');
            }, 500),
            verifyError(response){
                console.log(response);
                if(response.constructor.name === 'Object' &&
                    (response.data && response.data.status && response.data.status.toString().split('')[0] != 2) || response.error_message) {
                    this.formErrorMessage = response.data.error_message;
                } else {
                    this.exporterSession = response.data;
                }
            },
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
                        .then(exporterSessionUpdated => this.verifyError(exporterSessionUpdated));

                } else if (attributeName === 'mapping_selected'){
                    let exporterSessionUpdate = {
                        body: {
                            mapping_selected: this.selectedMapping ? this.selectedMapping : this.selectedMapping,
                        },
                        id: this.exporterSession.id,
                    };

                    this.updateExporterSession(exporterSessionUpdate)
                        .then(exporterSessionUpdated => this.verifyError(exporterSessionUpdated));

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
                            this.verifyError(exporterSessionUpdated);
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

            this.isLoading = true;
            this.createExporterSession(this.exporterType).then(exporterSession => {
                this.exporterSession = exporterSession ? exporterSession : {};
                this.selectedMapping = this.exporterSession.mapping_selected;
                this.isLoading = false;
            });

            this.isFetchingCollections = true;

            this.fetchCollections({ page: 1, collectionsPerPage: -1})
                .then(response => {
                    this.collections = response.collections;
                    this.isFetchingCollections = false;
                })
                .catch(error => {
                    this.isFetchingCollections = false;
                    this.$console.error(error);
                });
        }
    }
</script>

<style scoped>

</style>