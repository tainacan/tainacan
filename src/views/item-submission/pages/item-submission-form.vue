<template>
    <div v-if="collectionId">
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
                <span style="font-weight: 600;">{{ collection && collection.name ? collection.name : '' }}</span>
            </h1>
            <h1 v-else>
                <span
                        v-if="(item != null && item != undefined && item.status != undefined && !isLoading)"
                        class="status-tag">{{ $i18n.get('status_' + item.status) }}</span>
                {{ $i18n.get('title_edit_item') + ' ' }}
                <span style="font-weight: 600;">{{ (item != null && item != undefined) ? item.title : '' }}</span>
            </h1>
            <a
                    v-if="!$route.query.iframemode"
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
                    v-show="!isLoading && ((isCreatingNewItem && collection && collection.current_user_can_edit_items) || (!isCreatingNewItem && item && item.current_user_can_edit))"
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
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-upload"/>
                                        </span>
                                    </button>
                                    <p>{{ $i18n.get('label_file') }}</p>
                                </li>
                                <li>
                                    <button
                                            type="button"
                                            @click.prevent="setTextDocument()">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text"/>
                                        </span>
                                    </button>
                                    <p>{{ $i18n.get('label_text') }}</p>
                                </li>
                                <li>
                                    <button
                                            type="button"
                                            @click.prevent="setURLDocument()">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url"/>
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
                                scroll="keep"
                                trap-focus
                                aria-modal
                                aria-role="dialog">
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
                                scroll="keep"
                                trap-focus
                                role="dialog"
                                tabindex="-1"
                                aria-modal
                                aria-role="dialog">
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
                        <div 
                                v-if="!isLoading"
                                class="section-box section-thumbnail">
                            <div class="thumbnail-field">
                                <file-item
                                        v-if="item.thumbnail != undefined && ((item.thumbnail['tainacan-medium'] != undefined && item.thumbnail['tainacan-medium'] != false) || (item.thumbnail.medium != undefined && item.thumbnail.medium != false))"
                                        :show-name="false"
                                        :modal-on-click="false"
                                        :size="178"
                                        :file="{
                                            media_type: 'image',
                                            thumbnails: { 'tainacan-medium': [ item.thumbnail['tainacan-medium'] ? item.thumbnail['tainacan-medium'][0] : item.thumbnail.medium[0] ] },
                                            title: $i18n.get('label_thumbnail'),
                                            description: `<img alt='` + $i18n.get('label_thumbnail') + `' src='` + item.thumbnail.full[0] + `'/>` 
                                        }"/>
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
                                            v-if="item.thumbnail && item.thumbnail.thumbnail != undefined && item.thumbnail.thumbnail != false"
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

                        <div class="columns">

                            <!-- Collection -------------------------------- -->
                            <div class="column is-narrow">
                                <div class="section-label">
                                    <label>{{ $i18n.get('collection') }}</label>
                                </div>
                                <div class="section-status">
                                    <div class="field has-addons">
                                        <span>
                                            <span class="icon">
                                                <i class="tainacan-icon tainacan-icon-collection"/>
                                            </span>
                                            {{ collection && collection.name ? collection.name : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Visibility (status public or private) -------------------------------- -->
                            <div
                                    style="flex-wrap: wrap"
                                    class="column is-narrow">
                                <div class="section-label">
                                    <label>{{ $i18n.get('label_visibility') }}</label>
                                    <span class="required-metadatum-asterisk">*</span>
                                    <help-button
                                            :title="$i18n.get('label_visibility')"
                                            :message="$i18n.get('info_visibility_helper')"/>
                                </div>
                                <div class="section-status">
                                    <div
                                            style="display: flex; flex-direction: column; font-size: 1rem;"
                                            class="field has-addons">
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
                            </div>

                            <!-- Comment Status ------------------------ -->
                            <div
                                    class="column is-narrow"
                                    v-if="collection && collection.allow_comments && collection.allow_comments == 'open'">
                                <div class="section-label">
                                    <label>{{ $i18n.get('label_comments') }}</label>
                                    <help-button
                                                :title="$i18n.getHelperTitle('items', 'comment_status')"
                                                :message="$i18n.getHelperMessage('items', 'comment_status')"/>
                                </div>
                                <div class="section-status">
                                    <div class="field has-addons">
                                        <b-switch
                                                id="tainacan-checkbox-comment-status"
                                                size="is-small"
                                                true-value="open"
                                                false-value="closed"
                                                v-model="form.comment_status">
                                            {{ $i18n.get('label_allow_comments') }}
                                        </b-switch>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <b-tabs v-model="activeTab">

                            <!-- Metadata from Collection-------------------------------- -->
                            <b-tab-item>
                                <template slot="header">
                                    <span class="icon has-text-gray4">
                                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-metadata"/>
                                    </span>
                                    <span>{{ $i18n.get('metadata') }}</span>
                                </template>

                                <a
                                        class="collapse-all"
                                        @click="toggleCollapseAll()">
                                    {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                                    <span class="icon">
                                        <i
                                                :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll }"
                                                class="tainacan-icon tainacan-icon-1-25em"/>
                                    </span>
                                </a>
                                <tainacan-form-item
                                        v-for="(itemMetadatum, index) of metadatumList"
                                        :key="index"
                                        :item-metadatum="itemMetadatum"
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
                            </b-tab-item>

                            <!-- Attachments ------------------------------------------ -->
                            <b-tab-item>
                                <template slot="header">
                                    <span class="icon has-text-gray4">
                                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-attachments"/>
                                    </span>
                                    <span>
                                        {{ $i18n.get('label_attachments') }}
                                        <span
                                                v-if="totalAttachments != null && totalAttachments != undefined"
                                                class="has-text-gray">
                                            ({{ totalAttachments }})
                                        </span>
                                    </span>
                                </template>

                                <div v-if="item != undefined && item.id != undefined">
                                    <br>
                                    <button
                                            style="margin-left: calc(var(--tainacan-one-column) + 12px)"
                                            type="button"
                                            class="button is-secondary"
                                            @click.prevent="attachmentMediaFrame.openFrame($event)"
                                            :disabled="isLoadingAttachments">
                                        {{ $i18n.get("label_edit_attachments") }}
                                    </button>
                                    <attachments-list
                                            v-if="item != undefined && item.id != undefined"
                                            :item="item"
                                            :is-editable="true"
                                            :is-loading.sync="isLoadingAttachments"
                                            @isLoadingAttachments="(isLoading) => isLoadingAttachments = isLoading"
                                            @onDeleteAttachment="deleteAttachment($event)"/>
                                </div>
                            </b-tab-item>

                        </b-tabs>
                    </div>
                </div>

            </form>

            <!-- In case user enters this page whithout having permission -->
            <template v-if="!isLoading && ((isCreatingNewItem && collection && collection.current_user_can_edit_items == false) || (!isCreatingNewItem && item && item.current_user_can_edit != undefined && collection && collection.current_user_can_edit_items == false))">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-items"/>
                            </span>
                        </p>
                        <p>{{ $i18n.get('info_can_not_edit_item') }}</p>
                    </div>
                </section>
            </template>

        </transition>
        <footer class="footer">

            <!-- Last Updated Info -->
            <div class="update-info-section">
                <p
                        class="has-text-gray5"
                        v-if="isOnSequenceEdit">
                    {{ $i18n.get('label_sequence_editing_item') + " " + itemPosition + " " + $i18n.get('info_of') + " " + ((group != null && group.items_count != undefined) ? group.items_count : '') + "." }}
                </p>
                <p class="footer-message">
                    <span 
                            v-if="isUpdatingValues"
                            class="update-warning">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating"/>
                        </span>
                        <span>{{ $i18n.get('info_updating_metadata_values') }}</span>
                    </span>
                    <span v-else>{{ ($i18n.get('info_updated_at') + ' ' + lastUpdated) }}</span>

                    <span class="help is-danger">
                        {{ formErrorMessage }}
                        <item-metadatum-errors-tooltip 
                                v-if="formErrors.length"
                                :form-errors="formErrors" />
                    </span>
                </p>
            </div>
            <div
                    class="form-submission-footer"
                    v-if="form.status == 'trash'">
                <button 
                        v-if="item && item.current_user_can_delete"
                        @click="onDeletePermanently()"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('label_delete_permanently') }}</button>
                <button
                        @click="onSubmit('draft')"
                        type="button"
                        class="button is-secondary">{{ $i18n.get('label_save_as_draft') }}</button>
                <button 
                        v-if="collection && collection.current_user_can_publish_items"
                        @click="onSubmit(visibility)"
                        type="button"
                        class="button is-success">{{ $i18n.get('label_publish') }}</button>
            </div>
            <div
                    class="form-submission-footer"
                    v-if="form.status == 'auto-draft' || form.status == 'draft' || form.status == undefined">
                <button 
                        v-if="form.status == 'draft' && !isOnSequenceEdit && item && item.current_user_can_delete"
                        @click="onSubmit('trash')"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('label_send_to_trash') }}</button>
                <button
                        v-if="form.status == 'auto-draft'"
                        @click="onDiscard()"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('label_discard') }}</button>
                <button
                        v-if="!isOnSequenceEdit || (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                        @click="onSubmit('draft')"
                        type="button"
                        class="button is-secondary">{{ form.status == 'draft' ? $i18n.get('label_update') : $i18n.get('label_save_as_draft') }}</button>
                <button
                        v-else
                        @click="onSubmit('draft'); onNextInSequence();"
                        type="button"
                        class="button is-secondary">
                    <span>{{ $i18n.get('label_update_draft') }}</span>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                    </span>
                </button>
                <template v-if="collection && collection.current_user_can_publish_items">
                    <button 
                            v-if="!isOnSequenceEdit || (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                            @click="onSubmit(visibility)"
                            type="button"
                            class="button is-success">{{ $i18n.get('label_publish') }}</button>
                    <button 
                            v-else
                            @click="onSubmit(visibility, 'next')"
                            type="button"
                            class="button is-success">
                        <span>{{ $i18n.get('label_publish') }}</span>
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                        </span>
                    </button>
                </template>
            </div>
            <div
                    class="form-submission-footer"
                    v-if="form.status == 'publish' || form.status == 'private'">
                <button 
                        v-if="!isOnSequenceEdit && item && item.current_user_can_delete"
                        @click="onSubmit('trash')"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('label_send_to_trash') }}</button>
                <button
                        v-if="!isOnSequenceEdit || (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                        @click="onSubmit('draft')"
                        type="button"
                        class="button is-secondary">{{ isOnSequenceEdit ? $i18n.get('label_save_as_draft') : $i18n.get('label_return_to_draft') }}</button>
                <button
                        v-else
                        @click="onSubmit('draft', 'next')"
                        type="button"
                        class="button is-secondary">
                    <span>{{ $i18n.get('label_save_as_draft') }}</span>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                    </span>
                </button>
                <template v-if="collection && collection.current_user_can_publish_items">
                    <button 
                            v-if="!isOnSequenceEdit || (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                            :disabled="formErrorMessage != undefined && formErrorMessage != ''"
                            @click="onSubmit(visibility)"
                            type="button"
                            class="button is-success">{{ $i18n.get('label_update') }}</button>
                    <button 
                            v-else
                            :disabled="formErrorMessage != undefined && formErrorMessage != ''"
                            @click="onSubmit(visibility, 'next')"
                            type="button"
                            class="button is-success">
                        <span>{{ $i18n.get('label_update') }}</span>
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                        </span>
                    </button>
                </template>
            </div>
        </footer>
    </div>
</template>

<script>
export default {
    name: 'ItemSubmissionForm',
    props: {
        collectionId: String
    },
    mounted() {
        console.log(this.collectionId)
    }
}
</script>