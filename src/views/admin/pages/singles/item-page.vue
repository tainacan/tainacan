<template>
    <div>
        <b-loading
                :active.sync="isLoading"
                :can-cancel="false"/>

        <tainacan-title 
                 v-if="!$adminOptions.hideItemSinglePageTitle"
                :bread-crumb-items="[{ path: '', label: $i18n.get('item') }]">
            <h1>
                <span
                        v-if="(item != null && item != undefined && item.status != undefined && !isLoading)"
                        class="status-tag">
                    {{ $i18n.get('status_' + item.status) }}
                </span>
                    {{ $i18n.get('title_item_page') + ' ' }}
                    <span style="font-weight: 600;">{{ (item != null && item != undefined) ? item.title : '' }}    
                </span>
                <span
                        v-if="(item != null && item != undefined && item.status != undefined && item.status != 'autodraft' && !isLoading)"
                        class="icon has-text-gray4"
                        style="margin-left: 0.5em;">
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

        <div class="tainacan-form">
            <div class="columns">

                <div
                        class="column"
                        :class="!$adminOptions.hideItemSingleDocument || !$adminOptions.hideItemSingleThumbnail ? 'is-7' : 'is-12'">

                    <!-- Hook for extra Form options -->
                    <template v-if="hasBeginRightForm">
                        <div
                                id="view-item-begin-right"
                                class="form-hook-region"
                                v-html="getBeginRightForm"/>
                    </template>

                    <div class="b-tabs">
                        <nav 
                                v-if="tabs.length >= 2 "
                                role="list"
                                class="tabs">
                            <ul>
                                <li 
                                        v-for="(tab, tabIndex) of tabs"
                                        :key="tabIndex"
                                        :class="{ 'is-active': activeTab === tab.slug }"
                                        @click="activeTab = tab.slug"
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
                        </nav>

                        <section 
                                :style="tabs.length < 2 ? 'border-top: none; padding-top: 0;' : ''"
                                class="tab-content">

                            <div 
                                    v-if="activeTab === 'metadata'"
                                    class="tab-item"
                                    role="tabpanel"
                                    aria-labelledby="metadata-tab-label">

                                <!-- Metadata -------------------------------- -->
                                <div class="metadata-area">
                                    <div 
                                            v-for="(metadataSection, sectionIndex) of metadataSections"
                                            :key="sectionIndex">
                                        <div class="metadata-section-header section-label">
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
                                        </div>
                                        <template v-if="itemMetadata && Array.isArray(itemMetadata)">
                                            <div
                                                    v-for="(itemMetadatum, index) of itemMetadata.filter(
                                                        anItemMetadatum => anItemMetadatum.metadatum.metadata_section_id == metadataSection.id
                                                    )"
                                                    :key="index"
                                                    class="field">
                                                <label class="label">{{ itemMetadatum.metadatum.name }}</label>
                                                <div
                                                        :class="{
                                                            'metadata-type-textarea': itemMetadatum.metadatum.metadata_type_object.component == 'tainacan-textarea',
                                                            'metadata-type-compound': itemMetadatum.metadatum.metadata_type_object.component == 'tainacan-compound',
                                                            'metadata-type-relationship': itemMetadatum.metadatum.metadata_type_object.component == 'tainacan-relationship'
                                                        }"
                                                        class="content">
                                                    <component 
                                                            :is="
                                                                itemMetadatum.metadatum.metadata_type_object.component == 'tainacan-compound' ||
                                                                (itemMetadatum.metadatum.metadata_type_object.component == 'tainacan-relationship' &&
                                                                itemMetadatum.metadatum.metadata_type_object.options &&
                                                                itemMetadatum.metadatum.metadata_type_object.options.display_related_item_metadata &&
                                                                itemMetadatum.metadatum.metadata_type_object.options.display_related_item_metadata.length > 1
                                                                ) ? 'div' : 'p'" 
                                                            v-html="itemMetadatum.value_as_html != '' ? itemMetadatum.value_as_html : `<p><span class='has-text-gray is-italic'>` + $i18n.get('label_value_not_provided') + `</span></p>`"/>
                                                </div>
                                            </div>
                                            <br>
                                        </template>
                                    </div>
                                </div>

                                <!-- Hook for extra Form options -->
                                <template v-if="hasEndRightForm">
                                    <div
                                            id="view-item-end-right"
                                            class="form-hook-region"
                                            v-html="getEndRightForm"/>
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
                                        :is-editable="false"
                                        :is-loading.sync="isLoading" />
                            </div>

                        </section>
                    </div>
                </div>

                <div 
                        v-if="!$adminOptions.hideItemSingleDocument || !$adminOptions.hideItemSingleThumbnail"
                        class="column is-5">
                    <div class="sticky-container">

                        <!-- Hook for extra Form options -->
                        <template v-if="hasBeginLeftForm">
                            <div
                                    id="view-item-begin-left"
                                    class="form-hook-region"
                                    v-html="getBeginLeftForm"/>
                        </template>

                        <!-- Document -------------------------------- -->
                        <div 
                                v-if="!$adminOptions.hideItemSingleDocument"
                                class="section-label">
                            <label>
                                <span class="icon has-text-gray4 tainacan-icon-1-125em">
                                    <i :class="'tainacan-icon tainacan-icon-' + ( (!item.document_type || item.document_type == 'empty' ) ? 'item' : (item.document_type == 'attachment' ? 'attachments' : item.document_type))"/>
                                </span>
                                {{ item.document != undefined && item.document != null && item.document != '' ? $i18n.get('label_document') : $i18n.get('label_document_empty') }}
                            </label>
                        </div>
                        <div 
                                v-if="!$adminOptions.hideItemSingleDocument"
                                class="section-box document-field">
                            <div
                                    v-if="item.document !== undefined && item.document !== null &&
                                            item.document_type !== undefined && item.document_type !== null &&
                                            item.document !== '' && item.document_type !== 'empty'"
                                    class="document-field-content">
                                <div v-html="item.document_as_html"/>
                            </div>
                            <div v-else>
                                <p>{{ $i18n.get('info_no_document_to_item') }}</p>
                            </div>
                        </div>

                        <!-- Thumbnail -------------------------------- -->
                        <div 
                                v-if="!$adminOptions.hideItemSingleThumbnail"
                                class="section-label">
                            <label>
                                <span class="icon has-text-gray4">
                                    <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-image"/>
                                </span>
                                {{ $i18n.get('label_thumbnail') }}
                            </label>
                        </div>
                        <div 
                                v-if="!$adminOptions.hideItemSingleThumbnail"
                                class="section-box section-thumbnail">
                            <div class="thumbnail-field">
                                <file-item
                                        v-if="item.thumbnail != undefined && ((item.thumbnail['tainacan-medium'] != undefined && item.thumbnail['tainacan-medium'] != false) || (item.thumbnail.medium != undefined && item.thumbnail.medium != false))"
                                        :show-name="false"
                                        :modal-on-click="false"
                                        :size="125"
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
                                            v-if="item.document_type == 'empty'"
                                            class="image-placeholder">
                                        {{ $i18n.get('label_empty_thumbnail') }}
                                    </span>
                                    <img
                                            :alt="$i18n.get('label_thumbnail')"
                                            :src="$thumbHelper.getEmptyThumbnailPlaceholder(item.document_mimetype)">
                                </figure>
                            </div>
                            <br>
                            <div 
                                    v-if="item.thumbnail_id"
                                    class="thumbnail-alt-input">
                                <label class="label">{{ $i18n.get('label_thumbnail_alt') }}</label>
                                <help-button
                                        :title="$i18n.get('label_thumbnail_alt')"
                                        :message="$i18n.get('info_thumbnail_alt')"/>
                                <p> {{ item.thumbnail_alt }}</p>
                            </div>
                        </div>  

                        <!-- Attachments -------------------------------- -->
                        <div 
                                v-if="!$adminOptions.hideItemSingleAttachments"
                                class="section-label">
                            <label slot="header">
                                <span class="icon has-text-gray4">
                                    <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-attachments"/>
                                </span>
                                <span>
                                    {{ $i18n.get('label_attachments') }}&nbsp;
                                    <span
                                            v-if="totalAttachments"
                                            class="has-text-gray has-text-weight-normal">
                                        ({{ totalAttachments }})
                                    </span>
                                </span>
                            </label> 
                        </div>   
                        <div 
                                v-if="item != undefined && item.id != undefined && !isLoading && !$adminOptions.hideItemSingleAttachments"
                                class="section-box section-attachments">
                            <attachments-list
                                    :item="item"
                                    :form="item" />
                        </div>   

                        <!-- Hook for extra Form options -->
                        <template v-if="hasEndLeftForm">
                            <div
                                    id="view-item-end-left"
                                    class="form-hook-region"
                                    v-html="getEndLeftForm"/>
                        </template>

                    </div>
                </div>

                
            </div>
            <footer class="footer">

                <!-- Visibility -->
                <div 
                        v-if="!$adminOptions.hideItemSingleCurrentVisibility"
                        class="column is-narrow">
                    <div class="section-label">
                        <label>{{ $i18n.get('label_visibility') }}</label>
                    </div>
                    <div class="section-status">
                        <div class="field has-addons">
                            <span style="display: flex;">
                                <span class="icon has-text-gray4">
                                    <i 
                                            v-if="itemVisibility == 'open_access'"
                                            class="tainacan-icon tainacan-icon-see"/>
                                    <i
                                            class="tainacan-icon tainacan-icon-svg"
                                            style="display: flex;"
                                            v-else>
                                        <svg 
                                                xmlns:svg="http://www.w3.org/2000/svg"
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24px"
                                                height="24px"
                                                viewBox="-1 -1 8 8">
                                            <g
                                                    id="layer1"
                                                    transform="translate(-71.664352,-160.89128)">
                                                <path
                                                        style="fill:var(--tainacan-gray4);fill-opacity:1;stroke:none;stroke-width:0.332731"
                                                        d="m 74.839398,162.85685 c 0.09358,0 0.181945,0.0178 0.265146,0.052 0.08321,0.0356 0.153355,0.0831 0.213173,0.14544 0.06238,0.0624 0.110471,0.13511 0.145584,0.21852 0.03768,0.0806 0.05718,0.16896 0.05718,0.26522 0,0.0973 -0.0196,0.18714 -0.05718,0.2702 -0.03494,0.0806 -0.08321,0.14936 -0.145584,0.20783 -0.05978,0.0599 -0.129971,0.10801 -0.213173,0.14544 -0.08321,0.0356 -0.171572,0.052 -0.265146,0.052 -0.09358,0 -0.181945,-0.0179 -0.265146,-0.052 -0.08061,-0.0378 -0.150755,-0.0859 -0.213173,-0.14544 -0.06238,-0.0585 -0.11179,-0.12726 -0.145585,-0.20783 -0.03494,-0.083 -0.05198,-0.17289 -0.05198,-0.2702 0,-0.0962 0.01675,-0.18466 0.05198,-0.26522 0.03386,-0.0831 0.08321,-0.15578 0.145585,-0.21852 0.06238,-0.0624 0.132573,-0.11051 0.213173,-0.14544 0.08321,-0.0321 0.171571,-0.052 0.265146,-0.052 z"
                                                        id="path5508" />
                                                <path
                                                        id="path5461"
                                                        style="fill:var(--tainacan-gray4);fill-opacity:1;stroke:none;stroke-width:0.332732"
                                                        d="m 74.840268,161.8152 c -0.284646,0 -0.556123,0.0421 -0.816062,0.12511 -0.257339,0.0834 -0.494574,0.20141 -0.712908,0.35362 -0.215775,0.15329 -0.404697,0.33189 -0.571064,0.53975 -0.16601,0.20497 -0.301577,0.43572 -0.401586,0.68713 0.100026,0.24634 0.235559,0.47502 0.401586,0.68527 0.166367,0.20817 0.355335,0.38752 0.571064,0.53977 0.218341,0.14936 0.45556,0.267 0.712908,0.35006 0.259942,0.083 0.531423,0.12333 0.816062,0.12333 h 0.123411 c 0.04156,0 0.08074,-0.004 0.11974,-0.0107 -0.01034,-0.0732 -0.01461,-0.14473 -0.01461,-0.21745 0.0027,-0.0378 0.0053,-0.0752 0.0053,-0.11408 0.0028,-0.0417 0.0068,-0.0854 0.01462,-0.13083 -0.07667,0.0143 -0.158987,0.0214 -0.248677,0.0214 -0.211852,0 -0.418798,-0.0285 -0.618955,-0.0884 -0.19756,-0.0584 -0.385072,-0.14258 -0.561853,-0.2506 -0.174173,-0.10658 -0.33177,-0.23526 -0.473425,-0.3887 -0.139059,-0.15578 -0.254594,-0.33009 -0.348169,-0.52131 0.09618,-0.20034 0.216239,-0.37725 0.35921,-0.53053 0.145587,-0.15222 0.304788,-0.28054 0.478964,-0.38867 0.176742,-0.1105 0.362449,-0.19464 0.56001,-0.2506 0.197559,-0.0545 0.401772,-0.0828 0.609743,-0.0828 0.210571,0 0.415875,0.0285 0.613431,0.0884 0.200161,0.0599 0.388758,0.14545 0.565538,0.25596 0.176775,0.10765 0.334374,0.23812 0.473428,0.39422 0.137741,0.15329 0.254594,0.32617 0.348169,0.51583 -0.01426,0.0285 -0.02923,0.0524 -0.04605,0.0774 -0.01426,0.025 -0.03101,0.047 -0.04792,0.072 0.155958,0.0356 0.298689,0.10016 0.427371,0.19357 0.06238,-0.11694 0.114785,-0.23065 0.160271,-0.34259 -0.09749,-0.25201 -0.227859,-0.4818 -0.394219,-0.68713 -0.166368,-0.20818 -0.360082,-0.38646 -0.578427,-0.53975 -0.218343,-0.15221 -0.456649,-0.27056 -0.716588,-0.35362 -0.25734,-0.0831 -0.527216,-0.12512 -0.810547,-0.12512 z m 1.541874,2.02267 c -0.08238,0 -0.158347,0.0179 -0.230248,0.0478 -0.07073,0.0321 -0.131931,0.073 -0.186045,0.12726 -0.0529,0.0528 -0.09635,0.11586 -0.127119,0.18786 -0.03137,0.0706 -0.04605,0.1465 -0.04605,0.2285 v 0.23776 h -0.117885 c -0.0658,0 -0.122129,0.025 -0.167652,0.0699 -0.04613,0.0442 -0.06816,0.0987 -0.06816,0.16397 v 1.18268 c 0,0.0639 0.0221,0.11978 0.06816,0.16576 0.04552,0.0456 0.101846,0.0681 0.167652,0.0681 h 1.416601 c 0.06521,0 0.121522,-0.0214 0.16765,-0.0681 0.04549,-0.0442 0.06816,-0.10051 0.06816,-0.16576 v -1.18268 c 0,-0.0652 -0.02281,-0.11977 -0.06816,-0.16397 -0.04613,-0.0456 -0.102451,-0.0699 -0.16765,-0.0699 h -0.117888 v -0.23776 c 0,-0.0816 -0.01533,-0.15757 -0.04605,-0.2285 -0.03137,-0.072 -0.07483,-0.13511 -0.128937,-0.18786 -0.05286,-0.0542 -0.114108,-0.0959 -0.186045,-0.12726 -0.07073,-0.0321 -0.148473,-0.0478 -0.230283,-0.0478 z m 0,0.23777 c 0.0504,0 0.09881,0.007 0.141841,0.025 0.04242,0.018 0.07917,0.0449 0.110544,0.0773 0.03244,0.0321 0.05707,0.0681 0.07554,0.11051 0.01782,0.0431 0.0278,0.0895 0.0278,0.14011 v 0.23775 h -0.70922 v -0.23775 c 0,-0.0503 0.0093,-0.097 0.02745,-0.14011 0.01782,-0.0424 0.04231,-0.0791 0.07368,-0.11051 0.03244,-0.0321 0.06933,-0.0589 0.112361,-0.0773 0.04242,-0.0178 0.08898,-0.025 0.139987,-0.025 z m 0,1.17897 c 0.06517,0 0.121522,0.025 0.16765,0.0699 0.04552,0.046 0.07002,0.1023 0.07002,0.16755 0,0.0659 -0.02462,0.12226 -0.07002,0.16968 -0.04613,0.046 -0.102451,0.0681 -0.16765,0.0681 -0.0658,0 -0.120276,-0.0214 -0.165798,-0.0681 -0.04613,-0.0474 -0.07002,-0.10374 -0.07002,-0.16968 0,-0.0652 0.02388,-0.12157 0.07002,-0.16755 0.04549,-0.0456 0.09999,-0.0699 0.165798,-0.0699 z" />
                                                <g 
                                                        id="use1344"
                                                        transform="matrix(0.157413,0,0,0.157413,74.965914,165.96635)"
                                                        style="fill:var(--tainacan-gray4);fill-opacity:1" />
                                            </g>
                                        </svg>
                                    </i>
                                </span>
                                {{ $i18n.get('label_' + itemVisibility) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Comment Status ------------------------ -->
                <div
                        v-if="collection && collection.allow_comments && collection.allow_comments == 'open' && !$adminOptions.hideItemSingleCommentsOpen"
                        class="column is-narrow">
                    <div class="section-label">
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-comment"/>
                        </span>
                        <label>{{ $i18n.get('label_comments') }}</label>
                    </div>
                    <div class="section-status">
                        <div class="field has-addons">
                            <span>
                                <span
                                        v-if="item.comment_status != 'open'"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-close"/>
                                </span>
                                <span
                                        v-if="item.comment_status == 'open'"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-approved"/>
                                </span>
                                {{ item.comment_status == 'open' ? $i18n.get('label_allowed') : $i18n.get('label_not_allowed') }}
                            </span>
                        </div>
                    </div>
                </div>
                    
                <div class="form-submission-footer">
                    <router-link
                            v-if="item.current_user_can_edit && $route.query.recent == true"
                            class="button is-secondary"
                            style="margin-right: auto;"
                            :to="{ path: $routerHelper.getNewItemPath(collectionId)}">
                        <!-- <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-add"/>
                        </span> -->
                        <span>{{ $i18n.get('label_create_another_item') }}</span>
                    </router-link>
                    <button 
                            v-if="!$adminOptions.hideItemSingleActivities"
                            class="button sequence-button"
                            :aria-label="$i18n.get('label_view_activity_logs')"
                            :disabled="isLoading"
                            @click="openActivitiesModal()">
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-activities"/>
                        </span>
                        <span class="is-hidden-touch">{{ $i18n.get('label_view_activity_logs') }}</span>
                    </button>
                    <button 
                            v-if="!$adminOptions.hideItemSingleExposers"
                            class="button sequence-button"
                            :aria-label="$i18n.get('label_view_as')"
                            :disabled="isLoading"
                            @click="openExposersModal()">
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-viewas"/>
                        </span>
                        <span class="is-hidden-touch">{{ $i18n.get('label_view_as') }}</span>
                    </button>
                    <a
                            target="_blank"
                            class="button sequence-button is-pulled-right"
                            :href="item.url">
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-see"/>
                        </span>
                        <span>{{ $i18n.get('label_item_page_on_website') }}</span>
                    </a>
                    <router-link
                            v-if="item.current_user_can_edit"
                            class="button is-secondary"
                            :to="{ path: $routerHelper.getItemEditPath(collectionId, itemId)}">
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                        </span>
                        <span>{{ $i18n.getFrom('items','edit_item') }}</span>
                    </router-link>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';
    import FileItem from '../../components/other/file-item.vue';
    import { formHooks } from '../../js/mixins';
    import ActivitiesPage from '../lists/activities-page.vue';
    import ExposersModal from '../../components/modals/exposers-modal.vue';
    import AttachmentsList from '../../components/lists/attachments-list.vue';
    import RelatedItemsList from '../../components/lists/related-items-list.vue';

    export default {
        name: 'ItemPage',
        components: {
            FileItem,
            ActivitiesPage,
            RelatedItemsList,
            AttachmentsList
        },
        mixins: [formHooks],
        data() {
            return {
                collectionId: Number,
                itemId: Number,
                itemRequestCancel: undefined,
                isLoading: false,
                isLoadingMetadataSections: false,
                open: true,
                urls_open: false,
                activeTab: 'metadata'
            }
        },
        computed: {
            collection() {
                return this.getCollection();
            },
            metadataSections() {
                return this.getMetadataSections();
            },
            item() {
                // Fills hook forms with it's real values 
                this.updateExtraFormData(this.getItem());

                return this.getItem();
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
            totalRelatedItems() {
                return (this.item && this.item.related_items) ? Object.values(this.item.related_items).reduce((totalItems, aRelatedItemsGroup) => totalItems + parseInt(aRelatedItemsGroup.total_items), 0) : false;
            },
            totalAttachments() {
                return this.getTotalAttachments();
            },
            itemVisibility() {
                return (this.collection && this.collection.status == 'publish' && this.item && this.item.status == 'publish') ? 'open_access' : 'restrict_access'
            },
            tabs() {
                let pageTabs = [{
                    slug: 'metadata',
                    icon: 'metadata',
                    name: this.$i18n.get('metadata'),
                    total: this.itemMetadata.length
                }];
                if (this.totalRelatedItems) {
                    pageTabs.push({
                        slug: 'related',
                        icon: 'processes tainacan-icon-rotate-270',
                        name: this.$i18n.get('label_related_items'),
                        total: this.totalRelatedItems
                    });
                }
                return pageTabs;
            },
        },
        created() {
            // Obtains item and collection ID
            this.collectionId = this.$route.params.collectionId;
            this.itemId = this.$route.params.itemId;

            // Puts loading on Item Loading
            this.isLoading = true;

            // Cancels previous Request
            if (this.itemRequestCancel != undefined)
                this.itemRequestCancel.cancel('Item search Canceled.');

            // Obtains Item
            this.fetchItem({ 
                itemId: this.itemId,
                contextEdit: true,    
                fetchOnly: 'title,thumbnail,status,modification_date,document_type,document_mimetype,document,comment_status,document_as_html,related_items'       
            })
            .then((resp) => {
                resp.request.then((item) => {
                    this.$root.$emit('onCollectionBreadCrumbUpdate', [
                        {path: this.$routerHelper.getCollectionPath(this.collectionId), label: this.$i18n.get('items')},
                        {path: '', label: item.title}
                    ]);
                    this.loadMetadata();
                })
                .catch(() => this.isLoading = false);

                // Item resquest token for cancelling
                this.itemRequestCancel = resp.source;
            })
            .catch(() => this.isLoading = false);


            // Loads Metadata Sections
            this.isLoadingMetadataSections = true;
            this.fetchMetadataSections({
                    collectionId: this.collectionId
                })
                .then((metadataSections) => {
                    this.metadataSectionCollapses = Array(metadataSections.length).fill(true)
                    this.isLoadingMetadataSections = false;
                })
                .catch((error) => {
                    this.isLoadingMetadataSections = false;
                    this.$console.error('Error loading metadata sections ', error);
                });

        },
        methods: {
            ...mapActions('item', [
                'fetchItem',
                'fetchItemMetadata',
            ]),
            ...mapGetters('item', [
                'getItem',
                'getItemMetadata',
                'getTotalAttachments'
            ]),
            ...mapGetters('collection', [
                'getCollection'
            ]),
            ...mapActions('metadata',[
                'fetchMetadataSections'
            ]),
            ...mapGetters('metadata',[
                'getMetadataSections'
            ]),
            loadMetadata() {
                // Obtains Item Metadatum
                this.fetchItemMetadata(this.itemId).then(() => {
                    this.isLoading = false;
                });
            },
            openExposersModal() {
                this.$buefy.modal.open({
                    parent: this,
                    component: ExposersModal,
                    hasModalCard: true,
                    props: { 
                        collectionId: this.collectionId,
                        itemId: this.itemId,
                        itemURL: this.item.url,
                        totalItems: 1,
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });
            },
            openActivitiesModal() {
                this.$buefy.modal.open({
                    parent: this,
                    component: ActivitiesPage,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });
            }
        }
    }
</script>

<style lang="scss" scoped>

    .page-container {
        padding: 0px 0px 60px 0px;

        & > .tainacan-form {
            margin-bottom: 60px;

            .field:not(:last-child) {
                margin-bottom: 0.5em;
            }
        }

        .tainacan-page-title {
            margin-top: var(--tainacan-container-padding);
            padding: 0 var(--tainacan-one-column);

            .status-tag {
                color: var(--tainacan-white);
                background: var(--tainacan-turquoise5);
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
                        top: 0;
                        margin: 0;
                        max-height: calc(100vh - 202px);
                        overflow-y: auto;
                        overflow-x: hidden;
                    }
                }
            }
            .column.is-7 {
                padding-top: 0;
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
                    padding: 10px 0 14px 0px;
                    margin-left: -3px;
                }

                @media screen and (max-width: 769px) {
                    padding-right: var(--tainacan-one-column);
                    width: 100%;
                }
            }

            @media screen and (max-width: 1440px) {
                 &>.column.is-7 {
                     padding-left: 0.75em;
                 }
                 &>.column.is-5 {
                     padding-right: 0.75em;
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
                    width: 100%;

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
                    width: 100%;
                    padding-left: 0.5em;
                    padding-right: 0.5em;
                }
            }
        }
    
        .b-tabs {
            overflow: hidden !important;
            margin-top: -7px;
        }

        @media screen and (max-width: 769px) {
            /deep/ .section-attachments .table-container {
                padding-left: 0;
                padding-right: 0;
            }
        }
    }

    .metadata-area {
        .metadata-section-header {
            padding: 0.5em 0.25em 0.5em 0em;
            border-bottom: 1px solid var(--tainacan-input-border-color);
        }
        .field {
            border-bottom: 1px solid var(--tainacan-gray1);
            padding: 10px var(--tainacan-container-padding) !important;
            margin-left: 1em !important;

            .label {
                font-size: 0.875em;
                font-weight: 500;
                margin-bottom: 0.5em;
                display: inline-flex;
                align-items: center;

                span {
                    margin-right: 18px;
                }
            }
            p:empty {
                display: none;
                visibility: hidden;
            }
        } 
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
    .section-box+.section-label {
        border-top: 1px solid var(--tainacan-gray1);
        padding-top: 6px;
    }

    .section-box {
        padding: 0 1em 0 1.875em;
        margin-top: 10px;
        margin-bottom: 16px;
        position: relative;
    }

    .section-status {
        font-size: 0.875em;

        .field {
            border-bottom: none;

            .icon {
                font-size: 1.125em !important;
                color: var(--tainacan-info-color);
            }
        }
    }

    .section-thumbnail {
        display: flex;
        
        .thumbnail-alt-input {
            margin-left: 1em;
        }
    }

    .document-field {
        .document-field-content {
            max-height: 32vh;

            /deep/ img,
            /deep/ video,
            /deep/ figure {
                max-width: 100%;
                max-height: 32vh;
                width: auto;
                margin: 0;
            }
            /deep/ img {
                width: auto !important;
            }
            /deep/ a {
                min-height: 60px;
                display: block;
            }
            /deep/ audio,
            /deep/ iframe,
            /deep/ blockquote {
                max-width: 100%;
                max-height: 32vh;
                width: 100%;
                margin: 0;
                min-height: 150px;
            }
            /deep/ audio {
                min-height: 80px;
            }

            @media screen and (max-height: 760px) {
                max-height: 25vh;

                /deep/ img,
                /deep/ video,
                /deep/ figure {
                    max-height: 25vh;
                }
                /deep/ audio,
                /deep/ iframe,
                /deep/ blockquote {
                    max-height: 25vh;
                }
            }
        }
    }

    .section-attachments {
        padding-left: 0;
        padding-right: 0;
    }

    .uploaded-files {
        display: flex;
        flex-flow: wrap;
        margin-left: -15px;
        margin-right: -15px;
    }

    .thumbnail-field {

        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            height: 125px;
            width: 125px;
        }
        .image-placeholder {
            position: absolute;
            margin-left: 20px;
            margin-right: 20px;
            font-size: 0.8em;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: var(--tainacan-info-color);
            top: 45px;
            max-width: 84px;
        }
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
        justify-content: space-between;
        align-items: center;

        .form-submission-footer {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;

            .button {
                margin-left: 16px;
                margin-right: 6px;
            }
            .button.is-outlined {
                margin-left: 0px;
                margin-right: auto;
            }
        }

        .sequence-button {
            background-color: transparent;
            color: var(--tainacan-turquoise5);
            border: none;

            .icon {
                margin-right: 5px !important;
            }

            &:hover,
            &:focus,
            &:active {
                background-color: transparent !important;
                color: var(--tainacan-turquoise5) !important;
            }
        }

        @media screen and (max-width: 769px) {
            padding: 13px 0.5em;
            width: 100%;
            flex-wrap: wrap;
            height: auto;
            position: fixed;
        }
    }
</style>

