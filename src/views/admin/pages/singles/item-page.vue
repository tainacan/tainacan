<template>
    <div>
        <b-loading
                :active.sync="isLoading"
                :can-cancel="false"/>

        <div class="tainacan-page-title">
            <h1>
            <span
                    v-if="(item != null && item != undefined && item.status != undefined && !isLoading)"
                    class="status-tag">
                {{ $i18n.get('status_' + item.status) }}
            </span>
                {{ $i18n.get('title_item_page') + ' ' }}
                <span style="font-weight: 600;">{{ (item != null && item != undefined) ? item.title : '' }}</span>
            </h1>
            <a
                    @click="$router.go(-1)"
                    class="back-link has-text-secondary">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </div>
        <div class="tainacan-form">
            <div class="columns">

                <div class="column is-7">

                    <!-- Hook for extra Form options -->
                    <template
                            v-if="formHooks != undefined &&
                        formHooks['view-item'] != undefined &&
                        formHooks['view-item']['begin-right'] != undefined">
                        <div
                                id="view-item-begin-right"
                                class="form-hook-region"
                                v-html="formHooks['view-item']['begin-right'].join('')"/>
                    </template>

                    <div
                            style="flex-wrap: wrap"
                            class="columns">

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

                        <!-- Status (status public or private) -------------------------------- -->
                        <div class="column is-narrow">
                            
                            <div class="section-label">
                                <label>{{ $i18n.getHelperTitle('items', 'status') }}</label>
                            </div>
                            <div class="section-status">
                                <div class="field has-addons">
                                    <span v-if="item.status != 'private'">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-public"/>
                                        </span> {{ $i18n.get('status_public') }}
                                    </span>
                                    <span v-if="item.status == 'private'">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-private"/>
                                        </span>  {{ $i18n.get('status_private') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Visibility -->
                        <div class="column is-narrow">
                            <div class="section-label">
                                <label>{{ $i18n.get('label_visibility') }}</label>
                            </div>
                            <div class="section-status">
                                <div class="field has-addons">
                                    <span style="display: flex;">
                                        <span class="icon">
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
                                                                style="fill:var(--tainacan-gray3);fill-opacity:1;stroke:none;stroke-width:0.332731"
                                                                d="m 74.839398,162.85685 c 0.09358,0 0.181945,0.0178 0.265146,0.052 0.08321,0.0356 0.153355,0.0831 0.213173,0.14544 0.06238,0.0624 0.110471,0.13511 0.145584,0.21852 0.03768,0.0806 0.05718,0.16896 0.05718,0.26522 0,0.0973 -0.0196,0.18714 -0.05718,0.2702 -0.03494,0.0806 -0.08321,0.14936 -0.145584,0.20783 -0.05978,0.0599 -0.129971,0.10801 -0.213173,0.14544 -0.08321,0.0356 -0.171572,0.052 -0.265146,0.052 -0.09358,0 -0.181945,-0.0179 -0.265146,-0.052 -0.08061,-0.0378 -0.150755,-0.0859 -0.213173,-0.14544 -0.06238,-0.0585 -0.11179,-0.12726 -0.145585,-0.20783 -0.03494,-0.083 -0.05198,-0.17289 -0.05198,-0.2702 0,-0.0962 0.01675,-0.18466 0.05198,-0.26522 0.03386,-0.0831 0.08321,-0.15578 0.145585,-0.21852 0.06238,-0.0624 0.132573,-0.11051 0.213173,-0.14544 0.08321,-0.0321 0.171571,-0.052 0.265146,-0.052 z"
                                                                id="path5508" />
                                                        <path
                                                                id="path5461"
                                                                style="fill:var(--tainacan-gray3);fill-opacity:1;stroke:none;stroke-width:0.332732"
                                                                d="m 74.840268,161.8152 c -0.284646,0 -0.556123,0.0421 -0.816062,0.12511 -0.257339,0.0834 -0.494574,0.20141 -0.712908,0.35362 -0.215775,0.15329 -0.404697,0.33189 -0.571064,0.53975 -0.16601,0.20497 -0.301577,0.43572 -0.401586,0.68713 0.100026,0.24634 0.235559,0.47502 0.401586,0.68527 0.166367,0.20817 0.355335,0.38752 0.571064,0.53977 0.218341,0.14936 0.45556,0.267 0.712908,0.35006 0.259942,0.083 0.531423,0.12333 0.816062,0.12333 h 0.123411 c 0.04156,0 0.08074,-0.004 0.11974,-0.0107 -0.01034,-0.0732 -0.01461,-0.14473 -0.01461,-0.21745 0.0027,-0.0378 0.0053,-0.0752 0.0053,-0.11408 0.0028,-0.0417 0.0068,-0.0854 0.01462,-0.13083 -0.07667,0.0143 -0.158987,0.0214 -0.248677,0.0214 -0.211852,0 -0.418798,-0.0285 -0.618955,-0.0884 -0.19756,-0.0584 -0.385072,-0.14258 -0.561853,-0.2506 -0.174173,-0.10658 -0.33177,-0.23526 -0.473425,-0.3887 -0.139059,-0.15578 -0.254594,-0.33009 -0.348169,-0.52131 0.09618,-0.20034 0.216239,-0.37725 0.35921,-0.53053 0.145587,-0.15222 0.304788,-0.28054 0.478964,-0.38867 0.176742,-0.1105 0.362449,-0.19464 0.56001,-0.2506 0.197559,-0.0545 0.401772,-0.0828 0.609743,-0.0828 0.210571,0 0.415875,0.0285 0.613431,0.0884 0.200161,0.0599 0.388758,0.14545 0.565538,0.25596 0.176775,0.10765 0.334374,0.23812 0.473428,0.39422 0.137741,0.15329 0.254594,0.32617 0.348169,0.51583 -0.01426,0.0285 -0.02923,0.0524 -0.04605,0.0774 -0.01426,0.025 -0.03101,0.047 -0.04792,0.072 0.155958,0.0356 0.298689,0.10016 0.427371,0.19357 0.06238,-0.11694 0.114785,-0.23065 0.160271,-0.34259 -0.09749,-0.25201 -0.227859,-0.4818 -0.394219,-0.68713 -0.166368,-0.20818 -0.360082,-0.38646 -0.578427,-0.53975 -0.218343,-0.15221 -0.456649,-0.27056 -0.716588,-0.35362 -0.25734,-0.0831 -0.527216,-0.12512 -0.810547,-0.12512 z m 1.541874,2.02267 c -0.08238,0 -0.158347,0.0179 -0.230248,0.0478 -0.07073,0.0321 -0.131931,0.073 -0.186045,0.12726 -0.0529,0.0528 -0.09635,0.11586 -0.127119,0.18786 -0.03137,0.0706 -0.04605,0.1465 -0.04605,0.2285 v 0.23776 h -0.117885 c -0.0658,0 -0.122129,0.025 -0.167652,0.0699 -0.04613,0.0442 -0.06816,0.0987 -0.06816,0.16397 v 1.18268 c 0,0.0639 0.0221,0.11978 0.06816,0.16576 0.04552,0.0456 0.101846,0.0681 0.167652,0.0681 h 1.416601 c 0.06521,0 0.121522,-0.0214 0.16765,-0.0681 0.04549,-0.0442 0.06816,-0.10051 0.06816,-0.16576 v -1.18268 c 0,-0.0652 -0.02281,-0.11977 -0.06816,-0.16397 -0.04613,-0.0456 -0.102451,-0.0699 -0.16765,-0.0699 h -0.117888 v -0.23776 c 0,-0.0816 -0.01533,-0.15757 -0.04605,-0.2285 -0.03137,-0.072 -0.07483,-0.13511 -0.128937,-0.18786 -0.05286,-0.0542 -0.114108,-0.0959 -0.186045,-0.12726 -0.07073,-0.0321 -0.148473,-0.0478 -0.230283,-0.0478 z m 0,0.23777 c 0.0504,0 0.09881,0.007 0.141841,0.025 0.04242,0.018 0.07917,0.0449 0.110544,0.0773 0.03244,0.0321 0.05707,0.0681 0.07554,0.11051 0.01782,0.0431 0.0278,0.0895 0.0278,0.14011 v 0.23775 h -0.70922 v -0.23775 c 0,-0.0503 0.0093,-0.097 0.02745,-0.14011 0.01782,-0.0424 0.04231,-0.0791 0.07368,-0.11051 0.03244,-0.0321 0.06933,-0.0589 0.112361,-0.0773 0.04242,-0.0178 0.08898,-0.025 0.139987,-0.025 z m 0,1.17897 c 0.06517,0 0.121522,0.025 0.16765,0.0699 0.04552,0.046 0.07002,0.1023 0.07002,0.16755 0,0.0659 -0.02462,0.12226 -0.07002,0.16968 -0.04613,0.046 -0.102451,0.0681 -0.16765,0.0681 -0.0658,0 -0.120276,-0.0214 -0.165798,-0.0681 -0.04613,-0.0474 -0.07002,-0.10374 -0.07002,-0.16968 0,-0.0652 0.02388,-0.12157 0.07002,-0.16755 0.04549,-0.0456 0.09999,-0.0699 0.165798,-0.0699 z" />
                                                        <g 
                                                                id="use1344"
                                                                transform="matrix(0.157413,0,0,0.157413,74.965914,165.96635)"
                                                                style="fill:var(--tainacan-gray3);fill-opacity:1" />
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
                                v-if="collection && collection.allow_comments && collection.allow_comments == 'open'"
                                class="column is-narrow">
                            <div class="section-label">
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
                    </div>

                    <b-tabs v-model="activeTab">
                        <b-tab-item>
                            <template slot="header">
                                <span class="icon has-text-gray5">
                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-metadata"/>
                                </span>
                                <span>{{ $i18n.get('metadata') }}</span>
                            </template>

                            <!-- Metadata -------------------------------- -->
                            <div class="metadata-area">
                                <div
                                        v-for="(itemMetadatum, index) of metadatumList"
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
                            </div>

                            <!-- Hook for extra Form options -->
                            <template
                                    v-if="formHooks != undefined &&
                                formHooks['view-item'] != undefined &&
                                formHooks['view-item']['end-right'] != undefined">
                                <div
                                        id="view-item-end-right"
                                        class="form-hook-region"
                                        v-html="formHooks['view-item']['end-right'].join('')"/>
                            </template>
                        </b-tab-item>

                        <!-- Related items -->
                        <b-tab-item v-if="totalRelatedItems">
                            <template slot="header">
                                <span class="icon has-text-gray5">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-processes tainacan-icon-rotate-270"/>
                                </span>
                                <span>
                                    {{ $i18n.get('label_related_items') }}
                                    <span class="has-text-gray">
                                        ({{ totalRelatedItems }})
                                    </span>
                                </span>
                            </template>

                            <div class="attachments-list-heading">
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
                            
                        </b-tab-item>

                        <b-tab-item>
                            <template slot="header">
                                <span class="icon has-text-gray5">
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
                        
                            <attachments-list
                                    v-if="item != undefined && item.id != undefined"
                                    :item="item"
                                    :is-loading.sync="isLoadingAttachments"
                                    @isLoadingAttachments="(isLoading) => isLoadingAttachments = isLoading" />    
                        </b-tab-item>

                        <b-tab-item>
                            <template slot="header">
                                <span class="icon has-text-gray5">
                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-activities"/>
                                </span>
                                <span>{{ $i18n.get('activities') }}</span>
                            </template>
                            
                            <activities-page v-if="activeTab == 3"/>
                        </b-tab-item>
                    </b-tabs>
                </div>

                <div class="column is-5">
                    <div class="sticky-container">

                        <!-- Hook for extra Form options -->
                        <template
                                v-if="formHooks != undefined &&
                            formHooks['view-item'] != undefined &&
                            formHooks['view-item']['begin-left'] != undefined">
                            <div
                                    id="view-item-begin-left"
                                    class="form-hook-region"
                                    v-html="formHooks['view-item']['begin-left'].join('')"/>
                        </template>

                        <!-- Document -------------------------------- -->
                        <div class="section-label">
                            <label>{{ item.document !== undefined && item.document !== null && item.document !== ''
                                ?
                                $i18n.get('label_document') : $i18n.get('label_document_empty') }}</label>
                        </div>
                        <div class="section-box document-field">
                            <div
                                    v-if="item.document !== undefined && item.document !== null &&
                                    item.document_type !== undefined && item.document_type !== null &&
                                    item.document !== '' && item.document_type !== 'empty'">

                                <div v-if="item.document_type === 'attachment'">
                                    <!-- <div v-html="item.document_as_html"/> -->
                                    <document-item :document-html="item.document_as_html"/>
                                </div>

                                <div v-else-if="item.document_type === 'text'">
                                    <div v-html="item.document_as_html"/>
                                </div>

                                <div v-else-if="item.document_type === 'url'">
                                    <div v-html="item.document_as_html"/>
                                </div>
                            </div>
                            <div v-else>
                                <p>{{ $i18n.get('info_no_document_to_item') }}</p>
                            </div>
                        </div>

                        <!-- Thumbnail -------------------------------- -->
                        <div class="section-label">
                            <label>{{ $i18n.get('label_thumbnail') }}</label>
                        </div>
                        <div class="section-box section-thumbnail">
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

                        <!-- Hook for extra Form options -->
                        <template
                                v-if="formHooks != undefined &&
                                    formHooks['view-item'] != undefined &&
                                    formHooks['view-item']['end-left'] != undefined">
                            <div
                                    id="view-item-end-left"
                                    class="form-hook-region"
                                    v-html="formHooks['view-item']['end-left'].join('')"/>
                        </template>

                    </div>
                </div>

                
            </div>
            <footer class="footer">
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
                    <router-link
                            v-if="item.current_user_can_edit"
                            class="button sequence-button"
                            :to="{ path: $routerHelper.getItemEditPath(collectionId, itemId)}">
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                        </span>
                        <span>{{ $i18n.getFrom('items','edit_item') }}</span>
                    </router-link>
                    <button 
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
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';
    import FileItem from '../../components/other/file-item.vue';
    import DocumentItem from '../../components/other/document-item.vue';
    import {formHooks} from '../../js/mixins';
    import ActivitiesPage from '../lists/activities-page.vue';
    import ExposersModal from '../../components/modals/exposers-modal.vue';
    import AttachmentsList from '../../components/lists/attachments-list.vue';
    import RelatedItemsList from '../../components/lists/related-items-list.vue';

    export default {
        name: 'ItemPage',
        components: {
            FileItem,
            DocumentItem,
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
                open: true,
                urls_open: false,
                activeTab: 0
            }
        },
        computed: {
            collection() {
                return this.getCollection();
            },
            item() {
                // Fills hook forms with it's real values 
                this.updateExtraFormData(this.getItem());

                return this.getItem();
            },
            metadatumList() {
                return JSON.parse(JSON.stringify(this.getItemMetadata()));
            },
            totalRelatedItems() {
                return (this.item && this.item.related_items) ? Object.values(this.item.related_items).reduce((totalItems, aRelatedItemsGroup) => totalItems + parseInt(aRelatedItemsGroup.total_items), 0) : false;
            },
            totalAttachments() {
                return this.getTotalAttachments();
            },
            itemVisibility() {
                return (this.collection && this.collection.status == 'publish' && this.item && this.item.status == 'publish') ? 'open_access' : 'restrict_access'
            }
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
                });

                // Item resquest token for cancelling
                this.itemRequestCancel = resp.source;
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
                    customClass: 'tainacan-modal'
                });
            }
        }
    }
</script>

<style lang="scss" scoped>

    .page-container {
        padding: var(--tainacan-container-padding) 0;

        & > .tainacan-form {
            margin-bottom: 110px;

            .field:not(:last-child) {
                margin-bottom: 0.5em;
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
                background: var(--tainacan-turquoise5);
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
        }

        .tainacan-form > .columns {
            margin-bottom: 70px;
            margin-left: var(--tainacan-one-column);
            margin-right: var(--tainacan-one-column);
        }

        .column.is-5 {
            padding-left: var(--tainacan-one-column);
            padding-right: var(--tainacan-one-column);

            .sticky-container {
                position: relative;
                position: sticky;
                top: -25px;
                margin: 3px 0;
                max-height: calc(100vh - 202px);
                overflow-y: auto;
                overflow-x: hidden;
            }

            @media screen and (max-width: 769px) {
                width: 100%;
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
                padding: 10px 0 14px 0px;
                margin-left: -3px;
            }

            @media screen and (max-width: 769px) {
                padding-right: var(--tainacan-one-column);
                width: 100%;
            }
        }
    
        .b-tabs {
            overflow: hidden !important;
            margin-top: -15px;
        }
    }

    .metadata-area {
        .field {
            border-bottom: 1px solid var(--tainacan-gray2);
            padding: 10px var(--tainacan-container-padding);
            margin-left: 0px !important;

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
            color: var(--tainacan-gray5) !important;
            line-height: 1.2em;
        }
    }

    .section-box {
        background-color: var(--tainacan-white);
        padding: 0 var(--tainacan-one-column) 0 0;
        margin-top: 12px;
        margin-bottom: 18px;

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
                        background-color: var(--tainacan-turquoise2);
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
        font-size: 0.75em;

        .field {
            border-bottom: none;

            .icon {
                font-size: 1.125em !important;
                color: var(--tainacan-gray3);
            }
        }
    }

    .document-field {
        /deep/ iframe {
            max-width: 100%;
        }
        .document-buttons-row {
            text-align: right;
            top: -21px;
            position: relative;
        }
    }

    .section-attachments {
        margin-top: 0;
        p {
            margin: 4px 15px
        }
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

    .footer {
        padding: 18px var(--tainacan-one-column);
        position: absolute;
        bottom: 0;
        z-index: 999999;
        background-color: var(--tainacan-gray1);
        width: 100%;
        height: 65px;

        .form-submission-footer {
            width: 100%;
            display: flex;
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
    }
</style>

