<template>
    <div>
        <b-loading
                :is-full-page="false"
                :active.sync="isLoading"
                :can-cancel="false"/>

        <div class="tainacan-page-title">
            <h1 v-if="isCreatingNewItem">
                <span 
                        v-if="(item != null && item != undefined && item.status != undefined && !isLoading)"
                        class="status-tag">{{ $i18n.get('status_' + item.status) }}</span>
                {{ $i18n.get('title_create_item_collection') + ' ' }}
                <span style="font-weight: 600;">{{ collectionName }}</span>
            </h1>
            <h1 v-else>
                <span 
                        v-if="(item != null && item != undefined && item.status != undefined && !isLoading)"
                        class="status-tag">{{ $i18n.get('status_' + item.status) }}</span>
                {{ $i18n.get('title_edit_item') + ' ' }}
                <span style="font-weight: 600;">{{ (item != null && item != undefined) ? item.title : '' }}</span>
            </h1>
            <a 
                    @click="$router.go(-1)"
                    class="back-link has-text-secondary">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </div>
        <transition 
                mode="out-in"
                :name="(isOnSequenceEdit && sequenceRightDirection != undefined) ? (sequenceRightDirection ? 'page-right' : 'page-left') : ''">
            <form
                    v-if="!isLoading"
                    class="tainacan-form"
                    label-width="120px">
                <div class="columns">
                    <div class="column is-5">

                        <!-- Hook for extra Form options -->
                        <template 
                                v-if="formHooks != undefined && 
                                    formHooks['item'] != undefined &&
                                    formHooks['item']['begin-left'] != undefined">  
                            <form 
                                id="form-item-begin-left"
                                class="form-hook-region"
                                v-html="formHooks['item']['begin-left'].join('')"/>
                        </template>

                        <!-- Document -------------------------------- -->
                        <div class="section-label">
                            <label>{{ form.document != undefined && form.document != null && form.document != '' ? $i18n.get('label_document') : $i18n.get('label_document_empty') }}</label>
                            <help-button
                                    :title="$i18n.getHelperTitle('items', 'document')"
                                    :message="$i18n.getHelperMessage('items', 'document')"/>
                        </div>
                        <div class="section-box document-field">
                            <div
                                    v-if="form.document != undefined && form.document != null &&
                                            form.document_type != undefined && form.document_type != null &&
                                            form.document != '' && form.document_type != 'empty'">
                                <div v-if="form.document_type == 'attachment'">
                                    <!-- <div v-html="item.document_as_html" /> -->
                                    <document-item :document-html="item.document_as_html"/>

                                    <div class="document-buttons-row">
                                        <a
                                                class="button is-rounded is-secondary"
                                                size="is-small"
                                                id="button-edit-document"
                                                :aria-label="$i18n.get('label_button_edit_document')"
                                                @click.prevent="setFileDocument($event)">
                                            <span 
                                                    v-tooltip="{
                                                        content: $i18n.get('edit'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-edit"/>
                                            </span>
                                        </a>
                                        <a
                                                class="button is-rounded is-secondary"
                                                size="is-small"
                                                id="button-delete-document"
                                                :aria-label="$i18n.get('label_button_delete_document')"
                                                @click.prevent="removeDocument()">
                                            <span 
                                                    v-tooltip="{
                                                        content: $i18n.get('delete'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-delete"/>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <div v-if="form.document_type == 'text'">
                                    <div v-html="item.document_as_html" />
                                    <div class="document-buttons-row">
                                        <a
                                                class="button is-rounded is-secondary"
                                                :aria-label="$i18n.get('label_button_edit_document')"
                                                id="button-edit-document"
                                                @click.prevent="setTextDocument()">
                                            <span 
                                                    v-tooltip="{
                                                        content: $i18n.get('edit'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-edit"/>
                                            </span>
                                        </a>
                                        <a
                                                class="button is-rounded is-secondary"
                                                size="is-small"
                                                :aria-label="$i18n.get('label_button_delete_document')"
                                                id="button-delete-document"
                                                @click.prevent="removeDocument()">
                                            <span 
                                                    v-tooltip="{
                                                        content: $i18n.get('delete'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-delete"/>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <div v-if="form.document_type == 'url'">
                                    <div v-html="item.document_as_html" />
                                    <div class="document-buttons-row">
                                        <a
                                                class="button is-rounded is-secondary"
                                                size="is-small"
                                                :aria-label="$i18n.get('label_button_edit_document')"
                                                id="button-edit-document"
                                                @click.prevent="setURLDocument()">
                                            <span 
                                                    v-tooltip="{
                                                        content: $i18n.get('edit'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-edit"/>
                                            </span>
                                        </a>
                                        <a
                                                class="button is-rounded is-secondary"
                                                size="is-small"
                                                :aria-label="$i18n.get('label_button_delete_document')"
                                                id="button-delete-document"
                                                @click.prevent="removeDocument()">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('delete'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-delete"/>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <ul v-else>
                                <li>
                                    <button 
                                            type="button"
                                            @click.prevent="setFileDocument($event)">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-upload"/>
                                        </span>
                                    </button>
                                    <p>{{ $i18n.get('label_file') }}</p>
                                </li>
                                <li>
                                    <button 
                                            type="button"
                                            @click.prevent="setTextDocument()">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-text"/>
                                        </span>
                                    </button>
                                    <p>{{ $i18n.get('label_text') }}</p>
                                </li>
                                <li>
                                    <button 
                                            type="button"
                                            @click.prevent="setURLDocument()">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-url"/>
                                        </span>
                                    </button>
                                    <p>{{ $i18n.get('label_url') }}</p>
                                </li>
                            </ul>
                        </div>

                        <!-- Text Insert Modal ----------------- -->
                        <b-modal
                                :can-cancel="false"
                                :active.sync="isTextModalActive"
                                :width="640"
                                scroll="keep">
                            <div class="tainacan-modal-content">
                                <div class="tainacan-modal-title">
                                    <h2>{{ $i18n.get('instruction_write_text') }}</h2>
                                    <hr>
                                </div>
                                <b-input
                                        type="textarea"
                                        v-model="textContent"/>

                                <div class="field is-grouped form-submit">
                                    <div class="control">
                                        <button
                                                id="button-cancel-text-content-writing"
                                                class="button is-outlined"
                                                type="button"
                                                @click="cancelTextWriting()">
                                            {{ $i18n.get('cancel') }}</button>
                                    </div>
                                    <div class="control">
                                        <button
                                                id="button-submit-text-content-writing"
                                                type="submit"
                                                @click.prevent="confirmTextWriting()"
                                                class="button is-success">
                                            {{ $i18n.get('save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </b-modal>

                        <!-- URL Insert Modal ----------------- -->
                        <b-modal
                                :can-cancel="false"
                                :active.sync="isURLModalActive"
                                :width="640"
                                scroll="keep">
                            <div class="tainacan-modal-content">
                                <div class="tainacan-modal-title">
                                    <h2>{{ $i18n.get('instruction_insert_url') }}</h2>
                                    <hr>
                                </div>
                                <b-input v-model="urlLink"/>

                                <div class="field is-grouped form-submit">
                                    <div class="control">
                                        <button
                                                id="button-cancel-url-link-selection"
                                                class="button is-outlined"
                                                type="button"
                                                @click="cancelURLSelection()">
                                            {{ $i18n.get('cancel') }}</button>
                                    </div>
                                    <div class="control">
                                        <button
                                                id="button-submit-url-link-selection"
                                                @click.prevent="confirmURLSelection()"
                                                class="button is-success">
                                            {{ $i18n.get('save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </b-modal>

                        <!-- Thumbnail -------------------------------- -->
                        <div class="section-label">
                            <label>{{ $i18n.get('label_thumbnail') }}</label>
                            <help-button
                                    :title="$i18n.getHelperTitle('items', '_thumbnail_id')"
                                    :message="$i18n.getHelperMessage('items', '_thumbnail_id')"/>

                        </div>                    
                        <div class="section-box section-thumbnail">
                            <div class="thumbnail-field">
                                <file-item
                                        v-if="item.thumbnail != undefined && ((item.thumbnail['tainacan-medium'] != undefined && item.thumbnail['tainacan-medium'] != false) || (item.thumbnail.medium != undefined && item.thumbnail.medium != false))"
                                        :show-name="false"
                                        :modal-on-click="false"
                                        :size="178"
                                        :file="{ 
                                            media_type: 'image', 
                                            guid: { rendered: item.thumbnail['tainacan-medium'] ? item.thumbnail['tainacan-medium'][0] : item.thumbnail.medium[0] },
                                            title: { rendered: $i18n.get('label_thumbnail')},
                                            description: { rendered: `<img alt='` + $i18n.get('label_thumbnail') + `' src='` + item.thumbnail.full[0] + `'/>` }}"/>
                                <figure 
                                        v-if="item.thumbnail == undefined || ((item.thumbnail.medium == undefined || item.thumbnail.medium == false) && (item.thumbnail['tainacan-medium'] == undefined || item.thumbnail['tainacan-medium'] == false))"
                                        class="image">
                                    <span class="image-placeholder">{{ $i18n.get('label_empty_thumbnail') }}</span>
                                    <img
                                            :alt="$i18n.get('label_thumbnail')"
                                            :src="thumbPlaceholderPath">
                                </figure>
                                <div class="thumbnail-buttons-row">
                                    <a
                                            class="button is-rounded is-secondary"
                                            id="button-edit-thumbnail"
                                            :aria-label="$i18n.get('label_button_edit_thumb')"
                                            @click.prevent="thumbnailMediaFrame.openFrame($event)">
                                        <span 
                                                v-tooltip="{
                                                    content: $i18n.get('edit'),
                                                    autoHide: true,
                                                    placement: 'bottom'
                                                }"
                                                class="icon">
                                            <i class="tainacan-icon tainacan-icon-edit"/>
                                        </span>
                                    </a>
                                    <a
                                            v-if="item.thumbnail.thumbnail != undefined && item.thumbnail.thumbnail != false"
                                            id="button-delete-thumbnail"
                                            class="button is-rounded is-secondary"
                                            :aria-label="$i18n.get('label_button_delete_thumb')"
                                            @click="deleteThumbnail()">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('delete'),
                                                autoHide: true,
                                                placement: 'bottom'
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-delete"/>
                                    </span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Comment Status ------------------------ --> 
                        <b-field
                                :addons="false"
                                v-if="collectionAllowComments == 'open'">
                            <label class="label">{{ $i18n.get('label_comment_status') }}</label>
                            <b-switch
                                    id="tainacan-checkbox-comment-status" 
                                    size="is-small"
                                    true-value="open" 
                                    false-value="closed"
                                    v-model="form.comment_status" />
                            <help-button 
                                    :title="$i18n.getHelperTitle('items', 'comment_status')" 
                                    :message="$i18n.getHelperMessage('items', 'comment_status')"/>
                        </b-field>
                        <br>

                        <!-- Attachments ------------------------------------------ -->
                        <div class="section-label">
                            <label>{{ $i18n.get('label_attachments') }}</label>
                        </div>
                        <div class="section-box section-attachments">
                            <button
                                    type="button"
                                    class="button is-secondary"
                                    @click.prevent="attachmentMediaFrame.openFrame($event)">
                                {{ $i18n.get("label_edit_attachments") }}
                            </button>

                            <div class="uploaded-files">
                                <div
                                        class="file-item-container"
                                        v-for="(attachment, index) in attachmentsList"
                                        :key="index">
                                    <file-item
                                            :style="{ margin: 15 + 'px'}"
                                            v-if="attachmentsList.length > 0"   
                                            :modal-on-click="true"  
                                            :show-name="true"
                                            :file="attachment"/>
                                    <span class="file-item-control">
                                        <a 
                                                @click="deleteAttachment(attachment)"
                                                v-tooltip="{
                                                    content: $i18n.get('delete'),
                                                    autoHide: true,
                                                    placement: 'bottom'
                                                }"
                                                class="icon">
                                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-delete"/>
                                        </a>
                                    </span>
                                </div>
                                <p v-if="attachmentsList.length <= 0"><br>{{ $i18n.get('info_no_attachments_on_item_yet') }}</p>
                            </div>
                        </div>

                        <!-- Hook for extra Form options -->
                        <template 
                                v-if="formHooks != undefined && 
                                    formHooks['item'] != undefined &&
                                    formHooks['item']['end-left'] != undefined">  
                            <form 
                                id="form-item-end-left"
                                class="form-hook-region"
                                v-html="formHooks['item']['end-left'].join('')"/>
                        </template>

                    </div> 
                    <div class="column is-7">

                        <!-- Hook for extra Form options -->
                        <template 
                                v-if="formHooks != undefined && 
                                    formHooks['item'] != undefined &&
                                    formHooks['item']['begin-right'] != undefined">  
                            <form 
                                id="form-item-begin-right"
                                class="form-hook-region"
                                v-html="formHooks['item']['begin-right'].join('')"/>
                        </template>

                        
                        <!-- Visibility (status public or private) -------------------------------- -->
                        <div class="section-label">
                            <label>{{ $i18n.get('label_visibility') }}</label>
                            <span class="required-metadatum-asterisk">*</span>
                            <help-button
                                    :title="$i18n.get('label_visibility')"
                                    :message="$i18n.get('info_visibility_helper')"/>
                        </div>
                        <div class="section-status">
                            <div class="field has-addons">
                                <b-radio
                                        v-model="visibility"
                                        value="publish"
                                        native-value="publish">
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-public"/>
                                    </span>
                                    {{ $i18n.get('publish_visibility') }}
                                </b-radio>
                                <b-radio
                                        v-model="visibility"
                                        value="private"
                                        native-value="private">
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-private"/>
                                    </span>
                                    {{ $i18n.get('private_visibility') }}
                                </b-radio>
                            </div>
                        </div>

                        <!-- Collection -------------------------------- -->
                        <div class="section-label">
                            <label>{{ $i18n.get('collection') }}</label>
                        </div>
                        <div class="section-collection">
                            <div class="field has-addons">
                                <p>
                                    {{ collectionName }}
                                </p>
                            </div>
                        </div>

                        <!-- Metadata from Collection-------------------------------- -->
                        <span class="section-label">
                            <label>{{ $i18n.get('metadata') }}</label>
                        </span>
                        <br>
                        <a
                                class="collapse-all"
                                @click="toggleCollapseAll()">
                            {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                            <span class="icon">
                                <i 
                                        :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll }"
                                        class="tainacan-icon tainacan-icon-20px"/>
                            </span>
                        </a>
                        <tainacan-form-item
                                v-for="(metadatum, index) of metadatumList"
                                :key="index"
                                :metadatum="metadatum"
                                :is-collapsed="metadataCollapses[index]"
                                @changeCollapse="onChangeCollapse($event, index)"/>

                        <!-- Hook for extra Form options -->
                        <template 
                                v-if="formHooks != undefined && 
                                    formHooks['item'] != undefined &&
                                    formHooks['item']['end-right'] != undefined">  
                            <form 
                                id="form-item-end-right"
                                class="form-hook-region"
                                v-html="formHooks['item']['end-right'].join('')"/>
                        </template>
                    </div>
                </div>
                
            </form>
        </transition>
        <footer class="footer">
            <!-- Sequence Progress -->
            <div 
                    v-if="isOnSequenceEdit"
                    class="sequence-progress-background"/>
            <div 
                    v-if="isOnSequenceEdit && itemPosition != undefined && group != null && group.items_count != undefined"
                    :style="{ width: (itemPosition/group.items_count)*100 + '%' }"
                    class="sequence-progress"/>
            
            <!-- Last Updated Info --> 
            <div class="update-info-section">
                <p 
                        class="has-text-gray5"
                        v-if="isOnSequenceEdit">
                    {{ $i18n.get('label_sequence_editing_item') + " " + itemPosition + " " + $i18n.get('info_of') + " " + ((group != null && group.items_count != undefined) ? group.items_count : '') + "." }}
                </p>       
                <p v-if="!isUpdatingValues">
                    {{ ($i18n.get('info_updated_at') + ' ' + lastUpdated) }}
                    <span class="help is-danger">{{ formErrorMessage }}</span>
                </p>     
                <p 
                        class="update-warning"
                        v-if="isUpdatingValues">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-updating"/>
                    </span>
                    {{ $i18n.get('info_updating_metadata_values') }}
                    <span class="help is-danger">{{ formErrorMessage }}</span>
                </p> 
            </div>  
            <div 
                    class="form-submission-footer"
                    v-if="form.status == 'trash'">
                <button 
                        v-if="isOnSequenceEdit && itemPosition > 1"
                        @click="onPrevInSequence()"
                        type="button"
                        class="button sequence-button">
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-previous"/>
                    </span>
                    <span>{{ $i18n.get('previous') }}</span>
                </button>
                <button 
                        @click="onDeletePermanently()"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('label_delete_permanently') }}</button>
                <button 
                        @click="onSubmit('draft')"
                        type="button"
                        class="button is-secondary">{{ $i18n.get('label_save_as_draft') }}</button>
                <button 
                        @click="onSubmit(visibility)"
                        type="button"
                        class="button is-success">{{ $i18n.get('label_publish') }}</button>
                <button 
                        v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count > itemPosition)"
                        @click="onNextInSequence()"
                        type="button"
                        class="button sequence-button">
                    <span>{{ $i18n.get('next') }}</span>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-next"/>
                    </span>
                </button>
                <button 
                        v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                        @click="$router.push($routerHelper.getCollectionPath(form.collectionId))"
                        type="button"
                        class="button sequence-button">
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-approved"/>
                    </span>
                    <span>{{ $i18n.get('finish') }}</span>
                </button>
            </div>
            <div 
                    class="form-submission-footer"
                    v-if="form.status == 'auto-draft' || form.status == 'draft' || form.status == undefined">
                <button 
                        v-if="isOnSequenceEdit && itemPosition > 1"
                        @click="onPrevInSequence()"
                        type="button"
                        class="button sequence-button">                    
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-previous"/>
                    </span>
                    <span>{{ $i18n.get('previous') }}</span>
                </button>
                <button 
                        v-if="form.status == 'draft'"
                        @click="onSubmit('trash')"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('label_send_to_trash') }}</button>
                <button 
                        v-if="form.status == 'auto-draft'"
                        @click="onDiscard()"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('label_discard') }}</button>
                <button 
                        @click="onSubmit('draft')"
                        type="button"
                        class="button is-secondary">{{ form.status == 'draft' ? $i18n.get('label_update') : $i18n.get('label_save_as_draft') }}</button>
                <button 
                        @click="onSubmit(visibility)"
                        type="button"
                        class="button is-success">{{ $i18n.get('label_publish') }}</button>
                <button 
                        v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count > itemPosition)"
                        @click="onNextInSequence()"
                        type="button"
                        class="button sequence-button">
                    <span>{{ $i18n.get('next') }}</span>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-next"/>
                    </span>
                </button>
                <button 
                        v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                        @click="$router.push($routerHelper.getCollectionPath(form.collectionId))"
                        type="button"
                        class="button sequence-button">
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-approved"/>
                    </span>
                    <span>{{ $i18n.get('finish') }}</span>
                </button>
            </div>
            <div 
                    class="form-submission-footer"
                    v-if="form.status == 'publish' || form.status == 'private'">
                <button 
                        v-if="isOnSequenceEdit && itemPosition > 1"
                        @click="onPrevInSequence()"
                        type="button"
                        class="button sequence-button">
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-previous"/>
                    </span>
                    <span>{{ $i18n.get('previous') }}</span>
                </button>
                <button 
                        @click="onSubmit('trash')"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('label_send_to_trash') }}</button>
                <button 
                        @click="onSubmit('draft')"
                        type="button"
                        class="button is-secondary">{{ $i18n.get('label_return_to_draft') }}</button>
                <button 
                        :disabled="formErrorMessage != undefined && formErrorMessage != ''"
                        @click="onSubmit(visibility)"
                        type="button"
                        class="button is-success">{{ $i18n.get('label_update') }}</button>
                <button 
                        v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count > itemPosition)"
                        @click="onNextInSequence()"
                        type="button"
                        class="button sequence-button">
                    <span>{{ $i18n.get('next') }}</span>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-next"/>
                    </span>
                </button>
                <button 
                        v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                        @click="$router.push($routerHelper.getCollectionPath(form.collectionId))"
                        type="button"
                        class="button sequence-button">
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-approved"/>
                    </span>
                    <span>{{ $i18n.get('finish') }}</span>
                </button>
            </div>
        </footer>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { eventBus } from '../../../js/event-bus-web-components.js'
import wpMediaFrames from '../../js/wp-media-frames';
import FileItem from '../other/file-item.vue';
import DocumentItem from '../other/document-item.vue';
import CustomDialog from '../other/custom-dialog.vue';
import { formHooks } from '../../js/mixins';

export default {
    name: 'ItemEditionForm',
    mixins: [ formHooks ],
    data(){
        return {
            pageTitle: '',
            itemId: Number,
            item: {},
            collectionId: Number,
            sequenceId: Number,
            itemPosition: Number,
            isCreatingNewItem: false,
            isOnSequenceEdit: false,
            sequenceRightDirection: false,
            isLoading: false,
            metadataCollapses: [],
            collapseAll: true,
            visibility: 'publish',
            form: {
                collectionId: Number,
                status: '',
                document: '',
                document_type: '',
                comment_status: ''
            },
            thumbnail: {},
            formErrorMessage: '',
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
            thumbnailMediaFrame: undefined,
            attachmentMediaFrame: undefined,
            fileMediaFrame: undefined,
            isURLModalActive: false,
            urlLink: '',
            isTextModalActive: false,
            textLink: '',
            isUpdatingValues: false,
            collectionName: '',
            collectionAllowComments: '',
            entityName: 'item'
        }
    },
    computed: {
        metadatumList() {
            return JSON.parse(JSON.stringify(this.getMetadata()));
        },
        attachmentsList(){
            return this.getAttachments();
        },
        lastUpdated() {
            return this.getLastUpdated();
        },
        group() {
            return this.getGroup();
        },
        itemIdInSequence() {
            return this.getItemIdInSequence();
        }
    },
    components: {
        FileItem,
        DocumentItem
    },
    watch: {
        '$route.params.itemPosition'(newItemPosition, oldItemPosition) {
            if (oldItemPosition == undefined || oldItemPosition == newItemPosition)
                this.sequenceRightDirection = undefined;     
            
            this.itemPosition = Number(newItemPosition);

            // Saves current itemPosition to user prefs
            this.$userPrefs.set('sequence_' + this.sequenceId + '_position', this.itemPosition);

            // Clear form variables
            this.cleanMetadata();
            eventBus.clearAllErrors();
            this.formErrorMessage = '';
            
            this.isLoading = true;

            // Obtains current Item ID from Sequence
            this.fetchItemIdInSequence({ collectionId: this.collectionId, sequenceId: this.sequenceId, itemPosition: this.itemPosition  })
                .then(() => {
                    this.itemId = this.itemIdInSequence;
                    this.loadExistingItem();
                })
                .catch(() => {
                    this.isLoading = false;
                });
            
            // Obtains current Sequence Group Info
            this.fetchGroup({ collectionId: this.collectionId, groupId: this.sequenceId });
        }
    },
    methods: {
        ...mapActions('item', [
            'sendItem',
            'updateItem',
            'updateItemDocument',
            'fetchMetadata',
            'sendMetadatum',
            'fetchItem',
            'cleanMetadata',
            'sendAttachments',
            'updateThumbnail',
            'fetchAttachments',
            'cleanLastUpdated',
            'setLastUpdated',
            'removeAttachmentFromItem'
        ]),
        ...mapGetters('item',[
            'getMetadata',
            'getAttachments',
            'getLastUpdated'
        ]),
        ...mapActions('collection', [
            'fetchCollectionName',
            'fetchCollectionAllowComments',
            'deleteItem',
        ]),
        ...mapActions('bulkedition', [
            'fetchItemIdInSequence',
            'fetchGroup'
        ]),
        ...mapGetters('bulkedition', [
            'getItemIdInSequence',
            'getGroup'
        ]),
        onSubmit(status) {
            // Puts loading on Item edition
            this.isLoading = true;
            this.sequenceRightDirection = undefined; 

            let previousStatus = this.form.status;
            this.form.status = status;

            let data = {id: this.itemId, status: this.form.status, comment_status: this.form.comment_status};
            this.fillExtraFormData(data);
            this.updateItem(data).then(updatedItem => {

                this.item = updatedItem;

                // Fills hook forms with it's real values 
                this.updateExtraFormData(this.item);

                // Fill this.form data with current data.
                this.form.status = this.item.status;
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;
                this.form.comment_status = this.item.comment_status;

                this.isLoading = false;

                if (!this.isOnSequenceEdit) {                    
                    if (this.form.status != 'trash') {
                        if (previousStatus == 'auto-draft')
                            this.$router.push({ path: this.$routerHelper.getItemPath(this.form.collectionId, this.itemId), query: { recent: true } });
                        else
                            this.$router.push(this.$routerHelper.getItemPath(this.form.collectionId, this.itemId));
                    } else
                        this.$router.push(this.$routerHelper.getCollectionPath(this.form.collectionId));
                }
            })
            .catch((errors) => {
                for (let error of errors.errors) {
                    for (let metadatum of Object.keys(error)){
                       eventBus.errors.push({ metadatum_id: metadatum, errors: error[metadatum]});
                    }
                    
                }
                this.formErrorMessage = errors.error_message;
                this.form.status = previousStatus;
                this.item.status = previousStatus;

                this.isLoading = false;
            });
        },
        onDiscard() {
            this.$router.go(-1);
        },
        createNewItem() {
            // Puts loading on Draft Item creation
            this.isLoading = true;

            // Updates Collection BreadCrumb
            this.$root.$emit('onCollectionBreadCrumbUpdate', [
                { path: this.$routerHelper.getCollectionPath(this.collectionId), label: this.$i18n.get('items') },
                { path: '', label: this.$i18n.get('new') }
            ]);

            // Creates draft Item
            let data = {collection_id: this.form.collectionId, status: 'auto-draft', comment_status: this.form.comment_status};
            this.fillExtraFormData(data);
            this.sendItem(data).then(res => {

                this.itemId = res.id;
                this.item = res;

                // Initializes Media Frames now that itemId exists
                this.initializeMediaFrames();

                // Pre-fill status with publish to incentivate it
                this.visibility = 'publish';
                this.form.status = 'auto-draft'
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;
                this.form.comment_status = this.item.comment_status;

                this.loadMetadata();
                this.fetchAttachments(this.itemId);

            })
            .catch(error => this.$console.error(error));
        },
        loadMetadata() {
            // Obtains Item Metadatum
            this.fetchMetadata(this.itemId).then((metadata) => {
                this.metadataCollapses = [];

                if (this.isOnSequenceEdit && this.$route.query.collapses) {
                    for (let i = 0; i < metadata.length; i++) {
                        this.metadataCollapses.push(this.$route.query.collapses[i] != undefined ? this.$route.query.collapses[i] : true);
                    }
                } else if (this.isOnSequenceEdit && !this.$route.query.collapses) {
                    for (let i = 0; i < metadata.length; i++) {
                        this.metadataCollapses.push(true);
                        this.metadataCollapses[i] = false;
                    }
                } else {
                    for (let i = 0; i < metadata.length; i++) {
                        this.metadataCollapses.push(false);
                        this.metadataCollapses[i] = true;
                    }
                }
                this.isLoading = false;
            });
        },
        setFileDocument(event) {
            this.fileMediaFrame.openFrame(event);
        },
        setTextDocument() {
            this.isTextModalActive = true;
        },
        confirmTextWriting() {
            this.isLoading = true;
            this.isTextModalActive = false;
            this.form.document_type = 'text';
            this.form.document = this.textContent;
            this.updateItemDocument({ item_id: this.itemId, document: this.form.document, document_type: this.form.document_type })
            .then(item => {
                this.item.document_as_html = item.document_as_html;
                this.isLoading = false;
            })
            .catch((errors) => {
                for (let error of errors.errors) {
                    for (let metadatum of Object.keys(error)){
                       eventBus.errors.push({ metadatum_id: metadatum, errors: error[metadatum]});
                    }
                }
                this.formErrorMessage = errors.error_message;

                this.isLoading = false;
            });
        },
        cancelTextWriting() {
            this.isTextModalActive = false;
            this.textContent = '';
        },
        setURLDocument() {
            this.isURLModalActive = true;
        },
        confirmURLSelection() {
            this.isLoading = true;
            this.isURLModalActive = false;
            this.form.document_type = 'url';
            this.form.document = this.urlLink;
            this.updateItemDocument({ item_id: this.itemId, document: this.form.document, document_type: this.form.document_type })
                .then(item => {
                    this.item.document_as_html = item.document_as_html;
                    this.isLoading = false;

                    let oldThumbnail = this.item.thumbnail;
                    if (item.document_type == 'url' && oldThumbnail != item.thumbnail )
                        this.item.thumbnail = item.thumbnail;
                })
                .catch((errors) => {
                    for (let error of errors.errors) {
                        for (let metadatum of Object.keys(error)){
                        eventBus.errors.push({ metadatum_id: metadatum, errors: error[metadatum]});
                        }
                    }
                    this.formErrorMessage = errors.error_message;

                    this.isLoading = false;
                });
        },
        cancelURLSelection() {
            this.isURLModalActive = false;
            this.urlLink = '';
        },
        removeDocument() {
            this.textContent = '';
            this.urlLink = '';
            this.form.document_type = 'empty';
            this.form.document = '';
            this.updateItemDocument({ item_id: this.itemId, document: this.form.document, document_type: this.form.document_type });
        },
        deleteThumbnail() {
            this.updateThumbnail({itemId: this.itemId, thumbnailId: 0})
                .then(() => {
                    this.item.thumbnail = false;
                })
                .catch((error) => {
                    this.$console.error(error);
                });
        },
        deleteAttachment(attachment) {

            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_attachment_delete'),
                    onConfirm: () => {
                        this.removeAttachmentFromItem(attachment.id)
                            .then(() => { })
                            .catch((error) => {
                                this.$console.error(error);
                            });
                    }
                } 
            });

        },
        initializeMediaFrames() {

            this.fileMediaFrame = new wpMediaFrames.documentFileControl(
                'my-file-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_document_file_for_item'),
                        frame_button: this.$i18n.get('label_select_file'),
                    },
                    relatedPostId: this.itemId,
                    onSave: (file) => {
                        this.isLoading = true;
                        this.form.document_type = 'attachment';
                        this.form.document = file.id + '';
                        this.updateItemDocument({ item_id: this.itemId, document: this.form.document, document_type: this.form.document_type })
                        .then((item) => {
                            this.isLoading = false;
                            this.item.document_as_html = item.document_as_html;

                            let oldThumbnail = this.item.thumbnail;
                            if (item.document_type == 'attachment' && oldThumbnail != item.thumbnail )
                                this.item.thumbnail = item.thumbnail;

                        })
                        .catch((errors) => {
                            for (let error of errors.errors) {
                                for (let metadatum of Object.keys(error)){
                                eventBus.errors.push({ metadatum_id: metadatum, errors: error[metadatum]});
                                }
                            }
                            this.formErrorMessage = errors.error_message;
                            this.isLoading = false;
                        });
                    }
                }
            );

            this.thumbnailMediaFrame = new wpMediaFrames.thumbnailControl(
                'my-thumbnail-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_item_thumbnail'),
                    },
                    relatedPostId: this.itemId,
                    onSave: (media) => {
                        this.updateThumbnail({itemId: this.itemId, thumbnailId: media.id})
                        .then((res) => {
                            this.item.thumbnail = res.thumbnail;
                        })
                        .catch(error => this.$console.error(error));
                    }
                }
            );

            this.attachmentMediaFrame = new wpMediaFrames.attachmentControl(
                'my-attachment-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_files_to_attach_to_item'),
                        frame_button: this.$i18n.get('label_attach_to_item'),
                    },
                    relatedPostId: this.itemId,
                    onSave: () => {
                        // Fetch current existing attachments
                        this.fetchAttachments(this.itemId);
                    }
                }
            );

        },
        toggleCollapseAll() {
            this.collapseAll = !this.collapseAll;

            for (let i = 0; i < this.metadataCollapses.length; i++)
                this.metadataCollapses[i] = this.collapseAll;

        },
        onChangeCollapse(event, index) {
            this.metadataCollapses.splice(index, 1, event);
        },
        onDeletePermanently() {
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_item_delete') : this.$i18n.get('info_warning_item_trash'),
                    onConfirm: () => {
                        this.deleteItem({ itemId: this.itemId, isPermanently: true });
                        this.$router.push(this.$routerHelper.getCollectionPath(this.form.collectionId))
                    }
                }
            });
        },
        loadExistingItem() {
            // Initializes Media Frames now that itemId exists
            this.initializeMediaFrames();

            this.fetchItem({ 
                itemId: this.itemId, 
                contextEdit: true, 
                fetchOnly: 'title,thumbnail,status,modification_date,document_type,document,comment_status,document_as_html' 
            })
            .then(res => {
                this.item = res;

                // Checks if user has permission to edit
                if (!this.item.current_user_can_edit)
                    this.$router.push(this.$routerHelper.getCollectionPath(this.collectionId));

                // Updates Collection BreadCrumb
                if (this.isOnSequenceEdit) {
                    this.$root.$emit('onCollectionBreadCrumbUpdate', [
                        { path: this.$routerHelper.getCollectionPath(this.collectionId), label: this.$i18n.get('items') },
                        { path: '', label: this.$i18n.get('sequence') },
                        { path: '', label: this.item.title },   
                        { path: '', label: this.$i18n.get('edit') }
                    ]);
                } else {
                    this.$root.$emit('onCollectionBreadCrumbUpdate', [
                        { path: this.$routerHelper.getCollectionPath(this.collectionId), label: this.$i18n.get('items') },
                        { path: this.$routerHelper.getItemPath(this.form.collectionId, this.itemId), label: this.item.title },
                        { path: '', label: this.$i18n.get('edit') }
                    ]);
                }

                // Fills hook forms with it's real values 
                this.$nextTick()
                    .then(() => {
                        this.updateExtraFormData(this.item);
                    });
                    
                // Fill this.form data with current data.
                this.form.status = this.item.status;
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;
                this.form.comment_status = this.item.comment_status;
                
                if (this.form.document_type != undefined && this.form.document_type == 'url')
                    this.urlLink = this.form.document;
                if (this.form.document_type != undefined && this.form.document_type == 'text')
                    this.textContent = this.form.document;

                if (this.item.status == 'publish' || this.item.status == 'private')
                    this.visibility = this.item.status;

                this.loadMetadata();
                this.setLastUpdated(this.item.modification_date);
            });

            // Fetch current existing attachments
            this.fetchAttachments(this.itemId);
        },
        onNextInSequence() {
            this.sequenceRightDirection = true; 
            this.$router.push({ 
                path: this.$routerHelper.getCollectionSequenceEditPath(this.collectionId, this.sequenceId, this.itemPosition + 1), 
                query: { collapses: this.metadataCollapses }
            });
        },
        onPrevInSequence() {
            this.sequenceRightDirection = false; 
            this.$router.push({
                path: this.$routerHelper.getCollectionSequenceEditPath(this.collectionId, this.sequenceId, this.itemPosition - 1),
                query: { collapses: this.metadataCollapses }
            });
        }
    },
    created(){
        // Obtains collection ID
        this.cleanMetadata();
        eventBus.clearAllErrors();
        this.formErrorMessage = '';
        this.collectionId = this.$route.params.collectionId;
        this.form.collectionId = this.collectionId;

        // CREATING NEW SINGLE ITEM
        if (this.$route.fullPath.split("/").pop() == "new") {
            this.isCreatingNewItem = true;
            this.createNewItem();

        // EDITING EXISTING ITEM
        } else if (this.$route.fullPath.split("/").pop() == "edit") {
            this.isLoading = true;

            // Obtains current Item ID from URL
            this.itemId = this.$route.params.itemId;
            this.loadExistingItem();

        // EDITING EXISTING SEQUENCE
        } else if (this.$route.params.collectionId != undefined && this.$route.params.sequenceId != undefined){
            this.isLoading = true;

            this.sequenceId = this.$route.params.sequenceId;
            let savedItemPosition = (this.$userPrefs.get('sequence_' + this.sequenceId + '_position') != undefined ? Number(this.$userPrefs.get('sequence_' + this.sequenceId + '_position')) : 1);            
            this.itemPosition = this.$route.params.itemPosition != undefined ? Number(this.$route.params.itemPosition) : savedItemPosition;

            this.isOnSequenceEdit = true;

            // Saves current itemPosition to user prefs
            this.$userPrefs.set('sequence_' + this.sequenceId + '_position', this.itemPosition);

            // Obtains current Item ID from Sequence
            this.fetchItemIdInSequence({ collectionId: this.collectionId, sequenceId: this.sequenceId, itemPosition: this.itemPosition  })
                .then(() => {
                    this.itemId = this.itemIdInSequence;
                    this.loadExistingItem();
                })
                .catch(() => {
                    this.isLoading = false;
                });
            
            // Obtains current Sequence Group Info
            this.fetchGroup({ collectionId: this.collectionId, groupId: this.sequenceId });
        }

        // Obtains collection name
        if (!this.isRepositoryLevel) {
            this.fetchCollectionName(this.collectionId).then((collectionName) => {
                this.collectionName = collectionName;
            });
        }
        
        // Obtains if collection allow items comments
        this.fetchCollectionAllowComments(this.collectionId).then((collectionAllowComments) => {
            this.collectionAllowComments = collectionAllowComments;
        });

        // Sets feedback variables
        eventBus.$on('isUpdatingValue', (status) => {
            this.isUpdatingValues = status;
        });
        eventBus.$on('hasErrorsOnForm', (hasErrors) => {
            if (hasErrors)
                this.formErrorMessage = this.$i18n.get('info_errors_in_form');
            else 
                this.formErrorMessage = '';
        });
        this.cleanLastUpdated();
    },
    beforeDestroy () {
        eventBus.$off('isUpdatingValue');
        eventBus.$off('hasErrorsOnForm');
    },
    beforeRouteLeave ( to, from, next ) {
        if (this.item.status == 'auto-draft') {
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_item_not_saved'),
                    onConfirm: () => {
                        next();
                    },
                }
            });  
        } else {
            next()
        }  
    }
}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .page-container {
        padding: 25px 0px;

        &>.tainacan-form {
            margin-bottom: 110px;

            .field:not(:last-child) {
                margin-bottom: 0.5rem;
            }
        }

        .tainacan-page-title {
            padding: 0 $page-side-padding;
            margin-bottom: 35px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;

            h1, h2 {
                font-size: 20px;
                font-weight: 500;
                color: $gray5;
                display: inline-block;
                width: 80%;
                flex-shrink: 1;
                flex-grow: 1;
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
                max-width: 80%;
            }
            .status-tag {
                color: white;
                background: $turquoise5;
                padding: 0.15rem 0.5rem;
                font-size: 0.75rem;
                margin: 0 1rem 0 0;
                font-weight: 600;
                position: relative;
                top: -2px;
            }
            a.back-link{
                font-weight: 500;
                float: right;
                margin-top: 5px;
            }
            hr{
                margin: 3px 0px 4px 0px; 
                height: 1px;
                background-color: $secondary;
                width: 100%;
            }
        }

        .column.is-5 {
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;

            @media screen and (max-width: 769px) {
                max-width: 100%;
            }
        }
        .column.is-7 {
            padding-left: 0;
            padding-right: $page-side-padding;

            .field {
                padding: 10px 0px 14px 60px;
            }

            @media screen and (max-width: 769px) {
                padding-left: $page-side-padding;
                max-width: 100%;
            }

        }

    }

    .section-label {
        position: relative;
        label {
            font-size: 16px !important;
            font-weight: 500 !important;
            color: $gray5 !important;
            line-height: 1.2em;
        }
    }

    .collapse-all {
        font-size: 12px;
        .icon { 
            vertical-align: bottom; 
        }
    }

    .section-box {
       
        background-color: white;
        padding: 26px;
        margin-top: 16px;
        margin-bottom: 38px;

        ul {
            display: flex;
            justify-content: space-evenly;
            li {
                text-align: center;
                button {
                    border-radius: 50px;
                    height: 72px;
                    width: 72px;
                    border: none;
                    background-color: $gray2;
                    color: $secondary;
                    margin-bottom: 6px;
                    &:hover {
                        background-color: $turquoise2;
                        cursor: pointer;
                    }
                }
                p { color: $secondary; }
            }
        }
    }
    .section-status{
        padding: 16px 0;     
        .field .b-radio {
            margin-right: 24px;
            .icon  {
                font-size: 18px !important; 
                color: $gray3;
            }
        }
    }
    .section-attachments {
        border: 1px solid $gray2;
        height: 250px;
        max-width: 100%;
        resize: vertical;
        overflow: auto;

        p { margin: 4px 15px }
    }

    .uploaded-files {
        display: flex;
        flex-flow: wrap;
        margin-left: -15px;
        margin-right: -15px;

        .file-item-container {
            position: relative;

            &:hover .file-item-control {
                display: block;
                visibility: visible;
                opacity: 1;
            }

            .file-item-control {
                position: absolute;
                background-color: $gray1;
                width: 112px;
                margin: 15px;
                bottom: 0px;
                padding: 2px 8px 4px 8px;
                text-align: right;
                display: none;
                visibility: hidden;
                opacity: 0;
                transition: opacity ease 0.2s, visibility ease 0.2s, display ease 0.2s;

                .icon {
                    cursor: pointer;
                }
            }
        }
    }

    .document-field {  

        .document-buttons-row {
            text-align: right;
            top: -21px;
            position: relative;
        }
    }

    #button-edit-thumbnail, 
    #button-edit-document,
    #button-delete-thumbnail, 
    #button-delete-document {

        border-radius: 100px !important;
        max-height: 30px !important;
        max-width: 30px !important;
        min-height: 30px !important;
        min-width: 30px !important;
        padding: 0 !important;
        z-index: 99;
        margin-left: 12px !important;
        
        .icon {
            display: inherit;
            padding: 0;
            margin: 0;
            margin-top: -2px;
            font-size: 18px;
        }
    }

    .thumbnail-field {

        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            height: 178px;
            width: 178px;
        }
        .image-placeholder {
            position: absolute;
            margin-left: 45px;
            margin-right: 45px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: $gray4;
            top: 70px;
            max-width: 90px;
        }
    
        .thumbnail-buttons-row {
            position: relative;
            left: 90px;
            bottom: 22px;
        }
    }

    .footer {
        padding: 18px $page-side-padding;
        position: absolute;
        bottom: 0;
        z-index: 999999;
        background-color: $gray1;
        width: 100%;
        height: 65px;
        display: flex;
        justify-content: flex-end;
        align-items: center;

        .form-submission-footer {    
            .button {
                margin-left: 16px;
                margin-right: 6px;
            }
        }

        @keyframes blink {
            from { color: $blue5; }
            to { color: $gray4; }
        }

        .update-warning {
            color: $blue5;
            animation-name: blink;
            animation-duration: 0.5s;
            animation-delay: 0.5s;
            align-items: center;
            display: flex;
        }

        .update-info-section {
            color: $gray4;
            margin-right: auto;
        }

        .help {
            display: inline-block;
            font-size: 1.0em;
            margin-top: 0;
            margin-left: 24px;
        }

        .sequence-progress {
            height: 5px;
            background: $turquoise5;
            width: 0%;
            position: absolute;
            top: 0;
            left: 0;
            transition: width 0.2s;
        }
        .sequence-progress-background {
            height: 5px;
            background: $gray3;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        .sequence-button {
            background-color: transparent;
            color: $turquoise5;
            border: none;
        }
    }

</style>
