<template>
    <div :class="isCreatingNewItem ? 'item-creation-container' : 'item-edition-container'"><!-- Do NOT remove this classes, they may be used by third party plugins -->
        <b-loading
                v-model="isLoading"
                :is-full-page="false"
                :can-cancel="false" />

        <tainacan-title v-if="!$adminOptions.hideItemEditionPageTitle || ($adminOptions.hideItemEditionPageTitle && isEditingItemMetadataInsideIframe)">
            <h1 v-if="isCreatingNewItem">
                <span
                        v-if="(item != null && item != undefined && item.status != undefined && !isLoading)"
                        class="status-tag"
                        @mouseenter="$emit('toggleItemEditionFooterDropdown')">
                    {{ $i18n.get('status_' + item.status) }}
                </span>
                {{ $i18n.get('title_create_item_collection') + ' ' }}
                <span style="font-weight: 600;">{{ collection && collection.name ? collection.name : '' }}</span>
            </h1>
            <h1 v-else>
                <span
                        v-if="(item != null && item != undefined && item.status != undefined && !isLoading)"
                        class="status-tag"
                        @mouseenter="$emit('toggleItemEditionFooterDropdown')">
                    {{ $i18n.get('status_' + item.status) }}
                </span>
                {{ $i18n.get('title_edit_item') + ' ' }}
                <span style="font-weight: 600;">
                    {{ (item != null && item != undefined) ? item.title : '' }}
                </span>
                <span
                        v-if="$adminOptions.itemEditionStatusOptionOnFooterDropdown && (item != null && item != undefined && item.status != undefined && item.status != 'autodraft' && !isLoading)"
                        class="icon has-text-gray4"
                        style="margin-left: 0.5em;"
                        @mouseenter="$emit('toggleItemEditionFooterDropdown')">
                    <i 
                            class="tainacan-icon tainacan-icon-1em"
                            :class="$statusHelper.getIcon(item.status)"
                        />
                    <help-button
                            :title="$i18n.get('status_' + item.status)"
                            :message="$i18n.get('info_item_' + item.status) + ' ' + $i18n.get('instruction_edit_item_status')" />
                </span>
                
            </h1>
        </tainacan-title>

        <div 
                v-if="$adminOptions.mobileAppMode"
                class="tainacan-mobile-app-header">
            <button
                    @click="exitToMobileApp">
                <span class="icon">
                    <i class="tainacan-icon">
                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                height="24"
                                viewBox="0 0 24 24"
                                width="24">
                            <path
                                    d="M0 0h24v24H0z"
                                    fill="none" />
                            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
                        </svg>
                    </i>
                </span>
            </button>
            <transition name="item-appear">
                <h1 v-if="isMobileSubheaderOpen">
                    {{ $i18n.get('label_tainacan_mobile_panel') }}
                </h1>
                <h1 v-else-if="isCreatingNewItem && !isMobileSubheaderOpen">
                    {{ $i18n.get('title_create_item_collection') + ' ' }}
                    <span style="font-weight: 600;">{{ collection && collection.name ? collection.name : '' }}</span>
                </h1>
                <h1 v-else>
                    {{ $i18n.get('title_edit_item') + ' ' }}
                    <span style="font-weight: 600;">{{ (item != null && item != undefined) ? item.title : '' }}</span>
                </h1>
            </transition>
            <button 
                    v-if="!errors.length || isUpdatingValues"
                    @click="isMobileSubheaderOpen = !isMobileSubheaderOpen">
                <span 
                        v-if="isUpdatingValues"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-spin" />
                </span>
                <span 
                        v-else
                        class="icon">
                    <i class="tainacan-icon">
                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                height="24"
                                viewBox="0 0 24 24"
                                width="24">
                            <path
                                    d="M0 0h24v24H0z"
                                    fill="none" />
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                        </svg>
                    </i>
                </span>
            </button>
            <item-metadatum-errors-tooltip 
                    v-else
                    :form-errors="errors" />
        </div>

        <transition name="item-appear">
            <div 
                    v-if="isMobileSubheaderOpen"
                    class="tainacan-mobile-app-header_panel-backdrop"
                    @click="isMobileSubheaderOpen = false;" />
        </transition>
        <transition name="panel-from-top">
            <div 
                    v-if="$adminOptions.mobileAppMode && isMobileSubheaderOpen"
                    class="tainacan-mobile-app-header_panel">
                <h1 v-if="isCreatingNewItem">
                    {{ $i18n.get('title_create_item_collection') + ' ' }}
                    <span style="font-weight: 600;">{{ collection && collection.name ? collection.name : '' }}</span>
                </h1>
                <h1 v-else>
                    {{ $i18n.get('title_edit_item') + ' ' }}
                    <span style="font-weight: 600;">{{ (item != null && item != undefined) ? item.title : '' }}</span>
                </h1>
                <span v-if="!errors.length">{{ ($i18n.get('info_updated_at') + ' ' + lastUpdated) }}</span>
                <span 
                        v-else
                        class="help is-danger">
                    {{ formErrorMessage }}
                </span>
                <div class="tainacan-mobile-app-header_panel-shortcuts-area">
                    <h2>{{ $i18n.get('label_shortcuts') }}</h2>
                    <p>{{ $i18n.get('instruction_click_to_easily_see') }}:</p>
                    <div class="tainacan-mobile-app-header_panel-shortcuts">
                        <button @click="showOnlyRequiredMetadata = false; activeTab = 'metadata'; isMobileSubheaderOpen = false;">
                            <span><i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-metadata" /></span>
                            <span>{{ $i18n.get('label_all_metadata') }}</span>
                        </button>
                        <button 
                                v-if="shouldDisplayItemEditionDocument || shouldDisplayItemEditionThumbnail"
                                @click="activeTab = 'document'; isMobileSubheaderOpen = false;">
                            <span><i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-item" /></span>
                            <span>{{ $i18n.get('label_document_and_thumbnail') }}</span>
                        </button>
                        <button 
                                v-if="shouldDisplayItemEditionAttachments"
                                @click="activeTab = 'attachments'; isMobileSubheaderOpen = false;">
                            <span><i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-attachments" /></span>
                            <span>{{ $i18n.get('label_all_attachments') }}</span>
                        </button>
                        <button 
                                v-if="!$adminOptions.hideItemEditionRequiredOnlySwitch && (collection && collection.item_enable_metadata_required_filter === 'yes')"
                                @click="showOnlyRequiredMetadata = true; isMobileSubheaderOpen = false;">
                            <span><i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-metadata" /></span>
                            <span>{{ $i18n.get('label_required_metadata') }}</span>
                        </button>
                    </div>
                </div>
                <a @click="isMobileSubheaderOpen = false">{{ $i18n.get('label_close_panel') }}</a>
            </div>
        </transition>

        <transition
                mode="out-in"
                :name="(isOnSequenceEdit && sequenceRightDirection != undefined) ? (sequenceRightDirection ? 'page-right' : 'page-left') : ''">
            <form
                    v-show="!isLoading && ((isCreatingNewItem && collection && collection.current_user_can_edit_items) || (!isCreatingNewItem && item && item.current_user_can_edit))"
                    class="tainacan-form"
                    label-width="120px">
                <div class="columns is-multiline">

                    <div
                            class="column main-column"
                            :class="
                                (
                                    ( (shouldDisplayItemEditionDocument || shouldDisplayItemEditionThumbnail) && !$adminOptions.itemEditionDocumentInsideTabs ) ||
                                    ( shouldDisplayItemEditionAttachments && !$adminOptions.itemEditionAttachmentsInsideTabs )
                                ) ? 'is-12 is-6-desktop is-7-widescreen' : 'is-12'">

                        <!-- Hook for extra Form options -->
                        <template v-if="hasBeginRightForm">
                            <form
                                    id="form-item-begin-right"
                                    class="form-hook-region"
                                    v-html="getBeginRightForm" />
                        </template>

                        <div class="b-tabs">
                            <nav 
                                    v-if="tabs.length >= 2"
                                    id="tainacanTabsSwiper"       
                                    class="swiper tabs">
                                <ul class="swiper-wrapper">
                                    <li 
                                            v-for="(tab, tabIndex) of tabs"
                                            :id="tab.slug + '-tab-label'"
                                            :key="tabIndex"
                                            :class="{ 'is-active': activeTab === tab.slug }"
                                            class="swiper-slide"
                                            @click="activeTab = tab.slug">
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
                                        id="tainacan-tabs-prev" 
                                        class="swiper-button-prev">
                                    <svg
                                            width="24"
                                            height="24"
                                            viewBox="0 0 32 32">
                                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
                                        <path
                                                d="M0 0h24v24H0z"
                                                fill="none" />
                                    </svg>
                                </button>
                                <button 
                                        id="tainacan-tabs-next"
                                        class="swiper-button-next">
                                    <svg
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24">
                                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" />
                                        <path
                                                d="M0 0h24v24H0z"
                                                fill="none" />
                                    </svg>
                                </button>
                            </nav>
                        
                            <section 
                                    :style="tabs.length < 2 ? 'border-top: none; padding-top: 0;' : ''"
                                    class="tab-content item-edition-tab-content">
                                
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
                                                v-if="isMetadataNavigation"
                                                class="sequence-progress-background" />
                                        <div
                                                v-if="isMetadataNavigation && focusedMetadatum !== false"
                                                :style="{ width: ((focusedMetadatum + 1)/itemMetadata.length)*100 + '%' }"
                                                class="sequence-progress" />

                                        <b-field 
                                                v-if="itemMetadata && itemMetadata.length > 3"
                                                class="header-item metadata-navigation"
                                                :style="$adminOptions.hideItemEditionCollapses ? 'padding-left: 0.35em !important;' : ''">
                                            <b-button
                                                    v-if="!$adminOptions.hideItemEditionFocusMode && (collection && collection.item_enable_metadata_focus_mode === 'yes') && !isMetadataNavigation && !showOnlyRequiredMetadata && !metadataNameFilterString" 
                                                    class="collapse-all has-text-secondary"
                                                    size="is-small"
                                                    @click="isMetadataNavigation = true; setMetadatumFocus({ index: 0, scrollIntoView: true });">
                                                <span
                                                        class="icon">
                                                    <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-play" />
                                                </span>
                                                <span>{{ isMobileScreen ? $i18n.get('label_focus_mode') : $i18n.get('label_start_focus_mode') }}</span>
                                            </b-button>
                                            <b-button 
                                                    v-if="isMetadataNavigation"
                                                    :disabled="focusedMetadatum === 0"
                                                    outlined 
                                                    @click="focusPreviousMetadatum">
                                                <span
                                                        class="icon">
                                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-showmore tainacan-icon-rotate-180" />
                                                </span>
                                                <span>{{ $i18n.get('previous') }}</span>
                                            </b-button>
                                            <b-button 
                                                    v-if="isMetadataNavigation"
                                                    :disabled="(focusedMetadatum === itemMetadata.length - 1) && (!isCurrentlyFocusedOnCompoundMetadatum || isOnLastMetadatumOfCompoundNavigation)"
                                                    outlined
                                                    @click="focusNextMetadatum">
                                                <span
                                                        class="icon">
                                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-showmore" />
                                                </span>
                                                <span>{{ $i18n.get('next') }}</span>
                                            </b-button>
                                            <b-button
                                                    v-if="isMetadataNavigation" 
                                                    outlined
                                                    @click="setMetadatumFocus({ index: 0, scrollIntoView: true }); isMetadataNavigation = false;">
                                                <span
                                                        class="icon has-success-color">
                                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-finish" />
                                                </span>
                                                <span>{{ $i18n.get('finish') }}</span>
                                            </b-button>
                                        </b-field>

                                        <span 
                                                v-if="isUpdatingValues && isMetadataNavigation && !$adminOptions.mobileAppMode"
                                                class="update-warning">
                                            <span class="icon">
                                                <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-updating" />
                                            </span>
                                        </span>

                                        <b-switch
                                                v-if="!isMetadataNavigation && !$adminOptions.hideItemEditionRequiredOnlySwitch && (collection && collection.item_enable_metadata_required_filter === 'yes')"
                                                id="tainacan-switch-required-metadata"
                                                v-model="showOnlyRequiredMetadata"
                                                v-tooltip="{
                                                    content: $i18n.get('label_only_required'),
                                                    autoHide: true,
                                                    placement: 'auto',
                                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                                }"
                                                :style="'font-size: 0.625em;' + (isMobileScreen ? 'margin-right: 2rem;' : '')"
                                                size="is-small">
                                            {{ $i18n.get('label_required') }} *
                                        </b-switch>

                                        <b-field 
                                                v-if="!isMetadataNavigation && (collection && collection.item_enable_metadata_searchbar === 'yes')"
                                                class="header-item metadata-name-search">
                                            <b-input
                                                    v-if="!isMobileScreen || openMetadataNameFilter"
                                                    v-model="metadataNameFilterString"
                                                    :placeholder="$i18n.get('instruction_type_search_metadata_filter')"
                                                    icon="magnify"
                                                    size="is-small"
                                                    icon-right="close-circle"
                                                    icon-right-clickable
                                                    @icon-right-click="openMetadataNameFilterClose" />
                                            <span
                                                    v-else
                                                    class="icon is-small metadata-name-search-icon" 
                                                    @click="openMetadataNameFilter = true">
                                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-search" />
                                            </span>
                                        </b-field>
                                    </div>

                                    <a
                                            v-if="!isMetadataNavigation && !$adminOptions.hideItemEditionCollapses"
                                            class="collapse-all"
                                            @click="toggleCollapseAll()">
                                        <span class="icon">
                                            <i
                                                    :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll }"
                                                    class="tainacan-icon tainacan-icon-1-25em" />
                                        </span>
                                        <template v-if="isMobileScreen">{{ collapseAll ? $i18n.get('label_collapse') : $i18n.get('label_expand') }}</template>
                                        <template v-else>{{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}</template>
                                    </a>

                                    <div 
                                            v-for="(metadataSection, sectionIndex) of metadataSections"
                                            :id="'metadata-section-id-' + metadataSection.id"
                                            :key="sectionIndex"
                                            v-tooltip="{
                                                content: isSectionHidden(metadataSection.id) ? $i18n.get('info_metadata_section_hidden_conditional') : false,
                                                autoHide: true,
                                                placement: 'auto',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            :class="'metadata-section-slug-' + metadataSection.slug + (isSectionHidden(metadataSection.id) ? ' metadata-section-hidden' : '')">
                                        <div class="metadata-section-header section-label">
                                            <span   
                                                    class="collapse-handle"
                                                    @click="(isMetadataNavigation || $adminOptions.hideItemEditionCollapses || isSectionHidden(metadataSection.id)) ? null : toggleMetadataSectionCollapse(sectionIndex)">
                                                <span 
                                                        v-if="!$adminOptions.hideItemEditionCollapses && !isMetadataNavigation"
                                                        class="icon">
                                                    <i 
                                                            :class="{
                                                                'tainacan-icon-arrowdown' : (metadataSectionCollapses[sectionIndex] || formErrorMessage) && !isSectionHidden(metadataSection.id),
                                                                'tainacan-icon-arrowright' : !(metadataSectionCollapses[sectionIndex] || formErrorMessage) || isSectionHidden(metadataSection.id)
                                                            }"
                                                            class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                                                </span>
                                                <label>
                                                    <span class="icon has-text-gray4">
                                                        <i class="tainacan-icon tainacan-icon-metadata" />
                                                    </span>
                                                    <span
                                                            v-if="metadataSections.length > 1 && collection && collection.item_enable_metadata_enumeration === 'yes'"
                                                            style="opacity: 0.65;"
                                                            class="metadata-section-enumeration">
                                                        {{ Number(sectionIndex) + 1 }}.
                                                    </span>
                                                    {{ metadataSection.name }}&nbsp;
                                                    <span 
                                                            v-if="metadataSection.metadata_object_list && metadataSection.metadata_object_list.length"
                                                            class="has-text-gray has-text-weight-normal"
                                                            style="font-size: 0.875em;">
                                                        ({{ metadataSection.metadata_object_list.length }})
                                                    </span>
                                                </label>
                                                <help-button
                                                        v-if="metadataSection.description && metadataSection.description_bellow_name !== 'yes'" 
                                                        :title="metadataSection.name"
                                                        :message="metadataSection.description" />
                                            </span>
                                        </div>
                                        <div 
                                                v-show="((metadataSectionCollapses[sectionIndex] || isMetadataNavigation) && !isSectionHidden(metadataSection.id))"
                                                class="metadata-section-metadata-list"
                                                :class="((metadataSectionCollapses[sectionIndex] || isMetadataNavigation) && !isSectionHidden(metadataSection.id)) ? '' : 'is-section-content-hidden'">
                                            <p
                                                    v-if="metadataSection.description && metadataSection.description_bellow_name == 'yes'"
                                                    class="metadata-section-description-help-info metadatum-description-help-info">
                                                {{ metadataSection.description }}
                                            </p>

                                            <template 
                                                    v-for="(itemMetadatum, index) of itemMetadata"
                                                    :key="index">
                                                <tainacan-form-item
                                                        v-if="itemMetadatum.metadatum.metadata_section_id == metadataSection.id"
                                                        v-show="(!showOnlyRequiredMetadata || itemMetadatum.metadatum.required === 'yes') && (metadataNameFilterString == '' || filterByMetadatumName(itemMetadatum))"
                                                        :id="'metadatum-index--' + index"      
                                                        :ref="'tainacan-form-item--' + index"
                                                        :class="{ 'is-metadata-navigation-active': isMetadataNavigation }"
                                                        :item-metadatum="itemMetadatum"
                                                        :metadata-name-filter-string="metadataNameFilterString"
                                                        :is-collapsed="metadataCollapses[index]"
                                                        :hide-collapses="$adminOptions.hideItemEditionCollapses || isMetadataNavigation || (collection && collection.item_enable_metadata_collapses === 'no')"
                                                        :hide-metadata-types="hideMetadataTypes"
                                                        :hide-help-buttons="false"
                                                        :help-info-bellow-label="false"
                                                        :is-mobile-screen="isMobileScreen"
                                                        :enumerate-metadatum="metadataSections.length > 1 && collection && collection.item_enable_metadata_enumeration === 'yes' ? ( (Number(sectionIndex) + 1) + '.' + (Number(getMetadatumOrderInSection(sectionIndex, itemMetadatum.metadatum)) + 1) ) : false"
                                                        :is-last-metadatum="index > 2 && (index == itemMetadata.length - 1)"
                                                        :is-focused="focusedMetadatum === index"
                                                        :is-metadata-navigation="isMetadataNavigation"
                                                        @input="updateItemMetadataValue"
                                                        @change-collapse="onChangeCollapse($event, index)"
                                                        @touchstart="isMetadataNavigation ? setMetadatumFocus({ index: index, scrollIntoView: false }): ''"
                                                        @mousedown="isMetadataNavigation ? setMetadatumFocus({ index: index, scrollIntoView: false }) : ''"
                                                        @mobile-special-focus="setMetadatumFocus({ index: index, scrollIntoView: true })" />
                                            </template>
                                        </div>
                                    
                                    </div>

                                    <!-- Hook for extra Form options -->
                                    <template
                                            v-if="formHooks != undefined &&
                                                formHooks['item'] != undefined &&
                                                formHooks['item']['end-right'] != undefined">
                                        <form
                                                id="form-item-end-right"
                                                class="form-hook-region"
                                                v-html="formHooks['item']['end-right'].join('')" />
                                    </template>
                                </div>

                                <!-- Related items -->
                                <div    
                                        v-if="totalRelatedItems && activeTab === 'related'"
                                        class="tab-item"
                                        role="tabpanel"
                                        aria-labelledby="related-tab-label"
                                        tabindex="0"> 

                                    <div class="related-items-list-heading">
                                        <p>
                                            {{ $i18n.get("info_related_items") }}
                                        </p>
                                    </div>

                                    <related-items-list
                                            v-model:is-loading="isLoading"
                                            :item-id="itemId"
                                            :collection-id="collectionId"
                                            :related-items="item.related_items"
                                            :is-editable="!$adminOptions.itemEditionMode" />
                                    
                                </div>

                                <!-- Publication section -->
                                <div    
                                        v-if="activeTab === 'publication' && !$adminOptions.hideItemEditionPublicationSection && $adminOptions.itemEditionPublicationSectionInsideTabs"
                                        class="tab-item"
                                        role="tabpanel"
                                        aria-labelledby="publication-tab-label"
                                        tabindex="0"> 
                                    <item-publication-edition-form
                                            :item="item"
                                            :form="form"
                                            :collection="collection"
                                            :is-loading="isLoading"
                                            :is-updating-slug="isUpdatingSlug"
                                            :has-some-error="formErrorMessage != undefined && formErrorMessage != ''"
                                            :current-user-can-delete="item && item.current_user_can_delete"
                                            :current-user-can-publish="collection && collection.current_user_can_publish_items"
                                            @on-update-comment-status="updateCommentStatus"
                                            @on-update-item-author="updateItemAuthor"
                                            @on-update-item-slug="updateItemSlug"
                                            @on-submit="onSubmit" />
                                </div>

                                <!-- Document and thumbnail on mobile modal -->
                                <div    
                                        v-if="activeTab === 'document' && $adminOptions.itemEditionDocumentInsideTabs"
                                        class="tab-item"
                                        role="tabpanel"
                                        aria-labelledby="document-tab-label"
                                        tabindex="0"> 
                                    <item-document-edition-form 
                                            :item="item"
                                            :form="form"
                                            :collection="collection"
                                            @on-set-document="setDocument"
                                            @on-remove-document="removeDocument"
                                            @on-set-file-document="setFileDocument"
                                            @on-set-text-document="setTextDocument"
                                            @on-set-url-document="setURLDocument" />
                                    <item-thumbnail-edition-form 
                                            :item="item"
                                            :form="form"
                                            :collection="collection"
                                            :is-loading="isLoading"
                                            @on-delete-thumbnail="deleteThumbnail"
                                            @on-update-thumbnail-alt="($event) => onUpdateThumbnailAlt($event)"
                                            @open-thumbnail-media-frame="thumbnailMediaFrame.openFrame($event)" />
                                </div>

                                <!-- Attachments on mobile modal -->
                                <div    
                                        v-if="activeTab === 'attachments' && shouldDisplayItemEditionAttachments && $adminOptions.itemEditionAttachmentsInsideTabs"
                                        class="tab-item"
                                        role="tabpanel"
                                        aria-labelledby="attachments-tab-label"
                                        tabindex="0"> 
                                    <item-attachments-edition-form
                                            :item="item"
                                            :form="form"
                                            :collection="collection"
                                            :is-loading="isLoading"
                                            :total-attachments="totalAttachments"
                                            :should-load-attachments="shouldLoadAttachments"
                                            @open-attachments-media-frame="($event) => attachmentsMediaFrame.openFrame($event)"
                                            @on-delete-attachment="deleteAttachment($event)" />
                                </div>

                            </section>
                        </div>

                    </div>

                    <div 
                            v-if="( (shouldDisplayItemEditionDocument || shouldDisplayItemEditionThumbnail) && !$adminOptions.itemEditionDocumentInsideTabs) ||
                                (shouldDisplayItemEditionAttachments && !$adminOptions.itemEditionAttachmentsInsideTabs)"
                            class="column secondary-column is-12 is-6-desktop is-5-widescreen">
                
                        <div 
                                :style="isMetadataNavigation && !isMobileScreen ? 'max-height: calc(100vh - 142px);' : ''"
                                class="sticky-container">

                            <!-- Publication section -->
                            <item-publication-edition-form
                                    v-if="!$adminOptions.hideItemEditionPublicationSection && !$adminOptions.itemEditionPublicationSectionInsideTabs"
                                    :item="item"
                                    :form="form"
                                    :collection="collection"
                                    :is-loading="isLoading"
                                    :is-updating-slug="isUpdatingSlug"
                                    :has-some-error="formErrorMessage != undefined && formErrorMessage != ''"
                                    :current-user-can-delete="item && item.current_user_can_delete"
                                    :current-user-can-publish="collection && collection.current_user_can_publish_items"
                                    @on-update-comment-status="updateCommentStatus"
                                    @on-update-item-author="updateItemAuthor"
                                    @on-update-item-slug="updateItemSlug"
                                    @on-submit="onSubmit" />

                            <!-- <hr v-if="!$adminOptions.hideItemEditionPublicationSection && !$adminOptions.itemEditionPublicationSectionInsideTabs"> -->

                            <!-- Hook for extra Form options -->
                            <template v-if="hasBeginLeftForm">
                                <form
                                        id="form-item-begin-left"
                                        class="form-hook-region"
                                        v-html="getBeginLeftForm" />
                            </template>

                            <!-- Document -------------------------------- -->
                            <item-document-edition-form 
                                    v-if="shouldDisplayItemEditionDocument && !$adminOptions.itemEditionDocumentInsideTabs"
                                    :item="item"
                                    :form="form"
                                    :collection="collection"
                                    @on-set-document="setDocument"
                                    @on-remove-document="removeDocument"
                                    @on-set-file-document="setFileDocument"
                                    @on-set-text-document="setTextDocument"
                                    @on-set-url-document="setURLDocument" />

                            <hr v-if="shouldDisplayItemEditionDocument && !$adminOptions.itemEditionDocumentInsideTabs">

                            <!-- Thumbnail -------------------------------- -->
                            <item-thumbnail-edition-form 
                                    v-if="shouldDisplayItemEditionThumbnail && !$adminOptions.itemEditionDocumentInsideTabs"
                                    :item="item"
                                    :form="form"
                                    :collection="collection"
                                    :is-loading="isLoading"
                                    @on-delete-thumbnail="deleteThumbnail"
                                    @on-update-thumbnail-alt="($event) => onUpdateThumbnailAlt($event)"
                                    @open-thumbnail-media-frame="thumbnailMediaFrame.openFrame($event)" />

                            <hr v-if="shouldDisplayItemEditionThumbnail && !$adminOptions.itemEditionDocumentInsideTabs">

                            <!-- Attachments -->
                            <item-attachments-edition-form
                                    v-if="shouldDisplayItemEditionAttachments && !$adminOptions.itemEditionAttachmentsInsideTabs"
                                    :item="item"
                                    :form="form"
                                    :collection="collection"
                                    :is-loading="isLoading"
                                    :total-attachments="totalAttachments"
                                    :should-load-attachments="shouldLoadAttachments"
                                    @open-attachments-media-frame="($event) => attachmentsMediaFrame.openFrame($event)"
                                    @on-delete-attachment="deleteAttachment($event)" />

                            <hr v-if="(shouldDisplayItemEditionAttachments && !$adminOptions.itemEditionAttachmentsInsideTabs) || hasEndLeftForm">

                            <!-- Hook for extra Form options -->
                            <template v-if="hasEndLeftForm">
                                <form
                                        id="form-item-end-left"
                                        class="form-hook-region"
                                        v-html="getEndLeftForm" />
                            </template>

                        </div>
                       
                    </div>
                    
                </div>

            </form>

        </transition>

        <transition
                mode="out-in"
                :name="(isOnSequenceEdit && sequenceRightDirection != undefined) ? (sequenceRightDirection ? 'page-right' : 'page-left') : ''">
                
            <!-- In case user enters this page whithout having permission -->
            <template v-if="!isLoading && ((isCreatingNewItem && collection && collection.current_user_can_edit_items == false) || (!isCreatingNewItem && item && item.current_user_can_edit != undefined && collection && collection.current_user_can_edit_items == false))">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-items" />
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
                    class="sequence-progress-background" />
            <div
                    v-if="isOnSequenceEdit && itemPosition != undefined && group != null && group.items_count != undefined"
                    :style="{ width: (itemPosition/group.items_count)*100 + '%' }"
                    class="sequence-progress" />

            <!-- Last Updated Info -->
            <div 
                    v-if="!$adminOptions.mobileAppMode"
                    class="update-info-section">
                <p
                        v-if="isOnSequenceEdit"
                        class="footer-message">
                    {{ $i18n.get('label_sequence_editing_item') + " " + itemPosition + " " + $i18n.get('info_of') + " " + ((group != null && group.items_count != undefined) ? group.items_count : '') + "." }}&nbsp;
                </p>
                <p class="footer-message">
                    <span 
                            v-if="isUpdatingValues"
                            class="update-warning">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating" />
                        </span>
                        <span>{{ $i18n.get('info_updating_metadata_values') }}</span>
                    </span>
                    <template v-else> 
                        <span 
                                v-if="form.status === 'auto-draft'"
                                class="has-text-warning">
                            {{ $i18n.get('info_autodraft_updated') }}
                        </span>
                        <span v-else>
                            {{ ($i18n.get('info_updated_at') + ' ' + lastUpdated) }}
                        </span>
                    </template>
                    
                    <span class="help is-danger">
                        {{ formErrorMessage }}
                        <item-metadatum-errors-tooltip 
                                v-if="errors.length"
                                :form-errors="errors" />
                    </span>
                </p>

            </div>
            
            <item-form-footer-buttons
                    :status="form.status"
                    :collection-id="form.collectionId"
                    :is-on-sequence-edit="isOnSequenceEdit"
                    :is-last-item-on-sequence-edit="(group != null && group.items_count != undefined && group.items_count == itemPosition)"
                    :has-next-item-on-sequence-edit="(group != null && group.items_count != undefined && group.items_count > itemPosition)"
                    :has-previous-item-on-sequence-edit="itemPosition > 1"
                    :is-mobile-screen="isMobileScreen"
                    :has-some-error="formErrorMessage != undefined && formErrorMessage != ''"
                    :current-user-can-delete="item && item.current_user_can_delete"
                    :current-user-can-publish="collection && collection.current_user_can_publish_items"
                    :is-editing-item-metadata-inside-iframe="isEditingItemMetadataInsideIframe"
                    @on-submit="onSubmit"
                    @on-discard="onDiscard"
                    @on-prev-in-sequence="onPrevInSequence"
                    @on-next-in-sequence="onNextInSequence" />

        </footer>
    </div>
</template>

<script>
import { nextTick, defineAsyncComponent } from 'vue';
import { mapActions, mapGetters } from 'vuex';

import wpMediaFrames from '../../js/wp-media-frames';
import { permalinkGetter, formHooks } from '../../js/mixins';
import { itemMetadataMixin } from '../../js/item-metadata-mixin';

import RelatedItemsList from '../lists/related-items-list.vue';
import CustomDialog from '../other/custom-dialog.vue';
import ItemMetadatumErrorsTooltip from '../other/item-metadatum-errors-tooltip.vue';
import ItemDocumentTextModal from '../modals/item-document-text-modal.vue';
import ItemDocumentURLModal from '../modals/item-document-url-modal.vue';
import ItemPublicationEditionForm from '../edition/item-publication-edition-form.vue';
import ItemDocumentEditionForm from '../edition/item-document-edition-form.vue';
import ItemThumbnailEditionForm from '../edition/item-thumbnail-edition-form.vue';
import ItemAttachmentsEditionForm from '../edition/item-attachments-edition-form.vue';
import ItemFormFooterButtons from './item-form-footer-buttons.vue';

import 'swiper/css';
import 'swiper/css/mousewheel';
import 'swiper/css/navigation';

import Swiper from 'swiper';
import { Mousewheel, Navigation } from 'swiper/modules';

export default {
    name: 'ItemEditionForm',
    components: {
        RelatedItemsList,
        ItemMetadatumErrorsTooltip,
        ItemPublicationEditionForm,
        ItemThumbnailEditionForm,
        ItemDocumentEditionForm,
        ItemAttachmentsEditionForm,
        ItemFormFooterButtons,
        TainacanFormItem: defineAsyncComponent(() => import('../metadata-types/tainacan-form-item.vue')),
    },
    mixins: [ formHooks, permalinkGetter, itemMetadataMixin ],
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
                canCancel: ['escape', 'outside']
            });
        } else {
            next()
        }
    },
    emits: [
        'toggleItemEditionFooterDropdown'
    ],
    data(){
        return {
            swiper: {},
            pageTitle: '',
            itemId: [String, Number],
            item: {},
            itemRequestCancel: undefined,
            collectionId: [String, Number],
            sequenceId: Number,
            itemPosition: Number,
            isCreatingNewItem: false,
            isOnSequenceEdit: false,
            sequenceRightDirection: false,
            isLoading: false,
            isLoadingMetadataSections: false,
            isUpdatingSlug: false,
            metadataCollapses: [],
            metadataSectionCollapses: [],
            collapseAll: true,
            form: {
                collectionId: [String, Number],
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
            attachmentsMediaFrame: undefined,
            fileMediaFrame: undefined,
            urlLink: '',
            textContent: '',
            isUpdatingValues: false,
            entityName: 'item',
            activeTab: 'metadata',
            shouldLoadAttachments: false,
            metadataNameFilterString: '',
            isMobileSubheaderOpen: false,
            urlForcedIframe: false,
            urlIframeWidth: 600,
            urlIframeHeight: 450,
            urlIsImage: false,
            isMobileScreen: false,
            openMetadataNameFilter: false,
            focusedMetadatum: false,
            isMetadataNavigation: false,
            isOnFirstMetadatumOfCompoundNavigation: false,
            isOnLastMetadatumOfCompoundNavigation: false,
            hideMetadataTypes: this.$adminOptions.hideItemEditionMetadataTypes,
            showOnlyRequiredMetadata: false
        }
    },
    computed: {
        ...mapGetters('collection', {
            'collection': 'getCollection'
        }),
        itemMetadata() {
            const realItemMetadata = JSON.parse(JSON.stringify(this.getItemMetadata()));

            const tweakedItemMetadata = realItemMetadata.map((anItemMetadatum) => {

                // We need this because repository level metadata have an array of section IDs
                const metadatumSectionId = anItemMetadatum.metadatum.metadata_section_id;
                if ( !Array.isArray(metadatumSectionId) )
                    return anItemMetadatum;

                anItemMetadatum.metadatum.metadata_section_id = 'default_section';

                // To find which is the section of this metadatum, we look for an intersection of the existeing sections
                // in this collection and the list of section ids in the repository metadata
                const intersectionOfSections = this.metadataSections.filter(
                    (aMetadataSection) => metadatumSectionId.includes("" + aMetadataSection.id) && aMetadataSection.id !== 'default_section'
                ); 
                if (intersectionOfSections.length === 1)
                    anItemMetadatum.metadatum.metadata_section_id = intersectionOfSections[0].id;                          
                    
                return anItemMetadatum;
               
            });
            return tweakedItemMetadata;
        },
        ...mapGetters('metadata', {
            'metadataSections': 'getMetadataSections'
        }),
        ...mapGetters('item', {
            'totalAttachments': 'getTotalAttachments',
            'lastUpdated': 'getLastUpdated'
        }),
        ...mapGetters('bulkedition', { 
            'itemIdInSequence': 'getItemIdInSequence',
            'group': 'getGroup'
        }),
        totalRelatedItems() {
            return (this.item && this.item.related_items) ? Object.values(this.item.related_items).reduce((totalItems, aRelatedItemsGroup) => totalItems + parseInt(aRelatedItemsGroup.total_items), 0) : false;
        },
        isEditingItemMetadataInsideIframe() {
            return this.$route.query && this.$route.query.editingmetadata;
        },
        tabs() {
            let pageTabs = [{
                slug: 'metadata',
                icon: 'metadata',
                name: this.$i18n.get('metadata'),
                total: this.itemMetadata.length
            }];
            if ( this.$adminOptions.itemEditionPublicationSectionInsideTabs && !this.$adminOptions.hideItemEditionPublicationSection ) {
                pageTabs.push({
                    slug: 'publication',
                    icon: 'item',
                    name: this.collection && this.collection.item_publication_label ? this.collection.item_publication_label : this.$i18n.get('label_publication_data')
                });
            }
            if ( this.$adminOptions.itemEditionDocumentInsideTabs && (this.shouldDisplayItemEditionDocument || this.shouldDisplayItemEditionThumbnail) ) {
                pageTabs.push({
                    slug: 'document',
                    icon: 'item',
                    name: this.collection && this.collection.item_document_label ? this.collection.item_document_label : this.$i18n.get('label_document')
                });
            }
            if ( this.$adminOptions.itemEditionAttachmentsInsideTabs && this.shouldDisplayItemEditionAttachments ) {
                pageTabs.push({
                    slug: 'attachments',
                    icon: 'attachments',
                    name: this.collection && this.collection.item_attachment_label ? this.collection.item_attachment_label : this.$i18n.get('label_attachments'),
                    total: this.totalAttachments
                });
            }
            if ( this.totalRelatedItems ) {
                pageTabs.push({
                    slug: 'related',
                    icon: 'processes tainacan-icon-rotate-270',
                    name: this.$i18n.get('label_related_items'),
                    total: this.totalRelatedItems
                });
            }
            return pageTabs;
        },
        isCurrentlyFocusedOnCompoundMetadatum() {
            if (!this.isMetadataNavigation || !this.itemMetadata[this.focusedMetadatum])
                return false;
            return this.itemMetadata[this.focusedMetadatum].metadatum && this.itemMetadata[this.focusedMetadatum].metadatum.metadata_type === 'Tainacan\\Metadata_Types\\Compound';
        },
        shouldDisplayItemEditionDocument() {
            return !this.$adminOptions.hideItemEditionDocument && 
                ( this.collection && this.collection.item_enabled_document_types && (
                    ( this.collection.item_enabled_document_types['attachment'] && this.collection.item_enabled_document_types['attachment']['enabled'] === 'yes' ) || 
                    ( this.collection.item_enabled_document_types['text'] && this.collection.item_enabled_document_types['text']['enabled'] === 'yes' ) || 
                    ( this.collection.item_enabled_document_types['url'] && this.collection.item_enabled_document_types['url']['enabled'] === 'yes' )
                )
            );
        },
        shouldDisplayItemEditionThumbnail() {
            return !this.$adminOptions.hideItemEditionThumbnail && (this.collection && this.collection.item_enable_thumbnail === 'yes');
        },
        shouldDisplayItemEditionAttachments() {
            return !this.$adminOptions.hideItemEditionAttachments && (this.collection && this.collection.item_enable_attachments === 'yes');
        },
        hasRedirectHook() {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.hasFilter(`tainacan_item_edition_after_update_redirect`);

            return false;
        },
        getRedirectHook() {
            const itemViewURL = this.$routerHelper.getAbsoluteAdminPath() + this.$routerHelper.getItemPath(this.form.collectionId, this.itemId);
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.applyFilters(`tainacan_item_edition_after_update_redirect`, itemViewURL, this.form, this.itemId);

            return itemViewURL;
        },
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
            this.clearAllErrors();
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
        },
        tabs: {
            handler() {
                if (this.tabs.length >= 2) {
                    if (typeof this.swiper.update == 'function')
                        this.swiper.update();
                    else {
                        nextTick(() => {
                            this.swiper = new Swiper('#tainacanTabsSwiper', {
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
                                },
                                modules: [Mousewheel, Navigation]
                            });
                        });
                    }
                }
            },
            immediate: true,
            deep: true
        }
    },
    created() {
        // Obtains collection ID
        this.cleanItemMetadata();
        this.clearAllErrors();
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

        // Loads Metadata Sections
        this.isLoadingMetadataSections = true;
        this.fetchMetadataSections({
                collectionId: this.collectionId
            })
            .then((metadataSections) => {
                this.metadataSectionCollapses = Array(metadataSections.length).fill(true)
                this.isLoadingMetadataSections = false;

                /**
                 * Creates the conditional metadata set to later watch values
                 * of certain metadata that control sections visibility.
                 */
                this.conditionalSections = {};
                for (let metadataSection of metadataSections) {
                    if ( metadataSection.is_conditional_section == 'yes') { 
                        const conditionalSectionId = Object.keys(metadataSection.conditional_section_rules);
                        if ( conditionalSectionId.length ) {
                            this.conditionalSections[metadataSection.id] = {
                                sectionId: metadataSection.id,
                                metadatumId: conditionalSectionId[0],
                                metadatumValues: metadataSection.conditional_section_rules[conditionalSectionId[0]],
                                hide: true
                            };
                        }
                    }
                }
            })
            .catch((error) => {
                this.isLoadingMetadataSections = false;
                this.$console.error('Error loading metadata sections ', error);
            });

        // Sets feedback variables
        this.cleanLastUpdated();

        // Updates variables for metadata navigation from compound childs
        this.$emitter.on('isOnFirstMetadatumOfCompoundNavigation', (isOnFirstMetadatumOfCompoundNavigation) => {
            this.isOnFirstMetadatumOfCompoundNavigation = isOnFirstMetadatumOfCompoundNavigation
        });
        this.$emitter.on('isOnLastMetadatumOfCompoundNavigation', (isOnLastMetadatumOfCompoundNavigation) => {
            this.isOnLastMetadatumOfCompoundNavigation = isOnLastMetadatumOfCompoundNavigation
        });

        // Listens to window resize event to update responsiveness variable
        this.handleWindowResize();
        window.addEventListener('resize', this.handleWindowResize);

        // If we're in the mobile app, show panel
        if (this.$adminOptions.mobileAppMode)
            this.isMobileSubheaderOpen = true;
    },
    beforeUnmount () {
        this.$emitter.off('isOnFirstMetadatumOfCompoundNavigation');
        this.$emitter.off('isOnLastMetadatumOfCompoundNavigation');
        window.removeEventListener('resize', this.handleWindowResize);
        if (typeof this.swiper.destroy == 'function')
            this.swiper.destroy();
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
            'deletePermanentlyAttachment',
            'updateThumbnail',
            'cleanLastUpdated',
            'setLastUpdated',
            'removeAttachmentFromItem'
        ]),
        ...mapGetters('item',[
            'getItemMetadata',
        ]),
        ...mapActions('collection', [
            'deleteItem',
        ]),
        ...mapActions('bulkedition', [
            'fetchItemIdInSequence',
			'fetchSequenceGroup'
        ]),
        ...mapActions('metadata',[
            'fetchMetadataSections'
        ]),
        onSubmit(status, alternativeDestination) {

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
            this.errors = [];

            promise.then(updatedItem => {
                this.item = updatedItem;

                // Fills hook forms with it's real values
                this.updateExtraFormData(this.item);

                // Fill this.form data with current data.
                this.form.status = status == 'trash' ? status : this.item.status;
                this.form.slug = this.item.slug;
                this.form.author_id = this.item.author_id;
                this.form.author_name = this.item.author_name;
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;
                this.form.comment_status = this.item.comment_status;
                this.form.thumbnail_id = this.item.thumbnail_id;
                this.form.thumbnail_alt = this.item.thumbnail_alt;


                // Updater route document title
                this.$routerHelper.updatePageTitle( this.$i18n.get('title_edit_item') + ' ' + this.item.title);
                
                this.isLoading = false;

                if (
                    !this.$adminOptions.itemEditionMode &&
                    !this.$adminOptions.mobileAppMode &&
                    alternativeDestination !== 'current'
                ) {
                    if ( !this.isOnSequenceEdit ) {
                        if ( this.hasRedirectHook ) {
                            window.location.replace( this.getRedirectHook );
                        } else {
                            if (this.form.status != 'trash') {
                                if (previousStatus == 'auto-draft')
                                    this.$router.push({ path: this.$routerHelper.getItemPath(this.form.collectionId, this.itemId), query: { recent: true } });
                                else
                                    this.$router.push(this.$routerHelper.getItemPath(this.form.collectionId, this.itemId));
                            } else
                                this.$router.push(this.$routerHelper.getCollectionPath(this.form.collectionId));
                        }
                    } else {
                        if (alternativeDestination == 'next')
                            this.onNextInSequence();
                        else if (alternativeDestination == 'previous')
                            this.onPrevInSequence();
                    }
                }

                // Sends info to iframe containing item edition form and other use cases
                parent.postMessage({ 
                    type: 'itemEditionMessage',
                    item: this.$adminOptions.itemEditionMode ? JSON.parse(JSON.stringify(this.item)) : null
                },
                tainacan_plugin.admin_url);

                // In Mobile app, we send a message to inform updates
                if (
                    this.$adminOptions.mobileAppMode &&
                    webkit &&
                    webkit.messageHandlers &&
                    webkit.messageHandlers.cordova_iab
                )
                    webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({ 'type': 'item_updated', 'item': JSON.parse(JSON.stringify(this.item)) }));
            
                
                // Checks if user has permission to edit
                if ( !this.item.current_user_can_edit )
                    this.$router.push(this.$routerHelper.getCollectionPath(this.collectionId));
            })
            .catch((errors) => {
                
                this.prepareErrors(errors);
                this.form.status = previousStatus;
                this.item.status = previousStatus;

                this.isLoading = false;
            });
        },
        hasErrorsOnForm(hasErrors) {
            if (hasErrors)
                this.formErrorMessage = this.formErrorMessage ? this.formErrorMessage : this.$i18n.get('info_errors_in_form');
            else
                this.formErrorMessage = '';
        },
        onDiscard() {
            if (!this.$adminOptions.itemEditionMode && !this.$adminOptions.mobileAppMode)
                this.$router.go(-1);
            
            parent.postMessage({ 
                    type: 'itemEditionMessage',
                    item: this.$adminOptions.itemEditionMode ? false : null
                },
            tainacan_plugin.admin_url);

        },
        createNewItem() {
            // Puts loading on Draft Item creation
            this.isLoading = true;

            // Clear errors so we don't have them duplicated from api
            this.errors = [];

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
                this.form.status = 'auto-draft'
                this.form.slug = this.item.slug;
                this.form.author_id = this.item.author_id;
                this.form.author_name = this.item.author_name;
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;
                this.form.comment_status = this.item.comment_status;
                this.form.thumbnail_id = this.item.thumbnail_id;
                this.form.thumbnail_alt = this.item.thumbnail_alt;

                // If a parameter was passed with a suggestion of item title, also send a patch to item metadata
                if (this.$route.query.newitemtitle) {
                    this.updateItemMetadataValue({
                        itemId: this.itemId,
                        metadatumId: this.$route.query.newmetadatumid,
                        values: this.$route.query.newitemtitle,
                        parentMetaId: 0
                    });
                }
                /**
                 * Fires action tainacan_item_edition_item_loaded
                 * once the existing item is loaded. We cannot reliabilily send collection here since
                 * it is loaded async outside of the component.
                 */ 
                wp.hooks.doAction(
                    'tainacan_item_edition_item_loaded',
                    this.collection ? JSON.parse(JSON.stringify(this.collection)) : false,
                    this.item ? JSON.parse(JSON.stringify(this.item)) : false
                );

                // Loads metadata and attachments
                this.loadMetadata();

            })
            .catch((error) => {
                this.$console.error(error);
                this.isLoading = false;
            });
        },
        loadMetadata() {
            // Obtains Item Metadatum
            this.fetchItemMetadata(this.itemId)
                .then((metadata) => {
                    this.metadataCollapses = [];

                    if (this.isOnSequenceEdit && this.$route.query.collapses) {
                        for (let i = 0; i < metadata.length; i++) {
                            this.metadataCollapses.push(this.$route.query.collapses[i] != undefined ? (this.$route.query.collapses[i] == 'true' || this.$route.query.collapses[i] === true) : true);
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
                    
                    /* Sets initial state for conditional sections based on metadatum values */
                    for (let conditionalSectionId in this.conditionalSections) {
                        const currentItemMetadatum = metadata.find(anItemMetadatum => anItemMetadatum.metadatum.id == this.conditionalSections[conditionalSectionId].metadatumId);
                        if (currentItemMetadatum) {
                            const itemMetadatumValues = Array.isArray(currentItemMetadatum.value) ? currentItemMetadatum.value : [ currentItemMetadatum.value ];
                            const conditionalValues = Array.isArray(this.conditionalSections[conditionalSectionId].metadatumValues) ? this.conditionalSections[conditionalSectionId].metadatumValues : [this.conditionalSections[conditionalSectionId].metadatumValues];
                            this.conditionalSections[conditionalSectionId].hide = Array.isArray(itemMetadatumValues) ? itemMetadatumValues.every(aValue => conditionalValues.indexOf(aValue['id'] ? aValue['id'] : aValue) < 0) : conditionalValues.indexOf(itemMetadatumValues) < 0;
                        }
                    }
                    
                    /**
                     * Fires action tainacan_item_edition_metadata_loaded
                     * once the metadata is loaded. We cannot reliabilily send collection here since
                     * it is loaded async outside of the component.
                    */ 
                    wp.hooks.doAction(
                        'tainacan_item_edition_metadata_loaded',
                        this.colection ? JSON.parse(JSON.stringify(this.collection)) : false,
                        this.item ? JSON.parse(JSON.stringify(this.item)) : false,
                        metadata ? metadata : []
                    );

                    this.isLoading = false;
                });
        },
        updateItemAuthor(newAuthorId) {
            this.isLoading = true;
            this.form.author_id = newAuthorId;
            this.updateItem({ id: this.itemId, author_id: '' + newAuthorId })
                .then((updatedItem) => {
                    this.item.author_id = updatedItem.author_id;
                    this.item.author_name = updatedItem.author_name;
                    this.isLoading = false;
                    this.setLastUpdated(updatedItem.modification_date);
                })
                .catch((error) => {
                    this.$console.error(error);
                    this.isLoading = false;
                });
        },
        updateItemSlug: _.debounce(function(newSlug) {
            this.isUpdatingSlug = true;
            this.getSamplePermalink(this.collectionId, this.item.title, newSlug)
                .then((res) => {
                    this.form.slug = res.data.slug;
                    this.updateItem({ id: this.itemId, slug: res.data.slug })
                        .then((updatedItem) => {
                            this.item.slug = updatedItem.slug;
                            this.item.url = updatedItem.url;
                            this.setLastUpdated(updatedItem.modification_date);
                            this.isUpdatingSlug = false;
                        })
                        .catch((error) => {
                            this.$console.error(error);
                            this.isUpdatingSlug = false;
                        });
                })
                .catch(errors => {
                    this.$console.error(errors);
                    this.isUpdatingSlug = false;
                });
        }, 1000),
        updateCommentStatus(newStatus) {
            this.isLoading = true;
            this.form.comment_status = newStatus;
            this.updateItem({ id: this.itemId, comment_status: newStatus })
                .then((updatedItem) => {
                    this.item.comment_status = updatedItem.comment_status;
                    this.isLoading = false;
                    this.setLastUpdated(updatedItem.modification_date);
                })
                .catch((error) => {
                    this.$console.error(error);
                    this.isLoading = false;
                })
        },
        setDocument(event, documentType) {
            if (documentType === 'attachment')
                this.setFileDocument(event);
            else if (documentType === 'text')
                this.setTextDocument();
            else if (documentType === 'url')
                this.setURLDocument();
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
                    this.prepareErrors(errors);
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

                    if (item.document_type != undefined && item.document_type == 'text')
                        this.textContent = item.document;
                        
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
                    if (item.document_type == 'url' && oldThumbnail != item.thumbnail ) {
                        this.item.thumbnail = item.thumbnail;
                        this.item.thumbnail_id = item.thumbnail_id;
                    }
                })
                .catch((errors) => {
                    this.prepareErrors(errors);
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
                this.shouldLoadAttachments = !this.shouldLoadAttachments;
            })
            .catch((errors) => {
                this.prepareErrors(errors);
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
                                this.shouldLoadAttachments = !this.shouldLoadAttachments;
                            })
                            .catch((error) => {
                                this.$console.error(error);
                            });
                    }
                },
                trapFocus: true,
                customClass: 'tainacan-modal',
                canCancel: ['escape', 'outside']
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
                                if (item.document_type == 'attachment' && oldThumbnail != item.thumbnail ) {
                                    this.item.thumbnail = item.thumbnail;
                                    this.item.thumbnail_id = item.thumbnail_id;
                                }

                                this.shouldLoadAttachments = !this.shouldLoadAttachments;

                            })
                            .catch((errors) => {
                                this.prepareErrors(errors);
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
            this.attachmentsMediaFrame = new wpMediaFrames.attachmentControl(
                'my-attachment-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_files_to_attach_to_item'),
                        frame_button: this.$i18n.get('finish'),
                    },
                    nonce: this.item.nonces ? this.item.nonces['update-post_' + this.item.id] : null,
                    relatedPostId: this.itemId,
                    document: this.form.document_type == 'attachment' ? this.form.document : null, 
                    thumbnailId: this.form.thumbnail_id ? this.form.thumbnail_id : null, 
                    onSave: () => {
                        // Fetch current existing attachments
                        this.shouldLoadAttachments = !this.shouldLoadAttachments;
                    }
                }
            );

        },
        onUpdateThumbnailAlt(updatedThumbnailAlt) {
            this.isUpdatingValues = true;

            this.updateThumbnailAlt({ thumbnailId: this.item.thumbnail_id, thumbnailAlt: updatedThumbnailAlt })
                .then((res) => {
                    this.form.thumbnail_alt = res.alt_text;
                    this.isUpdatingValues = false;
                })
                .catch(error => this.$console.error(error));
        },
        toggleCollapseAll() {
            this.collapseAll = !this.collapseAll;

            for (let i = 0; i < this.metadataCollapses.length; i++)
                this.metadataCollapses[i] = this.collapseAll;
            
            for (let i = 0; i < this.metadataSectionCollapses.length; i++)
                Object.assign(this.metadataSectionCollapses, { [i]: this.collapseAll });

        },
        onChangeCollapse(event, index) {
            this.metadataCollapses.splice(index, 1, event);
        },
        toggleMetadataSectionCollapse(sectionIndex) {
            if ( !this.isMetadataNavigation ) 
                Object.assign( this.metadataSectionCollapses, { [sectionIndex]: (this.formErrorMessage ? true : !this.metadataSectionCollapses[sectionIndex]) });
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
                canCancel: ['escape', 'outside']
            });
        },
        loadExistingItem() {

            // Cancels previous Request
            if (this.itemRequestCancel != undefined)
                this.itemRequestCancel.cancel('Item search Canceled.');

            this.fetchItem({
                itemId: this.itemId,
                contextEdit: true,
                fetchOnly: 'title,thumbnail,status,modification_date,document_type,document,comment_status,document_as_html,document_options,related_items,slug,author_id,author_name'
            })
            .then((resp) => {
                resp.request.then((res) => {
                    this.item = res;

                    // Updater route document title
                    this.$routerHelper.updatePageTitle( this.$i18n.get('title_edit_item') + ' ' + this.item.title);
                    wp.hooks.doAction(
                        'tainacan_navigation_path_updated', 
                        { 
                            currentRoute: this.$route,
                            adminOptions: this.$adminOptions,
                            collection: this.collection,
                            parentEntity: {
                                rootLink: 'collections',
                                name: this.collection ? this.collection.name : '',
                                defaultLink: `collections/${this.collectionId}/items`,
                                label: this.$i18n.get('collections')
                            },
                            childEntity: {
                                rootLink: `collections/${this.collectionId}/items/`,
                                name: this.item.title,
                                defaultLink: `collections/${this.collectionId}/items/${this.item.id}/edit`,
                                label: this.$i18n.get('items')
                            }
                        }
                    );

                    // Checks if user has permission to edit
                    if (!this.item.current_user_can_edit)
                        this.$router.push(this.$routerHelper.getCollectionPath(this.collectionId));

                    // Fills hook forms with it's real values
                    nextTick()
                        .then(() => {
                            this.updateExtraFormData(this.item);
                        });

                    // Fill this.form data with current data.
                    this.form.slug = this.item.slug;
                    this.form.author_id = this.item.author_id;
                    this.form.author_name = this.item.author_name;
                    this.form.status = this.item.status;
                    this.form.document = this.item.document;
                    this.form.document_type = this.item.document_type;
                    this.form.document_options = this.item.document_options;
                    this.form.comment_status = this.item.comment_status;
                    this.form.thumbnail_id = this.item.thumbnail_id;
                    this.form.thumbnail_alt = this.item.thumbnail_alt ? this.item.thumbnail_alt : '';

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
                    
                    /**
                     * Fires action tainacan_item_edition_item_loaded
                     * once the existing item is loaded. We cannot reliabilily send collection here since
                     * it is loaded async outside of the component.
                     */ 
                    wp.hooks.doAction(
                        'tainacan_item_edition_item_loaded',
                        this.collection ? JSON.parse(JSON.stringify(this.collection)) : false,
                        this.item ? JSON.parse(JSON.stringify(this.item)) : false
                    );

                    this.loadMetadata();
                    this.setLastUpdated(this.item.modification_date);

                    this.shouldLoadAttachments = !this.shouldLoadAttachments;

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
            nextTick(() => {
                this.$emitter.emit('itemEditionFormResize');
                if (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth)
                    this.isMobileScreen = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= 768;
            });
        }, 500),
        focusPreviousMetadatum() {
            const previouslyFocusedMetadatum = this.itemMetadata[this.focusedMetadatum - 1];
            const isPreviouslyFocusedOnCompoundMetadatum = previouslyFocusedMetadatum.metadatum && previouslyFocusedMetadatum.metadatum.metadata_type === 'Tainacan\\Metadata_Types\\Compound';
            
            if (isPreviouslyFocusedOnCompoundMetadatum || this.isCurrentlyFocusedOnCompoundMetadatum)
                this.$emitter.emit('focusPreviousChildMetadatum');
                
            if ( !this.isCurrentlyFocusedOnCompoundMetadatum || (this.isCurrentlyFocusedOnCompoundMetadatum && this.isOnFirstMetadatumOfCompoundNavigation) )
                this.setMetadatumFocus({ index: this.focusedMetadatum - 1, scrollIntoView: true });
        },
        focusNextMetadatum() {
            if (this.isCurrentlyFocusedOnCompoundMetadatum && !this.isOnLastMetadatumOfCompoundNavigation)
                this.$emitter.emit('focusNextChildMetadatum');

            if ( !this.isCurrentlyFocusedOnCompoundMetadatum || (this.isCurrentlyFocusedOnCompoundMetadatum && this.isOnLastMetadatumOfCompoundNavigation) )
                this.setMetadatumFocus({ index: this.focusedMetadatum + 1, scrollIntoView: true });  
        },
        setMetadatumFocus({ index = 0, scrollIntoView = false }) {

            const previousIndex = this.focusedMetadatum;
            this.focusedMetadatum = index;

            if (previousIndex === index && !scrollIntoView)
                return;

            let fieldElement = this.$refs['tainacan-form-item--' + index] && this.$refs['tainacan-form-item--' + index][0] && this.$refs['tainacan-form-item--' + index][0]['$el'];
            if (fieldElement) {
                
                const isInsideCompound = fieldElement.classList.contains('tainacan-metadatum-component--tainacan-compound');
                if (!isInsideCompound) {

                    const inputElement = (fieldElement.getElementsByTagName('input')[0] || fieldElement.getElementsByTagName('select')[0] || fieldElement.getElementsByTagName('textarea')[0]);
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
                                        block: 'center'
                                    });
                                }, 300);
                            }

                        }, 100);
                    }
                }
            }
        },
        exitToMobileApp() {
            // In Mobile app, we send a message to inform updates
            if (
                this.$adminOptions.mobileAppMode &&
                webkit &&
                webkit.messageHandlers &&
                webkit.messageHandlers.cordova_iab
            )
                webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({ 'type': 'exited_from_navigation', 'item': JSON.parse(JSON.stringify(this.item)) }));
        },
        isSectionHidden(sectionId) {
            return this.conditionalSections[sectionId] && this.conditionalSections[sectionId].hide;
        },
        getMetadatumOrderInSection(sectionIndex, metadatum) {

            if ( !this.collection || !Array.isArray(this.collection['metadata_section_order']) || !this.collection['metadata_section_order'][sectionIndex] || !Array.isArray(this.collection['metadata_section_order'][sectionIndex]['metadata_order']) )
                return -1;

            let enabledMetadata = [];
            for (let metadatum of this.collection['metadata_section_order'][sectionIndex]['metadata_order']) {
                if ( metadatum.enabled )
                    enabledMetadata.push(metadatum.id);
            }

            return enabledMetadata.indexOf(metadatum.id);
        },
        prepareErrors(errors) {
            if ( errors.errors ) {
                for (let error of errors.errors) {
                    for (let metadatumId of Object.keys(error)) {

                        let parentCompoundId = false;

                        for (let itemMetadatum of this.itemMetadata) {
                            
                            if (
                                itemMetadatum.metadatum.metadata_type == 'Tainacan\\Metadata_Types\\Compound' &&
                                itemMetadatum.metadatum.metadata_type_options &&
                                itemMetadatum.metadatum.metadata_type_options.children_objects
                            ) {

                                for (let childMetadatum of itemMetadatum.metadatum.metadata_type_options.children_objects) {
                                    if ( childMetadatum.id == metadatumId ) {
                                        parentCompoundId = itemMetadatum.metadatum.id;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        this.errors.push({
                            metadatum_id: parentCompoundId ? parentCompoundId : metadatumId,
                            errors: error[metadatumId]
                        });
                    }   
                }
                this.formErrorMessage = errors.error_message;
            }
        }
    }
}
</script>

<style lang="scss">

    .tainacan-admin-collection-mobile-app-mode {
        .page-container.item-edition-container,
        .page-container.item-creation-container {
            padding-top: 0px;
            height: 100%;

            .tainacan-form > .columns {
                margin-left: 0px;
                margin-right: 0px;
            }
        }
        .column.main-column {
            padding-top: 0.75em !important;
            padding-right: 0px !important;
            padding-left: 0px !important;
        }
        .b-tabs {
            #tainacanTabsSwiper {
                background-color: var(--tainacan-gray1);
                --tainacan-background-color: var(--tainacan-gray1);
                position: sticky;
                top: 56px;
                margin-top: 0px;
            }
        }
        .footer {
            background-color: transparent !important;
            pointer-events: none;

            .item-edition-footer-dropdown {
                pointer-events: all;
            }
            .button {
                pointer-events: all;
                box-shadow: 2px 2px 12px -8px var(--tainacan-gray5) !important;
            }
        }
    }

    .page-container.item-edition-container,
    .page-container.item-creation-container {
        padding: 0px;
        transition: none;

        & > .tainacan-form {
            margin-bottom: 52px;

            .field:not(:last-child) {
                margin-bottom: 0em;
            }

            hr {
                height: 1px;
                margin: 1.25em 0 0.75em;
                background: var(--tainacan-gray0);
            }
        }

        .tainacan-page-title {
            margin-top: var(--tainacan-container-padding);
            padding: 0 var(--tainacan-one-column);
        
            .status-tag {
                color: var(--tainacan-secondary);
                background: var(--tainacan-primary);
                padding: 0.15em 0.5em;
                font-size: 0.75em;
                margin: 0 1em 0 0;
                font-weight: 600;
                position: relative;
                top: -2px;
                border-radius: 2px;
            }

            @media screen and (max-width: 769px) {
                padding: 0 0.5em;
                margin-bottom: 1.25rem !important;
            }
        }
        .tainacan-form > .columns {
            margin: 0 var(--tainacan-one-column);
            
            .column.secondary-column {
                padding-top: 0;
                padding-left: var(--tainacan-one-column);
                padding-right: 0;
                padding-bottom: 0;

                @media screen and (min-width: 770px) {
                    .sticky-container {
                        position: relative;
                        position: sticky;
                        top: 0px;
                        margin: 0;
                        max-height: calc(100vh - 52px - var(--tainacan-admin-header-height, 3.25em) - var(--wp-admin--admin-bar--height, 32px) - var(--tainacan-page-container-margin-top, 1rem) - var(--tainacan-breadcumbs-list-height, 1rem) - var(--tainacan-page-container--inner-padding-y, 1rem));
                        max-height: calc(100dvh - 52px - var(--tainacan-admin-header-height, 3.25em) - var(--wp-admin--admin-bar--height, 32px) - var(--tainacan-page-container-margin-top, 1rem) - var(--tainacan-breadcumbs-list-height, 1rem) - var(--tainacan-page-container--inner-padding-y, 1rem));
                        overflow-y: auto;
                        overflow-x: hidden;
                    }
                }
            }
            .column.main-column {
                padding-top: 0;
                padding-left: 0;
                padding-right: 0;

                .columns {
                    flex-wrap: wrap;
                    justify-content: space-between;

                    .column {
                        padding: 0.75em 12px 0 12px;
                    }
                }

                .metadata-section-metadata-list {
                    margin-bottom: 2rem;
                    
                    .field {
                        padding: 12px 0px 12px 42px;
                        margin-left: 10px;
                    }
                }
                .metadata-section-description-help-info {
                    margin: 0.25em 0 0 1.125rem;
                }
                .item-edition-tab-content .tab-item>.field:last-child {
                    margin-bottom: 187px;
                }

                @media screen and (max-width: 769px) {
                    padding-right: var(--tainacan-one-column);
                    max-width: 100%;

                    #tainacanTabsSwiper.tabs a {
                        padding: 0.75em 1.45em;
                    }
                }
            }

            @media screen and (max-width: 769px) {
                margin-left: 0;
                margin-right: 0;

                &>.column.main-column {
                    padding-left: 0;
                    padding-right: 0;
                    padding-top: 1.75em;
                    max-width: 100%;
                    width: 100%;

                    .metadata-section-metadata-list {

                        .sub-header {
                            padding-right: 0.5em;
                        }
                        .field {
                            padding: 1em 0.75em;
                        }
                        .field.has-collapses-hidden {
                            padding: 1em 0.75em !important;
                        }
                    }
                    .metadata-section-header {
                        padding-left: 0.75em;
                        padding-right: 0.75em;

                        .collapse-handle>.icon {
                            float: right;
                        }
                    }
                    .item-edition-tab-content {
                        padding-left: 0;
                        padding-right: 0;
                    }
                    .item-edition-tab-content .tab-item>.field:last-child {
                        margin-bottom: 24px;
                    }
                    &>.columns {
                        display: flex;
                    }
                }
                &>.column:not(.main-column) {
                    max-width: 100%;
                    width: 100%;
                    padding-left: 0;
                    padding-right: 0;

                    .section-box {
                        padding: 0 1em 0 1.875em;
                        margin-top: 10px;
                    }
                }
            }
        }

        .section-label {
            position: relative;
            padding: 0.5em 0.75em 0.5em 0em;
            label {
                font-size: 1em !important;
                font-weight: 500 !important;
                color: var(--tainacan-label-color) !important;
                line-height: 1.2em;
            }
        }
        .section-box+.section-label {
            border-top: 1px solid var(--tainacan-gray1);
            padding-top: 6px;
        }

        .sub-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.25rem 1rem;
            flex-wrap: wrap;
            background-color: var(--tainacan-background-color);
            
            .field {
                padding: 2px 0px !important;
            }

            &.is-metadata-navigation-active {
                width: calc(58.33333337% - (2 * var(--tainacan-one-column)) );
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
                    top: 0.5em;
                    max-width: 210px;
                }

                @media screen and (max-width: 1440px) {
                    width: calc(58.33333337% - var(--tainacan-one-column));
                }
            }

            .metadata-navigation {
                margin-right: auto;
            }
            .metadata-navigation :deep(.button) {
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

            @supports (contain: inline-size) {
                container-type: inline-size;
                container-name: itemeditionsubheader; 
            }
            @container itemeditionsubheader (max-width: 620px) {
                .metadata-name-search {
                    max-width: 160px;
                }
            }
            @media screen and (max-width: 769px) {
                .metadata-name-search {
                    position: absolute;
                    right: 0.5em;
                    z-index: 999;
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
                .field {
                    padding: 2px 0px 2px 3px !important;
                }
            }
        }

        .collapse-all {
            font-size: 0.75em;
            white-space: nowrap;
            background: transparent;
            border-color: transparent !important;

            .icon {
                font-size: 1.35em;
            }
        }

        .metadata-section-header {
            padding: 0.75em 0em;
            border-bottom: 1px solid var(--tainacan-input-border-color);
        }

        .metadata-section-hidden {
            opacity: 0.5;
            filter: grayscale(1.0);
        }

        .metadata-section-hidden > * {
            pointer-events: none;
        }

        .item-edition-tab-content {
            border-top: 1px solid var(--tainacan-input-border-color);
        }
        .swiper {
            width: 100%;
            position: relative;
            margin: 0;
            --swiper-navigation-size: 2em;
            --swiper-navigation-color: var(--tainacan-secondary);
            margin-top: 8px;
            
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
            .swiper-rtl .swiper-button-next::after {
                content: 'previous';
            }
            .swiper-button-next,
            .swiper-rtl .swiper-button-prev {
                right: 0;
                background-image: linear-gradient(90deg, rgba(255,255,255,0) 0%, var(--tainacan-background-color) 40%);
            }
            .swiper-button-prev,
            .swiper-rtl .swiper-button-next {
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

        .section-box {
            position: relative;
            padding: 0 0.75em 0 1.75em;
            margin-bottom: 16px;
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
            margin-right: 6px !important;

            .icon {
                display: inherit;
                padding: 0;
                margin: 0;
                margin-top: -2px;
                font-size: 1.125em;
            }
        }

        .metadata-section-metadata-list .metadatum-description-help-info {
            padding: 0rem 1rem 0.5rem 0.125rem;
        }

        .related-items-list-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
            margin-bottom: var(--tainacan-container-padding);
            p {
                color: var(--tainacan-info-color);
            }
            @media screen and (max-width: 768px) {
                flex-wrap: wrap;
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
            padding: 10px var(--tainacan-one-column);
            position: absolute;
            bottom: 0;
            right: 0;
            z-index: 1001;
            background-color: var(--tainacan-gray1);
            width: 100%;
            height: 52px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            transition: bottom 0.5s ease, width 0.2s linear;
            box-shadow: 0px 0px 12px -8px var(--tainacan-black);

            &::after,
            &::before {
                height: 18px;
                width: 18px;
                background: transparent;
                display: block;
                content: '';
                position: absolute;
            }
            &::before {
                left: 0;
                top: -18px;
                border-bottom-left-radius: 9px;
                box-shadow: -9px 0px 0 0 var(--tainacan-gray1), inset 2px -2px 5px -3px var(--tainacan-gray2)
            }
            &::after {
                right: 0;
                top: -18px;
                border-bottom-right-radius: 9px;
                box-shadow: 9px 0px 0 0 var(--tainacan-gray1), inset -2px -2px 5px -3px var(--tainacan-gray2)
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
                display: flex;
                flex-wrap: nowrap;
            }

            .help {
                display: inline-flex;
                font-size: 1.0em;
                margin-top: 0;
                margin-left: 24px;

                .tainacan-help-tooltip-trigger {
                    margin-left: 0.25em;
                }
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
                    background-color: color-mix(in srgb, var(--tainacan-white) 60%, transparent) !important;
                    color: var(--tainacan-secondary) !important;
                }
            }

            &.has-some-metadatum-focused {
                bottom: -300px;
            }

            @media screen and (max-width: 769px) {
                padding: 13px 0.5em;
                width: 100%;
                flex-wrap: wrap;
                height: auto;
                position: fixed;

                .update-info-section {
                    margin-left: auto;margin-bottom: 0.75em;
                    margin-top: -0.25em;
                }
            }
        }

        .tainacan-mobile-app-header {
            padding: 0 1rem;
            background: var(--tainacan-gray1);
            color: var(--tainacan-gray5);
            font-weight: bold;
            font-size: 1.125rem;
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0px;
            z-index: 99999;

            h1 {
                padding: 0.5em 1em;
                text-overflow: ellipsis;
                overflow: hidden;
                white-space: nowrap;
            }
            button {
                padding: 0px;
                margin: 0px;
                border: none;
                background: none;
            }
            svg {
                display: flex;
            }
        }

        .tainacan-mobile-app-header_panel {
            padding: 0 1rem 1rem 1rem;
            background: var(--tainacan-gray1);
            font-size: 0.875rem;
            font-weight: normal;
            position: absolute;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            z-index: 99998;

            h1 {
                font-size: 1.45em;
                padding: 0.5em 0em 0.25em 0em;
                text-overflow: initial;
                overflow: visible;
                white-space: normal;
                text-align: start;
            }

            a {
                margin: 0 auto;
            }

            .tainacan-mobile-app-header_panel-shortcuts-area {
                margin-top: 1.25em;
                text-align: left;
                
                h2 {
                    font-size: 1.125em;
                    padding: 0.5em 0em;
                    display: inline;
                }
            }

            .tainacan-mobile-app-header_panel-shortcuts {
                display: grid;
                grid-template-columns: 1fr 1fr;
                grid-template-rows: 1fr 1fr;
                width: 100%;
                gap: 1em;
                margin: 0.75em auto 1.875em auto;
                
                &>button {
                    border: 1px solid var(--tainacan-background-color); 
                    background: var(--tainacan-background-color);
                    padding: 1em;
                    display: flex;
                    align-items: center;
                    justify-content: space-evenly;
                    flex-direction: column;
                    border-radius: 1px;

                    &:hover,
                    &:focus,
                    &:active {
                        border-color: var(--tainacan-secondary);
                    }
                }
            }
        }
        .tainacan-mobile-app-header_panel-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(50,50,50,0.5);
            z-index: 99998;
        }
    }

</style>
