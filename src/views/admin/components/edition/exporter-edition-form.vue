<template>
    <div class="tainacan-repository-level-colors page-container">
        <tainacan-title :is-sticky="true">
            <h1>
                {{ $i18n.get('title_exporter_page') }} 
                <span 
                        v-if="exporterName"
                        class="is-italic has-text-weight-semibold">
                    {{ exporterName }}
                </span>
            </h1>
        </tainacan-title>
        <b-loading
                v-model="isLoading"
                :can-cancel="false" />
        <form
                v-if="exporterSession"
                label-width="120px"
                class="tainacan-form"
                @click="formErrorMessage = ''">
            <div class="columns">

                <div 
                        v-if="exporterSession.options_form"
                        class="column is-gapless">
                    <form id="exporterOptionsForm">
                        <div v-html="exporterSession.options_form" />
                    </form>
                </div>
                <div class="column is-gapless">
                    <b-field
                            v-if="exporterSession.manual_collection"
                            :addons="false"
                            :label="$i18n.get('label_source_collection')">
                        <span class="required-metadatum-asterisk">*</span>
                        <br>
                        <b-select
                                v-model="selectedCollection"
                                expanded
                                :loading="isFetchingCollections"
                                :placeholder="$i18n.get('instruction_select_a_collection')"
                                :required="true"
                                @update:model-value="formErrorMessage = null">
                            <option
                                    v-for="collection in collections"
                                    :key="collection.id"
                                    :value="collection.id">
                                {{ collection.name }}
                            </option>
                        </b-select>
                    </b-field>

                    <transition name="filter-item">
                        <b-field
                                v-if="Object.keys(exporterSession).length &&
                                    Object.keys(exporterSession.mapping_accept).length &&
                                    exporterSession.mapping_list.length &&
                                    selectedCollection"
                                class="is-block"
                                :label="$i18n.get('mapping')">
                            <template #message>
                                <span v-html="$i18n.getWithVariables('instruction_go_to_metadata_mapping_%s', [ $routerHelper.getAbsoluteAdminPath() + $routerHelper.getCollectionMetadataPath(selectedCollection) ])" />
                            </template>
                            <b-select
                                    v-model="selectedMapping"
                                    expanded
                                    :placeholder="$i18n.get('instruction_select_a_mapper')"
                                    @update:model-value="formErrorMessage = null">
                                <option 
                                        v-if="exporterSession.accept_no_mapping"
                                        :value="''">{{ $i18n.get('label_no_mapping') }}</option>
                                <option
                                        v-for="(mapping) in exporterSession.mapping_list"
                                        :key="mapping"
                                        :value="mapping">
                                    {{ mapping.replace(/-/, ' ') }}
                                </option>
                            </b-select>
                        </b-field>
                    </transition>

                    <b-field 
                            :addons="false"
                            :label="$i18n.get('label_send_email')">
                        <help-button
                                :title="$i18n.get('label_send_email')"
                                :message="'<span>' + $i18n.get('info_send_email') + `&nbsp;<a href='` + $routerHelper.getAbsoluteAdminPath() + $routerHelper.getProcessesPath() + `'>` + $i18n.get('activities') + ` ` + $i18n.get('label_page') + '</a></span>'"
                                extra-classes="tainacan-repository-tooltip" />
                        <b-checkbox
                                v-model="sendEmail"
                                true-value="1"
                                false-value="0"
                                @update:model-value="formErrorMessage = null">
                            {{ $i18n.get('label_yes') }}
                        </b-checkbox>
                    </b-field>
                </div>
            </div>
            <div class="columns is-mobile is-multiline">
                <span class="help is-danger">{{ formErrorMessage }}</span>

                <div class="column">
                    <button
                            class="button is-pulled-left is-outlined"
                            @click.prevent="$router.go(-1)">
                        {{ $i18n.get('cancel') }}
                    </button>
                </div>
                <div 
                        v-if="formErrorMessage"
                        class="column">
                    <span class="help is-danger">{{ formErrorMessage }}</span>
                </div>
                <div class="column">
                    <button
                            :class="{'is-loading': runButtonLoading}"
                            :disabled="!formIsValid()"
                            class="button is-pulled-right is-success"
                            @click.prevent="runExporter()">
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
        data() {
            return {
                exporterType: '',
                exporterName: '',
                collections: [],
                isFetchingCollections: false,
                selectedMapping: undefined,
                selectedCollection: undefined,
                sendEmail: '0',
                runButtonLoading: false,
                exporterSession: {},
                formErrorMessage: '',
                isLoading: false
            }
        },
        created() {
            this.selectedCollection = this.$route.query.sourceCollection;

            this.exporterType = this.$route.params.exporterSlug;

            this.isLoading = true;
            this.createExporterSession(this.exporterType)
                .then(exporterSession => {
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


            // Set exporter's name
            this.fetchAvailableExporters().then((exporterTypes) => {
            if ( exporterTypes[this.exporterType] ) 
                this.exporterName = exporterTypes[this.exporterType].name;
                this.$routerHelper.appendToPageTitle(this.importerName);
                wp.hooks.doAction(
                'tainacan_navigation_path_updated', 
                    { 
                        currentRoute: this.$route,
                        adminOptions: this.$adminOptions,
                        parentEntity: {
                            rootLink: 'exporters',
                            name: this.exporterName,
                            defaultLink: `exporters/${this.exporterType}/edit`,
                            label: this.$i18n.get('exporters')
                        }
                    }
                );
            });
        },
        methods: {
            ...mapActions('exporter', [
                'fetchAvailableExporters',
                'createExporterSession',
                'updateExporterSession',
                'runExporterSession'
            ]),
            ...mapActions('collection', [
                'fetchCollections'
            ]),
            runExporter(){
                this.runButtonLoading = true;

                let formElement = document.getElementById('exporterOptionsForm');
                let formData = new FormData(formElement);
 
                let options = {};

                for (let [key, value] of formData.entries())
                    options[key] = value;

                let exporterSessionUpdated = {
                    body: {
                        mapping_selected: this.selectedMapping,
                        send_email: this.sendEmail,
                        options: options
                    },
                    id: this.exporterSession.id,
                };

                if (this.exporterSession.manual_collection) {
                    exporterSessionUpdated['body']['collection'] = {
                        id: this.selectedCollection
                    };
                }             

                this.updateExporterSession(exporterSessionUpdated)
                    .then(() => {

                        if (!this.formErrorMessage) {
                            this.runExporterSession(this.exporterSession.id)
                                .then((bgp) => {
                                    this.runButtonLoading = false;
                                    this.$router.push(this.$routerHelper.getProcessesPath(bgp.bg_process_id));
                                })
                                .catch((error) => {
                                    this.formErrorMessage = error.error_message;
                                    this.runButtonLoading = false;
                                });
                        }

                    })
                    .catch((error) => {
                        this.formErrorMessage = error.error_message;
                        this.runButtonLoading = false;
                    }); 
            },
            formIsValid(){
                return (
                    ((this.exporterSession.manual_collection && this.selectedCollection) || !this.exporterSession.manual_collection) &&
                    ((!this.exporterSession.accept_no_mapping && this.selectedMapping) ||
                        this.exporterSession.accept_no_mapping) &&
                    !this.formErrorMessage
                );
            }
        }
    }
</script>

<style scoped>

    .tainacan-form >.columns {
        padding: var(--tainacan-container-padding) var(--tainacan-one-column) 0 var(--tainacan-one-column);
    }

</style>