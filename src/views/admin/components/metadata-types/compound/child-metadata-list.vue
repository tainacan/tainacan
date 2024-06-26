<template>
    <div class="child-metadata-list-container">    
        <span class="icon children-icon not-sortable-item">
            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-nextlevel" />
        </span> 
        <section 
                v-if="childrenMetadata.length <= 0"
                class="field is-grouped-centered section not-sortable-item">
            <div class="content has-text-gray has-text-centered">
                <p>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-metadata" />
                    </span>
                </p>
                <p>{{ $i18n.get('info_create_child_metadata') }}</p>
            </div>
        </section>
        <sortable
                :list="childrenMetadata"
                :style="{ minHeight: childrenMetadata.length > 0 ? '40px' : '70px' }"
                class="active-metadata-area child-metadata-area"
                item-key="id"
                :options="{
                    group: {
                        name: 'metadata',
                        pull: false,
                        put: isAvailableChildMetadata
                    },
                    sort: (openedMetadatumId == '' || openedMetadatumId == undefined),
                    handle: '.handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    filter: '.not-sortable-item',
                    preventOnFilter: false,
                    animation: 250
                }"
                @update="handleChange($event)"
                @add="handleChange($event)"
                @remove="handleChange($event)"> 
            <template #item="{ element: metadatum, index }">
                <div 
                        v-show="(metadataNameFilterString == '' || filterByMetadatumName(metadatum)) && filterByMetadatumType(metadatum)"
                        :data-metadatum-id="metadatum.id"
                        :data-collection-id="metadatum.collection_id"
                        class="active-metadatum-item" 
                        :class="{
                            'not-sortable-item': metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder || metadatum.parent == 0 || metadatum.collection_id != collectionId || metadataNameFilterString != '' || hasSomeMetadataTypeFilterApplied,
                            'not-focusable-item': openedMetadatumId == metadatum.id,
                            'disabled-metadatum': parent.enabled == false,
                            'inherited-metadatum': (metadatum.collection_id != collectionId && metadatum.parent == 0) || isRepositoryLevel
                        }">
                    <div 
                            :ref="'metadatum-handler-' + metadatum.id"
                            class="handle">
                        <span class="sorting-buttons">
                            <button 
                                    :disabled="index == 0"
                                    class="link-button"
                                    :aria-label="$i18n.get('label_move_up')"
                                    @click="moveMetadatumUpViaButton(index)">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-previous tainacan-icon-rotate-90" />
                                </span>
                            </button>
                            <button 
                                    :disabled="index == childrenMetadata.length - 1"
                                    class="link-button"
                                    :aria-label="$i18n.get('label_move_down')"
                                    @click="moveMetadatumDownViaButton(index)">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-next tainacan-icon-rotate-90" />
                                </span>
                            </button>
                        </span>
                        <span 
                                v-tooltip="{
                                    content: metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder ? $i18n.get('info_not_allowed_change_order_metadata') : $i18n.get('instruction_drag_and_drop_metadatum_sort'),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                :style="{ opacity: !(metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder || metadatum.parent == 0 || metadatum.collection_id != collectionId || metadataNameFilterString != '' || hasSomeMetadataTypeFilterApplied) ? '1.0' : '0.0' }"
                                class="icon grip-icon">
                            <!-- <i class="tainacan-icon tainacan-icon-18px tainacan-icon-drag"/> -->
                            <svg 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    height="24px"
                                    viewBox="0 0 24 24"
                                    width="24px"
                                    fill="currentColor">
                                <path
                                        d="M0 0h24v24H0V0z"
                                        fill="transparent" />
                                <path d="M11 18c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm-2-8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6 4c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                            </svg>
                        </span>
                        <span 
                                v-tooltip="{
                                    content: $i18n.get('label_view_metadata_details'),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                class="gray-icon icon"
                                :style="{ cursor: 'pointer', opacity: openedMetadatumId != metadatum.id ? '1.0' : '0.0' }"
                                @click="Object.assign( collapses, { [metadatum.id]: !isCollapseOpen(metadatum.id) })">
                            <i :class="'tainacan-icon tainacan-icon-1-25em tainacan-icon-' + (isCollapseOpen(metadatum.id) ? 'arrowdown' : 'arrowright')" />
                        </span>
                        <span class="metadatum-name">
                            {{ metadatum.name }}
                        </span>
                        <span   
                                v-if="metadatum.id != undefined && metadatum.metadata_type_object"
                                class="label-details">
                            <span 
                                    v-if="metadatum.required === 'yes'"
                                    v-tooltip="{
                                        content: $i18n.get('label_required'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }">
                                *&nbsp;
                            </span>
                            ({{ metadatum.metadata_type_object.name }}) 
                            <!-- <em v-if="metadatum.collection_id != collectionId">{{ $i18n.get('label_inherited') }}</em> -->
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
                                    v-if="metadatum.status == 'private'"
                                    v-tooltip="{
                                        content: $i18n.get('status_private'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-private" />
                            </span>
                            <span 
                                    v-tooltip="{
                                        content: (metadatum.collection_id == 'default') || isRepositoryLevel ? $i18n.get('label_repository_metadatum') : $i18n.get('label_collection_metadatum'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon icon-level-identifier">
                                <i 
                                        :class="{ 
                                            'tainacan-icon-collection': (metadatum.collection_id != 'default' && !isRepositoryLevel), 
                                            'tainacan-icon-repository': (metadatum.collection_id == 'default') || isRepositoryLevel,
                                            'has-text-turquoise5': (metadatum.collection_id != 'default' && !isRepositoryLevel), 
                                            'has-text-blue5': (metadatum.collection_id == 'default' || isRepositoryLevel),
                                            'has-text-gray3': !parent.enabled
                                        }"
                                        class="tainacan-icon" />
                            </span>
                        </span>
                        <span 
                                v-if="metadatum.id == undefined" 
                                class="loading-spinner" />
                        <span 
                                v-if="metadatum.id !== undefined" 
                                class="controls">
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
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto-start'
                                        }"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
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
                                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                            placement: 'auto-start'
                                        }"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-delete" />
                                </span>
                            </a>
                        </span>
                    </div>
                    <transition name="form-collapse">
                        <metadatum-details 
                                v-if="isCollapseOpen(metadatum.id) && openedMetadatumId !== metadatum.id"
                                :metadatum="metadatum" />
                    </transition>
                    <b-modal 
                            :model-value="openedMetadatumId == metadatum.id"
                            trap-focus
                            aria-modal
                            aria-role="dialog"
                            custom-class="tainacan-modal"
                            :close-button-aria-label="$i18n.get('close')"
                            @close="onEditionCanceled()">
                        <metadatum-edition-form
                                :collection-id="collectionId"
                                :original-metadatum="metadatum"
                                :is-parent-multiple="isParentMultiple"
                                :is-repository-level="isRepositoryLevel"
                                :index="index"
                                @on-edition-finished="onEditionFinished()"
                                @on-edition-canceled="onEditionCanceled()" />
                    </b-modal>
                </div>
            </template>
        </sortable>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import MetadatumEditionForm from '../../edition/metadatum-edition-form.vue';
    import MetadatumDetails from '../../other/metadatum-details.vue';
    import CustomDialog from '../../other/custom-dialog.vue';

    import { Sortable } from 'sortablejs-vue3';

    export default {
        components: {
            MetadatumEditionForm,
            MetadatumDetails,
            Sortable
        },
        props: {
            isRepositoryLevel: Boolean,
            parent: Object,
            isParentMultiple: Boolean,
            metadataNameFilterString: String,
            hasSomeMetadataTypeFilterApplied: {
                type: Boolean,
                default: new Boolean()
            },
            metadataTypeFilterOptions: {
                type: Array,
                default: new Array()
            },
            collapseAll: {
                type: Boolean,
                default: new Boolean()
            },
            sectionId: String
        },
        data() {
            return {
                collectionId: '',
                isLoadingMetadata: false,
                isUpdatingMetadataOrder: false,
                openedMetadatumId: '',
                highlightedMetadatum: '',
                metadataSearchCancel: undefined,
                childrenMetadata: [],
                collapses: {}
            }
        },
        watch: {
            '$route.query': {
                handler(newQuery) {
                    if (newQuery.edit != undefined) {
                        
                        let existingMetadataIndex = this.childrenMetadata.findIndex((metadatum) => metadatum && (metadatum.id == newQuery.edit));
                        if (existingMetadataIndex >= 0)
                            this.editMetadatum(this.childrenMetadata[existingMetadataIndex])                  
                    }
                },
                immediate: true,
                deep: true
            },
            'parent.metadata_type_options.children_objects': {
                handler(childrenObjects) {
                    if (childrenObjects && childrenObjects.length > 0)
                        this.childrenMetadata = childrenObjects;
                    else
                        this.childrenMetadata = [];
                }, 
                immediate: true,
                deep: true
            },
            collapseAll(isCollapsed) {
                this.childrenMetadata.forEach((metadatum) => Object.assign( this.collapses, { [metadatum.id]: isCollapsed }));
            }
        },
        mounted() {
            if (this.isRepositoryLevel)
                this.collectionId = 'default';
            else
                this.collectionId = this.$route.params.collectionId;
        },
        beforeUnmount() {

            // Cancels previous Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');

        },
        methods: {
             ...mapActions('metadata', [
                'fetchMetadata',
                'sendMetadatum',
                'deleteMetadatum',
                'updateChildMetadataOrder'
            ]),
            ...mapGetters('metadata',[
                'getMetadatumTypes'
            ]),
            handleChange($event) {  
                switch ($event.type) {
                    case 'add':
                        if ( !$event.from.classList.contains('active-metadata-area') ) {
                            this.addNewMetadatum(this.getMetadatumTypes()[$event.oldIndex], $event.newIndex);
                            $event.to.removeChild($event.item);
                        }
                        break;
                    case 'remove':
                        this.removeMetadatum(this.childrenMetadata[$event.oldIndex]);
                        break;
                    case 'change':
                    case 'update': {
                        const newChildrenMetadata = JSON.parse(JSON.stringify(this.childrenMetadata));
                        const element = newChildrenMetadata.splice($event.oldIndex, 1)[0];
                        newChildrenMetadata.splice($event.newIndex, 0, element);

                        this.childrenMetadata = newChildrenMetadata;

                        this.updateMetadataOrder();
                        break;
                    }
                }
            },
            updateMetadataOrder() {
                let metadataOrder = [];
                for (let metadatum of this.childrenMetadata)
                    if (metadatum != undefined)
                        metadataOrder.push({ 'id': metadatum.id });
           
                this.isUpdatingMetadataOrder = true;
                this.updateChildMetadataOrder({
                    isRepositoryLevel: this.isRepositoryLevel, 
                    collectionId: this.collectionId,
                    parentMetadatumId: this.parent.id,
                    childMetadataOrder: metadataOrder
                })
                    .then(() => this.isUpdatingMetadataOrder = false)
                    .catch(() => this.isUpdatingMetadataOrder = false);
            },
            addNewMetadatum(newMetadatum, newIndex) {
                this.sendMetadatum({
                    collectionId: this.collectionId, 
                    name: newMetadatum.name, 
                    metadatumType: newMetadatum.className ? newMetadatum.className : newMetadatum.metadata_type, 
                    status: 'auto-draft', 
                    isRepositoryLevel: this.isRepositoryLevel, 
                    newIndex: newIndex,
                    parent: this.parent.id,
                    sectionId: this.sectionId ? this.sectionId : false
                })
                .then((metadatum) => {
                    this.updateMetadataOrder();

                    this.toggleMetadatumEdition(metadatum.id)
                    this.highlightedMetadatum = '';
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
                                    isRepositoryLevel: this.isRepositoryLevel
                                })
                                .then(() => {
                                    this.updateMetadataOrder();
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
            },
            onEditionFinished() {
                this.openedMetadatumId = '';
                this.$router.push({ query: {}});
            },
            onEditionCanceled() {
                this.openedMetadatumId = '';
                this.$router.push({ query: {}});
            },
            moveMetadatumUpViaButton(index) {
                this.childrenMetadata.splice(index - 1, 0, this.childrenMetadata.splice(index, 1)[0]);
                this.updateMetadataOrder();
            },
            moveMetadatumDownViaButton(index) {
                this.childrenMetadata.splice(index + 1, 0, this.childrenMetadata.splice(index, 1)[0]);
                this.updateMetadataOrder();
            },
            isAvailableChildMetadata(to, from, item) {

                // Se não estamos na lista de metadados do repositório, não podemos inserir filhos em metadados que sejam herdados do repositório
                if ( !this.isRepositoryLevel && this.parent.collection_id === 'default' )
                    return false;
                
                if (!item || !item.dataset || !item.dataset.metadatumType)
                    return false;
                
                if (from.el && from.el.className === 'active-metadata-area')
                    return false;
                
                return !['tainacan-compound', 'tainacan-taxonomy'].includes(item.dataset.metadatumType);
            },
            isCollapseOpen(metadatumId) {
                return this.collapses[metadatumId] == true;
            },
            filterByMetadatumName(metadatum) {
                return metadatum.name.toString().toLowerCase().indexOf(this.metadataNameFilterString.toString().toLowerCase()) >= 0;
            },
            filterByMetadatumType(metadatum) {
                if (!this.hasSomeMetadataTypeFilterApplied)
                    return true;

                for (let metadatumType of this.metadataTypeFilterOptions) {
                    if (metadatumType.enabled && metadatum.metadata_type == metadatumType.type)
                        return true;
                }
                
                return false;
            }
        }
    }
</script>

<style lang="scss" scoped>

.child-metadata-list-container {
    position: relative;
    margin-left: 42px;
    border-left: 1px solid var(--tainacan-gray2);

    section.field {
        padding: 0.5em 1em 0 1em !important;
        position: absolute;
        width: 100% !important;
    }
    .children-icon {
        position: absolute;
        top: 0;
        left: -22px;

        .icon {
            color: var(--tainacan-info-color) !important;
        }
    }

    .child-metadata-area {
        padding: 0;
        margin: 0;
        font-size: 1em;

        section {
            padding: 0.5em 1em;
        }
        .active-metadatum-item {
            margin-left: 0;
        }
    }
}
</style>
