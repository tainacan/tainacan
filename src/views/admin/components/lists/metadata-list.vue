<template>
    <div class="metadata-list-page">
        <b-loading :active.sync="isLoadingMetadatumTypes"/>
        <b-loading :active.sync="isLoadingMetadatumMappers"/>

        <b-tabs 
                v-if="(isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_metadata') || (!isRepositoryLevel && collection && collection.current_user_can_edit_metadata))"
                v-model="activeTab">    
            <b-tab-item :label="$i18n.get('metadata')">
                <div
                        :style="{ height: activeMetadatumList.length <= 0 && !isLoadingMetadata ? 'auto' : 'calc(100vh - 6px - ' + columnsTopY + 'px)'}"
                        class="columns"
                        ref="metadataEditionPageColumns">
                    <div class="column">     
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
                                @change="handleChange"
                                :class="{'metadata-area-receive': isDraggingFromAvailable}"
                                :group="{ name:'metadata', pull: false, put: true }"
                                :sort="(openedMetadatumId == '' || openedMetadatumId == undefined) && !isRepositoryLevel"
                                :handle="'.handle'"
                                ghost-class="sortable-ghost"
                                chosen-class="sortable-chosen"
                                filter="not-sortable-item"
                                :animation="250">
                            <div 
                                    class="active-metadatum-item"
                                    :class="{
                                        'not-sortable-item': isRepositoryLevel || metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder,
                                        'not-focusable-item': openedMetadatumId == metadatum.id,
                                        'disabled-metadatum': metadatum.enabled == false,
                                        'inherited-metadatum': (metadatum.collection_id != collectionId && metadatum.parent == 0) || isRepositoryLevel
                                    }" 
                                    v-for="(metadatum, index) in activeMetadatumList"
                                    :key="metadatum.id">
                                <div class="handle">
                                    <span 
                                            v-if="!(isRepositoryLevel || metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder)"
                                            v-tooltip="{
                                                content: isRepositoryLevel || metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder ? $i18n.get('info_not_allowed_change_order_metadata') : $i18n.get('instruction_drag_and_drop_metadatum_sort'),
                                                autoHide: true,
                                                classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                                placement: 'auto-start'
                                            }"
                                            class="icon grip-icon">
                                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-drag"/>
                                    </span>
                                    <span 
                                            v-tooltip="{
                                                content: (metadatum.collection_id == 'default') || isRepositoryLevel ? $i18n.get('label_repository_filter') : $i18n.get('label_collection_filter'),
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
                                    <span 
                                            class="metadatum-name"
                                            :class="{'is-danger': formWithErrors == metadatum.id }">
                                            {{ metadatum.name }}
                                    </span>
                                    <span   
                                            v-if="metadatum.id != undefined && metadatum.metadata_type_object"
                                            class="label-details">  
                                        ({{ metadatum.metadata_type_object.name }}) 
                                        <em v-if="metadatum.collection_id != collectionId">{{ $i18n.get('label_inherited') }}</em>
                                        <em 
                                                v-if="metadatum.metadata_type_object.core && 
                                                    metadatum.metadata_type_object.related_mapped_prop == 'title'">
                                                {{ $i18n.get('label_core_title') }}
                                        </em>
                                        <em 
                                                v-if="metadatum.metadata_type_object.core && 
                                                    metadatum.metadata_type_object.related_mapped_prop == 'description'">
                                                {{ $i18n.get('label_core_description') }}
                                        </em>
                                        <span 
                                            class="not-saved" 
                                            v-if="(editForms[metadatum.id] != undefined && editForms[metadatum.id].saved != true) || metadatum.status == 'auto-draft'">
                                        {{ $i18n.get('info_not_saved') }}
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
                                                :style="{ visibility: 
                                                        metadatum.collection_id != collectionId ||
                                                         metadatum.metadata_type_object.related_mapped_prop == 'title' ||
                                                        metadatum.metadata_type_object.related_mapped_prop == 'description'
                                                        ? 'hidden' : 'visible'
                                                    }" 
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
                        </draggable> 
                    </div>
                
                    <div 
                            v-if="(isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_metadata')) || !isRepositoryLevel"
                            class="column available-metadata-area" >
                        <div class="field">
                            <h3 class="label has-text-secondary">{{ $i18n.get('label_available_metadata_types') }}</h3>
                            <draggable 
                                    v-model="availableMetadatumList"
                                    :sort="false" 
                                    :group="{ name:'metadata', pull: 'clone', put: false, revertClone: true }"
                                    drag-class="sortable-drag">
                                <div 
                                        @click.prevent="addMetadatumViaButton(metadatum)"
                                        class="available-metadatum-item"
                                        :class="{ 'hightlighted-metadatum' : hightlightedMetadatum == metadatum.name, 'inherited-metadatum': isRepositoryLevel }"
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
                                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-drag"/>
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
            <b-tab-item 
                    v-if="(isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_metadata') || !isRepositoryLevel)"
                    :label="$i18n.get('mapping')">
                <div>
                    <section 
                            v-if="activeMetadatumList.length <= 0 && !isLoadingMetadata"
                            class="field is-grouped-centered section">
                        <div class="content has-text-gray has-text-centered">
                            <p>
                                <span class="icon is-large">
                                    <i class="tainacan-icon tainacan-icon-36px tainacan-icon-metadata"/>
                                </span>
                            </p>
                            <p>{{ $i18n.get('info_there_is_no_metadatum') }}</p>
                        </div>
                    </section>
                    <section v-else>
                        <div class="field is-grouped form-submit">
                            <b-select
                                    id="mappers-options-dropdown"
                                    :placeholder="$i18n.get('instruction_select_a_mapper')"
                                    @input="onSelectMetadataMapper($event)">
                                <option
                                        v-for="metadatum_mapper in metadatum_mappers"
                                        :key="metadatum_mapper.slug"
                                        :value="metadatum_mapper">
                                    {{ $i18n.get(metadatum_mapper.name) }}
                                </option>
                            </b-select>
                            <div
                                    class="control"
                                    v-if="mapper != '' && !isLoadingMetadatumMappers">
                                <button
                                        class="button is-outlined"
                                        type="button"
                                        @click="onCancelUpdateMetadataMapperMetadata">{{ $i18n.get('cancel') }}</button>
                            </div>
                            <div
                                    class="control"
                                    v-if="mapper != '' && !isLoadingMetadatumMappers">
                                <button
                                        @click.prevent="onUpdateMetadataMapperMetadataClick"
                                        class="button is-success">{{ $i18n.get('save') }}</button>
                            </div>
                        </div>
                    </section>
                    <template>
                        <section>
                            <b-table
                                :data="mapperMetadata"
                                :loading="isMapperMetadataLoading">
    
                                <template slot-scope="props">
                                    <b-table-column
                                            field="label"
                                            :label="$i18n.get('label_mapper_metadata')">
                                        {{ props.row.label }}
                                    </b-table-column>
    
                                    <b-table-column
                                            field="slug"
                                            :label="$i18n.get('metadatum')">
                                        <b-select
                                                :name="'mappers-metadatum-select-' + props.row.slug"
                                                v-model="props.row.selected"
                                                @input="onSelectMetadatumForMapperMetadata">
                                            <option
                                                    value="">
                                                {{ $i18n.get('instruction_select_a_metadatum') }}
                                            </option>
                                            <option
                                                v-for="(metadatum, index) in activeMetadatumList"
                                                :key="index"
                                                :value="metadatum.id"
                                                :disabled="isMetadatumSelected(metadatum.id)">
                                                {{ metadatum.name }}
                                            </option>
                                        </b-select>
                                    </b-table-column>
                                    <b-table-column
                                            field="isCustom"
                                            label="">
                                        <a 
                                                :style="{ visibility: 
                                                        props.row.isCustom
                                                        ? 'visible' : 'hidden'
                                                    }" 
                                                @click.prevent="editMetadatumCustomMapper(props.row)">
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
                                                :style="{ visibility: 
                                                        props.row.isCustom
                                                        ? 'visible' : 'hidden'
                                                    }" 
                                                @click.prevent="removeMetadatumCustomMapper(props.row)">
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
                                    </b-table-column>
                                </template>
                            </b-table>
                        </section>
                        <section
                                v-if="mapper != '' && mapper.allow_extra_metadata">
                            <div
                                    class="modal-new-link">
                                <a
                                        v-if="collectionId != null && collectionId != undefined"
                                        class="is-inline is-pulled-left add-link"
                                        @click="onNewMetadataMapperMetadata()">
                                    <span class="icon is-small">
                                        <i class="tainacan-icon tainacan-icon-add"/>
                                    </span>
                                    {{ $i18n.get('label_add_more_mapper_metadata') }}
                                </a>
                            </div>
                        </section>
                        <b-modal
                                @close="onCancelNewMetadataMapperMetadata"
                                :active.sync="isMapperMetadataCreating"
                                trap-focus
                                aria-modal
                                aria-role="dialog">
                            <div 
                                    autofocus
                                    role="dialog"
                                    tabindex="-1"
                                    aria-modal
                                    class="tainacan-modal-content">
                                <div class="tainacan-modal-title">
                                    <h2>{{ $i18n.get('instruction_insert_mapper_metadatum_info') }}</h2>
                                    <hr>
                                </div>
                                <b-field>
                                    <b-input
                                            v-model="new_metadata_label"
                                            required
                                            :placeholder="$i18n.get('label_name')"/>
                                </b-field>
                                <b-field>
                                    <b-input
                                            placeholder="URI"
                                            type="url"
                                            required
                                            v-model="new_metadata_uri"/>
                                </b-field>
                                <div class="field is-grouped form-submit">
                                    <div class="control">
                                        <button
                                                class="button is-outlined"
                                                type="button"
                                                @click="onCancelNewMetadataMapperMetadata">{{ $i18n.get('cancel') }}</button>
                                    </div>
                                    <div class="control">
                                        <button
                                                @click.prevent="onSaveNewMetadataMapperMetadata"
                                                :disabled="isNewMetadataMapperMetadataDisabled"
                                                class="button is-success">{{ $i18n.get('save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </b-modal>
                    </template>
                    <section
                            v-if="mapper != '' && !isLoadingMetadatumMappers">
                        <div class="field is-grouped form-submit w-100">
                            <div class="control">
                                <button
                                        class="button is-outlined"
                                        type="button"
                                        @click="onCancelUpdateMetadataMapperMetadata">{{ $i18n.get('cancel') }}</button>
                            </div>
                            <div class="control">
                                <button
                                        @click.prevent="onUpdateMetadataMapperMetadataClick"
                                        class="button is-success">{{ $i18n.get('save') }}</button>
                            </div>
                        </div>
                    </section>
                </div>
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
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import MetadatumEditionForm from './../edition/metadatum-edition-form.vue';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'MetadataList',
    components: {
        MetadatumEditionForm
    },
    props: {
        isRepositoryLevel: Boolean
    },
    data(){           
        return {
            collectionId: '',
            isDraggingFromAvailable: false,
            isLoadingMetadatumMappers: true,
            mapper: '',
            mapperMetadata: [],
            isMapperMetadataLoading: false,
            isMapperMetadataCreating: false,
            mappedMetadata: [],
            isLoadingMetadatumTypes: true,
            isLoadingMetadata: false,
            isLoadingMetadatum: false,
            isUpdatingMetadataOrder: false,
            openedMetadatumId: '',
            formWithErrors: '',
            hightlightedMetadatum: '',
            editForms: {},
            newMapperMetadataList: [],
            new_metadata_label: '',
            new_metadata_uri: '',
            new_metadata_slug: '',
            columnsTopY: 0,
            metadataSearchCancel: undefined 
        }
    },
    computed: {
        collection() {
            return this.getCollection();
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
        },
        metadatum_mappers: {
            get() {
                return this.getMetadatumMappers();
            }
        },
        isNewMetadataMapperMetadataDisabled: {
            get() {
                return !this.new_metadata_label || !this.new_metadata_uri;
            }
        }
    },
    watch: {
        '$route.query': {
            handler(newQuery) {
                if (newQuery.edit != undefined) {
                    let existingMetadataIndex = this.activeMetadatumList.findIndex((metadatum) => metadatum.id == newQuery.edit);
                    if (existingMetadataIndex >= 0)
                        this.editMetadatum(this.activeMetadatumList[existingMetadataIndex])                        
                }
            },
            immediate: true
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
                trapFocus: true
            });  
        } else {
            next()
        }  
    },
    mounted() {

        if (!this.isRepositoryLevel)
            this.$root.$emit('onCollectionBreadCrumbUpdate', [{ path: '', label: this.$i18n.get('metadata') }]);

        this.$nextTick(() => { 
            this.columnsTopY = this.$refs.metadataEditionPageColumns ? this.$refs.metadataEditionPageColumns.getBoundingClientRect().top : 0;
        });

        this.cleanMetadata();
        this.isLoadingMetadatumTypes = true;
        this.isLoadingMetadata = true;

        this.fetchMetadatumTypes()
            .then(() => {
                this.isLoadingMetadatumTypes = false;
            })
            .catch(() => {
                this.isLoadingMetadatumTypes = false;
            });
        this.refreshMetadata();
        this.fetchMetadatumMappers()
            .then(() => {
                this.isLoadingMetadatumMappers = false;
            })
            .catch(() => {
                this.isLoadingMetadatumMappers = false;
            });
    },
    beforeDestroy() {

        // Cancels previous Request
        if (this.metadataSearchCancel != undefined)
            this.metadataSearchCancel.cancel('Metadata search Canceled.');

    },
    methods: {
        ...mapActions('metadata', [
            'fetchMetadatumTypes',
            'updateMetadatumTypes',
            'fetchMetadata',
            'sendMetadatum',
            'deleteMetadatum',
            'updateMetadata',
            'updateCollectionMetadataOrder',
            'fetchMetadatumMappers',
            'updateMetadataMapperMetadata',
            'cleanMetadata'
        ]),
        ...mapGetters('metadata',[
            'getMetadatumTypes',
            'getMetadata',
            'getMetadatumMappers'
        ]),
        ...mapGetters('collection', [
            'getCollection',
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
            for (let metadatum of this.activeMetadatumList) {
                metadataOrder.push({'id': metadatum.id, 'enabled': metadatum.enabled});
            }
            this.isUpdatingMetadataOrder = true;
            this.updateCollectionMetadataOrder({ collectionId: this.collectionId, metadataOrder: metadataOrder })
                .then(() => this.isUpdatingMetadataOrder = false)
                .catch(() => this.isUpdatingMetadataOrder = false);
        },
        onChangeEnable($event, index) {
            let metadataOrder = [];
            for (let metadatum of this.activeMetadatumList) {
                metadataOrder.push({'id': metadatum.id, 'enabled': metadatum.enabled});
            }
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
                newIndex: newIndex
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
                trapFocus: true
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
        onSelectMetadataMapper(metadatum_mapper) {

            this.isMapperMetadataLoading = true;
            this.mapper = metadatum_mapper; //TODO try to use v-model again
            this.mapperMetadata = [];
            this.mappedMetadata = [];
            
            if(metadatum_mapper != '') {
                for (var k in metadatum_mapper.metadata) {
                    var item = metadatum_mapper.metadata[k];
                    item.slug = k;
                    item.selected = '';
                    item.isCustom = false;
                    this.activeMetadatumList.forEach((metadatum) => {
                        if(
                                metadatum.exposer_mapping.hasOwnProperty(metadatum_mapper.slug) &&
                                metadatum.exposer_mapping[metadatum_mapper.slug] == item.slug
                        ) {
                            item.selected = metadatum.id;
                            this.mappedMetadata.push(metadatum.id);
                        }
                    });
                    this.mapperMetadata.push(item);
                }
                this.activeMetadatumList.forEach((metadatum) => {
                    if(
                            metadatum.exposer_mapping.hasOwnProperty(metadatum_mapper.slug) &&
                            typeof metadatum.exposer_mapping[metadatum_mapper.slug] == 'object'
                    ) {
                        this.newMapperMetadataList.push(Object.assign({},metadatum.exposer_mapping[metadatum_mapper.slug]));
                        this.mappedMetadata.push(metadatum.id);
                        var item = Object.assign({},metadatum.exposer_mapping[metadatum_mapper.slug]);
                        item.selected = metadatum.id;
                        item.isCustom = true;
                        this.mapperMetadata.push(item);
                    }
                });
            }
            this.isMapperMetadataLoading = false;
        },
        isMetadatumSelected(id) {
            return this.mappedMetadata.indexOf(id) > -1;
        },
        onSelectMetadatumForMapperMetadata() {
            this.mappedMetadata = [];
            this.mapperMetadata.forEach((item) => {
                if(item.selected.length != 0) {
                    this.mappedMetadata.push(item.selected);
                }
            });
        },
        onUpdateMetadataMapperMetadataClick() {
            this.isMapperMetadataLoading = true;
            var metadataMapperMetadata = [];
            this.mapperMetadata.forEach((item) => {
                if (item.selected.length != 0) {
                    var map = {
                            metadatum_id: item.selected,
                            mapper_metadata: item.slug
                    };
                    metadataMapperMetadata.push(map);
                }
            });
            this.activeMetadatumList.forEach((item) => {
                if(this.mappedMetadata.indexOf(item.id) == -1) {
                    var map = {
                            metadatum_id: item.id,
                            mapper_metadata: ''
                    };
                    metadataMapperMetadata.push(map);
                }
            });
            this.newMapperMetadataList.forEach((item) => {
                var slug = item.slug;
                metadataMapperMetadata.forEach( (meta, index) => {
                    if(meta.mapper_metadata == slug) {
                        var item_clone = Object.assign({}, item); // TODO check if still need to clone
                        delete item_clone.selected;
                        delete item_clone.isCustom;
                        meta.mapper_metadata = item_clone;
                        metadataMapperMetadata[index] = meta;
                    }
                });
            });
            this.updateMetadataMapperMetadata({metadataMapperMetadata: metadataMapperMetadata, mapper: this.mapper.slug}).then(() => {
                this.isLoadingMetadata = true;
                this.refreshMetadata();
                this.isMapperMetadataLoading = false;
            })
            .catch(() => {
                this.isMapperMetadataLoading = false;
            });
        },
        onCancelUpdateMetadataMapperMetadata() {
            this.isMapperMetadataLoading = true;
            this.onSelectMetadataMapper(this.mapper);
            this.isMapperMetadataLoading = false;
        },
        onNewMetadataMapperMetadata() {
            this.isMapperMetadataCreating = true;
        },
        onCancelNewMetadataMapperMetadata() {
            this.isMapperMetadataCreating = false;
            this.new_metadata_label = '';
            this.new_metadata_uri = '';
            this.new_metadata_slug = '';
        },
        onSaveNewMetadataMapperMetadata() {
            this.isMapperMetadataLoading = true;
            var newMapperMetadata = {
                    label: this.new_metadata_label,
                    uri: this.new_metadata_uri,
                    slug: this.stringToSlug(this.new_metadata_label),
                    isCustom: true
            };
            var selected = '';
            if(this.new_metadata_slug != '') { // Editing
                this.newMapperMetadataList.forEach((meta, index) => {
                    if(meta.slug == this.new_metadata_slug) {
                        this.newMapperMetadataList.splice(index);
                        this.mapperMetadata.forEach((item, index2) => {
                            if (item.slug == this.new_metadata_slug) {
                                selected = item.selected;
                                this.mapperMetadata.splice(index2);
                            }
                        });
                    }
                });
            }
            this.newMapperMetadataList.push(newMapperMetadata);
            newMapperMetadata.selected = selected;
            this.mapperMetadata.push(newMapperMetadata);
            this.new_metadata_label = '';
            this.new_metadata_uri = '';
            this.new_metadata_slug = '';
            this.isMapperMetadataCreating = false;
            this.isMapperMetadataLoading = false;
        },
        stringToSlug(str) { // adapted from https://gist.github.com/spyesx/561b1d65d4afb595f295
            str = str.replace(/^\s+|\s+$/g, ''); // trim
            str = str.toLowerCase();

            // remove accents, swap ñ for n, etc
            var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
            var to   = "aaaaeeeeiiiioooouuuunc------";

            for (var i=0, l=from.length ; i<l ; i++) {
                str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }

            str = str.replace('.', '-') // replace a dot by a dash 
                .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
                .replace(/\s+/g, '-') // collapse whitespace and replace by a dash
                .replace(/-+/g, '-'); // collapse dashes

            return str;
        },
        editMetadatumCustomMapper(customMapperMeta) {
            this.new_metadata_label = customMapperMeta.label;
            this.new_metadata_uri = customMapperMeta.uri;
            this.new_metadata_slug = customMapperMeta.slug;
            this.isMapperMetadataCreating = true;
        },
        removeMetadatumCustomMapper(customMapperMeta) {
            var itemid = 0;
            this.newMapperMetadataList.forEach((meta, index) => {
                if(meta.slug == customMapperMeta.slug) {
                    this.newMapperMetadataList.splice(index);
                    var rem = this.mappedMetadata.indexOf(meta.selected);
                    this.mappedMetadata.splice(rem);
                    itemid = customMapperMeta.selected;
                }
            });
            if(itemid != '' && itemid > 0) {
                this.mapperMetadata.forEach((item, index) => {
                    if (item.selected == itemid) {
                        this.mapperMetadata.splice(index);
                    }
                });
            }
            return true;
        },
        refreshMetadata() {
            
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
                includeDisabled: true
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
        }
    }
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .metadata-list-page {
        padding-bottom: 0;

        .tainacan-page-title {
            margin-bottom: 35px;
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
            a.back-link{
                font-weight: 500;
                float: right;
                margin-top: 5px;
            }
            hr{
                margin: 3px 0px 4px 0px; 
                height: 1px;
                background-color: var(--tainacan-secondary);
                width: 100%;
            }
        }
                  
        .b-tabs .tab-content {
            overflow: visible;
            min-height: 500px;
        }

        .column {
            overflow-x: hidden;
            overflow-y: auto;

            &:not(.available-metadata-area){
                margin-right: $page-side-padding;
                flex-grow: 2;

                @media screen and (max-width: 769px) {
                    margin-right: 0;
                }
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

        .w-100 {
            width: 100%;
            position: relative;
            float: left;
        }
        
        .modal-new-link {
            padding: 0.5em 1em 3em 1em;
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
            margin-right: 0.8em;
            margin-left: -0.8em;
            padding-right: 3em;
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
                border: 1px dashed gray;
            }

            .collapse {
                display: initial;
            }

            .active-metadatum-item {
                background-color: var(--tainacan-white);
                padding: 0.7em 0.9em;
                margin: 4px;
                min-height: 40px;
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
                    position: absolute;
                    right: 5px;
                    top: 10px;
                    .switch {
                        position: relative;
                        bottom: 3px;
                    }
                    .icon {
                        bottom: 1px;   
                        position: relative;
                        i, i:before { font-size: 1.25em; }
                    }
                }
        
                &.not-sortable-item, &.not-sortable-item:hover {
                    cursor: default;
                    background-color: var(--tainacan-white) !important;
                } 
                &.not-focusable-item, &.not-focusable-item:hover {
                    cursor: default;
                
                    .metadatum-name {
                        color: var(--tainacan-secondary);
                    }
                }
                &.disabled-metadatum {
                    color: var(--tainacan-gray3);
                }    
            }
            .active-metadatum-item:hover:not(.not-sortable-item) {
                background-color: var(--tainacan-secondary);
                border-color: var(--tainacan-secondary);
                color: var(--tainacan-white) !important;
                        
                &>.field, form {
                    background-color: var(--tainacan-white) !important;
                }

                .label-details, .icon, .not-saved, .icon-level-identifier>i {
                    color: var(--tainacan-white) !important;
                }

                .grip-icon { 
                    color: var(--tainacan-white); 
                }

                .switch.is-small {
                    input[type="checkbox"] + .check {
                        background-color: var(--tainacan-secondary) !important;
                        border: 1.5px solid white !important;
                        &::before { background-color: var(--tainacan-white) !important; }
                    } 
                    input[type="checkbox"]:checked + .check {
                        border: 1.5px solid white !important;
                        &::before { background-color: var(--tainacan-white) !important; }
                    }
                    &:hover input[type="checkbox"] + .check {
                        border: 1.5px solid white !important;
                        background-color: var(--tainacan-secondary) !important;
                    }
                }
            }
            .sortable-ghost {
                border: 1px dashed var(--tainacan-gray2);
                display: block;
                padding: 0.7em 0.9em;
                margin: 4px;
                height: 40px;
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
                margin: 0.2em 0em 1em 0em;
                font-weight: 500;
            }

            .available-metadatum-item {
                padding: 0.6em;
                margin: 4px 4px 4px 1.2em;
                background-color: var(--tainacan-white);
                cursor: pointer;
                left: 0;
                height: 40px;
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
                    border-top-width: 20px;
                    border-bottom-width: 20px;
                    left: -19px;
                }
                &::before {
                    top: -1px;
                    border-color: transparent var(--tainacan-gray2) transparent transparent;
                    border-right-width: 16px;
                    border-top-width: 20px;
                    border-bottom-width: 20px;
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
                    background-color: #2cb4c1; 
                    border-color: #2cb4c1;
                }
                75%  {
                    color: var(--tainacan-white);            
                    background-color: #2cb4c1; 
                    border-color: #2cb4c1;
                }
                100% {
                    color: #222;             
                    background-color: var(--tainacan-white);
                    border-color: var(--tainacan-white);
                }
            }
            @keyframes hightlighten-icon {
                0%   { color: #b1b1b1; }
                25%  { color: var(--tainacan-white); }
                75%  { color: var(--tainacan-white); }
                100% { color: #b1b1b1; }
            }
            @keyframes hightlighten-arrow {
                0%   {
                    border-color: transparent white transparent transparent;
                    border-color: transparent white transparent transparent; 
                }
                25%  {
                    border-color: transparent #2cb4c1 transparent transparent;
                    border-color: transparent #2cb4c1 transparent transparent; 
                }
                75%  {
                    border-color: transparent #2cb4c1 transparent transparent;
                    border-color: transparent #2cb4c1 transparent transparent; 
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
                background-color: var(--tainacan-secondary);
                border-color: var(--tainacan-secondary);
                color: var(--tainacan-white);
                position: relative;
                left: -4px;

                &:after {
                    border-color: transparent var(--tainacan-secondary) transparent transparent;
                }
                &:before {
                    border-color: transparent var(--tainacan-secondary) transparent transparent;
                }
                .icon {
                    color: var(--tainacan-white) !important;
                }
            
                .grip-icon { 
                    color: var(--tainacan-white);
                }
                
            }
        }
        .inherited-metadatum {
            &.active-metadatum-item:hover:not(.not-sortable-item) {
                background-color: var(--tainacan-blue5);
                border-color: var(--tainacan-blue5);
                
                .switch.is-small {
                    input[type="checkbox"] + .check {
                        background-color: var(--tainacan-blue5) !important;
                    } 
                    &:hover input[type="checkbox"] + .check {
                        background-color: var(--tainacan-blue5) !important;
                    }
                }
            }
            &.available-metadatum-item:hover {
                background-color: var(--tainacan-blue5) !important;
                border-color: var(--tainacan-blue5) !important;
            
                &:after {
                    border-color: transparent var(--tainacan-blue5) transparent transparent !important;
                }
                &:before {
                    border-color: transparent var(--tainacan-blue5) transparent transparent !important;
                }

            }
        }

    }

</style>
