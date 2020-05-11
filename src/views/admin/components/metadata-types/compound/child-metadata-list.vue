<template>
    <div class="child-metadata-list-container">    
        <span class="icon children-icon not-sortable-item">
            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-nextlevel"/>
        </span> 
        <section 
                v-if="childrenMetadata.length <= 0"
                class="field is-grouped-centered section not-sortable-item">
            <div class="content has-text-gray has-text-centered">
                <p>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-metadata"/>
                    </span>
                </p>
                <p>{{ $i18n.get('info_create_child_metadata') }}</p>
            </div>
        </section>    
        <draggable 
                v-model="childrenMetadata"
                :style="{ minHeight: childrenMetadata.length > 0 ? '40px' : '70px' }"
                class="active-metadata-area child-metadata-area"
                @change="handleChange"
                :group="{ name:'metadata', pull: false, put: isAvailableChildMetadata }"
                :sort="(openedMetadatumId == '' || openedMetadatumId == undefined)"
                :handle="'.handle'"
                ghost-class="sortable-ghost"
                chosen-class="sortable-chosen"
                filter=".not-sortable-item"
                :prevent-on-filter="false"
                :animation="250"> 
            <div 
                    class="active-metadatum-item"
                    :class="{
                        'not-sortable-item': metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder,
                        'not-focusable-item': openedMetadatumId == metadatum.id,
                        'disabled-metadatum': parent.enabled == false,
                        'inherited-metadatum': (metadatum.collection_id != collectionId && metadatum.parent == 0) || isRepositoryLevel
                    }" 
                    v-for="(metadatum, index) in childrenMetadata"
                    :key="metadatum.id">
                <div 
                        :ref="'metadatum-handler-' + metadatum.id"
                        class="handle">
                    <span 
                            :style="{ opacity: !( metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder) ? '1.0' : '0.0' }"
                            v-tooltip="{
                                content: metadatum.id == undefined || openedMetadatumId != '' || isUpdatingMetadataOrder ? $i18n.get('info_not_allowed_change_order_metadata') : $i18n.get('instruction_drag_and_drop_metadatum_sort'),
                                autoHide: true,
                                classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                placement: 'auto-start'
                            }"
                            class="icon grip-icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-drag"/>
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
                                'has-text-turquoise5': (metadatum.collection_id != 'default' && !isRepositoryLevel), 
                                'has-text-blue5': (metadatum.collection_id == 'default' || isRepositoryLevel),
                                'has-text-gray3': !parent.enabled
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
                        <span 
                                v-if="metadatum.status == 'private'"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-private"/>
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
                                :edited-metadatum="editForms[metadatum.id]"
                                :is-parent-multiple="isParentMultiple"/>
                    </div>
                </transition>
            </div>
        </draggable>
    </div>
</template>

<script>
    import { mapActions } from 'vuex';
    import MetadatumEditionForm from '../../edition/metadatum-edition-form.vue';
    import CustomDialog from '../../other/custom-dialog.vue';

    export default {
        components: {
            MetadatumEditionForm 
        },
        props: {
            isRepositoryLevel: Boolean,
            parent: Object,
            isParentMultiple: Boolean
        },
        data() {
            return {
                collectionId: '',
                isLoadingMetadata: false,
                isUpdatingMetadataOrder: false,
                openedMetadatumId: '',
                formWithErrors: '',
                hightlightedMetadatum: '',
                editForms: {},
                metadataSearchCancel: undefined,
                childrenMetadata: []
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
                immediate: true
            },
            'parent.metadata_type_options.children_objects': {
                handler(childrenObjects) {
                    if (childrenObjects && childrenObjects.length > 0)
                        this.childrenMetadata = childrenObjects;
                    else
                        this.childrenMetadata = [];
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
                next();
            }  
        },
        mounted() {
            if (this.isRepositoryLevel)
                this.collectionId = 'default';
            else
                this.collectionId = this.$route.params.collectionId;
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
                'updateChildMetadataOrder'
            ]),
            handleChange(event) {     
               if (event.added) {
                    this.addNewMetadatum(event.added.element, event.added.newIndex);
                } else if (event.removed) {
                    this.removeMetadatum(event.removed.element);
                } else if (event.moved) {
                    this.updateMetadataOrder();
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
                    metadatumType: newMetadatum.className, 
                    status: 'auto-draft', 
                    isRepositoryLevel: this.isRepositoryLevel, 
                    newIndex: newIndex,
                    parent: this.parent.id
                })
                .then((metadatum) => {
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
            isAvailableChildMetadata(to, from, item) {
                return !['tainacan-compound', 'tainacan-taxonomy'].includes(item.id);
            },
        }
    }
</script>

<style lang="scss" scoped>
.child-metadata-list-container {
    position: relative;
    margin-left: 42px;
    border-left: 1px solid var(--tainacan-gray2);

    section.field {
        padding: 0.5em 1em 0 1em;
        position: absolute;
        width: 100%;
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
