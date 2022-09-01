<template>
    <div class="column">
                           
        <b-loading :active.sync="isLoadingMetadata"/>

        <div class="tainacan-form sub-header">
            <!-- <h3>{{ $i18n.get('metadata') }}<span class="has-text-gray">{{ ( activeMetadatumList && activeMetadatumList.length ? (' (' + activeMetadatumList.length + ')') : '' ) }}</span></h3> -->

            <template v-if="activeMetadatumList && !isLoadingMetadata">
                <button
                        aria-controls="filters-items-list"
                        :aria-expanded="!collapseAll"
                        v-if="activeMetadatumList.length > 0"
                        class="link-style collapse-all"
                        @click="collapseAll = !collapseAll">
                    <span class="icon">
                        <i 
                                :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll }"
                                class="has-text-secondary tainacan-icon tainacan-icon-1-125em"/>
                    </span>
                    <span class="collapse-all__text">
                        {{ collapseAll ? $i18n.get('label_show_less_details') : $i18n.get('label_show_more_details') }}
                    </span>
                </button>
                <b-field class="header-item">
                    <b-dropdown
                            :mobile-modal="true"
                            :disabled="activeMetadatumList.length <= 0"
                            class="show metadata-options-dropdown"
                            aria-role="list"
                            trap-focus>
                        <button
                                :aria-label="$i18n.get('label_filter_by_metadata_type')"
                                class="button is-white"
                                slot="trigger">
                            <span>{{ $i18n.get('label_filter_by_metadata_type') }}</span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"/>
                            </span>
                        </button>
                        <div class="metadata-options-container">
                            <b-dropdown-item
                                    v-for="(metadataType, index) in metadataTypeFilterOptions"
                                    :key="index"
                                    class="control"
                                    custom
                                    aria-role="listitem">
                                <b-checkbox
                                        v-model="metadataType.enabled"
                                        :native-value="metadataType.enabled">
                                    {{ metadataType.name }}
                                </b-checkbox>
                            </b-dropdown-item>   
                        </div>
                    </b-dropdown>
                </b-field>
                <b-field class="header-item">
                    <b-input 
                            :placeholder="$i18n.get('instruction_type_search_metadata_filter')"
                            v-model="metadataNameFilterString"
                            icon="magnify"
                            size="is-small"
                            icon-right="close-circle"
                            icon-right-clickable
                            @icon-right-click="metadataNameFilterString = ''" />
                </b-field>
            </template>
        </div>

        <section 
                v-if="activeMetadatumList.length <= 0 && !isLoadingMetadata"
                class="field is-grouped-centered section">
            <div class="content has-text-gray has-text-centered">
                <p>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-metadata"/>
                    </span>
                </p>
                <p>{{ $i18n.get('info_there_is_no_metadatum' ) }}</p>
                <p>{{ $i18n.get('info_create_metadata' ) }}</p>
            </div>
        </section>
        
        <!-- The Repository Metadata list -->
        <div class="active-metadata-sections-area">
            <draggable 
                    v-model="activeMetadatumList"
                    class="active-metadata-area"
                    @change="handleChange($event)"
                    :group="{ name:'metadata', pull: false, put: true }"
                    :sort="false"
                    :handle="'.handle'"
                    ghost-class="sortable-ghost"
                    chosen-class="sortable-chosen"
                    filter=".not-sortable-item"
                    :prevent-on-filter="false"
                    :animation="250">
                <div    
                        v-for="(metadatum, index) in activeMetadatumList.filter((meta) => meta != undefined && meta.parent == 0)"
                        :key="metadatum.id"
                        v-show="(metadataNameFilterString == '' || filterByMetadatumName(metadatum)) && filterByMetadatumType(metadatum)">
                    <div 
                            class="active-metadatum-item"
                            :class="{
                                'is-compact-item': !isCollapseOpen(metadatum.id),
                                'not-sortable-item': true,
                                'not-focusable-item': openedMetadatumId == metadatum.id,
                                'disabled-metadatum': metadatum.enabled == false,
                                'inherited-metadatum': true,
                                'child-metadatum': metadatum.parent > 0
                            }">
                        <div 
                                :ref="'metadatum-handler-' + metadatum.id"
                                class="handle">
                            <span 
                                    :style="{ opacity: '0.0' }"
                                    v-tooltip="{
                                        content: $i18n.get('info_not_allowed_change_order_metadata'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }"
                                    class="icon grip-icon">
                                <svg 
                                        xmlns="http://www.w3.org/2000/svg" 
                                        height="24px"
                                        viewBox="0 0 24 24"
                                        width="24px"
                                        fill="currentColor">
                                    <path
                                            d="M0 0h24v24H0V0z"
                                            fill="transparent"/>
                                    <path d="M11 18c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm-2-8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6 4c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                </svg>
                            </span>
                            <span 
                                    v-tooltip="{
                                        content: $i18n.get('label_view_metadata_details'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }"
                                    @click="$set(collapses, metadatum.id, !isCollapseOpen(metadatum.id))"
                                    class="gray-icon icon"
                                    :style="{ cursor: 'pointer', opacity: openedMetadatumId != metadatum.id ? '1.0' : '0.0' }">
                                <i :class="'tainacan-icon tainacan-icon-1-25em tainacan-icon-' + (isCollapseOpen(metadatum.id) ? 'arrowdown' : 'arrowright')" />
                            </span>

                            <span class="metadatum-name">
                                    {{ metadatum.name }}
                            </span>
                            <span   
                                    v-if="metadatum.id != undefined && metadatum.metadata_type_object"
                                    class="label-details"
                                    :class="{ 'has-text-weight-bold': metadatum.metadata_type_object.core }">
                                <span 
                                        v-if="metadatum.required === 'yes'"
                                        v-tooltip="{
                                            content: $i18n.get('label_required'),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }">
                                    *&nbsp;
                                </span>
                                ({{ metadatum.metadata_type_object.name }}) 
                                <span 
                                        v-if="metadatum.status === 'private'"
                                        class="icon"
                                        v-tooltip="{
                                            content: $i18n.get('status_private'),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }">
                                    <i class="tainacan-icon tainacan-icon-private"/>
                                </span>
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('label_repository_metadatum'),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }"
                                        class="icon icon-level-identifier">
                                    <i 
                                        :class="{
                                            'has-text-blue5': metadatum.enabled,
                                            'has-text-gray3': !metadatum.enabled
                                        }"
                                        class="tainacan-icon tainacan-icon-repository" />
                                </span>
                            </span>
                            <span 
                                    class="loading-spinner" 
                                    v-if="metadatum.id == undefined"/>
                            <span 
                                    class="controls" 
                                    v-if="metadatum.id !== undefined">
                                <a 
                                        v-if="metadatum.current_user_can_edit"
                                        :style="{ visibility: 
                                                metadatum.collection_id != collectionId
                                                ? 'hidden' : 'visible'
                                            }" 
                                        @click.prevent="toggleMetadatumEdition(metadatum)">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('edit'),
                                                autoHide: true,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'auto-start'
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                                    </span>
                                </a>
                                <a 
                                        v-if="metadatum.current_user_can_delete"
                                        :style="{ visibility: metadatum.collection_id != collectionId || metadatum.metadata_type_object.core ? 'hidden' : 'visible' }"
                                        @click.prevent="removeMetadatum(metadatum)">
                                    <span
                                            v-tooltip="{
                                                content: $i18n.get('delete'),
                                                autoHide: true,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'auto-start'
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-delete"/>
                                    </span>
                                </a>
                            </span>
                        </div>
                        <transition name="form-collapse">
                            <metadatum-details 
                                    v-if="isCollapseOpen(metadatum.id) && openedMetadatumId !== metadatum.id"
                                    :metadatum="metadatum" />
                        </transition>
                    </div>
                    
                    <!-- Child metadata list, inside each compound metadata -->
                    <child-metadata-list
                            v-if="metadatum.metadata_type_object && metadatum.metadata_type_object.component == 'tainacan-compound'"
                            :parent="metadatum"
                            :metadata-name-filter-string="metadataNameFilterString"
                            :metadata-type-filter-options="metadataTypeFilterOptions"
                            :has-some-metadata-type-filter-applied="hasSomeMetadataTypeFilterApplied"
                            :is-parent-multiple="metadatum.multiple == 'yes'"
                            :is-repository-level="true"
                            :collapse-all="collapseAll" />
                    
                    <!-- Metadata edition form, for each metadata -->
                    <b-modal 
                            @close="onEditionCanceled()"
                            :active="openedMetadatumId == metadatum.id"
                            trap-focus
                            aria-modal
                            aria-role="dialog"
                            custom-class="tainacan-modal"
                            :close-button-aria-label="$i18n.get('close')">
                        <metadatum-edition-form
                                :collection-id="collectionId"
                                :original-metadatum="metadatum"
                                :is-repository-level="true"
                                @onEditionFinished="onEditionFinished()"
                                @onEditionCanceled="onEditionCanceled()"
                                :index="index" />
                    </b-modal>

                </div>
            </draggable><!-- End of .active-metadata-area -->
        </div>
    </div> <!-- End of .columns -->
</template>

<script>

import MetadatumEditionForm from '../edition/metadatum-edition-form.vue';
import MetadatumDetails from '../other/metadatum-details.vue';
import ChildMetadataList from '../metadata-types/compound/child-metadata-list.vue';
import CustomDialog from '../other/custom-dialog.vue';
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'RepositoryMetadataList',
    components: {
        MetadatumEditionForm,
        ChildMetadataList,
        MetadatumDetails
    },
    props: {
        metadataTypeFilterOptions: Array
    },
    data() {
        return {
            collectionId: 'default',
            isLoadingMetadata: false,
            openedMetadatumId: '',
            hightlightedMetadatum: '',
            collapses: {},
            collapseAll: false,
            metadataNameFilterString: '',
            metadataSearchCancel: undefined
        }
    },
    computed: {
        hasSomeMetadataTypeFilterApplied() {
            return this.metadataTypeFilterOptions.length && this.metadataTypeFilterOptions.some((metadatumType) => metadatumType.enabled);
        },
        activeMetadatumList: {
            get() {
                return this.getMetadata();
            },
            set(value) {
                this.updateMetadata(value);
            }
        },
    },
    watch: {
        '$route.query': {
            handler(newQuery) {
                if (newQuery.edit != undefined) {
                    let existingMetadataIndex = this.activeMetadatumList.findIndex((metadatum) => metadatum && (metadatum.id == newQuery.edit));
                    if (existingMetadataIndex >= 0)
                        this.editMetadatum(this.activeMetadatumList[existingMetadataIndex])                        
                }
            },
            immediate: true
        },
        collapseAll(isCollapsed) {
            this.activeMetadatumList.forEach((metadatum) => this.$set(this.collapses, metadatum.id, isCollapsed));
        }
    },
    mounted() {
        this.cleanMetadata();
        this.loadMetadata();
        this.$eventBusMetadataList.$on('addMetadatumViaButton', this.addMetadatumViaButton);
    },
    beforeDestroy() {
        // Cancels previous Request
        if (this.metadataSearchCancel != undefined)
            this.metadataSearchCancel.cancel('Metadata search Canceled.');
    },
    methods: {
        ...mapActions('metadata', [
            'fetchMetadata',
            'sendMetadatum',
            'deleteMetadatum',
            'updateMetadata',
            'cleanMetadata'    
        ]),
        ...mapGetters('metadata',[
            'getMetadata'
        ]),
        handleChange(event) {
            if (event.added)
                this.addNewMetadatum(event.added.element, event.added.newIndex);
            else if (event.removed)
                this.removeMetadatum(event.removed.element);
        },
        addMetadatumViaButton(metadatumType) {
            let lastIndex = this.activeMetadatumList.length;
            this.addNewMetadatum(metadatumType, lastIndex);
            
            // Higlights the clicked metadatum
            this.hightlightedMetadatum = metadatumType.name;
            this.$emit('onUpdatehightlightedMetadatum', this.hightlightedMetadatum);
        },
        addNewMetadatum(newMetadatum, newIndex) {
            this.sendMetadatum({
                collectionId: this.collectionId, 
                name: newMetadatum.name, 
                metadatumType: newMetadatum.className, 
                status: 'auto-draft', 
                isRepositoryLevel: true, 
                newIndex: newIndex,
                parent: '0'
            })
            .then((metadatum) => {
                this.toggleMetadatumEdition(metadatum);
                this.hightlightedMetadatum = '';
                this.$emit('onUpdatehightlightedMetadatum', this.hightlightedMetadatum);
            })
            .catch((error) => {
                this.$console.error(error);
            });
        },
        removeMetadatum(removedMetadatum) {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_metadatum_delete'),
                    onConfirm: () => { 
                        this.deleteMetadatum({
                            collectionId: this.collectionId,
                            metadatumId: removedMetadatum.id,
                            isRepositoryLevel: true
                        })
                        .catch(() => {
                            this.$console.log("Error deleting metadatum.")
                        });
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            }); 
        },
        toggleMetadatumEdition(metadatum) {
            this.$router.push({ query: { edit: metadatum.id } });
        },
        editMetadatum(metadatum) {
            this.openedMetadatumId = metadatum.id;
        },
        onEditionFinished() {
            this.openedMetadatumId = '';
            this.$router.push({ query: {}});
        },
        onEditionCanceled() {
            this.openedMetadatumId = '';
            this.$router.push({ query: {}});
        },
        loadMetadata() {
            
            this.isLoadingMetadata = true;

            // Cancels previous Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');

            this.fetchMetadata({
                collectionId: this.collectionId, 
                isRepositoryLevel: true, 
                isContextEdit: true, 
                includeDisabled: true,
                parent: '0',
                includeOptionsAsHtml: true
            }).then((resp) => {
                    resp.request
                        .then(() => {
                            this.isLoadingMetadata = false;
                            
                            // Checks URL as router watcher would not wait for list to load
                            if (this.$route.query.edit != undefined) {
                                let existingMetadataIndex = this.activeMetadatumList.findIndex((metadatum) => metadatum.id == this.$route.query.edit);
                                if (existingMetadataIndex >= 0)
                                    this.editMetadatum(this.activeMetadatumList[existingMetadataIndex]);                        
                            }
                        })
                        .catch(() => {
                            this.isLoadingMetadata = false;
                        });

                    // Search Request Token for cancelling
                    this.metadataSearchCancel = resp.source;
                })
                .catch(() => this.isLoadingMetadata = false);  
        },
        filterByMetadatumName(metadatum) {
            if (metadatum.metadata_type_object && 
                metadatum.metadata_type_object.component == 'tainacan-compound' &&
                metadatum.metadata_type_options &&
                metadatum.metadata_type_options.children_objects &&
                metadatum.metadata_type_options.children_objects.length
            ) {
                let childNamesArray = metadatum.metadata_type_options.children_objects.map((children) => children.name);
                childNamesArray.push(metadatum.name);

                return childNamesArray.some((childName) => childName.toString().toLowerCase().indexOf(this.metadataNameFilterString.toString().toLowerCase()) >= 0);
            }
            else 
                return metadatum.name.toString().toLowerCase().indexOf(this.metadataNameFilterString.toString().toLowerCase()) >= 0;
        },
        filterByMetadatumType(metadatum) {
            if (!this.hasSomeMetadataTypeFilterApplied)
                return true;

            if (metadatum.metadata_type_object && 
                metadatum.metadata_type_object.component == 'tainacan-compound' &&
                metadatum.metadata_type_options &&
                metadatum.metadata_type_options.children_objects &&
                metadatum.metadata_type_options.children_objects.length
            ) {
                let childTypesArray = metadatum.metadata_type_options.children_objects.map((children) => children.metadata_type);
                childTypesArray.push(metadatum.metadata_type);

                for (let metadatumType of this.metadataTypeFilterOptions) {
                    if (metadatumType.enabled && childTypesArray.some((childType) => childType == metadatumType.type))
                        return true;
                }
            } else {
                for (let metadatumType of this.metadataTypeFilterOptions) {
                    if (metadatumType.enabled && metadatum.metadata_type == metadatumType.type)
                        return true;
                }
            }
            return false;
        },
        isCollapseOpen(metadatumId) {
            return this.collapses[metadatumId] == true;
        }
    }
}
</script>