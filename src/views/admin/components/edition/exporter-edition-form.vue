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
                        <b-autocomplete
                                id="tainacan-select-source-collection"
                                v-model="collectionSearch"
                                v-a11y-autocomplete="{ appendToBody: true }"
                                :placeholder="$i18n.get('instruction_select_a_collection')"
                                :data="collections"
                                field="name"
                                clearable
                                icon-right="menu-down"
                                :loading="isFetchingCollections"
                                :append-to-body="true"
                                open-on-focus
                                expanded
                                check-infinite-scroll
                                @select="onSelectCollection"
                                @focus="browseCollections"
                                @active="onCollectionSuggestionsActive"
                                @typing="fetchCollections"
                                @infinite-scroll="fetchMoreCollections">
                            <template #empty>
                                {{ $i18n.get('info_no_options_found') }}
                            </template>
                        </b-autocomplete>
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
                    <p
                            v-if="exporterFilesExpirationNotice"
                            class="help exporter-files-expiration-notice">
                        <span
                                aria-hidden="true"
                                class="icon has-text-warning">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-alertcircle" />
                        </span>
                        <span>{{ exporterFilesExpirationNotice }}</span>
                    </p>
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
    import { tainacanApi, CancelToken, isCancel } from '../../js/axios';

    export default {
        name: "ExporterEditionForm",
        data() {
            return {
                exporterType: '',
                exporterName: '',
                collections: [],
                isFetchingCollections: false,
                collectionSearch: '',
                committedCollectionName: '',
                collectionSearchQuery: '',
                collectionSearchCancel: null,
                collectionsPage: 1,
                totalCollectionPages: 0,
                selectedMapping: undefined,
                selectedCollection: undefined,
                sendEmail: '0',
                runButtonLoading: false,
                exporterSession: {},
                formErrorMessage: '',
                isLoading: false,
                exporterFilesExpirationNotice:
                    typeof tainacan_plugin !== 'undefined'
                        ? tainacan_plugin.exporter_files_expiration_notice
                        : ''
            }
        },
        watch: {
            collectionSearch(name) {
                if (!name)
                    this.onSelectCollection(null);
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

            if (this.selectedCollection != undefined)
                this.fetchSelectedCollection(this.selectedCollection);

            // Set exporter's name
            this.fetchAvailableExporters().then((exporterTypes) => {
            if ( exporterTypes[this.exporterType] ) 
                this.exporterName = exporterTypes[this.exporterType].name;
                this.$routerHelper.appendToPageTitle(this.exporterName);
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
        beforeUnmount() {
            this.cancelCollectionSearch();
        },
        methods: {
            ...mapActions('exporter', [
                'fetchAvailableExporters',
                'createExporterSession',
                'updateExporterSession',
                'runExporterSession'
            ]),
            fetchSelectedCollection(id) {
                return tainacanApi.get('/collections/' + id + '?fetch_only=name,id')
                    .then(res => {
                        const name = res.data && res.data.name ? res.data.name : String(id);
                        this.committedCollectionName = name;
                        this.collectionSearch = name;
                    })
                    .catch(error => {
                        this.$console.error(error);
                        this.committedCollectionName = String(id);
                        this.collectionSearch = String(id);
                    });
            },
            onCollectionSuggestionsActive(isOpen) {
                if (isOpen)
                    return;

                if (this.selectedCollection && this.committedCollectionName && this.collectionSearch !== this.committedCollectionName)
                    this.collectionSearch = this.committedCollectionName;
            },
            cancelCollectionSearch() {
                if (this.collectionSearchCancel) {
                    this.collectionSearchCancel.cancel('Collection search canceled.');
                    this.collectionSearchCancel = null;
                }
            },
            beginCollectionSearch() {
                this.cancelCollectionSearch();
                this.collectionSearchCancel = CancelToken.source();
            },
            browseCollections() {
                this.collectionSearchQuery = '';
                this.collectionsPage = 1;
                this.totalCollectionPages = 0;
                this.beginCollectionSearch();
                this.isFetchingCollections = true;
                this.loadCollectionPage('');
            },
            fetchCollections: _.debounce(function(search) {
                const query = search || '';

                if (this.committedCollectionName && query === this.committedCollectionName)
                    return;

                if (query !== this.collectionSearchQuery) {
                    this.collectionSearchQuery = query;
                    this.collectionsPage = 1;
                    this.totalCollectionPages = 0;
                }

                if (this.totalCollectionPages > 0 && this.collectionsPage > this.totalCollectionPages)
                    return;

                this.beginCollectionSearch();
                this.isFetchingCollections = true;
                this.loadCollectionPage(query);
            }, 500),
            fetchMoreCollections: _.debounce(function() {
                this.fetchCollections(this.collectionSearchQuery);
            }, 250),
            loadCollectionPage(query) {
                const source = this.collectionSearchCancel;
                let endpoint = '/collections?paged=' + this.collectionsPage + '&perpage=12&fetch_only=name,id&order=asc&orderby=title';
                if (query)
                    endpoint += '&search=' + encodeURIComponent(query);

                return tainacanApi.get(endpoint, { cancelToken: source.token })
                    .then(res => {
                        if (this.collectionSearchCancel !== source)
                            return;

                        const pageCollections = Array.isArray(res.data) ? res.data : [];

                        if (this.collectionsPage === 1)
                            this.collections = pageCollections;
                        else {
                            for (let collection of pageCollections)
                                this.collections.push(collection);
                        }

                        this.totalCollectionPages = res.headers['x-wp-totalpages'] ? Number(res.headers['x-wp-totalpages']) : 0;
                        this.collectionsPage++;

                        this.isFetchingCollections = false;
                    })
                    .catch(error => {
                        if (isCancel(error) || this.collectionSearchCancel !== source)
                            return;

                        this.$console.error(error);
                        this.isFetchingCollections = false;
                    });
            },
            onSelectCollection(collection) {
                this.formErrorMessage = null;

                if (!collection || !collection.id) {
                    if (this.collectionSearch || (this.selectedCollection == undefined && !this.committedCollectionName))
                        return;

                    this.committedCollectionName = '';
                    this.selectedCollection = undefined;
                    return;
                }

                this.committedCollectionName = collection.name || '';
                this.collectionSearch = this.committedCollectionName;
                this.selectedCollection = collection.id;
            },
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