<template>
    <div :class="{ 'repository-level-page page-container': isRepositoryLevel }">
        <tainacan-title 
                :bread-crumb-items="[{ path: '', label: this.$i18n.get('metadata') }]"/>
        
        <template v-if="isRepositoryLevel">
            <p>{{ $i18n.get('info_repository_metadata_inheritance') }}</p>
            <br>
        </template>
        
        <div class="metadata-list-page">
            <b-tabs 
                    v-if="(isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_metadata') || (!isRepositoryLevel && collection && collection.current_user_can_edit_metadata))"
                    v-model="activeTab">    
                <b-tab-item :label="$i18n.get('metadata')">
                    <div
                            :style="{ height: activeMetadatumList && activeMetadatumList.length <= 0 && !isLoadingMetadata ? 'auto' : 'calc(100vh - 6px - ' + columnsTopY + 'px)'}"
                            class="columns"
                            ref="metadataEditionPageColumns">
                        <b-loading :active.sync="isLoadingMetadatumTypes"/>

                        <div class="column">
                           
                            <div class="tainacan-form sub-header">
                                <h3>{{ $i18n.get('metadata') }}</h3>

                                <template v-if="activeMetadatumList && !isLoadingMetadata">
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
                                    {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                                </span>
                            </button>

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

                            <draggable 
                                    v-model="activeMetadatumList"
                                    class="active-metadata-area"
                                    @change="handleChange($event)"
                                    :group="{ name:'metadata', pull: false, put: true }"
                                    :sort="(openedMetadatumId == '' || openedMetadatumId == undefined) && !isRepositoryLevel"
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
                                                'not-sortable-item': isRepositoryLevel || metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder || metadataNameFilterString != '' || hasSomeMetadataTypeFilterApplied,
                                                'not-focusable-item': openedMetadatumId == metadatum.id,
                                                'disabled-metadatum': metadatum.enabled == false,
                                                'inherited-metadatum': metadatum.inherited || isRepositoryLevel,
                                                'child-metadatum': metadatum.parent > 0
                                            }">
                                        <div 
                                                :ref="'metadatum-handler-' + metadatum.id"
                                                class="handle">
                                            <span 
                                                    v-tooltip="{
                                                        content: $i18n.get('label_view_metadata_details'),
                                                        autoHide: true,
                                                        classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                                        placement: 'auto-start'
                                                    }"
                                                    @click="$set(collapses, metadatum.id, !isCollapseOpen(metadatum.id))"
                                                    class="gray-icon icon"
                                                    :style="{ cursor: 'pointer', opacity: openedMetadatumId != metadatum.id ? '1.0' : '0.0' }">
                                                <i :class="'tainacan-icon tainacan-icon-1-25em tainacan-icon-' + (isCollapseOpen(metadatum.id) ? 'arrowdown' : 'arrowright')" />
                                            </span>
                                            <span 
                                                    :style="{ opacity: !(isRepositoryLevel || metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder || metadataNameFilterString != '' || hasSomeMetadataTypeFilterApplied) ? '1.0' : '0.0' }"
                                                    v-tooltip="{
                                                        content: isRepositoryLevel || metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder ? $i18n.get('info_not_allowed_change_order_metadata') : $i18n.get('instruction_drag_and_drop_metadatum_sort'),
                                                        autoHide: true,
                                                        classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                                        placement: 'auto-start'
                                                    }"
                                                    class="icon grip-icon">
                                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-drag"/>
                                            </span>
                                            <span 
                                                    class="metadatum-name"
                                                    :class="{'is-danger': formWithErrors == metadatum.id }">
                                                    {{ metadatum.name }}
                                            </span>
                                            <span   
                                                    v-if="metadatum.id != undefined && metadatum.metadata_type_object"
                                                    class="label-details"
                                                    :class="{ 'has-text-weight-bold': metadatum.metadata_type_object.core }">  
                                                ({{ metadatum.metadata_type_object.name }}) 
                                                <em v-if="metadatum.inherited">{{ $i18n.get('label_inherited') }}</em>
                                                <span 
                                                    class="not-saved" 
                                                    v-if="(editForms[metadatum.id] != undefined && editForms[metadatum.id].saved != true) || metadatum.status == 'auto-draft'">
                                                {{ $i18n.get('info_not_saved') }}
                                                </span>
                                                <span 
                                                        v-if="metadatum.status === 'private'"
                                                        class="icon"
                                                        v-tooltip="{
                                                            content: $i18n.get('status_private'),
                                                            autoHide: true,
                                                            classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                                            placement: 'auto-start'
                                                        }">
                                                    <i class="tainacan-icon tainacan-icon-private"/>
                                                </span>
                                                <span 
                                                        v-if="metadatum.required === 'yes'"
                                                        v-tooltip="{
                                                            content: $i18n.get('label_required'),
                                                            autoHide: true,
                                                            classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                                            placement: 'auto-start'
                                                        }">
                                                    *&nbsp;
                                                </span>
                                                <span 
                                                        v-tooltip="{
                                                            content: (metadatum.collection_id == 'default') || isRepositoryLevel ? $i18n.get('label_repository_metadatum') : $i18n.get('label_collection_metadatum'),
                                                            autoHide: true,
                                                            classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                                            placement: 'auto-start'
                                                        }"
                                                        class="icon icon-level-identifier">
                                                    <i 
                                                        :class="{ 
                                                            'tainacan-icon-collections': (metadatum.collection_id != 'default' && !isRepositoryLevel), 
                                                            'tainacan-icon-repository': (metadatum.collection_id == 'default') || isRepositoryLevel,
                                                            'has-text-turquoise5': metadatum.enabled && (metadatum.collection_id != 'default' && !isRepositoryLevel), 
                                                            'has-text-blue5': metadatum.enabled && (metadatum.collection_id == 'default' || isRepositoryLevel),
                                                            'has-text-gray3': !metadatum.enabled
                                                        }"
                                                        class="tainacan-icon" />
                                                </span>
                                            </span>
                                            <span 
                                                    class="loading-spinner" 
                                                    v-if="metadatum.id == undefined"/>
                                            <span 
                                                    class="controls" 
                                                    v-if="metadatum.id !== undefined">
                                                <b-switch 
                                                        v-if="!isRepositoryLevel"
                                                        :disabled="isUpdatingMetadataOrder"
                                                        size="is-small" 
                                                        :value="metadatum.enabled"
                                                        @input="onChangeEnable($event, index)"/>
                                                <a 
                                                        v-if="metadatum.current_user_can_edit"
                                                        :style="{ visibility: 
                                                                metadatum.collection_id != collectionId
                                                                ? 'hidden' : 'visible'
                                                            }" 
                                                        @click.prevent="toggleMetadatumEdition(metadatum.id)">
                                                    <span 
                                                            v-tooltip="{
                                                                content: $i18n.get('edit'),
                                                                autoHide: true,
                                                                classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
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
                                                                classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
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
                                        <transition name="form-collapse">
                                            <div v-if="openedMetadatumId == metadatum.id">
                                                <metadatum-edition-form
                                                        :collection-id="collectionId"
                                                        :is-repository-level="isRepositoryLevel"
                                                        @onEditionFinished="onEditionFinished()"
                                                        @onEditionCanceled="onEditionCanceled()"
                                                        @onErrorFound="formWithErrors = metadatum.id"
                                                        :index="index"
                                                        :original-metadatum="metadatum"
                                                        :edited-metadatum="editForms[metadatum.id]"/>
                                            </div>
                                        </transition>
                                    </div>
                                    <child-metadata-list
                                            v-if="metadatum.metadata_type_object && metadatum.metadata_type_object.component == 'tainacan-compound'"
                                            :parent.sync="metadatum"
                                            :metadata-name-filter-string="metadataNameFilterString"
                                            :metadata-type-filter-options="metadataTypeFilterOptions"
                                            :has-some-metadata-type-filter-applied="hasSomeMetadataTypeFilterApplied"
                                            :is-parent-multiple="metadatum.multiple == 'yes' || (editForms[metadatum.id] && editForms[metadatum.id].multiple == 'yes')"
                                            :is-repository-level="isRepositoryLevel"
                                            :collapse-all="collapseAll" />
                                </div>
                            </draggable> 
                        </div>
                        
                        <div 
                                v-if="(isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_metadata')) || !isRepositoryLevel"
                                class="column available-metadata-area" >
                            <div class="field">
                                <h3 class="label">{{ $i18n.get('label_available_metadata_types') }}</h3>
                                <draggable 
                                        v-model="availableMetadatumList"
                                        :sort="false" 
                                        :group="{ name:'metadata', pull: 'clone', put: false, revertClone: true }"
                                        drag-class="sortable-drag">
                                    <div 
                                            :id="metadatum.component"
                                            @click.prevent="addMetadatumViaButton(metadatum)"
                                            class="available-metadatum-item"
                                            :class="{ 'hightlighted-metadatum' : hightlightedMetadatum == metadatum.name, 'inherited-metadatum': metadatum.inherited || isRepositoryLevel }"
                                            v-for="(metadatum, index) in availableMetadatumList"
                                            :key="index">
                                        <span
                                                v-tooltip="{
                                                    content: $i18n.get('instruction_click_or_drag_metadatum_create'),
                                                    autoHide: true,
                                                    classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                                    placement: 'auto-start'
                                                }"   
                                                class="icon grip-icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-drag"/>
                                        </span>
                                        <span class="metadatum-name">
                                            {{ metadatum.name }}
                                            <span 
                                                    v-tooltip.top="{
                                                        classes: ['metadata-type-preview-tooltip'],
                                                        content: getPreviewTemplateContent(metadatum),
                                                        html: true
                                                    }"
                                                    class="icon preview-help-icon has-text-secondary">
                                                <i class="tainacan-icon tainacan-icon-help"/>
                                            </span>
                                        </span>
                                        <span 
                                                class="loading-spinner" 
                                                v-if="hightlightedMetadatum == metadatum.name"/>
                                    </div>
                                </draggable>
                            </div>
                        </div> 
                    </div>
                </b-tab-item>

                <!-- Mapping --------------- -->
                <b-tab-item :label="$i18n.get('mapping')">
                    <metadata-mapping-list :is-repository-level="isRepositoryLevel"/>
                </b-tab-item>
            </b-tabs>
            <section 
                    v-else
                    class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-30px tainacan-icon-metadata"/>
                        </span>
                    </p>
                    <p>{{ $i18n.get('info_can_not_edit_metadata') }}</p>
                </div>
            </section>
            </div>
    </div>
</template>

<script>
import MetadataMappingList from '../../components/lists/metadata-mapping-list.vue';
import MetadatumEditionForm from '../../components/edition/metadatum-edition-form.vue';
import MetadatumDetails from '../../components/other/metadatum-details.vue';
import ChildMetadataList from '../../components/metadata-types/compound/child-metadata-list.vue';
import CustomDialog from '../../components/other/custom-dialog.vue';
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'MetadataPage',
    components: {
        MetadataMappingList,
        MetadatumEditionForm,
        ChildMetadataList,
        MetadatumDetails
    },
    data() {
        return {
            isRepositoryLevel: false,
            activeTab: 0,
            collectionId: '',
            isLoadingMetadatumTypes: true,
            isLoadingMetadata: false,
            isUpdatingMetadataOrder: false,
            openedMetadatumId: '',
            formWithErrors: '',
            hightlightedMetadatum: '',
            editForms: {},
            collapses: {},
            columnsTopY: 0,
            collapseAll: false,
            metadataNameFilterString: '',
            metadataTypeFilterOptions: []
        }
    },
    computed: {
        collection() {
            return this.getCollection();
        },
        hasSomeMetadataTypeFilterApplied() {
            return this.metadataTypeFilterOptions.length && this.metadataTypeFilterOptions.some((metadatumType) => metadatumType.enabled);
        },
        availableMetadatumList: {
            get() {
                return this.getMetadatumTypes();
            },
            set(value) {
                return this.updateMetadatumTypes(value);
            }
        },
        activeMetadatumList: {
            get() {
                return this.getMetadata();
            },
            set(value) {
                this.updateMetadata(value);
            }
        }
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
    beforeRouteLeave ( to, from, next ) {
        
        let hasUnsavedForms = false;
        for (let editForm in this.editForms) {
            if (!this.editForms[editForm].saved) 
                hasUnsavedForms = true;
        }
        if ((this.openedMetadatumId != '' && this.openedMetadatumId != undefined) || hasUnsavedForms ) {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_metadata_not_saved'),
                    onConfirm: () => {
                        this.onEditionCanceled();
                        next();
                    },
                },
                trapFocus: true,
                customClass: 'tainacan-modal'
            });  
        } else {
            next();
        }  
    },
    created() {
        this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
    },
    mounted() {

        if (!this.isRepositoryLevel)
            this.$root.$emit('onCollectionBreadCrumbUpdate', [{ path: '', label: this.$i18n.get('metadata') }]);

        this.$nextTick(() => { 
            this.columnsTopY = this.$refs.metadataEditionPageColumns ? this.$refs.metadataEditionPageColumns.getBoundingClientRect().top : 0;
        });

        this.cleanMetadata();
        this.isLoadingMetadatumTypes = true;

        this.fetchMetadatumTypes()
            .then(() => {
                this.metadataTypeFilterOptions = JSON.parse(JSON.stringify(this.getMetadatumTypes()))
                    .map((metadatumType) => {
                        return {
                            enabled: false,
                            name: metadatumType.name,
                            type: metadatumType.className
                        }
                    });
                this.isLoadingMetadatumTypes = false;
            })
            .catch(() => {
                this.isLoadingMetadatumTypes = false;
            });
        this.refreshMetadata();
    },
    beforeDestroy() {

        // Cancels previous Request
        if (this.metadataSearchCancel != undefined)
            this.metadataSearchCancel.cancel('Metadata search Canceled.');

    },
    methods: {
         ...mapGetters('collection', [
            'getCollection',
        ]),
        ...mapActions('metadata', [
            'fetchMetadatumTypes',
            'updateMetadatumTypes',
            'fetchMetadata',
            'sendMetadatum',
            'deleteMetadatum',
            'updateMetadata',
            'updateCollectionMetadataOrder',
            'cleanMetadata',
        ]),
        ...mapGetters('metadata',[
            'getMetadatumTypes',
            'getMetadata',
        ]),
        handleChange(event) {
            if (event.added) {
                this.addNewMetadatum(event.added.element, event.added.newIndex);
            } else if (event.removed) {
                this.removeMetadatum(event.removed.element);
            } else if (event.moved) {
                if (!this.isRepositoryLevel)
                    this.updateMetadataOrder();
            }
        },
        updateMetadataOrder() {
            let metadataOrder = [];
            for (let metadatum of this.activeMetadatumList)
                if (metadatum != undefined)
                    metadataOrder.push({ 'id': metadatum.id, 'enabled': metadatum.enabled });
            
            this.isUpdatingMetadataOrder = true;
            this.updateCollectionMetadataOrder({ collectionId: this.collectionId, metadataOrder: metadataOrder })
                .then(() => this.isUpdatingMetadataOrder = false)
                .catch(() => this.isUpdatingMetadataOrder = false);
        },
        onChangeEnable($event, index) {
            let metadataOrder = [];
            for (let metadatum of this.activeMetadatumList)
                if (metadatum != undefined)
                    metadataOrder.push({'id': metadatum.id, 'enabled': metadatum.enabled});
            
            metadataOrder[index].enabled = $event;
            this.isUpdatingMetadataOrder = true;
            this.updateCollectionMetadataOrder({ collectionId: this.collectionId, metadataOrder: metadataOrder })
                .then(() => this.isUpdatingMetadataOrder = false)
                .catch(() => this.isUpdatingMetadataOrder = false);
        },
        addMetadatumViaButton(metadatumType) {
            let lastIndex = this.activeMetadatumList.length;
            this.addNewMetadatum(metadatumType, lastIndex);
            
            // Higlights the clicker metadatum
            this.hightlightedMetadatum = metadatumType.name;
        },
        addNewMetadatum(newMetadatum, newIndex) {
            this.sendMetadatum({
                collectionId: this.collectionId, 
                name: newMetadatum.name, 
                metadatumType: newMetadatum.className, 
                status: 'auto-draft', 
                isRepositoryLevel: this.isRepositoryLevel, 
                newIndex: newIndex,
                parent: '0'
            })
            .then((metadatum) => {

                if (!this.isRepositoryLevel)
                    this.updateMetadataOrder();

                this.toggleMetadatumEdition(metadatum.id)
                this.hightlightedMetadatum = '';
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
                        this.deleteMetadatum({ collectionId: this.collectionId, metadatumId: removedMetadatum.id, isRepositoryLevel: this.isRepositoryLevel})
                            .then(() => {
                                if (!this.isRepositoryLevel)
                                    this.updateMetadataOrder();
                                else 
                                    this.$root.$emit('metadatumUpdated', this.isRepositoryLevel);
                            })
                            .catch(() => {
                                this.$console.log("Error deleting metadatum.")
                            });
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal'
            }); 
        },
        toggleMetadatumEdition(metadatumId) {
            // Closing collapse
            if (this.openedMetadatumId == metadatumId) {
                this.openedMetadatumId = '';
                this.$router.push({ query: {}});

            // Opening collapse
            } else {
                this.$router.push({ query: { edit: metadatumId}})
            }
        },
        editMetadatum(metadatum) {

            this.openedMetadatumId = metadatum.id;
            
            // Scroll to opened metadata form
            this.$nextTick(() => { 
                if (this.$refs['metadatum-handler-' + metadatum.id] && this.$refs['metadatum-handler-' + metadatum.id][0])
                    this.$refs['metadatum-handler-' + metadatum.id][0].scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            
            // First time opening
            if (this.editForms[this.openedMetadatumId] == undefined) {
                this.editForms[this.openedMetadatumId] = JSON.parse(JSON.stringify(metadatum));
                this.editForms[this.openedMetadatumId].saved = true;

                // Metadatum inserted now
                if (this.editForms[this.openedMetadatumId].status == 'auto-draft') {
                    this.editForms[this.openedMetadatumId].status = 'publish';
                    this.editForms[this.openedMetadatumId].saved = false;
                }
            }      
        },
        onEditionFinished() {
            this.formWithErrors = '';
            delete this.editForms[this.openedMetadatumId];
            this.openedMetadatumId = '';
            this.$router.push({ query: {}});
        },
        onEditionCanceled() {
            this.formWithErrors = '';
            delete this.editForms[this.openedMetadatumId];
            this.openedMetadatumId = '';
            this.$router.push({ query: {}});
        },
        refreshMetadata() {
            this.isLoadingMetadata = true;

            // Cancels previous Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');

            if (this.isRepositoryLevel)
                this.collectionId = 'default';
            else
                this.collectionId = this.$route.params.collectionId;

            this.fetchMetadata({
                collectionId: this.collectionId, 
                isRepositoryLevel: this.isRepositoryLevel, 
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
        getPreviewTemplateContent(metadatum) {
            return `<div class="metadata-type-preview tainacan-form">
                        <span class="metadata-type-label">` + this.$i18n.get('label_metadatum_type_preview') + `</span>
                        <div class="field">
                            <span class="collapse-handle">
                                <span class="icon">
                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"></i>
                                </span> 
                                <label class="label has-tooltip">`
                                    + metadatum.name +
                                `</label>
                            </span>
                            <div>` + metadatum.preview_template + `</div>
                        </div>
                    </div>`;
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

<style lang="scss">

    .metadata-list-page {
        padding-bottom: 0;

        .tainacan-page-title {
            margin-bottom: 18px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;

            h1, h2 {
                font-size: 1.25em;
                font-weight: 500;
                color: var(--tainacan-heading-color);
                display: inline-block;
                width: 80%;
                flex-shrink: 1;
                flex-grow: 1;
            }
            a.back-link {
                font-weight: 500;
                float: right;
                margin-top: 5px;
            }
            hr {
                margin: 3px 0px 4px 0px; 
                height: 1px;
                background-color: var(--tainacan-secondary);
                width: 100%;
            }
        }
                  
        .b-tabs .tab-content {
            overflow: visible;
            min-height: 300px;
        }

        .column {
            overflow-x: hidden;
            overflow-y: auto;
            padding: 0.75em 0;

            &>section.field {
                position: absolute;
            }

            &:not(.available-metadata-area){
                margin-right: var(--tainacan-one-column);
                flex-grow: 2;

                @media screen and (max-width: 769px) {
                    margin-right: 0;
                }
            }
            h3 {
                font-weight: 500;
            }
        }

        .page-title {
            border-bottom: 1px solid var(--tainacan-secondary);
            margin: 1em 0em 2.0em 0em;
            h2 {
                color: var(--tainacan-blue5);
                font-weight: 500;
            }
        }

        .sub-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5em 1em 0.5em 0.5em;

            .header-item {
                margin-left: 0.75rem;
                margin-bottom: 0px;
            }

            h3 {
                margin-right: auto;
            }

            .dropdown-menu {
                display: block;

                div.dropdown-content {
                    padding: 0;

                    .metadata-options-container {
                        max-height: 288px;
                        overflow: auto;
                        font-size: 1.125em;
                    }
                    .dropdown-item {
                        padding: 0.25em 1.0em 0.25em 0.75em;
                    }
                    .dropdown-item span{
                        vertical-align: middle;
                    }
                }
            }
        }

        .collapse-all {
            display: inline-flex;
            align-items: center;
            margin-left: 0.25em;
        }
        .collapse-all__text {
            font-size: 0.75em !important;
        }
        .loading-spinner {
            animation: spinAround 500ms infinite linear;
            border: 2px solid var(--tainacan-gray2);
            border-radius: 290486px;
            border-right-color: transparent;
            border-top-color: transparent;
            content: "";
            display: inline-block;
            height: 1em; 
            width: 1em;
        }

        .active-metadata-area {
            font-size: 0.875em;
            margin-left: -0.8em;
            padding-right: 1em;
            min-height: 330px;

            @media screen and (max-width: 769px) {
                min-height: 45px;
                margin: 0; 
                padding-right: 0em;
            }
            @media screen and (max-width: 1216px) {
                padding-right: 1em;
            }

            &.metadata-area-receive {
                border: 1px dashed var(--tainacan-gray4);
            }

            .collapse {
                display: initial;
            }

            .active-metadatum-item {
                background-color: var(--tainacan-white);
                padding: 0.7em 0.9em;
                margin: 4px;
                min-height: 2.8571em;
                display: block; 
                position: relative;
                cursor: grab;
                opacity: 1 !important;
                        
                &>.field, form {
                    background-color: var(--tainacan-white) !important;
                }
                
                .handle {
                    padding-right: 6em;
                    white-space: nowrap;
                    display: flex;
                }
                .grip-icon { 
                    color: var(--tainacan-gray3); 
                    position: relative;
                }
                .metadatum-name {
                    text-overflow: ellipsis;
                    overflow-x: hidden;
                    white-space: nowrap;
                    font-weight: bold;
                    margin-left: 0.4em;
                    margin-right: 0.4em;

                    &.is-danger {
                        color: var(--tainacan-danger) !important;
                    }
                }
                .label-details {
                    font-weight: normal;
                    color: var(--tainacan-gray3);
                }
                .not-saved {
                    font-style: italic;
                    font-weight: bold;
                    color: var(--tainacan-danger);
                    margin-left: 0.5em;
                }
                .controls { 
                    font-size: 0.875em;
                    position: absolute;
                    right: 10px;
                    top: 10px;
                    .switch {
                        position: relative;
                        bottom: 1px;
                    }
                    .icon {
                        bottom: 1px;   
                        position: relative;
                        i, i:before { font-size: 1.25em; }
                    }
                }
        
                &.not-sortable-item,
                &.not-sortable-item:hover {
                    cursor: default;
                    background-color: var(--tainacan-white) !important;
                } 
                &.not-focusable-item,
                &.not-focusable-item:hover {
                    cursor: default;
                
                    .metadatum-name {
                        color: var(--tainacan-secondary);
                    }
                }
                &.disabled-metadatum:not(.not-sortable-item),
                &.disabled-metadatum:not(.not-sortable-item):hover {
                    color: var(--tainacan-gray3);
                    .label-details, .not-saved {
                        color: var(--tainacan-gray3) !important;
                    }
                }
            }
            .active-metadatum-item:not(:hover) .icon-level-identifier .tainacan-icon::before,
            .active-filter-item:hover.not-sortable-item .icon-level-identifier .tainacan-icon::before {
                color: var(--tainacan-gray3) !important;
            }
            .active-metadatum-item:hover:not(.not-sortable-item) {
                background-color: var(--tainacan-turquoise1);
                border-color: var(--tainacan-turquoise1);

                .label-details, .not-saved {
                    color: var(--tainacan-gray4) !important;
                }
                .grip-icon { 
                    color: var(--tainacan-secondary) !important; 
                }
            }
            .sortable-ghost {
                border: 1px dashed var(--tainacan-gray2);
                background: var(--tainacan-white);
                display: block;
                padding: 0.7em 0.9em;
                margin: 4px;
                height: 2.8571em;
                position: relative;

                .grip-icon { 
                    color: var(--tainacan-white); 
                }
            }
        }

        .available-metadata-area {
            padding: 10px 0px 10px 10px;
            margin: 0;
            max-width: 500px;
            min-width: 20.8333333%;
            font-size: 0.875em;

            @media screen and (max-width: 769px) {
                max-width: 100%;
                padding: 10px;
                h3 {
                    margin: 1em 0em 1em 0em !important;
                }
                .available-metadatum-item::before,
                .available-metadatum-item::after {
                    display: none !important;
                }
            }

            h3 {
                margin: 0.875em 0em 1em 0em;
            }

            .available-metadatum-item {
                padding: 0.6em;
                margin: 4px 4px 4px 1.2em;
                background-color: var(--tainacan-white);
                cursor: pointer;
                left: 0;
                height: 2.8571em;
                position: relative;
                border: 1px solid var(--tainacan-gray2);
                border-radius: 1px;
                transition: left 0.2s ease;
                
                .grip-icon { 
                    color: var(--tainacan-gray3);
                    top: -6px;
                    position: relative;
                    display: inline-block;
                }
                .icon {
                    position: relative;
                    bottom: 1px;
                }
                .preview-help-icon {
                    position: absolute;
                    top: 6px;
                }
                .metadatum-name {
                    text-overflow: ellipsis;
                    overflow-x: hidden;
                    white-space: nowrap;
                    font-weight: bold;
                    margin-left: 0.4em;
                    display: inline-block;
                    max-width: 180px;
                    width: 60%;
                }
                &::after,
                &::before {
                    content: '';
                    display: block;
                    position: absolute;
                    right: 100%;
                    width: 0;
                    height: 0;
                    border-style: solid;
                }
                &::after {
                    top: -1px;
                    border-color: transparent white transparent transparent;
                    border-right-width: 16px;
                    border-top-width: 1.4286em;
                    border-bottom-width: 1.4286em;
                    left: -19px;
                }
                &::before {
                    top: -1px;
                    border-color: transparent var(--tainacan-gray2) transparent transparent;
                    border-right-width: 16px;
                    border-top-width: 1.4286em;
                    border-bottom-width: 1.4286em;
                    left: -20px;
                }
            }

            .sortable-drag {
                opacity: 1 !important;
            }

            .sortable-chosen {
                .metadata-type-preview {
                    display: none;
                }
            }

            @keyframes hightlighten {
                0%   {
                    color: #222;             
                    background-color: var(--tainacan-white);
                    border-color: var(--tainacan-white);
                }
                25%  {
                    color: var(--tainacan-white);            
                    background-color: var(--tainacan-secondary); 
                    border-color: var(--tainacan-secondary);
                }
                75%  {
                    color: var(--tainacan-white);            
                    background-color: var(--tainacan-secondary); 
                    border-color: var(--tainacan-secondary);
                }
                100% {
                    color: #222;             
                    background-color: var(--tainacan-white);
                    border-color: var(--tainacan-white);
                }
            }
            @keyframes hightlighten-icon {
                0%   { color: var(--tainacan-gray3); }
                25%  { color: var(--tainacan-white); }
                75%  { color: var(--tainacan-white); }
                100% { color: var(--tainacan-gray3); }
            }
            @keyframes hightlighten-arrow {
                0%   {
                    border-color: transparent white transparent transparent;
                    border-color: transparent white transparent transparent; 
                }
                25%  {
                    border-color: transparent var(--tainacan-secondary) transparent transparent;
                    border-color: transparent var(--tainacan-secondary) transparent transparent; 
                }
                75%  {
                    border-color: transparent var(--tainacan-secondary) transparent transparent;
                    border-color: transparent var(--tainacan-secondary)transparent transparent; 
                }
                100% {
                    border-color: transparent white transparent transparent;
                    border-color: transparent white transparent transparent;  
                }
            }
            .hightlighted-metadatum {
                background-color: var(--tainacan-white);
                position: relative;
                left: 0px;
                animation-name: hightlighten;
                animation-duration: 1.0s;
                animation-iteration-count: 2;
                
                .grip-icon{
                    animation-name: hightlighten-icon;
                    animation-duration: 1.0s;
                    animation-iteration-count: 2; 
                }

                &::before,
                &::after {
                    animation-name: hightlighten-arrow;
                    animation-duration: 1.0s;
                    animation-iteration-count: 2;
                }
            }
            .available-metadatum-item:hover {
                background-color: var(--tainacan-turquoise1);
                border-color: var(--tainacan-turquoise2);
                position: relative;
                left: -4px;

                &:after {
                    border-color: transparent var(--tainacan-turquoise1) transparent transparent;
                }
                &:before {
                    border-color: transparent var(--tainacan-turquoise2) transparent transparent;
                }
                .grip-icon { 
                    color: var(--tainacan-secondary);
                }
            }
        }
        .inherited-metadatum {
            .switch.is-small input[type="checkbox"]:checked + .check {
                border: 1.5px solid var(--tainacan-blue5);
                &::before { background-color: var(--tainacan-blue5); }
            }

            &.active-metadatum-item:hover:not(.not-sortable-item) {
                background-color: var(--tainacan-blue1);
                border-color: var(--tainacan-blue1);
                
                .grip-icon { 
                    color: var(--tainacan-blue5) !important; 
                }
            }
            &.available-metadatum-item:hover {
                background-color: var(--tainacan-blue1) !important;
                border-color: var(--tainacan-blue2) !important;

                .grip-icon { 
                    color: var(--tainacan-blue5) !important; 
                }
                &:after {
                    border-color: transparent var(--tainacan-blue1) transparent transparent !important;
                }
                &:before {
                    border-color: transparent var(--tainacan-blue2) transparent transparent !important;
                }
            }
        }
    }
</style>
