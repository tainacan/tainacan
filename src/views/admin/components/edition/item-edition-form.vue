<template>
    <div :class="isCreatingNewItem ? 'item-creation-container' : 'item-edition-container'"><!-- Do NOT remove this classes, they may be used by third party plugins -->
        <b-loading
                :is-full-page="false"
                :active.sync="isLoading"
                :can-cancel="false"/>

        <tainacan-title 
                v-if="!$adminOptions.hideItemEditionPageTitle || ($adminOptions.hideItemEditionPageTitle && isEditingItemMetadataInsideIframe)"
                :bread-crumb-items="[{ path: '', label: $i18n.get('item') }]">
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
                        v-if="(item != null && item != undefined && item.status != undefined && item.status != 'autodraft' && !isLoading)"
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
                                    fill="none"/>
                            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
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
                    v-if="!formErrors.length || isUpdatingValues"
                    @click="isMobileSubheaderOpen = !isMobileSubheaderOpen">
                <span 
                        v-if="isUpdatingValues"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-updating tainacan-icon-spin"/>
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
                                    fill="none"/>
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                        </svg>
                    </i>
                </span>
            </button>
            <item-metadatum-errors-tooltip 
                    v-else
                    :form-errors="formErrors" />
        </div>

        <transition name="item-appear">
            <div 
                    v-if="isMobileSubheaderOpen"
                    @click="isMobileSubheaderOpen = false;"
                    class="tainacan-mobile-app-header_panel-backdrop" />
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
                <span v-if="!formErrors.length">{{ ($i18n.get('info_updated_at') + ' ' + lastUpdated) }}</span>
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
                                v-if="!$adminOptions.hideItemEditionDocument"
                                @click="activeTab = 'document'; isMobileSubheaderOpen = false;">
                            <span><i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-item" /></span>
                            <span>{{ $i18n.get('label_document_and_thumbnail') }}</span>
                        </button>
                        <button 
                                v-if="!$adminOptions.hideItemEditionAttachments"
                                @click="activeTab = 'attachments'; isMobileSubheaderOpen = false;">
                            <span><i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-attachments" /></span>
                            <span>{{ $i18n.get('label_all_attachments') }}</span>
                        </button>
                        <button 
                                v-if="!$adminOptions.hideItemEditionRequiredOnlySwitch"
                                @click="showOnlyRequiredMetadata = true; isMobileSubheaderOpen = false;">
                            <span><i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-metadata" /></span>
                            <span>{{ $i18n.get('label_only_required_metadata') }}</span>
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
                <div class="columns">

                    <div
                            class="column main-column"
                            :class="
                                (( (!$adminOptions.hideItemEditionDocument || !$adminOptions.hideItemEditionThumbnail) && !$adminOptions.itemEditionDocumentInsideTabs) ||
                                (!$adminOptions.hideItemEditionAttachments && !$adminOptions.itemEditionAttachmentsInsideTabs)) ? 'is-7' : 'is-12'">

                        <!-- Hook for extra Form options -->
                        <template v-if="hasBeginRightForm">
                            <form
                                id="form-item-begin-right"
                                class="form-hook-region"
                                v-html="getBeginRightForm"/>
                        </template>

                        <div class="b-tabs">
                            <nav 
                                    v-if="tabs.length >= 2"
                                    id="tainacanTabsSwiper"       
                                    class="swiper tabs">
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
                                            viewBox="0 0 32 32">
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
                                                v-if="isMetadataNavigation && itemMetadata && itemMetadata.length > 3"
                                                class="sequence-progress-background" />
                                        <div
                                                v-if="isMetadataNavigation && focusedMetadatum !== false && itemMetadata && itemMetadata.length > 3"
                                                :style="{ width: ((focusedMetadatum + 1)/itemMetadata.length)*100 + '%' }"
                                                class="sequence-progress" />

                                        <a
                                                v-if="!isMetadataNavigation && !$adminOptions.hideItemEditionCollapses"
                                                class="collapse-all"
                                                @click="toggleCollapseAll()">
                                            <span class="icon">
                                                <i
                                                        :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll }"
                                                        class="tainacan-icon tainacan-icon-1-25em"/>
                                            </span>
                                            <template v-if="isMobileScreen">{{ collapseAll ? $i18n.get('label_collapse') : $i18n.get('label_expand') }}</template>
                                            <template v-else>{{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}</template>
                                        </a>

                                        <b-field 
                                                v-if="itemMetadata && itemMetadata.length > 3"
                                                class="header-item metadata-navigation"
                                                :style="$adminOptions.hideItemEditionCollapses ? 'padding-left: 0.35em !important;' : ''">
                                            <b-button
                                                    v-if="!$adminOptions.hideItemEditionFocusMode && !isMetadataNavigation && !showOnlyRequiredMetadata && !metadataNameFilterString" 
                                                    @click="isMetadataNavigation = true; setMetadatumFocus({ index: 0, scrollIntoView: true });"
                                                    class="collapse-all has-text-secondary"
                                                    size="is-small">
                                                <span
                                                        class="icon">
                                                    <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-play" />
                                                </span>
                                                <span>{{ isMobileScreen ? $i18n.get('label_focus_mode') : $i18n.get('label_start_focus_mode') }}</span>
                                            </b-button>
                                            <b-button 
                                                    v-if="isMetadataNavigation"
                                                    :disabled="focusedMetadatum === 0"
                                                    @click="focusPreviousMetadatum" 
                                                    outlined>
                                                <span
                                                        class="icon">
                                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-showmore tainacan-icon-rotate-180" />
                                                </span>
                                                <span>{{ $i18n.get('previous') }}</span>
                                            </b-button>
                                            <b-button 
                                                    v-if="isMetadataNavigation"
                                                    :disabled="(focusedMetadatum === itemMetadata.length - 1) && (!isCurrentlyFocusedOnCompoundMetadatum || isOnLastMetadatumOfCompoundNavigation)"
                                                    @click="focusNextMetadatum"
                                                    outlined>
                                                <span
                                                        class="icon">
                                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-showmore" />
                                                </span>
                                                <span>{{ $i18n.get('next') }}</span>
                                            </b-button>
                                            <b-button
                                                    v-if="isMetadataNavigation" 
                                                    @click="setMetadatumFocus({ index: 0, scrollIntoView: true }); isMetadataNavigation = false;"
                                                    outlined>
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
                                                <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-updating"/>
                                            </span>
                                        </span>

                                        <b-switch
                                                v-if="!isMetadataNavigation && !$adminOptions.hideItemEditionRequiredOnlySwitch && itemMetadata && itemMetadata.length > 3"
                                                id="tainacan-switch-required-metadata"
                                                :style="'font-size: 0.625em;' + (isMobileScreen ? 'margin-right: 2rem;' : '')"
                                                size="is-small"
                                                v-model="showOnlyRequiredMetadata">
                                            {{ isMobileScreen ? $i18n.get('label_required') : $i18n.get('label_only_required') }} *
                                        </b-switch>

                                        <b-field 
                                                v-if="!isMetadataNavigation && itemMetadata && itemMetadata.length > 5"
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

                                    <div 
                                            v-for="(metadataSection, sectionIndex) of metadataSections"
                                            :key="sectionIndex"
                                            :class="'metadata-section-slug-' + metadataSection.slug + (isSectionHidden(metadataSection.id) ? ' metadata-section-hidden' : '')"
                                            :id="'metadata-section-id-' + metadataSection.id"
                                            v-tooltip="{
                                                content: isSectionHidden(metadataSection.id) ? $i18n.get('info_metadata_section_hidden_conditional') : false,
                                                autoHide: true,
                                                placement: 'auto',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }">
                                        <div class="metadata-section-header section-label">
                                            <span   
                                                    class="collapse-handle"
                                                    @click="(isMetadataNavigation || $adminOptions.hideItemEditionCollapses || isSectionHidden(metadataSection.id)) ? null : toggleMetadataSectionCollapse(sectionIndex)">
                                                <span 
                                                        v-if="!$adminOptions.hideItemEditionCollapses && !isMetadataNavigation"
                                                        class="icon">
                                                    <i 
                                                            :class="{
                                                                'tainacan-icon-arrowdown' : (metadataSectionCollapses[sectionIndex] || errorMessage) && !isSectionHidden(metadataSection.id),
                                                                'tainacan-icon-arrowright' : !(metadataSectionCollapses[sectionIndex] || errorMessage) || isSectionHidden(metadataSection.id)
                                                            }"
                                                            class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
                                                </span>
                                                <label>
                                                    <span class="icon has-text-gray4">
                                                        <i class="tainacan-icon tainacan-icon-metadata"/>
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
                                                        v-if="metadataSection.description" 
                                                        :title="metadataSection.name"
                                                        :message="metadataSection.description" />
                                            </span>
                                        </div>
                                        <transition name="filter-item">
                                            <div 
                                                    class="metadata-section-metadata-list"
                                                    v-show="(metadataSectionCollapses[sectionIndex] || isMetadataNavigation) && !isSectionHidden(metadataSection.id)">
                                                <p
                                                        class="metadatum-description-help-info"
                                                        v-if="metadataSection.description && metadataSection.description_bellow_name == 'yes'">
                                                    {{ metadataSection.description }}
                                                </p>

                                                <template v-for="(itemMetadatum, index) of itemMetadata">
                                                    <tainacan-form-item
                                                            v-if="itemMetadatum.metadatum.metadata_section_id == metadataSection.id"
                                                            :key="index"
                                                            :id="'metadatum-index--' + index"
                                                            v-show="(!showOnlyRequiredMetadata || itemMetadatum.metadatum.required === 'yes') && (metadataNameFilterString == '' || filterByMetadatumName(itemMetadatum))"      
                                                            :class="{ 'is-metadata-navigation-active': isMetadataNavigation }"
                                                            :ref="'tainacan-form-item--' + index"
                                                            :item-metadatum="itemMetadatum"
                                                            :metadata-name-filter-string="metadataNameFilterString"
                                                            :is-collapsed="metadataCollapses[index]"
                                                            :hide-collapses="$adminOptions.hideItemEditionCollapses || isMetadataNavigation"
                                                            :hide-metadata-types="hideMetadataTypes"
                                                            :hide-help-buttons="false"
                                                            :help-info-bellow-label="false"
                                                            :is-mobile-screen="isMobileScreen"
                                                            :is-last-metadatum="index > 2 && (index == itemMetadata.length - 1)"
                                                            :is-focused="focusedMetadatum === index"
                                                            :is-metadata-navigation="isMetadataNavigation"
                                                            @changeCollapse="onChangeCollapse($event, index)"
                                                            @touchstart.native="isMetadataNavigation ? setMetadatumFocus({ index: index, scrollIntoView: false }): ''"
                                                            @mousedown.native="isMetadataNavigation ? setMetadatumFocus({ index: index, scrollIntoView: false }) : ''"
                                                            @mobileSpecialFocus="setMetadatumFocus({ index: index, scrollIntoView: true })" />
                                                </template>
                                            </div>
                                        </transition>
                                    </div>

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

                                    <div class="related-items-list-heading">
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
                                            @onSetDocument="setDocument"
                                            @onRemoveDocument="removeDocument"
                                            @onSetFileDocument="setFileDocument"
                                            @onSetTextDocument="setTextDocument"
                                            @onSetURLDocument="setURLDocument" />
                                    <item-thumbnail-edition-form 
                                            :item="item"
                                            :form="form"
                                            :is-loading="isLoading"
                                            @onDeleteThumbnail="deleteThumbnail"
                                            @onUpdateThumbnailAlt="($event) => onUpdateThumbnailAlt($event)"
                                            @openThumbnailMediaFrame="thumbnailMediaFrame.openFrame($event)" />
                                </div>

                                <!-- Attachments on mobile modal -->
                                <div    
                                        v-if="activeTab === 'attachments' && $adminOptions.itemEditionAttachmentsInsideTabs"
                                        class="tab-item"
                                        role="tabpanel"
                                        aria-labelledby="attachments-tab-label"
                                        tabindex="0"> 
                                    <item-attachments-edition-form
                                            :item="item"
                                            :form="form"
                                            :is-loading="isLoading"
                                            :total-attachments="totalAttachments"
                                            :should-load-attachments="shouldLoadAttachments"
                                            @openAttachmentsMediaFrame="($event) => attachmentsMediaFrame.openFrame($event)"
                                            @onDeleteAttachment="deleteAttachment($event)" />
                                </div>

                            </section>
                        </div>

                    </div>

                    <div 
                            v-if="( (!$adminOptions.hideItemEditionDocument || !$adminOptions.hideItemEditionThumbnail) && !$adminOptions.itemEditionDocumentInsideTabs) ||
                                (!$adminOptions.hideItemEditionAttachments && !$adminOptions.itemEditionAttachmentsInsideTabs)"
                            class="column is-5">
                
                        <div 
                                :style="isMetadataNavigation && !isMobileScreen ? 'max-height: calc(100vh - 142px);' : ''"
                                class="sticky-container">

                            <!-- Hook for extra Form options -->
                            <template v-if="hasBeginLeftForm">
                                <form
                                        id="form-item-begin-left"
                                        class="form-hook-region"
                                        v-html="getBeginLeftForm"/>
                            </template>

                            <!-- Document -------------------------------- -->
                            <item-document-edition-form 
                                    v-if="!$adminOptions.itemEditionDocumentInsideTabs"
                                    :item="item"
                                    :form="form"
                                    @onSetDocument="setDocument"
                                    @onRemoveDocument="removeDocument"
                                    @onSetFileDocument="setFileDocument"
                                    @onSetTextDocument="setTextDocument"
                                    @onSetURLDocument="setURLDocument" />
                            <hr>

                            <!-- Thumbnail -------------------------------- -->
                            <item-thumbnail-edition-form 
                                    v-if="!$adminOptions.itemEditionDocumentInsideTabs"
                                    :item="item"
                                    :form="form"
                                    :is-loading="isLoading"
                                    @onDeleteThumbnail="deleteThumbnail"
                                    @onUpdateThumbnailAlt="($event) => onUpdateThumbnailAlt($event)"
                                    @openThumbnailMediaFrame="thumbnailMediaFrame.openFrame($event)" />

                            <hr v-if="!$adminOptions.itemEditionAttachmentsInsideTabs || hasEndLeftForm">

                            <!-- Attachments -->
                            <item-attachments-edition-form
                                    v-if="!$adminOptions.itemEditionAttachmentsInsideTabs"
                                    :item="item"
                                    :form="form"
                                    :is-loading="isLoading"
                                    :total-attachments="totalAttachments"
                                    :should-load-attachments="shouldLoadAttachments"
                                    @openAttachmentsMediaFrame="($event) => attachmentsMediaFrame.openFrame($event)"
                                    @onDeleteAttachment="deleteAttachment($event)" />

                            <hr v-if="hasEndLeftForm">

                            <!-- Hook for extra Form options -->
                            <template v-if="hasEndLeftForm">
                                <form
                                    id="form-item-end-left"
                                    class="form-hook-region"
                                    v-html="getEndLeftForm"/>
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
            <div 
                    v-if="!$adminOptions.mobileAppMode"
                    class="update-info-section">
                <p
                        class="footer-message"
                        v-if="isOnSequenceEdit">
                    {{ $i18n.get('label_sequence_editing_item') + " " + itemPosition + " " + $i18n.get('info_of') + " " + ((group != null && group.items_count != undefined) ? group.items_count : '') + "." }}&nbsp;
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

                <!-- Comment Status ------------------------ -->
                <div 
                        style="margin-left: 2em;"
                        class="section-status"
                        v-if="collection && collection.allow_comments && collection.allow_comments == 'open' && !$adminOptions.hideItemEditionCommentsToggle">
                    <div class="field has-addons">
                        <b-switch
                                id="tainacan-checkbox-comment-status"
                                size="is-small"
                                true-value="open"
                                false-value="closed"
                                v-model="form.comment_status">
                            <span class="icon has-text-gray4">
                                <i class="tainacan-icon tainacan-icon-comment"/>
                            </span>
                            {{ $i18n.get('label_allow_comments') }}
                            <help-button
                                    :title="$i18n.getHelperTitle('items', 'comment_status')"
                                    :message="$i18n.getHelperMessage('items', 'comment_status')"/>
                        </b-switch>
                    </div>
                </div>
            </div>
            
            <item-form-footer-buttons
                    :status="form.status"
                    :collection-id="form.collectionId"
                    :is-on-sequence-edit="isOnSequenceEdit"
                    :is-current-item-on-sequence-edit="(group != null && group.items_count != undefined && group.items_count == itemPosition)"
                    :has-next-item-on-sequence-edit="(group != null && group.items_count != undefined && group.items_count < itemPosition)"
                    :has-previous-item-on-sequence-edit="itemPosition > 1"
                    :is-mobile-screen="isMobileScreen"
                    :has-some-error="formErrorMessage != undefined && formErrorMessage != ''"
                    :current-user-can-delete="item && item.current_user_can_delete"
                    :current-user-can-publish="collection && collection.current_user_can_publish_items"
                    :is-editing-item-metadata-inside-iframe="isEditingItemMetadataInsideIframe"
                    @onSubmit="onSubmit"
                    @onDiscard="onDiscard"
                    @onPrevInSequence="onPrevInSequence"
                    @onNextInSequence="onNextInSequence" />

        </footer>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

import { eventBusItemMetadata } from '../../js/event-bus-item-metadata';
import wpMediaFrames from '../../js/wp-media-frames';
import { formHooks } from '../../js/mixins';

import RelatedItemsList from '../lists/related-items-list.vue';
import CustomDialog from '../other/custom-dialog.vue';
import ItemMetadatumErrorsTooltip from '../other/item-metadatum-errors-tooltip.vue';
import ItemDocumentTextModal from '../modals/item-document-text-modal.vue';
import ItemDocumentURLModal from '../modals/item-document-url-modal.vue';
import ItemDocumentEditionForm from '../edition/item-document-edition-form.vue';
import ItemThumbnailEditionForm from '../edition/item-thumbnail-edition-form.vue';
import ItemAttachmentsEditionForm from '../edition/item-attachments-edition-form.vue';
import ItemFormFooterButtons from './item-form-footer-buttons.vue';

import 'swiper/css';
import 'swiper/css/mousewheel';
import 'swiper/css/navigation';
import Swiper, { Mousewheel, Navigation } from 'swiper';

export default {
    name: 'ItemEditionForm',
    components: {
        RelatedItemsList,
        ItemMetadatumErrorsTooltip,
        ItemThumbnailEditionForm,
        ItemDocumentEditionForm,
        ItemAttachmentsEditionForm,
        ItemFormFooterButtons
    },
    mixins: [ formHooks ],
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
    data(){
        return {
            swiper: {},
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
            isLoadingMetadataSections: false,
            metadataCollapses: [],
            metadataSectionCollapses: [],
            collapseAll: true,
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
        collection() {
            return this.getCollection()
        },
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
        metadataSections() {
            return this.getMetadataSections();
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
        conditionalSections() {
            return eventBusItemMetadata && eventBusItemMetadata.conditionalSections ? eventBusItemMetadata.conditionalSections : [];
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
            if ( this.$adminOptions.itemEditionDocumentInsideTabs && (!this.$adminOptions.hideItemEditionDocument || !this.$adminOptions.hideItemEditionThumbnail) ) {
                pageTabs.push({
                    slug: 'document',
                    icon: 'item',
                    name: this.$i18n.get('label_document')
                });
            }
            if ( this.$adminOptions.itemEditionAttachmentsInsideTabs && !this.$adminOptions.hideItemEditionAttachments ) {
                pageTabs.push({
                    slug: 'attachments',
                    icon: 'attachments',
                    name: this.$i18n.get('label_attachments'),
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
        },
        tabs:{
            handler() {
                if (this.tabs.length >= 2) {
                    if (typeof this.swiper.update == 'function')
                        this.swiper.update();
                    else {
                        this.$nextTick(() => {
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
            immediate: true
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
                eventBusItemMetadata.conditionalSections = {};
                for (let metadataSection of metadataSections) {
                    if ( metadataSection.is_conditional_section == 'yes') { 
                        const conditionalSectionId = Object.keys(metadataSection.conditional_section_rules);
                        if ( conditionalSectionId.length ) {
                            eventBusItemMetadata.conditionalSections[metadataSection.id] = {
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

        // Updates variables for metadata navigation from compound childs
        eventBusItemMetadata.$on('isOnFirstMetadatumOfCompoundNavigation', (isOnFirstMetadatumOfCompoundNavigation) => {
            this.isOnFirstMetadatumOfCompoundNavigation = isOnFirstMetadatumOfCompoundNavigation
        });
        eventBusItemMetadata.$on('isOnLastMetadatumOfCompoundNavigation', (isOnLastMetadatumOfCompoundNavigation) => {
            this.isOnLastMetadatumOfCompoundNavigation = isOnLastMetadatumOfCompoundNavigation
        });

        // Listens to window resize event to update responsiveness variable
        this.handleWindowResize();
        window.addEventListener('resize', this.handleWindowResize);

        // If we're in the mobile app, show panel
        if (this.$adminOptions.mobileAppMode)
            this.isMobileSubheaderOpen = true;
    },
    beforeDestroy () {
        eventBusItemMetadata.$off('isUpdatingValue');
        eventBusItemMetadata.$off('hasErrorsOnForm');
        eventBusItemMetadata.$off('isOnFirstMetadatumOfCompoundNavigation');
        eventBusItemMetadata.$off('isOnLastMetadatumOfCompoundNavigation');
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
        ...mapActions('metadata',[
            'fetchMetadataSections'
        ]),
        ...mapGetters('metadata',[
            'getMetadataSections'
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

                if (!this.$adminOptions.itemEditionMode && !this.$adminOptions.mobileAppMode) {

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

                }

                // Sends info to iframe containing item edition form and other use cases
                parent.postMessage({ 
                    type: 'itemEditionMessage',
                    item: this.$adminOptions.itemEditionMode ? this.item : null
                },
                tainacan_plugin.admin_url);

                // In Mobile app, we send a message to inform updates
                if (
                    this.$adminOptions.mobileAppMode &&
                    webkit &&
                    webkit.messageHandlers &&
                    webkit.messageHandlers.cordova_iab
                )
                    webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({ 'type': 'item_updated', 'item': this.item }));
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
                            const conditionalValues = Array.isArray(eventBusItemMetadata.conditionalSections[conditionalSectionId].metadatumValues) ? eventBusItemMetadata.conditionalSections[conditionalSectionId].metadatumValues : [eventBusItemMetadata.conditionalSections[conditionalSectionId].metadatumValues];
                            eventBusItemMetadata.conditionalSections[conditionalSectionId].hide = itemMetadatumValues.every(aValue => conditionalValues.indexOf(aValue) < 0);
                        }
                    }

                    this.isLoading = false;
                });
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
                this.shouldLoadAttachments = !this.shouldLoadAttachments;
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
                            if (item.document_type == 'attachment' && oldThumbnail != item.thumbnail ) {
                                this.item.thumbnail = item.thumbnail;
                                this.item.thumbnail_id = item.thumbnail_id;
                            }

                            this.shouldLoadAttachments = !this.shouldLoadAttachments;

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
            this.attachmentsMediaFrame = new wpMediaFrames.attachmentControl(
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
                this.$set(this.metadataSectionCollapses, i, this.collapseAll);

        },
        onChangeCollapse(event, index) {
            if (event && !this.metadataCollapses[index] && this.itemMetadata[index].metadatum && this.itemMetadata[index].metadatum['metadata_type'] === "Tainacan\\Metadata_Types\\GeoCoordinate")
                eventBusItemMetadata.$emit('itemEditionFormResize');
                
            this.metadataCollapses.splice(index, 1, event);
        },
        toggleMetadataSectionCollapse(sectionIndex) {
            if (!this.isMetadataNavigation) 
                this.$set(this.metadataSectionCollapses, sectionIndex, (this.errorMessage ? true : !this.metadataSectionCollapses[sectionIndex]));
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
            this.$nextTick(() => {
                eventBusItemMetadata.$emit('itemEditionFormResize');
                if (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth)
                    this.isMobileScreen = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= 768;
            });
        }, 500),
        focusPreviousMetadatum() {
            const previouslyFocusedMetadatum = this.itemMetadata[this.focusedMetadatum - 1];
            const isPreviouslyFocusedOnCompoundMetadatum = previouslyFocusedMetadatum.metadatum && previouslyFocusedMetadatum.metadatum.metadata_type === 'Tainacan\\Metadata_Types\\Compound';
            
            if (isPreviouslyFocusedOnCompoundMetadatum || this.isCurrentlyFocusedOnCompoundMetadatum)
                eventBusItemMetadata.$emit('focusPreviousChildMetadatum');
                
            if ( !this.isCurrentlyFocusedOnCompoundMetadatum || (this.isCurrentlyFocusedOnCompoundMetadatum && this.isOnFirstMetadatumOfCompoundNavigation) )
                this.setMetadatumFocus({ index: this.focusedMetadatum - 1, scrollIntoView: true });
        },
        focusNextMetadatum() {
            if (this.isCurrentlyFocusedOnCompoundMetadatum && !this.isOnLastMetadatumOfCompoundNavigation)
                eventBusItemMetadata.$emit('focusNextChildMetadatum');

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
                webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({ 'type': 'exited_from_navigation', 'item': this.item }));
        },
        isSectionHidden(sectionId) {
            return this.conditionalSections[sectionId] && this.conditionalSections[sectionId].hide;
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
        padding: 0px 0px 60px 0px;
        height: calc(100% - 2.35em);
        transition: none;

        &>.tainacan-form {
            margin-bottom: 60px;

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
            padding: 0 var(--tainacan-one-column);
            margin-top: var(--tainacan-container-padding);
        
            .status-tag {
                color: var(--tainacan-white);
                background: var(--tainacan-secondary);
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
            margin-left: var(--tainacan-one-column);
            margin-right: var(--tainacan-one-column);

            .column.is-5 {
                padding-top: 0;
                padding-left: var(--tainacan-one-column);
                padding-right: var(--tainacan-one-column);

                @media screen and (min-width: 770px) {
                    .sticky-container {
                        position: relative;
                        position: sticky;
                        top: 0px;
                        margin: 0;
                        max-height: calc(100vh - 184px);
                        overflow-y: auto;
                        overflow-x: hidden;
                    }
                }
            }
            .column.main-column {
                padding-top: 0;
                padding-left: var(--tainacan-one-column);
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
                        margin-left: 12px;
                    }
                }
                .item-edition-tab-content .tab-item>.field:last-child {
                    margin-bottom: 187px;
                }

                @media screen and (max-width: 769px) {
                    padding-right: var(--tainacan-one-column);
                    max-width: 100%;

                    .metadata-section-metadata-list {
                        .field {
                            margin-left: 0px;
                        }
                    }
                    #tainacanTabsSwiper.tabs a {
                        padding: 0.75em 1.45em;
                    }
                }
            }

            @media screen and (max-width: 1440px) {
                &>.column.main-column {
                    padding-left: 0.75em;
                }
                &>.column:not(.main-column) {
                    padding-right: 0.75em;
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
            background-color: var(--tainacan-background-color);
            
            .field {
                padding: 2px 0px 2px 16px !important;
            }

            &.is-metadata-navigation-active {
                width: calc(58.33333337% - (2 * var(--tainacan-one-column)) - var(--tainacan-sidebar-width, 3.25em));
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
                    max-width: 220px;
                }

                @media screen and (max-width: 1440px) {
                    width: calc(58.33333337% - var(--tainacan-sidebar-width, 3.25em) - var(--tainacan-one-column));
                }
            }

            .metadata-navigation {
                margin-right: auto;
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
                .metadata-name-search {
                    position: absolute;
                    right: 0.5em;
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

            & > {
                pointer-events: none;
            }
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
            padding: 0 1.75em 0 1.75em;
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
            margin-left: 1.5em;
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
            padding: 14px var(--tainacan-one-column);
            position: fixed;
            bottom: 0;
            right: 0;
            z-index: 9999;
            background-color: var(--tainacan-gray1);
            width: calc(100% - var(--tainacan-sidebar-width, 3.25em));
            height: 60px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            transition: bottom 0.5s ease, width 0.2s linear;

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
