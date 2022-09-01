<template>
    <div class="column">
                           
        <b-loading :active.sync="isLoadingMetadataSections"/>

        <div class="tainacan-form sub-header">
            <template v-if="activeMetadataSectionsList">
                <button
                        aria-controls="filters-items-list"
                        :aria-expanded="!collapseAll"
                        v-if="activeMetadataSectionsList.length > 0"
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
                            :disabled="activeMetadataSectionsList.length <= 0"
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
                v-if="activeMetadataSectionsList.length <= 0 && !isLoadingMetadataSections"
                class="field is-grouped-centered section">
            <div class="content has-text-gray has-text-centered">
                <p>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-metadata"/>
                    </span>
                </p>
                <p>{{ $i18n.get('info_there_is_no_metadata_section') }}</p>
            </div>
        </section>

        <!-- The Metadata Sections list -->
        <draggable 
                v-model="activeMetadataSectionsList"
                class="active-metadata-sections-area"
                @change="handleSectionChange($event)"
                :group="{ name:'metadata-sections', pull: false, put: [ 'metadata-sections' ] }"
                :sort="(openedMetadataSectionId == '' || openedMetadataSectionId == undefined) && (openedMetadatumId == '' || openedMetadatumId == undefined)"
                :handle="'.handle'"
                ghost-class="sortable-ghost"
                chosen-class="sortable-chosen"
                filter=".not-sortable-item"
                :prevent-on-filter="false"
                :animation="250">
            <div    
                    v-for="(metadataSection, sectionIndex) in activeMetadataSectionsList"
                    :key="metadataSection.id">
                <div 
                        class="active-metadata-sections-item"
                        :class="{
                            'is-compact-item': !isCollapseOpen(metadataSection.id),
                            'not-sortable-item': 
                                metadataSection.id == undefined ||
                                openedMetadatumId != '' ||
                                openedMetadataSectionId != '' ||
                                isUpdatingMetadataOrder ||
                                isUpdatingMetadatum ||
                                isUpdatingMetadataSectionsOrder ||
                                metadataNameFilterString != '' ||
                                hasSomeMetadataTypeFilterApplied,
                            'not-focusable-item': openedMetadataSectionId == metadataSection.id,
                            'disabled-metadatum': metadataSection.enabled == false,
                            'inherited-metadatum': false
                        }">
                    <div 
                            :ref="'metadata-section-handler-' + metadataSection.id"
                            class="handle">
                        <span class="sorting-buttons">
                            <button 
                                    :disabled="sectionIndex == 0"
                                    class="link-button"
                                    @click="moveMetadataSectionUpViaButon(sectionIndex)">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-previous tainacan-icon-rotate-90" />
                                </span>
                            </button>
                            <button 
                                    :disabled="sectionIndex == activeMetadataSectionsList.length - 1"
                                    class="link-button"
                                    @click="moveMetadataSectionDownViaButton(sectionIndex)">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-next tainacan-icon-rotate-90" />
                                </span>
                            </button>
                        </span>
                        <span 
                                :style="{ opacity: !(metadataSection.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder || openedMetadataSectionId != '' || isUpdatingMetadataSectionsOrder || metadataNameFilterString != '' || hasSomeMetadataTypeFilterApplied) ? '1.0' : '0.0' }"
                                v-tooltip="{
                                    content: metadataSection.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder || openedMetadataSectionId != '' || isUpdatingMetadataSectionsOrder || isUpdatingMetadatum ? $i18n.get('info_not_allowed_change_order_metadata_sections') : $i18n.get('instruction_drag_and_drop_metadata_sections_sort'),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip'],
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
                        <span class="metadatum-name">
                            <h3>{{ metadataSection.name }}</h3>
                        </span>
                        <span   
                                v-if="metadataSection.id != undefined"
                                class="label-details"
                                :class="{ 'has-text-weight-bold': metadataSection.id === 'default_section' }">
                            <span 
                                    v-if="metadataSection.id === 'default_section'"
                                    v-tooltip="{
                                        content: $i18n.get('label_required'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip'],
                                        placement: 'auto-start'
                                    }">
                                *&nbsp;({{ $i18n.get('label_default_section') }}) 
                            </span>
                            <span 
                                    v-if="metadataSection.status === 'private'"
                                    class="icon"
                                    v-tooltip="{
                                        content: $i18n.get('status_private'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip'],
                                        placement: 'auto-start'
                                    }">
                                <i class="tainacan-icon tainacan-icon-private"/>
                            </span>
                        </span>
                        <span 
                                class="loading-spinner" 
                                v-if="metadataSection.id == undefined"/>
                        <span 
                                class="controls" 
                                v-if="metadataSection.id !== undefined">
                            <b-switch 
                                    :disabled="isUpdatingMetadataSectionsOrder"
                                    size="is-small" 
                                    :value="metadataSection.enabled"
                                    @input="onChangeEnableSection($event, sectionIndex)"/>
                            <a 
                                    v-if="metadataSection.current_user_can_edit"
                                    :style="{ visibility: 
                                            metadataSection.collection_id != collectionId
                                            ? 'hidden' : 'visible'
                                        }" 
                                    @click.prevent="toggleMetadataSectionEdition(metadataSection)">
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('edit'),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip'],
                                            placement: 'auto-start'
                                        }"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                                </span>
                            </a>
                            <a 
                                    v-if="metadataSection.current_user_can_delete"
                                    :disabled="metadataSection.metadata_object_list.length"
                                    :style="{ visibility: metadataSection.collection_id != collectionId || metadataSection.id === 'default_section' || metadataSection.metadata_object_list.length ? 'hidden' : 'visible' }"
                                    @click.prevent="removeMetadataSection(metadataSection)">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('delete'),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip'],
                                            placement: 'auto-start'
                                        }"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-delete"/>
                                </span>
                            </a>
                        </span>
                    </div>
                </div>

                <section 
                        v-if="metadataSection.metadata_object_list && metadataSection.metadata_object_list.length <= 0"
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

                <b-loading :active.sync="isUpdatingMetadatum"/>

                <!-- The Metadata list, inside each metadata section -->
                <template v-if="metadataSection.metadata_object_list && Array.isArray(metadataSection.metadata_object_list)">
                    <draggable 
                            v-model="metadataSection.metadata_object_list"
                            class="active-metadata-area"
                            @change="handleChange($event, sectionIndex)"
                            :group="{ name:'metadata', pull: [ 'metadata' ], put: [ 'metadata' ] }"
                            :sort="(openedMetadatumId == '' || openedMetadatumId == undefined)"
                            :handle="'.handle'"
                            ghost-class="sortable-ghost"
                            chosen-class="sortable-chosen"
                            filter=".not-sortable-item"
                            :prevent-on-filter="false"
                            :animation="250">
                        <div    
                                v-for="(metadatum, index) in metadataSection.metadata_object_list.filter((meta) => meta != undefined && meta.parent == 0)"
                                :key="metadatum.id"
                                v-show="(metadataNameFilterString == '' || filterByMetadatumName(metadatum)) && filterByMetadatumType(metadatum)">
                            <div 
                                    class="active-metadatum-item"
                                    :class="{
                                        'is-compact-item': !isCollapseOpen(metadatum.id),
                                        'not-sortable-item': metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder || metadataNameFilterString != '' || hasSomeMetadataTypeFilterApplied || isUpdatingMetadatum,
                                        'not-focusable-item': openedMetadatumId == metadatum.id,
                                        'disabled-metadatum': metadataSection.enabled == false || metadatum.enabled == false,
                                        'inherited-metadatum': metadatum.inherited,
                                        'child-metadatum': metadatum.parent > 0
                                    }">
                                <div 
                                        :ref="'metadatum-handler-' + metadatum.id"
                                        class="handle">
                                    <span class="sorting-buttons">
                                        <button 
                                                :disabled="index == 0"
                                                class="link-button"
                                                @click="moveMetadatumUpViaButton(index, sectionIndex)"
                                                :aria-label="$i18n.get('label_move_up')">
                                            <span class="icon">
                                                <i class="tainacan-icon tainacan-icon-previous tainacan-icon-rotate-90" />
                                            </span>
                                        </button>
                                        <button 
                                                :disabled="index == metadataSection.metadata_object_list.filter((meta) => meta != undefined && meta.parent == 0).length - 1"
                                                class="link-button"
                                                @click="moveMetadatumDownViaButton(index, sectionIndex)"
                                                :aria-label="$i18n.get('label_move_down')">
                                            <span class="icon">
                                                <i class="tainacan-icon tainacan-icon-next tainacan-icon-rotate-90" />
                                            </span>
                                        </button>
                                    </span>
                                    <span 
                                            :style="{ opacity: !(metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder || metadataNameFilterString != '' || hasSomeMetadataTypeFilterApplied) ? '1.0' : '0.0' }"
                                            v-tooltip="{
                                                content: metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder || isUpdatingMetadatum ? $i18n.get('info_not_allowed_change_order_metadata') : $i18n.get('instruction_drag_and_drop_metadatum_sort'),
                                                autoHide: true,
                                                popperClass: ['tainacan-tooltip', 'tooltip'],
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
                                                popperClass: ['tainacan-tooltip', 'tooltip'],
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
                                                    popperClass: ['tainacan-tooltip', 'tooltip'],
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
                                                    popperClass: ['tainacan-tooltip', 'tooltip'],
                                                    placement: 'auto-start'
                                                }">
                                            <i class="tainacan-icon tainacan-icon-private"/>
                                        </span>
                                        <span 
                                                v-tooltip="{
                                                    content: (metadatum.collection_id == 'default') ? $i18n.get('label_repository_metadatum') : $i18n.get('label_collection_metadatum'),
                                                    autoHide: true,
                                                    popperClass: ['tainacan-tooltip', 'tooltip'],
                                                    placement: 'auto-start'
                                                }"
                                                class="icon icon-level-identifier">
                                            <i 
                                                v-if="metadatum.collection_id == 'default'"
                                                :class="{
                                                    'has-text-blue5': metadatum.enabled,
                                                    'has-text-gray3': !metadatum.enabled
                                                }"
                                                class="tainacan-icon tainacan-icon-repository" />
                                            <i 
                                                v-else
                                                :class="{ 
                                                    'has-text-turquoise5': metadatum.enabled, 
                                                    'has-text-gray3': !metadatum.enabled
                                                }"
                                                class="tainacan-icon tainacan-icon-collection" />
                                        </span>
                                    </span>
                                    <span 
                                            class="loading-spinner" 
                                            v-if="metadatum.id == undefined || isUpdatingMetadatum"/>
                                    <span 
                                            class="controls" 
                                            v-if="metadatum.id !== undefined">
                                        <b-switch 
                                                :style="{ visibility: !metadataSection.enabled ? 'hidden' : 'visible' }"
                                                :disabled="isUpdatingMetadataOrder || !metadataSection.enabled"
                                                size="is-small" 
                                                :value="metadatum.enabled"
                                                @input="onChangeEnable($event, index, sectionIndex)"/>
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
                                                        popperClass: ['tainacan-tooltip', 'tooltip'],
                                                        placement: 'auto-start'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                                            </span>
                                        </a>
                                        <a 
                                                v-if="metadatum.current_user_can_delete"
                                                :style="{ visibility: metadatum.collection_id != collectionId || metadatum.metadata_type_object.core ? 'hidden' : 'visible' }"
                                                @click.prevent="removeMetadatum(metadatum, sectionIndex)">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('delete'),
                                                        autoHide: true,
                                                        popperClass: ['tainacan-tooltip', 'tooltip'],
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
                                    :is-repository-level="false"
                                    :collapse-all="collapseAll"
                                    :section-id="metadataSection.id" />
                            
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
                                        :is-repository-level="false"
                                        @onEditionFinished="onEditionFinished()"
                                        @onEditionCanceled="onEditionCanceled()"
                                        :index="index" />
                            </b-modal>

                        </div>
                    </draggable><!-- End of .active-metadata-area -->
                </template>
                
                <!-- Metadata Section edition form, for each metadata section -->
                <b-modal 
                        @close="onSectionEditionCanceled()"
                        :active="openedMetadataSectionId == metadataSection.id"
                        trap-focus
                        aria-modal
                        aria-role="dialog"
                        custom-class="tainacan-modal"
                        :close-button-aria-label="$i18n.get('close')">
                    <metadata-section-edition-form
                            :collection-id="collectionId"
                            :original-metadata-section="metadataSection"
                            @onEditionFinished="onSectionEditionFinished()"
                            @onEditionCanceled="onSectionEditionCanceled()"
                            :index="sectionIndex" />
                </b-modal>

            </div>
        </draggable> <!-- End of .active-metadata-sections-area -->
    </div> <!-- End of .columns -->
</template>

<script>
import MetadatumEditionForm from '../../components/edition/metadatum-edition-form.vue';
import MetadataSectionEditionForm from '../../components/edition/metadata-section-edition-form.vue';
import MetadatumDetails from '../../components/other/metadatum-details.vue';
import ChildMetadataList from '../../components/metadata-types/compound/child-metadata-list.vue';
import CustomDialog from '../../components/other/custom-dialog.vue';
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'CollectionMetadataList',
    components: {
        MetadatumEditionForm,
        MetadataSectionEditionForm,
        ChildMetadataList,
        MetadatumDetails
    },
    props: {
        metadataTypeFilterOptions: Array
    },
    data() {
        return {
            collectionId: '',
            isLoadingMetadataSections: false,
            isUpdatingMetadataOrder: false,
            openedMetadatumId: '',
            openedMetadataSectionId: '',
            hightlightedMetadatum: '',
            collapses: {},
            collapseAll: false,
            metadataNameFilterString: '',
            isUpdatingMetadataSectionsOrder: false,
            isUpdatingMetadatum: false,
            metadataSearchCancel: undefined
        }
    },
    computed: {
        hasSomeMetadataTypeFilterApplied() {
            return this.metadataTypeFilterOptions.length && this.metadataTypeFilterOptions.some((metadatumType) => metadatumType.enabled);
        },
        activeMetadataSectionsList: {
            get() {
                return this.getMetadataSections();
            },
            set(value) {
                this.updateMetadataSections(value);
            }
        }
    },
    watch: {
        '$route.query': {
            handler(newQuery) {
                if (newQuery.edit != undefined) {
                    let existingMetadataIndex = -1;
                    let existingMetadataSectionIndex = -1;

                    for (let i = 0; i < this.activeMetadataSectionsList.length; i++) {
                        existingMetadataIndex = this.activeMetadataSectionsList[i].metadata_object_list.findIndex((metadatum) => metadatum && (metadatum.id == newQuery.edit));
                        if (existingMetadataIndex >= 0) {
                            existingMetadataSectionIndex = i;
                            break;
                        }
                    }
                    if (existingMetadataIndex >= 0 && existingMetadataSectionIndex >= 0)
                        this.editMetadatum(newQuery.edit);

                } else if (newQuery.sectionEdit != undefined) {
                    let existingMetadataSectionIndex = this.activeMetadataSectionsList.findIndex((metadataSection) => metadataSection && (metadataSection.id == newQuery.sectionEdit));
                    if (existingMetadataSectionIndex >= 0)
                        this.editMetadataSection(newQuery.sectionEdit);                        
                }
            },
            immediate: true
        },
        collapseAll(isCollapsed) {
            this.activeMetadataSectionsList.forEach((metadataSection) => {
                if ( metadataSection.metadata_object_list && Array.isArray(metadataSection.metadata_object_list) )
                    metadataSection.metadata_object_list.forEach((metadatum) => this.$set(this.collapses, metadatum.id, isCollapsed));
            });
        }
    },
    mounted() {
        this.cleanMetadataSections();
        
        this.$eventBusMetadataList.$on('addMetadatumViaButton', this.addMetadatumViaButton);
        this.$eventBusMetadataList.$on('addMetadataSectionViaButton', this.addMetadataSectionViaButton);

        this.collectionId = this.$route.params.collectionId;
        this.isLoadingMetadataSections = true;
        this.fetchMetadataSections({ collectionId: this.collectionId, isContextEdit: true, includeDisabled: true })
            .then(() => {
                this.isLoadingMetadataSections = false;
            })
            .catch((error) => {
                this.$console.error(error);
                this.isLoadingMetadataSections = false;
            });
    },
    beforeDestroy() {
        // Cancels previous Request
        if (this.metadataSearchCancel != undefined)
            this.metadataSearchCancel.cancel('Metadata search Canceled.');
        
        this.$eventBusMetadataList.$off('addMetadatumViaButton', this.addMetadatumViaButton);
        this.$eventBusMetadataList.$off('addMetadataSectionViaButton', this.addMetadataSectionViaButton);
    },
    methods: {
        ...mapActions('metadata', [
            'sendMetadatum',
            'sendMetadataSection',
            'deleteMetadatum',
            'updateMetadatum',
            'updateCollectionMetadataOrder',
            'updateCollectionMetadataSectionsOrder',
            'updateMetadataSections',
            'fetchMetadataSections',
            'deleteMetadataSection',
            'cleanMetadataSections',
            'moveMetadataSectionUp',
            'moveMetadataSectionDown',
            'moveMetadatumUp',
            'moveMetadatumDown'
        ]),
        ...mapGetters('metadata',[
            'getMetadataSections'
        ]),
        handleSectionChange(event) {
            if (event.added)
                this.addNewMetadataSection(event.added.newIndex);
            else if (event.removed)
                this.removeMetadataSection(event.removed.element);
            else if (event.moved)
                this.updateMetadataSectionsOrder();
        },
        handleChange(event, sectionIndex) {
            if (event.added) {
                if (!event.added.element.id)
                    this.addNewMetadatum(event.added.element, event.added.newIndex, sectionIndex);
                else {
                    this.updateMetadatum({
                        collectionId: this.collectionId,
                        metadatumId: event.added.element.id,
                        isRepositoryLevel: event.added.element.collection_id === 'default',
                        index: event.added.newIndex,
                        options: {},
                        includeOptionsAsHtml: true,
                        sectionId: this.activeMetadataSectionsList[sectionIndex].id
                    });
                    this.updateMetadataSectionsOrder(sectionIndex);
                }
            }
            else if (event.moved)
                this.updateMetadataOrder(sectionIndex);
        },
        updateMetadataOrder(sectionIndex) {
            let metadataOrder = [];
            for (let metadatum of this.activeMetadataSectionsList[sectionIndex].metadata_object_list)
                if (metadatum != undefined)
                    metadataOrder.push({
                        'id': metadatum.id,
                        'enabled': metadatum.enabled
                    });
            
            this.isUpdatingMetadataOrder = true;
            this.updateCollectionMetadataOrder({ collectionId: this.collectionId, metadataOrder: metadataOrder, metadataSectionId: this.activeMetadataSectionsList[sectionIndex].id  })
                .then(() => this.isUpdatingMetadataOrder = false)
                .catch(() => this.isUpdatingMetadataOrder = false);
        },
        updateMetadataSectionsOrder() {
            let metadataSectionsOrder = [];
            for (let metadataSection of this.activeMetadataSectionsList)
                if (metadataSection != undefined) {
                    metadataSectionsOrder.push({
                        'id': metadataSection.id,
                        'enabled': metadataSection.enabled,
                        'metadata_order': metadataSection.metadata_object_list.map((aMetadatum) => { return { 'id': aMetadatum.id, 'enabled': aMetadatum.enabled } })
                    });
                }
            
            this.isUpdatingMetadataSectionsOrder = true;
            this.updateCollectionMetadataSectionsOrder({ collectionId: this.collectionId, metadataSectionsOrder: metadataSectionsOrder })
                .then(() => this.isUpdatingMetadataSectionsOrder = false)
                .catch(() => this.isUpdatingMetadataSectionsOrder = false);
        },
        onChangeEnable($event, index, sectionIndex) {
            let metadataOrder = [];
            for (let metadatum of this.activeMetadataSectionsList[sectionIndex].metadata_object_list)
                if (metadatum != undefined)
                    metadataOrder.push({'id': metadatum.id, 'enabled': metadatum.enabled});
            
            metadataOrder[index].enabled = $event;
            this.isUpdatingMetadataOrder = true;
            this.updateCollectionMetadataOrder({ collectionId: this.collectionId, metadataOrder: metadataOrder, metadataSectionId: this.activeMetadataSectionsList[sectionIndex].id })
                .then(() => this.isUpdatingMetadataOrder = false)
                .catch(() => this.isUpdatingMetadataOrder = false);
        },
        onChangeEnableSection($event, index) {
            let metadataSectionsOrder = [];
            for (let metadataSection of this.activeMetadataSectionsList)
                if (metadataSection != undefined)
                    metadataSectionsOrder.push({
                        'id': metadataSection.id,
                        'enabled': metadataSection.enabled,
                        'metadata_order': metadataSection.metadata_object_list.map((aMetadatum) => { return { 'id': aMetadatum.id, 'enabled': aMetadatum.enabled } })
                    });
            
            metadataSectionsOrder[index].enabled = $event;
            this.isUpdatingMetadataSectionsOrder = true;
            this.updateCollectionMetadataSectionsOrder({ collectionId: this.collectionId, metadataSectionsOrder: metadataSectionsOrder })
                .then(() => this.isUpdatingMetadataSectionsOrder = false)
                .catch(() => this.isUpdatingMetadataSectionsOrder = false);
        },
        addMetadatumViaButton(metadatumType) {
            this.addNewMetadatum(metadatumType, this.activeMetadataSectionsList[0].metadata_object_list.length, 0);
            
            // Higlights the clicked metadatum
            this.hightlightedMetadatum = metadatumType.name;
            this.$emit('onUpdatehightlightedMetadatum', this.hightlightedMetadatum);
        },
        addMetadataSectionViaButton() {
            let lastIndex = this.activeMetadataSectionsList.length;
            this.addNewMetadataSection(lastIndex);
        },
        addNewMetadatum(newMetadatum, newIndex, sectionIndex) {
            this.isUpdatingMetadatum = true;
            this.sendMetadatum({
                collectionId: this.collectionId, 
                name: newMetadatum.name, 
                metadatumType: newMetadatum.className, 
                status: 'auto-draft', 
                isRepositoryLevel: false, 
                newIndex: newIndex,
                parent: '0',
                sectionId: this.activeMetadataSectionsList[sectionIndex].id
            })
            .then((metadatum) => {

                this.updateMetadataOrder(sectionIndex);

                this.toggleMetadatumEdition(metadatum)
                this.hightlightedMetadatum = '';
                this.isUpdatingMetadatum = false;
                this.$emit('onUpdatehightlightedMetadatum', this.hightlightedMetadatum);
            })
            .catch((error) => {
                this.isUpdatingMetadatum = false;
                this.$console.error(error);
            });
        },
        addNewMetadataSection(newIndex) {
            this.isUpdatingMetadatum = true;
            this.sendMetadataSection({
                collectionId: this.collectionId, 
                name: this.$i18n.get('label_new_metadata_section'), 
                status: 'auto-draft',  
                newIndex: newIndex
            })
            .then((metadataSection) => {
                this.updateMetadataSectionsOrder();
                this.toggleMetadataSectionEdition(metadataSection);
                this.isUpdatingMetadatum = false;
            })
            .catch((error) => {
                this.$console.error(error);
                this.isUpdatingMetadatum = false;
            });
        },
        removeMetadatum(removedMetadatum, sectionIndex) {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_metadatum_delete'),
                    onConfirm: () => { 
                        this.isUpdatingMetadataOrder = true;
                        this.deleteMetadatum({
                                collectionId: this.collectionId,
                                metadatumId: removedMetadatum.id,
                                isRepositoryLevel: false
                            })
                            .then(() => {
                                this.updateMetadataOrder(sectionIndex);
                            })
                            .catch(() => {
                                this.isUpdatingMetadataOrder = false;
                                this.$console.log("Error deleting metadatum.")
                            });
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            }); 
        },
        removeMetadataSection(removedMetadataSection) {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_metadata_section_delete'),
                    onConfirm: () => { 
                        this.isUpdatingMetadataSectionsOrder = true;
                        this.deleteMetadataSection({ collectionId: this.collectionId, metadataSectionId: removedMetadataSection.id })
                            .then(() => {
                                this.updateMetadataSectionsOrder();
                            })
                            .catch(() => {
                                this.isUpdatingMetadataSectionsOrder = false;
                                this.$console.log("Error deleting metadata section.")
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
        toggleMetadataSectionEdition(metadataSection) {
            this.$router.push({ query: { sectionEdit: metadataSection.id } });
        },
        editMetadatum(metadatumId) {
            this.openedMetadatumId = metadatumId;
        },
        editMetadataSection(metadataSectionId) {
            this.openedMetadataSectionId = metadataSectionId;
        },
        onEditionFinished() {
            this.openedMetadatumId = '';
            this.$router.push({ query: {}});
        },
        onEditionCanceled() {
            this.openedMetadatumId = '';
            this.$router.push({ query: {}});
        },
        onSectionEditionFinished() {
            this.openedMetadataSectionId = '';
            this.$router.push({ query: {}});
        },
        onSectionEditionCanceled() {
            this.openedMetadataSectionId = '';
            this.$router.push({ query: {}});
        },
        moveMetadatumUpViaButton(index, sectionIndex) {
            this.moveMetadatumUp({ index, sectionIndex });
            this.updateMetadataOrder(sectionIndex);
        },
        moveMetadatumDownViaButton(index, sectionIndex) {
            this.moveMetadatumDown({ index, sectionIndex });
            this.updateMetadataOrder(sectionIndex);
        },
        moveMetadataSectionUpViaButon(sectionIndex) {
            this.moveMetadataSectionUp(sectionIndex);
            this.updateMetadataSectionsOrder();
        },
        moveMetadataSectionDownViaButton(sectionIndex) {
            this.moveMetadataSectionDown(sectionIndex);
            this.updateMetadataSectionsOrder();
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