<template>

    <div ><!--
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-compound', 'children') }}<span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-compound', 'children')"
                        :message="$i18n.getHelperMessage('tainacan-compound', 'children')"/>
            </label>
            <div class="child-metadata-list-container">     
                <section 
                        v-if="childrenMetadata.length <= 0"
                        class="field is-grouped-centered section">
                    <div class="content has-text-gray has-text-centered">
                        <p>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-metadata"/>
                            </span>
                        </p>
                        <p>{{ $i18n.get('info_there_is_no_metadatum' ) }}</p>
                        <p>{{ $i18n.get('info_create_metadata' ) }}</p>
                    </div>
                </section>     
                <draggable 
                        v-model="childrenMetadata"
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
                            v-for="(metadatum, index) in childrenMetadata"
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
                                <pre> Metadadum Edition Form will go here.</pre>
                            </div>
                        </transition>
                    </div>
                </draggable>
            </div>
        </b-field>  -->
    </div> 
</template>
<!--
<script>
    export default {
        props: {
            value: [ String, Object, Array ],
            metadatum: [ String, Object ],
            errors: [ String, Object, Array ]
        },
        data() {
            return {
                childrenMetadata: [],
                collectionId: '',
                openedMetadatumId: '',
                isRepositoryLevel: false,
                isUpdatingMetadataOrder: false,
                formWithErrors: '',
            }
        },
        created(){
            this.isRepositoryLevel = (this.$route.params.collectionId === undefined);

            if (this.metadatum.metadatum &&
                this.metadatum.metadatum.metadata_type_options &&
                this.metadatum.metadatum.metadata_type_options.children_objects.length > 0 
            ) {
                for (let metadatum of this.metadatum.metadatum.metadata_type_options.children_objects)
                    this.childrenMetadata.push(metadatum);   
            }
        },
        methods: {
            handleChange(event) {     
                if (event.added) {
                    this.addNewMetadatum(event.added.element, event.added.newIndex);
                } else if (event.removed) {
                    this.removeMetadatum(event.removed.element);
                } else if (event.moved) {
                    this.updateMetadataOrder();
                }
            },
            addNewMetadatum(newMetadatum, newIndex) {
                console.log(newMetadatum, newIndex)
            },
            removeMetadatum(removedMetadatum) {
                console.log(removedMetadatum)
            },
            updateMetadataOrder() {
                console.log("atualizando ordem do metadado...")
            },
            onChangeEnable($event, index) {
                console.log($event, index)
            },
            emitValues(){
                this.$emit('input',{
                    options: ( this.options.length > 0 ) ? this.options.join('\n') : ''
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
.child-metadata-list-container{
    section {
        padding: 1em 0.5em;
    }
    .active-metadata-area {
        padding: 0;
        margin: 0;
    }
}
</style>
-->