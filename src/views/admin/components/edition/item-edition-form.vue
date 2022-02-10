<template>
    <div :class="isCreatingNewItem ? 'item-creation-container' : 'item-edition-container'"><!-- Do NOT remove this classes, they may be used by third party plugins -->
        <b-loading
                :is-full-page="false"
                :active.sync="isLoading"
                :can-cancel="false"/>

        <div 
                v-if="!$adminOptions.hideItemEditionPageTitle || ($adminOptions.hideItemEditionPageTitle && isEditingItemMetadataInsideIframe)"
                class="tainacan-page-title">
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
                            <div 
                                    v-if="!$adminOptions.hideItemEditionCollectionName"
                                    class="column is-narrow">
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
                                    v-if="!$adminOptions.hideItemEditionStatusOptions"
                                    style="flex-wrap: wrap"
                                    class="column is-narrow">
                                <div class="section-label">
                                    <label>{{ $i18n.get('label_status') }}</label>
                                    <span class="required-metadatum-asterisk">*</span>
                                    <help-button
                                            :title="$i18n.getHelperTitle('items', 'status')"
                                            :message="$i18n.getHelperMessage('items', 'status')"/>
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
                                            {{ $i18n.get('status_public') }}
                                        </b-radio>
                                        <b-radio
                                                v-model="visibility"
                                                value="private"
                                                native-value="private">
                                            <span class="icon">
                                                <i class="tainacan-icon tainacan-icon-private"/>
                                            </span>
                                            {{ $i18n.get('status_private') }}
                                        </b-radio>
                                    </div>
                                </div>
                            </div>

                            <!-- Comment Status ------------------------ -->
                            <div
                                    class="column is-narrow"
                                    v-if="collection && collection.allow_comments && collection.allow_comments == 'open' && !$adminOptions.hideItemEditionCommentsToggle">
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

                        <div class="b-tabs">
                            <nav 
                                    role="list"
                                    ref="tainacanTabsSwiper"
                                    v-swiper:mySwiper="swiperOptions"
                                    class="tabs">
                                <ul class="swiper-wrapper">
                                    <li 
                                            v-for="(tab, tabIndex) of tabs"
                                            :key="tabIndex"
                                            :class="{ 'is-active': activeTab === tab.slug }"
                                            @click="activeTab = tab.slug"
                                            class="swiper-slide"
                                            :id="tab.slug + '-tab-label'">
                                        <a>
                                            <span class="icon has-text-gray4">
                                                <i :class="'tainacan-icon tainacan-icon-18px tainacan-icon-' + tab.icon" />
                                            </span>
                                            <span>{{ tab.name }}</span>
                                            <span 
                                                    v-if="tab.total"
                                                    class="has-text-gray">
                                                &nbsp;({{ tab.total }})
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                                <button 
                                        class="swiper-button-prev" 
                                        id="tainacan-tabs-prev" 
                                        slot="button-prev">
                                    <svg
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24">
                                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                                        <path
                                                d="M0 0h24v24H0z"
                                                fill="none"/>
                                    </svg>
                                </button>
                                <button 
                                        class="swiper-button-next" 
                                        id="tainacan-tabs-next" 
                                        slot="button-next">
                                    <svg
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24">
                                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                                        <path
                                                d="M0 0h24v24H0z"
                                                fill="none"/>
                                    </svg>
                                </button>
                            </nav>
                        
                            <section class="tab-content">
                                
                                <!-- Metadata from Collection-------------------------------- -->
                                <div    
                                        v-if="activeTab === 'metadata'"
                                        class="tab-item"
                                        role="tabpanel"
                                        aria-labelledby="metadata-tab-label"
                                        tabindex="0"> 

                                    <div 
                                            class="sub-header"
                                            :class="{ 'is-metadata-navigation-active': isMetadataNavigation }">

                                        <!-- Metadata navigation Progress -->
                                        <div
                                                v-if="isMetadataNavigation && metadatumList && metadatumList.length > 3"
                                                class="sequence-progress-background" />
                                        <div
                                                v-if="isMetadataNavigation && focusedMetadatum !== false && metadatumList && metadatumList.length > 3"
                                                :style="{ width: ((focusedMetadatum + 1)/metadatumList.length)*100 + '%' }"
                                                class="sequence-progress" />

                                        <a
                                                class="collapse-all"
                                                @click="toggleCollapseAll()">
                                            <span class="icon">
                                                <i
                                                        :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll }"
                                                        class="tainacan-icon tainacan-icon-1-25em"/>
                                            </span>
                                            {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                                        </a>

                                        <span 
                                                v-if="isUpdatingValues && isMetadataNavigation"
                                                class="update-warning">
                                            <span class="icon">
                                                <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-updating"/>
                                            </span>
                                        </span>

                                        <b-field 
                                                v-if="metadatumList && metadatumList.length > 3"
                                                class="header-item metadata-navigation">
                                            <b-button
                                                    v-if="!isMetadataNavigation" 
                                                    @click="isMetadataNavigation = true; setMetadatumFocus({ index: 0, scrollIntoView: true });"
                                                    class="collapse-all has-text-secondary"
                                                    size="is-small">
                                                <span>{{ $i18n.get('label_focus_mode') }}</span>
                                                <span
                                                        class="icon">
                                                    <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-play" />
                                                </span>
                                            </b-button>
                                            <b-button 
                                                    v-if="isMetadataNavigation"
                                                    :disabled="focusedMetadatum === 0"
                                                    @click="setMetadatumFocus({ index: focusedMetadatum - 1, scrollIntoView: true })" 
                                                    outlined>
                                                <span
                                                        class="icon">
                                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-showmore tainacan-icon-rotate-180" />
                                                </span>
                                            </b-button>
                                            <b-button 
                                                    v-if="isMetadataNavigation"
                                                    :disabled="focusedMetadatum === metadatumList.length - 1"
                                                    @click="setMetadatumFocus({ index: focusedMetadatum + 1, scrollIntoView: true })"
                                                    outlined>
                                                <span
                                                        class="icon">
                                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-showmore" />
                                                </span>
                                            </b-button>
                                            <b-button
                                                    v-if="isMetadataNavigation" 
                                                    @click="setMetadatumFocus({ index: 0, scrollIntoView: true }); isMetadataNavigation = false;"
                                                    outlined>
                                                <span
                                                        class="icon has-success-color">
                                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-finish" />
                                                </span>
                                            </b-button>
                                        </b-field>

                                        <b-field 
                                                v-if="metadatumList && metadatumList.length > 5"
                                                class="header-item metadata-name-search">
                                            <b-input
                                                    v-if="!isMobileScreen || openMetadataNameFilter"
                                                    :placeholder="$i18n.get('instruction_type_search_metadata_filter')"
                                                    v-model="metadataNameFilterString"
                                                    icon="magnify"
                                                    size="is-small"
                                                    icon-right="close-circle"
                                                    icon-right-clickable
                                                    @icon-right-click="openMetadataNameFilterClose" />
                                            <span
                                                    @click="openMetadataNameFilter = true"
                                                    v-else 
                                                    class="icon is-small metadata-name-search-icon">
                                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-search" />
                                            </span>
                                        </b-field>
                                    </div>

                                    <tainacan-form-item
                                            v-show="(metadataNameFilterString == '' || filterByMetadatumName(itemMetadatum))"
                                            v-for="(itemMetadatum, index) of metadatumList"
                                            :key="index"
                                            :class="{ 'is-metadata-navigation-active': isMetadataNavigation }"
                                            :ref="'tainacan-form-item--' + index"
                                            :item-metadatum="itemMetadatum"
                                            :metadata-name-filter-string="metadataNameFilterString"
                                            :is-collapsed="metadataCollapses[index]"
                                            :is-mobile-screen="isMobileScreen"
                                            :is-last-metadatum="index > 2 && (index == metadatumList.length - 1)"
                                            :is-focused="focusedMetadatum === index"
                                            :is-metadata-navigation="isMetadataNavigation"
                                            @changeCollapse="onChangeCollapse($event, index)"
                                            @touchstart.native="isMetadataNavigation ? setMetadatumFocus({ index: index, scrollIntoView: false }): ''"
                                            @mousedown.native="isMetadataNavigation ? setMetadatumFocus({ index: index, scrollIntoView: false }) : ''"
                                            @mobileSpecialFocus="setMetadatumFocus({ index: index, scrollIntoView: true })" />

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

                                <!-- Related items -->
                                <div    
                                        v-if="totalRelatedItems && activeTab === 'related'"
                                        class="tab-item"
                                        role="tabpanel"
                                        aria-labelledby="related-tab-label"
                                        tabindex="0"> 

                                    <div class="attachments-list-heading">
                                        <p>
                                            {{ $i18n.get("info_related_items") }}
                                        </p>
                                    </div>

                                    <related-items-list
                                            :item-id="itemId"
                                            :collection-id="collectionId"
                                            :related-items="item.related_items"
                                            :is-editable="!$adminOptions.itemEditionMode"
                                            :is-loading.sync="isLoading" />
                                    
                                </div>

                                <!-- Attachments ------------------------------------------ -->
                                <div    
                                        v-if="activeTab === 'attachments'"
                                        class="tab-item"
                                        role="tabpanel"
                                        aria-labelledby="attachments-tab-label"
                                        tabindex="0">

                                    <div v-if="item != undefined && item.id != undefined">
                                        <div class="attachments-list-heading">
                                            <button
                                                    style="margin-left: calc(var(--tainacan-one-column) + 12px)"
                                                    type="button"
                                                    class="button is-secondary"
                                                    @click.prevent="attachmentMediaFrame.openFrame($event)"
                                                    :disabled="isLoadingAttachments">
                                                {{ $i18n.get("label_edit_attachments") }}
                                            </button>
                                            <p>
                                                {{ $i18n.get("info_edit_attachments") }}
                                            </p>
                                        </div>

                                        <attachments-list
                                                v-if="item != undefined && item.id != undefined"
                                                :item="item"
                                                :is-editable="true"
                                                :is-loading.sync="isLoadingAttachments"
                                                @isLoadingAttachments="(isLoading) => isLoadingAttachments = isLoading"
                                                @onDeleteAttachment="deleteAttachment($event)"/>
                                    </div>
                                </div>

                            </section>
                        </div>
                    </div>

                    <div class="column is-5">
                
                        <div class="sticky-container">

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
                                                            placement: 'bottom',
                                                            popperClass: ['tainacan-tooltip', 'tooltip']
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
                                                            placement: 'bottom',
                                                            popperClass: ['tainacan-tooltip', 'tooltip']
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
                                                            placement: 'bottom',
                                                            popperClass: ['tainacan-tooltip', 'tooltip']
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
                                                            placement: 'bottom',
                                                            popperClass: ['tainacan-tooltip', 'tooltip']
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
                                                            placement: 'bottom',
                                                            popperClass: ['tainacan-tooltip', 'tooltip']
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
                                                            placement: 'bottom',
                                                            popperClass: ['tainacan-tooltip', 'tooltip']
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

                            <!-- Thumbnail -------------------------------- -->
                            <div 
                                    v-if="!$adminOptions.hideItemEditionThumbnail"
                                    class="section-label">
                                <label>{{ $i18n.get('label_thumbnail') }}</label>
                                <help-button
                                        :title="$i18n.getHelperTitle('items', '_thumbnail_id')"
                                        :message="$i18n.getHelperMessage('items', '_thumbnail_id')"/>

                            </div>
                            <div 
                                    v-if="!isLoading && !$adminOptions.hideItemEditionThumbnail"
                                    class="section-box section-thumbnail">
                                <div class="thumbnail-field">
                                    <file-item
                                            v-if="item.thumbnail != undefined && ((item.thumbnail['tainacan-medium'] != undefined && item.thumbnail['tainacan-medium'] != false) || (item.thumbnail.medium != undefined && item.thumbnail.medium != false))"
                                            :show-name="false"
                                            :modal-on-click="false"
                                            :size="148"
                                            :file="{
                                                media_type: 'image',
                                                thumbnails: { 'tainacan-medium': [ $thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium', item.document_mimetype) ] },
                                                title: $i18n.get('label_thumbnail'),
                                                description: `<img alt='` + $i18n.get('label_thumbnail') + `' src='` + $thumbHelper.getSrc(item['thumbnail'], 'full', item.document_mimetype) + `'/>` 
                                            }"/>
                                    <figure
                                            v-if="item.thumbnail == undefined || ((item.thumbnail.medium == undefined || item.thumbnail.medium == false) && (item.thumbnail['tainacan-medium'] == undefined || item.thumbnail['tainacan-medium'] == false))"
                                            class="image">
                                        <span 
                                                class="image-placeholder"
                                                v-if="item.document_type == 'empty' && item.document_mimetype == 'empty'">
                                            {{ $i18n.get('label_empty_thumbnail') }}
                                        </span>
                                        <img
                                                :alt="$i18n.get('label_thumbnail')"
                                                :src="$thumbHelper.getEmptyThumbnailPlaceholder(item.document_mimetype)">
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
                                                        placement: 'bottom',
                                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-edit"/>
                                            </span>
                                        </a>
                                        <a
                                                v-if="item.thumbnail && item.thumbnail.thumbnail != undefined && item.thumbnail.thumbnail != false"
                                                id="button-alt-text-thumbnail"
                                                class="button is-rounded is-secondary"
                                                :aria-label="$i18n.get('label_button_delete_thumb')"
                                                @click="openThumbnailModalAltText">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('label_thumbnail_alt'),
                                                        autoHide: true,
                                                        placement: 'bottom',
                                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-text"/>
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
                                                        placement: 'bottom',
                                                        popperClass: ['tainacan-tooltip', 'tooltip']
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

        <footer 
                class="footer"
                :class="{ 'has-some-metadatum-focused': isMetadataNavigation && activeTab === 'metadata' && focusedMetadatum !== false }">
                
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
                    v-if="isEditingItemMetadataInsideIframe">
                <button
                        @click="onSubmit()"
                        type="button"
                        class="button is-secondary">
                    {{ $i18n.get('label_back_to_related_item') }}
                </button>
            </div>
            <div
                    class="form-submission-footer"
                    v-if="form.status == 'trash' && !isEditingItemMetadataInsideIframe">
                <button 
                        v-if="item && item.current_user_can_delete"
                        @click="onDeletePermanently()"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('label_delete_permanently') }}</button>
                <button
                        @click="onSubmit('draft')"
                        type="button"
                        class="button is-secondary">
                    <span v-if="!isMobileScreen">{{ $i18n.get('label_save_as_draft') }}</span>
                    <span v-else>{{ $i18n.get('status_draft') }}</span>
                </button>
                <button 
                        v-if="collection && collection.current_user_can_publish_items"
                        @click="onSubmit(visibility)"
                        type="button"
                        class="button is-success">{{ $i18n.get('label_verb_publish') }}</button>
            </div>
            <div
                    class="form-submission-footer"
                    v-if="(form.status == 'auto-draft' || form.status == 'draft' || form.status == undefined) && !isEditingItemMetadataInsideIframe">
                <button
                        v-if="isOnSequenceEdit && itemPosition > 1"
                        @click="onPrevInSequence()"
                        type="button"
                        class="button sequence-button">
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-previous"/>
                    </span>
                    <span>{{ $i18n.get('previous') }}</span>
                </button>
                <button 
                        v-if="form.status == 'draft' && !isOnSequenceEdit && item && item.current_user_can_delete"
                        @click="onSubmit('trash')"
                        type="button"
                        class="button is-outlined">
                    <span v-if="!isMobileScreen">{{ $i18n.get('label_send_to_trash') }}</span>
                    <span v-else>{{ $i18n.get('status_trash') }}</span>
                </button>
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
                    <span v-if="!isMobileScreen">{{ $i18n.get('label_update_draft') }}</span>
                    <span v-else>{{ $i18n.get('status_draft') }}</span>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                    </span>
                </button>
                <template v-if="collection && collection.current_user_can_publish_items">
                    <button 
                            v-if="!isOnSequenceEdit || (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                            @click="onSubmit(visibility)"
                            type="button"
                            class="button is-success">{{ $i18n.get('label_verb_publish') }}</button>
                    <button 
                            v-else
                            @click="onSubmit(visibility, 'next')"
                            type="button"
                            class="button is-success">
                        <span>{{ $i18n.get('label_verb_publish') }}</span>
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                        </span>
                    </button>
                </template>
                <template v-else>
                    <button 
                            v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count < itemPosition)"
                            @click="onNextInSequence()"
                            type="button"
                            class="button is-success">
                        <span>{{ $i18n.get('label_next') }}</span>
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                        </span>
                    </button>
                </template>
                <button 
                        v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                        @click="$router.push($routerHelper.getCollectionPath(form.collectionId))"
                        type="button"
                        class="button sequence-button">
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-approved"/>
                    </span>
                    <span>{{ $i18n.get('finish') }}</span>
                </button>
            </div>
            <div
                    class="form-submission-footer"
                    v-if="(form.status == 'publish' || form.status == 'private') && !isEditingItemMetadataInsideIframe">
                <button
                        v-if="isOnSequenceEdit && itemPosition > 1"
                        @click="onPrevInSequence()"
                        type="button"
                        class="button sequence-button">
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-previous"/>
                    </span>
                    <span>{{ $i18n.get('previous') }}</span>
                </button>
                <button 
                        v-if="!isOnSequenceEdit && item && item.current_user_can_delete"
                        @click="onSubmit('trash')"
                        type="button"
                        class="button is-outlined">
                    <span v-if="!isMobileScreen">{{ $i18n.get('label_send_to_trash') }}</span>
                    <span v-else>{{ $i18n.get('status_trash') }}</span>
                </button>
                <button
                        v-if="!isOnSequenceEdit || (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                        @click="onSubmit('draft')"
                        type="button"
                        class="button is-secondary">
                    <span v-if="!isMobileScreen">{{ isOnSequenceEdit ? $i18n.get('label_save_as_draft') : $i18n.get('label_return_to_draft') }}</span>
                    <span v-else>{{ $i18n.get('status_draft') }}</span>
                </button>
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
                <template v-else>
                    <button 
                            v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count < itemPosition)"
                            :disabled="formErrorMessage != undefined && formErrorMessage != ''"
                            @click="onNextInSequence()"
                            type="button"
                            class="button is-success">
                        <span>{{ $i18n.get('label_next') }}</span>
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-next"/>
                        </span>
                    </button>
                </template>
                <button 
                        v-if="isOnSequenceEdit && (group != null && group.items_count != undefined && group.items_count == itemPosition)"
                        @click="$router.push($routerHelper.getCollectionPath(form.collectionId))"
                        type="button"
                        class="button sequence-button">
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-approved"/>
                    </span>
                    <span>{{ $i18n.get('finish') }}</span>
                </button>
            </div>
        </footer>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { eventBusItemMetadata } from '../../js/event-bus-item-metadata';
import wpMediaFrames from '../../js/wp-media-frames';
import FileItem from '../other/file-item.vue';
import DocumentItem from '../other/document-item.vue';
import RelatedItemsList from '../lists/related-items-list.vue';
import CustomDialog from '../other/custom-dialog.vue';
import AttachmentsList from '../lists/attachments-list.vue';
import { formHooks } from '../../js/mixins';
import ItemMetadatumErrorsTooltip from '../other/item-metadatum-errors-tooltip.vue';
import { directive } from 'vue-awesome-swiper';
import ItemDocumentTextModal from '../modals/item-document-text-modal.vue';
import ItemDocumentURLModal from '../modals/item-document-url-modal.vue';
import ItemThumbnailAltTextModal from '../modals/item-thumbnail-alt-text-modal.vue';

export default {
    name: 'ItemEditionForm',
    components: {
        FileItem,
        DocumentItem,
        AttachmentsList,
        RelatedItemsList,
        ItemMetadatumErrorsTooltip
    },
    directives: {
        swiper: directive
    },
    mixins: [ formHooks ],
    data(){
        return {
            swiperOptions: {
                watchOverflow: true,
                mousewheel: true,
                observer: true,
                preventInteractionOnTransition: true,
                allowClick: true,
                allowTouchMove: true,
                slideToClickedSlide: true,
                slidesPerView: 'auto',
                navigation: {
                    nextEl: '#tainacan-tabs-next',
                    prevEl: '#tainacan-tabs-prev',
                }
            },
            selected: 'Home',
            pageTitle: '',
            itemId: Number,
            item: {},
            itemRequestCancel: undefined,
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
                comment_status: '',
                thumbnail_id: '',
                thumbnail_alt: ''
            },
            thumbnail: {},
            formErrorMessage: '',
            thumbnailMediaFrame: undefined,
            attachmentMediaFrame: undefined,
            fileMediaFrame: undefined,
            urlLink: '',
            textLink: '',
            isUpdatingValues: false,
            entityName: 'item',
            activeTab: 'metadata',
            isLoadingAttachments: false,
            metadataNameFilterString: '',
            
            urlForcedIframe: false,
            urlIframeWidth: 600,
            urlIframeHeight: 450,
            urlIsImage: false,
            isMobileScreen: false,
            openMetadataNameFilter: false,
            focusedMetadatum: false,
            isMetadataNavigation: false
        }
    },
    computed: {
        collection() {
            return this.getCollection()
        },
        metadatumList() {
            return JSON.parse(JSON.stringify(this.getItemMetadata()));
        },
        lastUpdated() {
            return this.getLastUpdated();
        },
        group() {
            return this.getGroup();
        },
        itemIdInSequence() {
            return this.getItemIdInSequence();
        },
        totalAttachments() {
            return this.getTotalAttachments();
        },
        totalRelatedItems() {
            return (this.item && this.item.related_items) ? Object.values(this.item.related_items).reduce((totalItems, aRelatedItemsGroup) => totalItems + parseInt(aRelatedItemsGroup.total_items), 0) : false;
        },
        formErrors() {
           return eventBusItemMetadata && eventBusItemMetadata.errors && eventBusItemMetadata.errors.length ? eventBusItemMetadata.errors : []
        },
        isEditingItemMetadataInsideIframe() {
            return this.$route.query && this.$route.query.editingmetadata;
        },
        tabs() {
            let pageTabs = [{
                slug: 'metadata',
                icon: 'metadata',
                name: this.$i18n.get('metadata'),
                total: this.metadatumList.length
            }];
            if (this.totalRelatedItems) {
                pageTabs.push({
                    slug: 'related',
                    icon: 'processes tainacan-icon-rotate-270',
                    name: this.$i18n.get('label_related_items'),
                    total: this.totalRelatedItems
                });
            }
            pageTabs.push({
                slug: 'attachments',
                icon: 'attachments',
                name: this.$i18n.get('label_attachments'),
                total: this.totalAttachments
            });
            return pageTabs;
        }
    },
    watch: {
        '$route.params.itemPosition'(newItemPosition, oldItemPosition) {
            if (oldItemPosition == undefined || oldItemPosition == newItemPosition)
                this.sequenceRightDirection = undefined;

            this.itemPosition = Number(newItemPosition);

            // Saves current itemPosition to user prefs
            this.$userPrefs.set('sequence_' + this.sequenceId + '_position', this.itemPosition);

            // Clear form variables
            this.cleanItemMetadata();
            eventBusItemMetadata.clearAllErrors();
            this.formErrorMessage = '';

            this.isLoading = true;

            // Obtains current Item ID from Sequence
            this.fetchItemIdInSequence({
                    collectionId: this.collectionId,
                    sequenceId: this.sequenceId,
                    itemPosition: this.itemPosition
                })
                .then(() => {
                    this.itemId = this.itemIdInSequence;
                    this.loadExistingItem();
                })
                .catch(() => {
                    this.isLoading = false;
                });

            // Obtains current Sequence Group Info
            this.fetchSequenceGroup({ collectionId: this.collectionId, groupId: this.sequenceId });
        }
    },
    created() {
        // Obtains collection ID
        this.cleanItemMetadata();
        eventBusItemMetadata.clearAllErrors();
        this.formErrorMessage = '';
        this.collectionId = this.$route.params.collectionId;
        this.form.collectionId = this.collectionId;

        // CREATING NEW SINGLE ITEM
        if (this.$route.path.split("/").pop() == "new") {
            this.isCreatingNewItem = true;
            this.createNewItem();

        // EDITING EXISTING ITEM
        } else if (this.$route.path.split("/").pop() == "edit") {
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
            this.fetchItemIdInSequence({
                    collectionId: this.collectionId,
                    sequenceId: this.sequenceId,
                    itemPosition: this.itemPosition
                })
                .then(() => {
                    this.itemId = this.itemIdInSequence;
                    this.loadExistingItem();
                })
                .catch(() => {
                    this.isLoading = false;
                });

            // Obtains current Sequence Group Info
            this.fetchSequenceGroup({ collectionId: this.collectionId, groupId: this.sequenceId });
        }

        // Sets feedback variables
        eventBusItemMetadata.$on('isUpdatingValue', (status) => {
            this.isUpdatingValues = status;
        });
        eventBusItemMetadata.$on('hasErrorsOnForm', (hasErrors) => {
            if (hasErrors)
                this.formErrorMessage = this.formErrorMessage ? this.formErrorMessage : this.$i18n.get('info_errors_in_form');
            else
                this.formErrorMessage = '';
        });
        this.cleanLastUpdated();

        // Listens to window resize event to update responsiveness variable
        this.handleWindowResize();
        window.addEventListener('resize', this.handleWindowResize);
    },
    beforeDestroy () {
        eventBusItemMetadata.$off('isUpdatingValue');
        eventBusItemMetadata.$off('hasErrorsOnForm');
        window.removeEventListener('resize', this.handleWindowResize);
    },
    beforeRouteLeave ( to, from, next ) {
        if (this.item.status == 'auto-draft') {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_item_not_saved'),
                    onConfirm: () => {
                        next();
                    },
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        } else {
            next()
        }
    },
    methods: {
        ...mapActions('item', [
            'sendItem',
            'updateItem',
            'updateItemDocument',
            'updateThumbnailAlt',
            'fetchItemMetadata',
            'fetchItem',
            'cleanItemMetadata',
            'sendAttachments',
            'fetchAttachments',
            'deletePermanentlyAttachment',
            'updateThumbnail',
            'cleanLastUpdated',
            'setLastUpdated',
            'removeAttachmentFromItem'
        ]),
        ...mapGetters('item',[
            'getItemMetadata',
            'getTotalAttachments',
            'getLastUpdated',
            'getAttachments'
        ]),
        ...mapActions('collection', [
            'deleteItem',
        ]),
        ...mapGetters('collection', [
            'getCollection',
        ]),
        ...mapActions('bulkedition', [
            'fetchItemIdInSequence',
			'fetchSequenceGroup'
        ]),
        ...mapGetters('bulkedition', [
            'getItemIdInSequence',
            'getGroup'
        ]),
        onSubmit(status, sequenceDirection) {

            // Puts loading on Item edition
            this.isLoading = true;
            this.sequenceRightDirection = undefined;

            let previousStatus = this.form.status;
            this.form.status = status;

            this.form.comment_status = this.form.comment_status == 'open' ? 'open' : 'closed';

            let data = { id: this.itemId, status: this.form.status, comment_status: this.form.comment_status };
            this.fillExtraFormData(data);

            let promise = null;
            if (status == 'trash')
                promise = this.deleteItem({ itemId: this.itemId, isPermanently: false });
            else
                promise = this.updateItem(data);

            // Clear errors so we don't have them duplicated from api
            eventBusItemMetadata.errors = [];

            promise.then(updatedItem => {

                this.item = updatedItem;

                // Fills hook forms with it's real values
                this.updateExtraFormData(this.item);

                // Fill this.form data with current data.
                this.form.status = status == 'trash' ? status : this.item.status;
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;
                this.form.comment_status = this.item.comment_status;
                this.form.thumbnail_id = this.item.thumbnail_id;
                this.form.thumbnail_alt = this.item.thumbnail_alt;
                
                this.isLoading = false;

                if (!this.$adminOptions.itemEditionMode) {

                    if (!this.isOnSequenceEdit) {
                        if (this.form.status != 'trash') {
                            if (previousStatus == 'auto-draft')
                                this.$router.push({ path: this.$routerHelper.getItemPath(this.form.collectionId, this.itemId), query: { recent: true } });
                            else
                                this.$router.push(this.$routerHelper.getItemPath(this.form.collectionId, this.itemId));
                        } else
                            this.$router.push(this.$routerHelper.getCollectionPath(this.form.collectionId));
                    } else {
                        if (sequenceDirection == 'next')
                            this.onNextInSequence();
                        else if (sequenceDirection == 'previous')
                            this.onPrevInSequence();
                    }

                } else {
                    parent.postMessage({ 
                        type: 'itemEditionMessage',
                        item: this.item
                    },
                    tainacan_plugin.admin_url);
                }
            })
            .catch((errors) => {
                
                if (errors.errors) {
                    for (let error of errors.errors) {
                        for (let metadatum of Object.keys(error)){
                            eventBusItemMetadata.errors.push({ 
                                metadatum_id: metadatum,
                                errors: error[metadatum]
                            });
                        }   
                    }
                    this.formErrorMessage = errors.error_message;
                }
                this.form.status = previousStatus;
                this.item.status = previousStatus;

                this.isLoading = false;
            });
        },
        onDiscard() {
            if (!this.$adminOptions.itemEditionMode)
                this.$router.go(-1);
            else
                parent.postMessage({ 
                        type: 'itemEditionMessage',
                        item: null
                    },
                    tainacan_plugin.admin_url);

        },
        createNewItem() {
            // Puts loading on Draft Item creation
            this.isLoading = true;

            // Updates Collection BreadCrumb
            this.$root.$emit('onCollectionBreadCrumbUpdate', [
                { path: this.$routerHelper.getCollectionPath(this.collectionId), label: this.$i18n.get('items') },
                { path: '', label: this.$i18n.get('new') }
            ]);

            // Clear errors so we don't have them duplicated from api
            eventBusItemMetadata.errors = [];

            // Creates draft Item
            this.form.comment_status = this.form.comment_status == 'open' ? 'open' : 'closed';
            let data = { collection_id: this.form.collectionId, status: 'auto-draft', comment_status: this.form.comment_status };

            // If a parameter was passed with a suggestion of item title, use it
            if (this.$route.query.newitemtitle)
                data.title = this.$route.query.newitemtitle;

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
                this.form.thumbnail_id = this.item.thumbnail_id;
                this.form.thumbnail_alt = this.item.thumbnail_alt;

                // If a parameter was passed with a suggestion of item title, also send a patch to item metadata
                if (this.$route.query.newitemtitle) {
                    eventBusItemMetadata.$emit('input', {
                        itemId: this.itemId,
                        metadatumId: this.$route.query.newmetadatumid,
                        values: this.$route.query.newitemtitle,
                        parentMetaId: 0
                    });
                }

                // Loads metadata and attachments
                this.loadMetadata();
                this.isLoadingAttachments = true;
                this.fetchAttachments({ page: 1, attachmentsPerPage: 24, itemId: this.itemId })
                    .then(() => this.isLoadingAttachments = false)
                    .catch(() => this.isLoadingAttachments = false);

            })
            .catch((error) => {
                this.$console.error(error);
                this.isLoading = false;
            });
        },
        loadMetadata() {
            // Obtains Item Metadatum
            this.fetchItemMetadata(this.itemId).then((metadata) => {
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
            this.$buefy.modal.open({
                parent: this,
                component: ItemDocumentTextModal,
                canCancel: false,
                width: 640,
                scroll: 'keep',
                trapFocus: true,
                autoFocus: false,
                ariaModal: true,
                ariaRole: 'dialog',
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close'),
                props: {
                    textContent: this.textContent
                },
                events: {
                    confirmTextWriting: this.confirmTextWriting
                }
            });
        },
        confirmTextWriting(newTextContent) {
            this.isLoading = true;
            this.form.document_type = 'text';
            this.form.document = newTextContent;
            this.updateItemDocument({ item_id: this.itemId, document: this.form.document, document_type: this.form.document_type })
                .then(item => {
                    this.item.document_as_html = item.document_as_html;
                    this.item.document_mimetype = item.document_mimetype;
                    if (item.document_type != undefined && item.document_type == 'text')
                        this.textContent = item.document;
                    this.isLoading = false;
                })
                .catch((errors) => {
                    for (let error of errors.errors) {
                        for (let metadatum of Object.keys(error)){
                            eventBusItemMetadata.errors.push({ 
                                metadatum_id: metadatum, 
                                errors: error[metadatum]
                            });
                        }
                    }
                    this.formErrorMessage = errors.error_message;

                    this.isLoading = false;
                });
        },
        setURLDocument() {
            this.$buefy.modal.open({
                parent: this,
                component: ItemDocumentURLModal,
                canCancel: false,
                width: 860,
                scroll: 'keep',
                trapFocus: true,
                autoFocus: false,
                ariaModal: true,
                ariaRole: 'dialog',
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close'),
                props: {
                    urlLink: this.urlLink,
                    urlForcedIframe: this.urlForcedIframe,
                    urlIframeWidth: this.urlIframeWidth,
                    urlIframeHeight: this.urlIframeHeight,
                    urlIsImage: this.urlIsImage
                },
                events: {
                    confirmURLSelection: this.confirmURLSelection
                }
            });
        },
        confirmURLSelection(updatedValues) {
            this.isLoading = true;
            this.isURLModalActive = false;
            this.form.document_type = 'url';
            this.form.document = updatedValues.urlLink;
            this.form.document_options = {
                forced_iframe: updatedValues.urlForcedIframe,
                forced_iframe_width: updatedValues.urlIframeWidth,
                forced_iframe_height: updatedValues.urlIframeHeight,
                is_image: updatedValues.urlIsImage
            }
            this.updateItemDocument({
                    item_id: this.itemId,
                    document: this.form.document,
                    document_type: this.form.document_type,
                    document_options: this.form.document_options
                })
                .then(item => {
                    this.item.document_as_html = item.document_as_html;
                    this.item.document_mimetype = item.document_mimetype;

                    if (item.document_type != undefined && item.document_type == 'url')
                        this.urlLink = item.document;
                        
                    if (item.document_options !== undefined && item.document_options['forced_iframe'] !== undefined)
                        this.urlForcedIframe = item.document_options['forced_iframe'];
                    if (item.document_options !== undefined && item.document_options['is_image'] !== undefined)
                        this.urlIsImage = item.document_options['is_image'];
                    if (item.document_options !== undefined && item.document_options['forced_iframe_width'] !== undefined)
                        this.urlIframeWidth = item.document_options['forced_iframe_width'];
                    if (item.document_options !== undefined && item.document_options['forced_iframe_height'] !== undefined)
                        this.urlIframeHeight = item.document_options['forced_iframe_height'];

                    this.isLoading = false;

                    let oldThumbnail = this.item.thumbnail;
                    if (item.document_type == 'url' && oldThumbnail != item.thumbnail )
                        this.item.thumbnail = item.thumbnail;
                })
                .catch((errors) => {
                    for (let error of errors.errors) {
                        for (let metadatum of Object.keys(error)){
                            eventBusItemMetadata.errors.push({ 
                                metadatum_id: metadatum, 
                                errors: error[metadatum]
                            });
                        }
                    }
                    this.formErrorMessage = errors.error_message;

                    this.isLoading = false;
                });
        },
        removeDocument() {
            this.textContent = '';
            this.urlLink = '';
            this.form.document_type = 'empty';
            this.form.document = '';
            this.updateItemDocument({
                item_id: this.itemId,
                document: this.form.document,
                document_type: this.form.document_type
            })
            .then(() => {
                this.item.document_mimetype = 'empty';
                this.isLoadingAttachments = true;
                this.fetchAttachments({
                    page: 1,
                    attachmentsPerPage: 24,
                    itemId: this.itemId,
                    documentId: this.form.document,
                    thumbnailId: this.form.thumbnail_id
                })
                    .then(() => this.isLoadingAttachments = false)
                    .catch(() => this.isLoadingAttachments = false);
            })
            .catch((errors) => {
                for (let error of errors.errors) {
                    for (let metadatum of Object.keys(error)){
                        eventBusItemMetadata.errors.push({
                            metadatum_id: metadatum,
                            errors: error[metadatum]
                        });
                    }
                }
                this.formErrorMessage = errors.error_message;
            });
        },
        openThumbnailModalAltText() {
            this.$buefy.modal.open({
                parent: this,
                component: ItemThumbnailAltTextModal,
                canCancel: false,
                width: 640,
                scroll: 'keep',
                trapFocus: true,
                autoFocus: false,
                ariaModal: true,
                ariaRole: 'dialog',
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close'),
                props: {
                    altText: this.form.thumbnail_alt ? this.form.thumbnail_alt : ''
                },
                events: {
                    onUpdateThumbnailAlt: (altText) => this.form.thumbnail_alt = altText
                }
            });
        },
        deleteThumbnail() {
            this.updateThumbnail({ itemId: this.itemId, thumbnailId: 0 })
                .then(() => {
                    this.item.thumbnail = false;
                    this.item.thumbnail_id = null;
                })
                .catch((error) => {
                    this.$console.error(error);
                });
        },
        deleteAttachment(attachment) {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_attachment_delete'),
                    onConfirm: () => {
                        this.deletePermanentlyAttachment(attachment.id)
                            .then(() => {
                                this.isLoadingAttachments = true;

                                this.fetchAttachments({ 
                                        page: 1,
                                        attachmentsPerPage: 24,
                                        itemId: this.itemId,
                                        documentId: this.form.document,
                                        thumbnailId: this.form.thumbnail_id
                                    })
                                    .then(() => this.isLoadingAttachments = false)
                                    .catch(() => this.isLoadingAttachments = false);
                            })
                            .catch((error) => {
                                this.$console.error(error);
                            });
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
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
                            this.item.document_mimetype = item.document_mimetype;

                            let oldThumbnail = this.item.thumbnail;
                            if (item.document_type == 'attachment' && oldThumbnail != item.thumbnail )
                                this.item.thumbnail = item.thumbnail;

                        })
                        .catch((errors) => {
                            for (let error of errors.errors) {
                                for (let metadatum of Object.keys(error)){
                                    eventBusItemMetadata.errors.push({ metadatum_id: metadatum, errors: error[metadatum]});
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
                    thumbnail: this.form.thumbnail_id,
                    relatedPostId: this.itemId,
                    onSave: (media) => {
                        this.updateThumbnail({ itemId: this.itemId, thumbnailId: media.id})
                            .then((res) => {
                                this.item.thumbnail = res.thumbnail;
                                this.item.thumbnail_id = res.thumbnail_id;
                                this.item.thumbnail_alt = res.thumbnail_alt;
                                this.form.thumbnail = res.thumbnail;
                                this.form.thumbnail_id = res.thumbnail_id;
                                this.form.thumbnail_alt = res.thumbnail_alt;
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
                    nonce: this.item.nonces ? this.item.nonces['update-post_' + this.item.id] : null,
                    relatedPostId: this.itemId,
                    document: this.form.document_type == 'attachment' ? this.form.document : null, 
                    thumbnailId: this.form.thumbnail_id ? this.form.thumbnail_id : null, 
                    onSave: () => {
                        // Fetch current existing attachments
                        this.isLoadingAttachments = true;
                        this.fetchAttachments({ 
                            page: 1,
                            attachmentsPerPage: 24,
                            itemId: this.itemId,
                            documentId: this.form.document,
                            thumbnailId: this.form.thumbnail_id
                        })
                            .then(() => this.isLoadingAttachments = false)
                            .catch(() => this.isLoadingAttachments = false);
                    }
                }
            );

        },
        onUpdateThumbnailAlt(updatedThumbnailAlt) {

            this.updateThumbnailAlt({ thumbnailId: this.item.thumbnail_id, thumbnailAlt: updatedThumbnailAlt })
                .then((res) => {
                    this.form.thumbnail_id = res.thumbnail_id;
                    this.form.thumbnail_alt = res.thumbnail_alt;
                })
                .catch(error => this.$console.error(error));
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
            this.$buefy.modal.open({
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
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
            });
        },
        loadExistingItem() {

            // Cancels previous Request
            if (this.itemRequestCancel != undefined)
                this.itemRequestCancel.cancel('Item search Canceled.');

            this.fetchItem({
                itemId: this.itemId,
                contextEdit: true,
                fetchOnly: 'title,thumbnail,status,modification_date,document_type,document,comment_status,document_as_html,document_options,related_items'
            })
            .then((resp) => {
                resp.request.then((res) => {
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
                    this.form.document_options = this.item.document_options;
                    this.form.comment_status = this.item.comment_status;
                    this.form.thumbnail_id = this.item.thumbnail_id;
                    this.form.thumbnail_alt = this.item.thumbnail_alt;

                    if (this.form.document_type != undefined && this.form.document_type == 'url')
                        this.urlLink = this.form.document;
                    if (this.form.document_type != undefined && this.form.document_type == 'text')
                        this.textContent = this.form.document;

                    if (this.form.document_options !== undefined && this.form.document_options['forced_iframe'] !== undefined)
                        this.urlForcedIframe = this.form.document_options['forced_iframe'];
                    if (this.form.document_options !== undefined && this.form.document_options['is_image'] !== undefined)
                        this.urlIsImage = this.form.document_options['is_image'];
                    if (this.form.document_options !== undefined && this.form.document_options['forced_iframe_width'] !== undefined)
                        this.urlIframeWidth = this.form.document_options['forced_iframe_width'];
                    if (this.form.document_options !== undefined && this.form.document_options['forced_iframe_height'] !== undefined)
                        this.urlIframeHeight = this.form.document_options['forced_iframe_height'];

                    if (this.item.status == 'publish' || this.item.status == 'private')
                        this.visibility = this.item.status;

                    this.loadMetadata();
                    this.setLastUpdated(this.item.modification_date);

                    // Fetch current existing attachments now that item.document
                    this.fetchAttachments({
                        page: 1,
                        attachmentsPerPage: 24,
                        itemId: this.itemId,
                        documentId: this.form.document,
                        thumbnailId: this.form.thumbnail_id });

                    // Initializes Media Frames now that itemId and item.document exists
                    this.initializeMediaFrames();
                });

                // Item resquest token for cancelling
                this.itemRequestCancel = resp.source;
            });
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
        },
        filterByMetadatumName(itemMetadatum) {
            if (itemMetadatum.metadatum &&
                itemMetadatum.metadatum.metadata_type_object && 
                itemMetadatum.metadatum.metadata_type_object.component == 'tainacan-compound' &&
                itemMetadatum.metadatum.metadata_type_options &&
                itemMetadatum.metadatum.metadata_type_options.children_objects &&
                itemMetadatum.metadatum.metadata_type_options.children_objects.length
            ) {
                let childNamesArray = itemMetadatum.metadatum.metadata_type_options.children_objects.map((children) => children.name);
                childNamesArray.push(itemMetadatum.metadatum.name);

                return childNamesArray.some((childName) => childName.toString().toLowerCase().indexOf(this.metadataNameFilterString.toString().toLowerCase()) >= 0);
            }
            else 
                return itemMetadatum.metadatum.name.toString().toLowerCase().indexOf(this.metadataNameFilterString.toString().toLowerCase()) >= 0;
        },
        openMetadataNameFilterClose() {
            this.metadataNameFilterString = '';
            if (this.isMobileScreen)
                this.openMetadataNameFilter = false;
        },
        handleWindowResize: _.debounce( function() {
            this.$nextTick(() => {
                if (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth)
                    this.isMobileScreen = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= 768;
            });
        }, 500),
        setMetadatumFocus({ index = 0, scrollIntoView = false }) {
            
            const previousIndex = this.focusedMetadatum;
            this.focusedMetadatum = index;
            
            if (previousIndex === index && !scrollIntoView)
                return;

            let fieldElement = this.$refs['tainacan-form-item--' + index] && this.$refs['tainacan-form-item--' + index][0] && this.$refs['tainacan-form-item--' + index][0]['$el'];
            if (fieldElement) {
                
                let inputElement = fieldElement.getElementsByTagName('input')[0] || fieldElement.getElementsByTagName('select')[0] || fieldElement.getElementsByTagName('textarea')[0];
                if (inputElement) {

                    setTimeout(() => {
                        
                        if (previousIndex !== index && inputElement !== document.activeElement) {
                            inputElement.focus();
                            
                            if (inputElement.type !== 'checkbox' && inputElement.type !== 'radio' && !inputElement.classList.contains('is-special-hidden-for-mobile'))
                                inputElement.click();
                        }
                        
                        if (scrollIntoView) {
                            setTimeout(() => {
                                fieldElement.scrollIntoView({
                                    behavior: 'smooth',
                                    block: this.isMobileScreen ? 'start' : 'center'
                                });
                            }, 300);
                        }

                    }, 100);
                }
            }
        }
    }
}
</script>

<style lang="scss" scoped>

    .page-container {
        padding: var(--tainacan-container-padding) 0px;

        &>.tainacan-form {
            margin-bottom: 64px;

            .field:not(:last-child) {
                margin-bottom: 0em;
            }
        }

        .tainacan-page-title {
            padding: 0 var(--tainacan-one-column);
            margin-bottom: 32px;
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
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
                max-width: 80%;
            }
            .status-tag {
                color: var(--tainacan-white);
                background: var(--tainacan-secondary);
                padding: 0.15em 0.5em;
                font-size: 0.75em;
                margin: 0 1em 0 0;
                font-weight: 600;
                position: relative;
                top: -2px;
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

            @media screen and (max-width: 769px) {
                padding: 0 0.5em;
            }
        }

        .tainacan-form > .columns {
            margin-left: var(--tainacan-one-column);
            margin-right: var(--tainacan-one-column);

            .column.is-5 {
                padding-left: var(--tainacan-one-column);
                padding-right: var(--tainacan-one-column);

                @media screen and (min-width: 770px) {
                    .sticky-container {
                        position: relative;
                        position: sticky;
                        top: -25px;
                        margin: 3px 0;
                        max-height: calc(100vh - 202px);
                        overflow-y: auto;
                        overflow-x: hidden;
                    }
                }
            }
            .column.is-7 {
                padding-left: var(--tainacan-one-column);
                padding-right: 0;

                .columns {
                    flex-wrap: wrap;
                    justify-content: space-between;

                    .column {
                        padding: 1em 12px 0 12px;
                    }
                }

                .field {
                    padding: 12px 0px 12px 42px;
                }
                .tab-item>.field:last-child {
                    margin-bottom: 187px;
                }

                @media screen and (max-width: 769px) {
                    padding-right: var(--tainacan-one-column);
                    max-width: 100%;
                }
            }

            @media screen and (max-width: 769px) {
                margin-left: 0;
                margin-right: 0;
                display: flex;
                flex-direction: column-reverse;

                &>.column.is-7 {
                    padding-left: 0;
                    padding-right: 0;
                    max-width: 100%;
                    widows: 100%;

                    .sub-header {
                        padding-right: 0.5em;
                        padding-left: 0.5em;
                    }

                    .field {
                        padding: 1em 0.75em;
                        
                        /deep/ .collapse-handle {
                            font-size: 1em;
                            margin-left: 0;
                            margin-right: 22px;
                            width: 100%;
                            display: block;

                            .label {
                                margin-left: 2px;
                            }
                            .icon {
                                float: right;
                                width: 3em;
                                justify-content: flex-end;
                            }
                        }
                    }
                    .tab-content {
                        padding-left: 0;
                        padding-right: 0;

                    }
                    .tab-item>.field:last-child {
                        margin-bottom: 24px;
                    }
                    &>.columns {
                        display: flex;
                    }
                }
                &>.column.is-5 {
                    max-width: 100%;
                    widows: 100%;
                    padding-left: 0.5em;
                    padding-right: 0.5em;
                }
            }
        }

        // .b-tabs {
        //     overflow: hidden !important;
        // }
    }

    .section-label {
        position: relative;
        label {
            font-size: 1em !important;
            font-weight: 500 !important;
            color: var(--tainacan-label-color) !important;
            line-height: 1.2em;
        }
    }

    .sub-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: var(--tainacan-background-color);
        
        .field {
            padding: 2px 0px 2px 24px !important;
        }

        &.is-metadata-navigation-active {
            width: calc(58.33333337% - (2 * var(--tainacan-one-column)) - 3.25em);
            position: fixed;
            z-index: 99999;
            bottom: 0;
            padding: 0.5em 0.5em 0.25em 0.5em;
            border-top-right-radius: 3px;
            border-top-left-radius: 3px;
            overflow: hidden;
            transition: top 0.3s ease, bottom 0.3s ease, background-color 0.3s ease;
            background-color: var(--tainacan-gray1);

            .metadata-name-search {
                top: 0.25em;
                max-width: 220px;
            }
        }

        .metadata-navigation {
            margin-left: auto;
            margin-right: 0.25em;
        }
        .metadata-navigation /deep/ .button {
            border-radius: 0 !important;
            margin-left: 0;
            min-height: 2.25em;
            background-color: var(--tainacan-background-color) !important;

            &>span {
                display: flex;
                align-items: center;
            }

            &:last-of-type {
                margin-left: 0;
            }
        }
        .metadata-name-search-icon {
            padding: 1.25em 0.75em;
            color: var(--tainacan-blue5);
        }

        @media screen and (max-width: 769px) {
            .metadata-navigation {
                margin-right: 1.75em !important;
            }
            .metadata-name-search {
                position: absolute;
                right: 0.5em;
                top: 0.35em;
                z-index: 9999;
                padding-left: 0 !important;
            }
            &.is-metadata-navigation-active {
                width: 100%;
                left: 0;
                right: 0;
                background-color: var(--tainacan-gray1);
                border-top-right-radius: 0px;
                border-top-left-radius: 0px;
                overflow: visible;
            }
        }
    }

    .collapse-all {
        font-size: 0.75em;
        white-space: nowrap;
        border-color: transparent !important;

        .icon {
            font-size: 1.25em;
        }
    }

    .section-box {
        padding: 0 var(--tainacan-one-column) 0 0;
        margin-top: 12px;
        margin-bottom: 16px;

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
                    background-color: var(--tainacan-gray2);
                    color: var(--tainacan-secondary);
                    margin-bottom: 6px;
                    &:hover {
                        background-color: var(--tainacan-primary);
                        cursor: pointer;
                    }
                }
                p { 
                    color: var(--tainacan-secondary); 
                    font-size: 0.8125em;
                }
            }
        }
    }
    .section-status {
        padding-bottom: 16px;
        font-size: 0.875em;

        .field {
            padding: 10px 0 14px 0px !important;

            .b-radio {
                margin-right: 24px;
                margin-left: 0;
            }
            .icon  {
                font-size: 1.125em !important;
                color: var(--tainacan-info-color);
            }
        }
    }

    .tab-content {
        border-top: 1px solid var(--tainacan-input-border-color);
    }
    .swiper-container {
        width: 100%;
        position: relative;
        margin: 0;
        --swiper-navigation-size: 2em;
        --swiper-navigation-color: var(--tainacan-secondary);
        
        .swiper-wrapper {
            border: none !important;
        }
        .swiper-slide {
            width: auto;
        }
        .swiper-button-next,
        .swiper-button-prev {
            padding: 34px 26px;
            border: none;
            background-color: transparent;
            position: absolute;
            top: 0;
            bottom: 0;
        }
        .swiper-button-prev::after,
        .swiper-container-rtl .swiper-button-next::after {
            content: 'previous';
        }
        .swiper-button-next,
        .swiper-container-rtl .swiper-button-prev {
            right: 0;
            background-image: linear-gradient(90deg, rgba(255,255,255,0) 0%, var(--tainacan-background-color) 40%);
        }
        .swiper-button-prev,
        .swiper-container-rtl .swiper-button-next {
            left: 0;
            background-image: linear-gradient(90deg, var(--tainacan-background-color) 0%, rgba(255,255,255,0) 60%);
        }
        .swiper-button-next.swiper-button-disabled,
        .swiper-button-prev.swiper-button-disabled {
            display: none;
            visibility: hidden;
        }
        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-family: "TainacanIcons";
            opacity: 0.7;
            transition: opacity ease 0.2s;
        }
    }   

    .document-field {
        /deep/ iframe {
            max-width: 100%;
            max-height: 100%;
        }
        .document-buttons-row {
            text-align: right;
            top: -21px;
            position: relative;
        }
    }

    #button-edit-thumbnail,
    #button-edit-document,
    #button-delete-thumbnail,
    #button-alt-text-thumbnail,
    #button-delete-document {
        border-radius: 100px !important;
        max-height: 2.125em !important;
        max-width: 2.125em !important;
        min-height: 2.125em !important;
        min-width: 2.125em !important;
        padding: 0 !important;
        z-index: 99;
        margin-left: 6px !important;

        .icon {
            display: inherit;
            padding: 0;
            margin: 0;
            margin-top: -2px;
            font-size: 1.125em;
        }
    }

    .thumbnail-field {

        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            height: 148px;
            width: 148px;
        }
        .image-placeholder {
            position: absolute;
            margin-left: 32px;
            margin-right: 32px;
            font-size: 0.8em;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: var(--tainacan-info-color);
            top: 60px;
            max-width: 84px;
        }

        .thumbnail-buttons-row {
            position: relative;
            left: 33px;
            bottom: 1.25em;
        }

        .thumbnail-alt-input {
            .label {
                font-size: 0.875em;
                font-weight: 500;
                margin-left: 15px;
                margin-bottom: 0;
                margin-top: 0.15em;
            }
        }
    }

    .attachments-list-heading {
        display: flex;
        align-items: center;
        margin-top: 12px;
        margin-bottom: 24px;

        button {
            margin-right: 12px;
        }
    }

    .sequence-progress {
        height: 5px;
        background: var(--tainacan-secondary);
        width: 0%;
        position: absolute;
        top: 0;
        left: 0;
        transition: width 0.2s;
    }
    .sequence-progress-background {
        height: 5px;
        background: var(--tainacan-gray3);
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
    }

    .update-warning {
        color: var(--tainacan-blue5);
        animation-name: blink;
        animation-duration: 0.5s;
        animation-delay: 0.5s;
    }

    .footer {
        padding: 18px var(--tainacan-one-column);
        position: fixed;
        bottom: 0;
        right: 0;
        z-index: 9999;
        background-color: var(--tainacan-gray1);
        width: calc(100% - var(--tainacan-sidebar-width, 3.25em));
        height: 65px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        transition: bottom 0.5s ease, width 0.2s linear;

        .form-submission-footer {
            .button {
                margin-left: 16px;
                margin-right: 6px;
            }
        }

        @keyframes blink {
            from { color: var(--tainacan-blue5); }
            to { color: var(--tainacan-info-color); }
        }

        .footer-message {
            display: flex;
            align-items: center;
        }

        .update-info-section {
            color: var(--tainacan-info-color);
            margin-right: auto;
        }

        .help {
            display: inline-block;
            font-size: 1.0em;
            margin-top: 0;
            margin-left: 24px;
        }

        .sequence-button {
            background-color: transparent;
            color: var(--tainacan-secondary);
            border: none;

            .icon {
                margin-right: 5px !important;
            }

            &:hover,
            &:focus,
            &:active {
                background-color: transparent !important;
                color: var(--tainacan-secondary) !important;
            }
        }

        &.has-some-metadatum-focused {
            bottom: -300px;
        }

        @media screen and (max-width: 769px) {
            padding: 16px 0.5em;
            width: 100%;
            flex-wrap: wrap;
            height: auto;
            position: fixed;

            .update-info-section {
                margin-left: auto;margin-bottom: 0.75em;
                margin-top: -0.25em;
            }
            .form-submission-footer {
                display: flex;
                justify-content: space-between;
                width: 100%;

                .button {
                    margin-left: 6px;
                    margin-right: 6px;
                }
                .button:first-of-type {
                    margin-left: 0px;
                }
                .button:last-of-type {
                    margin-right: 0px;
                }
                .button.is-success {
                    margin-left: auto;
                }
            }
        }
    }

</style>
