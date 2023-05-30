<template>
    <div v-if="collectionId">
        <b-loading
                :is-full-page="false"
                :active.sync="isLoading"
                :can-cancel="false"/>

        <template v-if="couldLoadCollection && collecionAllowsItemSubmission">
            <form
                    v-if="!hasSentForm"
                    v-show="!isLoading && !isSubmitting && !isUploading"
                    class="tainacan-form"
                    label-width="120px">

                <!-- Hook for extra options -->
                <template v-if="hasBeginLeftForm">
                    <form
                            id="form-item-begin-left"
                            class="form-hook-region"
                            v-html="getBeginLeftForm"/>
                </template>

                <!-- JS-side hook for extra content -->
                <div 
                        v-if="hasBeforeHook('document')"
                        class="item-submission-hook item-submission-hook-document-before"
                        v-html="getBeforeHook('document')" />

                <!-- Document -------------------------------- -->
                <template v-if="!hideFileModalButton || !hideTextModalButton || !hideLinkModalButton">
                    <div
                            v-if="documentSectionLabel"
                            class="section-label">
                        <label>{{ documentSectionLabel }}</label>
                        <help-button
                                v-if="!hideHelpButtons && !helpInfoBellowLabel && $i18n.getHelperMessage('items', 'document')"
                                :title="$i18n.getHelperTitle('items', 'document')"
                                :message="$i18n.getHelperMessage('items', 'document')"/>
                        <p
                                class="metadatum-description-help-info"
                                v-if="!hideHelpButtons && helpInfoBellowLabel && $i18n.getHelperMessage('items', 'document')">
                            {{ $i18n.getHelperMessage('items', 'document') }}
                        </p>
                    </div>
                    <div
                            class="section-box document-field"
                            id="tainacan-item-metadatum_id-document">
                        <div v-if="form.document_type != '' && form.document_type != undefined && form.document_type != null && form.document_type != 'empty'">
                            <div v-if="form.document_type == 'attachment'">
                                <b-upload
                                        expanded
                                        v-if="!form.document"
                                        v-model="form.document"
                                        drag-drop>
                                    <section class="section">
                                        <div class="content has-text-centered">
                                            <p>
                                                <span class="icon">
                                                    <i class="tainacan-icon tainacan-icon-36px tainacan-icon-upload" />
                                                </span>
                                            </p>
                                            <p>{{ $i18n.get('instruction_drop_file_or_click_to_upload') }}</p>
                                        </div>
                                    </section>
                                </b-upload>
                                <div
                                        v-else
                                        class="files-list">
                                    <b-tag
                                            rounded
                                            closable
                                            attached
                                            :aria-close-label="$i18n.get('delete')"
                                            @close="form.document = ''"
                                            :class="documentErrorMessage ? 'is-danger' : ''">
                                        {{ form.document.name }}
                                    </b-tag>
                                </div>
                                <p
                                        v-if="documentErrorMessage"
                                        class="help is-danger">
                                    {{ documentErrorMessage }}
                                </p>
                            </div>
                            <div v-if="form.document_type == 'text'">
                                <b-input
                                        type="textarea"
                                        v-model="form.document" />
                                <br v-if="hasMoreThanOneDocumentTypeOption">
                            </div>
                            <div v-if="form.document_type == 'url'">
                                <b-input
                                        :placeholder="$i18n.get('instruction_insert_url')"
                                        type="url"
                                        v-model="form.document" />
                                <b-field
                                        :addons="false"
                                        :label="$i18n.get('label_document_option_forced_iframe')">
                                        &nbsp;
                                    <b-switch
                                            size="is-small"
                                            v-model="form.document_options.forced_iframe" />
                                    <help-button
                                            :title="$i18n.get('label_document_option_forced_iframe')"
                                            :message="$i18n.get('info_document_option_forced_iframe')" />
                                </b-field>
                                <b-field
                                        v-if="form.document_options && form.document_options.forced_iframe"
                                        grouped>
                                    <b-field
                                            style="padding: 0"
                                            :label="$i18n.get('label_document_option_iframe_width')">
                                        <b-numberinput
                                                :aria-minus-label="$i18n.get('label_decrease')"
                                                :aria-plus-label="$i18n.get('label_increase')"
                                                min="1"
                                                v-model="form.document_options.forced_iframe_width"
                                                step="1" />
                                    </b-field>
                                    <b-field
                                            style="padding: 0; margin-left: 12px;"
                                            :label="$i18n.get('label_document_option_iframe_height')">
                                        <b-numberinput
                                                :aria-minus-label="$i18n.get('label_decrease')"
                                                :aria-plus-label="$i18n.get('label_increase')"
                                                min="1"
                                                v-model="form.document_options.forced_iframe_height"
                                                step="1" />
                                    </b-field>
                                </b-field>
                                <p
                                        class="metadatum-description-help-info"
                                        v-if="form.document_options.forced_iframe"
                                        style="padding: 0px 0px 0px 34px">
                                    {{ $i18n.get('info_iframe_dimensions') }}
                                </p>
                                <b-field
                                        v-if="form.document_options && form.document_options.forced_iframe"
                                        :addons="false"
                                        :label="$i18n.get('label_document_option_is_image')">
                                        &nbsp;
                                    <b-switch
                                            size="is-small"
                                            v-model="form.document_options.is_image" />
                                    <help-button
                                            :title="$i18n.get('label_document_option_is_image')"
                                            :message="$i18n.get('info_document_option_is_image')" />
                                </b-field>
                                <br v-if="hasMoreThanOneDocumentTypeOption">
                            </div>
                            <button
                                    v-if="hasMoreThanOneDocumentTypeOption"
                                    type="button"
                                    class="button is-outlined"
                                    @click="form.document = ''; form.document_type = 'empty'">
                                {{ $i18n.get('label_switch_document_type') }}
                            </button>
                        </div>
                        <ul v-else>
                            <li v-if="!hideFileModalButton">
                                <button
                                        type="button"
                                        @click.prevent="form.document_type = 'attachment'">
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-upload"/>
                                    </span>
                                </button>
                                <p>{{ $i18n.get('label_file') }}</p>
                            </li>
                            <li v-if="!hideTextModalButton">
                                <button
                                        type="button"
                                        @click.prevent="form.document_type = 'text'">
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text"/>
                                    </span>
                                </button>
                                <p>{{ $i18n.get('label_text') }}</p>
                            </li>
                            <li v-if="!hideLinkModalButton">
                                <button
                                        type="button"
                                        @click.prevent="form.document_type = 'url'">
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url"/>
                                    </span>
                                </button>
                                <p>{{ $i18n.get('label_url') }}</p>
                            </li>
                        </ul>
                    </div>

                </template>

                <!-- JS-side hook for extra content -->
                <div 
                        v-if="hasAfterHook('document')"
                        class="item-submission-hook item-submission-hook-document-after"
                        v-html="getAfterHook('document')" />

                <!-- JS-side hook for extra content -->
                <div 
                        v-if="hasBeforeHook('thumbnail')"
                        class="item-submission-hook item-submission-hook-thumbnail-before"
                        v-html="getBeforeHook('thumbnail')" />

                <!-- Thumbnail -------------------------------- -->
                <template v-if="!hideThumbnailSection">
                    <div
                            v-if="thumbnailSectionLabel"
                            class="section-label">
                        <label>{{ thumbnailSectionLabel }}</label>
                        <help-button
                                v-if="!hideHelpButtons && !helpInfoBellowLabel && $i18n.getHelperMessage('items', '_thumbnail_id')"
                                :title="$i18n.getHelperTitle('items', '_thumbnail_id')"
                                :message="$i18n.getHelperMessage('items', '_thumbnail_id')"/>
                        <p
                                class="metadatum-description-help-info"
                                v-if="!hideHelpButtons && helpInfoBellowLabel && $i18n.getHelperMessage('items', '_thumbnail_id')">
                            {{ $i18n.getHelperMessage('items', '_thumbnail_id') }}
                        </p>
                    </div>
                    <div class="section-toggle">
                        <p>{{ showThumbnailInput ? $i18n.get('info_thumbnail_custom') : $i18n.get('info_thumbnail_default_from_document') }}</p>
                        <div class="field has-addons">
                            <b-switch
                                    id="tainacan-checkbox-show-thumbnail-input"
                                    size="is-small"
                                    v-model="showThumbnailInput">
                                {{ $i18n.get('label_upload_custom_thumbnail') }}
                            </b-switch>
                        </div>
                    </div>
                    <div
                            v-if="!isLoading && showThumbnailInput"
                            class="section-box section-thumbnail"
                            id="tainacan-item-metadatum_id-thumbnail">
                        <b-upload
                                expanded
                                v-if="!form.thumbnail"
                                v-model="form.thumbnail"
                                drag-drop>
                            <section class="section">
                                <div class="content has-text-centered">
                                    <p>
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-upload" />
                                        </span>
                                    </p>
                                    <p>{{ $i18n.get('instruction_drop_file_or_click_to_upload') }}</p>
                                </div>
                            </section>
                        </b-upload>
                        <div
                                v-else
                                class="files-list">
                            <b-tag
                                    rounded
                                    closable
                                    attached
                                    :aria-close-label="$i18n.get('delete')"
                                    @close="form.thumbnail = null"
                                    :class="thumbnailErrorMessage ? 'is-danger' : ''">
                                {{ form.thumbnail.name }}
                            </b-tag>
                        </div>
                        <p
                                v-if="thumbnailErrorMessage"
                                class="help is-danger">
                            {{ thumbnailErrorMessage }}
                        </p>
                    </div>
                </template>

                <!-- JS-side hook for extra content -->
                <div 
                        v-if="hasAfterHook('thumbnail')"
                        class="item-submission-hook item-submission-hook-thumbnail-after"
                        v-html="getAfterHook('thumbnail')" />

                <!-- Hook for extra options -->
                <template v-if="hasEndLeftForm">
                    <form
                        id="form-item-end-left"
                        class="form-hook-region"
                        v-html="getEndLeftForm"/>
                </template>


                <!-- JS-side hook for extra content -->
                <div 
                        v-if="hasBeforeHook('attachments')"
                        class="item-submission-hook item-submission-hook-attachments-before"
                        v-html="getBeforeHook('attachments')" />

                <!-- Attachments ------------------------------------------ -->
                <template v-if="!hideAttachmentsSection">

                    <div class="section-label">
                        <label v-if="attachmentsSectionLabel">
                            <span>{{ attachmentsSectionLabel }}</span>
                            <span class="icon has-text-gray4">
                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-attachments"/>
                            </span>
                        </label>
                    </div>

                    <div
                            v-if="itemSubmission != undefined"
                            class="section-box"
                            id="tainacan-item-metadatum_id-attachments">
                        <b-upload
                                expanded
                                v-model="form.attachments"
                                multiple
                                drag-drop>
                            <section class="section">
                                <div class="content has-text-centered">
                                    <p>
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-upload" />
                                        </span>
                                    </p>
                                    <p>{{ $i18n.get('instruction_drop_file_or_click_to_upload') }}</p>
                                </div>
                            </section>
                        </b-upload>
                        <div
                                v-if="form.attachments && form.attachments.length"
                                class="files-list">
                            <b-tag
                                    v-for="(attachment, index) of form.attachments"
                                    :key="index"
                                    rounded
                                    closable
                                    attached
                                    :aria-close-label="$i18n.get('delete')"
                                    @close="form.attachments.splice(index, 1)"
                                    :class="attachmentsErrorMessage.includes(attachment.name) ? 'is-danger' : ''">
                                {{ attachment.name }}
                            </b-tag>
                        </div>
                        <p
                                v-if="attachmentsErrorMessage"
                                class="help is-danger">
                            {{ attachmentsErrorMessage }}
                        </p>
                    </div>
                </template>

                <!-- JS-side hook for extra content -->
                <div 
                        v-if="hasAfterHook('attachments')"
                        class="item-submission-hook item-submission-hook-attachments-after"
                        v-html="getAfterHook('attachments')" />

                <!-- Hook for extra options -->
                <template v-if="hasBeginRightForm">
                    <form
                        id="form-item-begin-right"
                        class="form-hook-region"
                        v-html="getBeginRightForm"/>
                </template>

                <!-- Comment Status ------------------------ -->
                <template v-if="showAllowCommentsSection">
                    <div class="section-label">
                        <label>{{ $i18n.get('label_comments') }}</label>
                        <help-button
                                v-if="!hideHelpButtons && !helpInfoBellowLabel && $i18n.getHelperMessage('items', 'comment_status')"
                                :title="$i18n.getHelperTitle('items', 'comment_status')"
                                :message="$i18n.getHelperMessage('items', 'comment_status')"/>
                        <p
                                class="metadatum-description-help-info"
                                v-if="!hideHelpButtons && helpInfoBellowLabel && $i18n.getHelperMessage('items', 'comment_status')">
                            {{ $i18n.getHelperMessage('items', 'comment_status') }}
                        </p>
                    </div>
                    <div class="section-toggle">
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
                </template>

                <!-- Metadata from Collection-------------------------------- -->
                <div class="section-label">
                    <label v-if="metadataSectionLabel">
                        <span>{{ metadataSectionLabel }}</span>
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-metadata"/>
                        </span>
                    </label>
                </div>

                <a
                        v-if="!showSteppedLayout && !hideCollapses"
                        class="collapse-all"
                        @click="toggleCollapseAll()">
                    {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                    <span class="icon">
                        <i
                                :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll }"
                                class="tainacan-icon tainacan-icon-1-25em"/>
                    </span>
                </a>

                <!-- JS-side hook for extra content -->
                <div 
                        v-if="hasBeforeHook('metadata')"
                        class="item-submission-hook item-submission-hook-metadata-before"
                        v-html="getBeforeHook('metadata')" />
                <component
                        v-if="metadataSections.length"
                        :is="showSteppedLayout ? 'b-steps' : 'div'" 
                        v-model="activeSectionStep"
                        :has-navigation="false"
                        type="is-secondary"
                        mobile-mode="compact"
                        size="is-small"
                        ref="item-submission-steps-layout">
                    <component
                            :is="showSteppedLayout ? 'b-step-item' : 'div'"
                            v-for="(metadataSection, sectionIndex) of metadataSections"
                            :key="sectionIndex"
                            :step="sectionIndex + 1"
                            :label="metadataSection.name"
                            :label-position="'right'"
                            :clickable="true"
                            :class="'metadata-section-slug-' + metadataSection.slug + (!showSteppedLayout && isSectionHidden(metadataSection.id) ? ' metadata-section-hidden' : '')"
                            :id="'metadata-section-id-' + metadataSection.id"
                            v-tooltip="{
                                content: !showSteppedLayout && isSectionHidden(metadataSection.id) ? $i18n.get('info_metadata_section_hidden_conditional') : false,
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }">
                        <div 
                                    v-if="!showSteppedLayout"
                                    class="metadata-section-header section-label">
                            <span   
                                    class="collapse-handle"
                                    @click="!hideCollapses && !isSectionHidden(metadataSection.id) ? toggleMetadataSectionCollapse(sectionIndex) : ''">
                                <span 
                                        v-if="!hideCollapses"
                                        class="icon"
                                        @click="toggleMetadataSectionCollapse(sectionIndex)">
                                    <i 
                                            :class="{
                                                'tainacan-icon-arrowdown' : (metadataSectionCollapses[sectionIndex] || formErrorMessage) && !isSectionHidden(metadataSection.id),
                                                'tainacan-icon-arrowright' : !(metadataSectionCollapses[sectionIndex] || formErrorMessage) || isSectionHidden(metadataSection.id)
                                            }"
                                            class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
                                </span>
                                <label>{{ metadataSection.name }}</label>
                                <help-button
                                        v-if="!hideHelpButtons &&
                                                !helpInfoBellowLabel &&
                                                metadataSection.description" 
                                        :title="metadataSection.name"
                                        :message="metadataSection.description" />
                            </span>
                        </div>
                        <transition name="filter-item">
                            <div 
                                    class="metadata-section-metadata-list"
                                    v-show="metadataSectionCollapses[sectionIndex] && (showSteppedLayout || !isSectionHidden(metadataSection.id))">

                                <!-- JS-side hook for extra content -->
                                <div 
                                        v-if="hasBeforeHook('metadata_section')"
                                        class="item-submission-hook item-submission-hook-metadata-section-before"
                                        v-html="getBeforeHook('metadata_section', { metadataSection: metadataSection, sectionIndex: sectionIndex })" />

                                <p
                                        class="metadatum-description-help-info"
                                        v-if="metadataSection.description && (!hideHelpButtons && helpInfoBellowLabel)">
                                    {{ metadataSection.description }}
                                </p>
                                <template v-if="itemMetadata && Array.isArray(itemMetadata)">
                                    <template v-for="(itemMetadatum, index) of itemMetadata.filter(anItemMetadatum => anItemMetadatum.metadatum.metadata_section_id == metadataSection.id)">
                                
                                        <!-- JS-side hook for extra content -->
                                        <div 
                                                :key="index"
                                                v-if="hasBeforeHook('metadatum')"
                                                class="item-submission-hook item-submission-hook-metadatum-before"
                                                v-html="getBeforeHook('metadatum', { metadatum: itemMetadatum.metadatum, index: index, metadataSection: metadataSection, sectionIndex: sectionIndex })" />

                                        <tainacan-form-item
                                                :key="index"
                                                v-if="enabledMetadata[index] == 'true'"
                                                :item-metadatum="itemMetadatum"
                                                :hide-collapses="hideCollapses"
                                                :hide-metadata-types="hideMetadataTypes"
                                                :hide-help-buttons="hideHelpButtons"
                                                :help-info-bellow-label="helpInfoBellowLabel"
                                                :is-collapsed="metadataCollapses[index]"
                                                @changeCollapse="onChangeCollapse($event, index)"/>

                                        <!-- JS-side hook for extra content -->
                                        <div 
                                                :key="index"
                                                v-if="hasAfterHook('metadatum')"
                                                class="item-submission-hook item-submission-hook-metadatum-after"
                                                v-html="getAfterHook('metadatum', { metadatum: itemMetadatum.metadatum, index: index, metadataSection: metadataSection, sectionIndex: sectionIndex })" />
                                    </template>
                                </template>
                                <!-- JS-side hook for extra content -->
                                <div 
                                        v-if="hasAfterHook('metadata_section')"
                                        class="item-submission-hook item-submission-hook-metadata-section-after"
                                        v-html="getAfterHook('metadata_section', { metadataSection: metadataSection, sectionIndex: sectionIndex })" />

                            </div>
                        </transition>

                    </component>

                </component>

                <!-- JS-side hook for extra content -->
                <div 
                        v-if="hasAfterHook('metadata')"
                        class="item-submission-hook item-submission-hook-metadata-after"
                        v-html="getAfterHook('metadata')" />

                <!-- Hook for extra options -->
                <template v-if="hasEndRightForm">
                    <form
                        id="form-item-end-right"
                        class="form-hook-region"
                        v-html="getEndRightForm"/>
                </template>


                <!-- Form errors area -->
                <div 
                        v-if="formErrorMessage"
                        class="form-error-area is-danger">
                    <div class="form-error-area-icon">
                        <a class="help-button has-text-danger">
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-alertcircle" />
                            </span>
                        </a>
                    </div>
                    <div class="form-error-area-messages">
                        <strong>{{ formErrorMessage }}</strong>
                        <template v-if="formErrors.length && formErrors[0].errors && formErrors[0].errors.length">
                            <p>{{ $i18n.get('instruction_click_error_to_go_to_metadata') }}</p>
                            <ol>
                                <template v-for="(error, index) of formErrors">
                                    <li 
                                            v-if="error.errors.length"
                                            :key="index">
                                        <a 
                                                v-if="['thumbnail', 'attachments', 'document'].includes(error.metadatum_id) || metadataElements[error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : '')]"
                                                class="has-text-danger"
                                                @click="goToErrorMetadatum(error)">
                                            {{ getErrorMessage(error.errors) }}
                                        </a>                           
                                        <p v-else>{{ getErrorMessage(error.errors) }}</p>
                                    </li>
                                </template>
                            </ol>
                        </template>
                    </div>
                </div>

                <!-- Google reCAPTCHA -->
                <template v-if="useCaptcha == 'yes'">
                    <div
                            class="g-recaptcha"
                            id="tainacan-g-recaptcha"
                            :data-sitekey="captchaSiteKey" />
                    <br>
                </template>

                <div
                        v-if="showTermsAgreementCheckbox"
                        class="terms-agreement-confirmation-section">
                    <b-field>
                        <b-checkbox
                                v-model="userHasAgreedToTerms"
                                size="is-medium">
                            <span v-html="termsAgreementMessage" />
                        </b-checkbox>
                    </b-field>
                </div>

                <footer class="form-submission-footer">

                    <!-- JS-side hook for extra content -->
                    <div 
                            v-if="hasBeforeHook('footer')"
                            class="item-submission-hook item-submission-hook-footer-before"
                            v-html="getBeforeHook('footer')" />

                    <div 
                            class="wp-block-buttons"
                            style="gap: 1rem;">
                        <div
                                class="wp-block-button is-style-outline"
                                style="margin-right: auto;">
                            <button 
                                    @click="onDiscard()"
                                    id="tainacan-item-submission-block-button--cancel"
                                    type="button"
                                    class="wp-block-button__link wp-element-button">
                                {{ $i18n.get('cancel') }}
                            </button>
                        </div>
                        <div 
                                v-if="showSteppedLayout && activeSectionStep > 0"
                                class="wp-block-button">
                            <button 
                                    @click="onPreviousStep()"
                                    id="tainacan-item-submission-block-button--previous"
                                    type="button"
                                    class="wp-block-button__link wp-element-button">
                                {{ $i18n.get('previous') }}
                            </button>
                        </div>
                        <div 
                                v-if="showSteppedLayout && activeSectionStep < metadataSections.length - 1"
                                class="wp-block-button">
                            <button 
                                    @click="onNextStep()"
                                    id="tainacan-item-submission-block-button--next"
                                    type="button"
                                    class="wp-block-button__link wp-element-button">
                                {{ $i18n.get('next') }}
                            </button>
                        </div>
                        <div 
                                v-if="!showSteppedLayout || activeSectionStep == metadataSections.length - 1"
                                class="wp-block-button">
                            <button 
                                    :disabled="showTermsAgreementCheckbox && !userHasAgreedToTerms"
                                    id="tainacan-item-submission-block-button--submit"
                                    @click="onSubmit()"
                                    type="button"
                                    class="wp-block-button__link wp-element-button">
                                {{ $i18n.get('label_submit') }}
                            </button>
                        </div>
                    </div>

                    <!-- JS-side hook for extra content -->
                    <div 
                            v-if="hasAfterHook('footer')"
                            class="item-submission-hook item-submission-hook-footer-after"
                            v-html="getAfterHook('footer')" />
                </footer>

            </form>

            <!-- Message displayed when the form is being submitted -->
            <section
                    v-if="isSubmitting || isUploading"
                    class="section"
                    id="submission-form-processing">
                <div class="content has-text-grey has-text-centered">
                    <br>
                    <p>
                        <span class="icon is-medium">
                            <i class="tainacan-icon tainacan-icon-30px tainacan-icon-updating tainacan-icon-spin"/>
                        </span>
                    </p>
                    <h2 id="submission-form-is-processing-label">{{ $i18n.get('label_sending_form') }}</h2>
                    <p
                            id="submission-form-is-processing-info"
                            v-if="isSubmitting">
                        {{ $i18n.get('info_submission_processing') }}
                    </p>
                    <p
                            id="submission-form-is-uploading-info"
                            v-if="isUploading">
                        {{ $i18n.get('info_submission_uploading') }}
                    </p>
                    <br>
                </div>
            </section>

            <!-- Message displayed once the form is submitted -->
            <section
                    v-if="hasSentForm"
                    class="section"
                    id="submission-form-sent">
                <div class="content has-text-grey has-text-centered">
                    <br>
                    <p>
                        <span class="icon is-medium">
                            <i class="tainacan-icon tainacan-icon-30px tainacan-icon-approvedcircle"/>
                        </span>
                    </p>
                    <div>
                        <h2 
                                id="submission-form-sent-label" 
                                v-if="sentFormHeading">
                            {{ sentFormHeading }}
                        </h2>
                        <p 
                                id="submission-form-sent-info"
                                v-if="sentFormMessage">
                            {{ sentFormMessage }}
                        </p>
                        <p 
                                id="submission-form-sent-link"
                                v-if="showItemLinkButton && linkToCreatedItem">
                            <a
                                    style="text-decoration: none"
                                    :href="linkToCreatedItem"
                                    class="button is-secondary">
                                {{ itemLinkButtonLabel }}
                            </a>
                        </p>
                    </div>
                    <br>
                </div>
            </section>

        </template>

        <!-- Message displayed if the collection could not be loaded -->
        <section
                v-else
                class="section">
            <div class="content has-text-grey has-text-centered">
                <br>
                <h2>{{ $i18n.get('label_form_not_loaded') }}</h2>
                <p>{{ $i18n.get('info_form_not_loaded') }}</p>
                <br>
            </div>
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { eventBusItemMetadata } from '../../../../admin/js/event-bus-item-metadata';
import { formHooks } from '../../../../admin/js/mixins';

export default {
    name: 'ItemSubmissionForm',
    mixins: [ formHooks ],
    props: {
        collectionId: String,
        hideFileModalButton: Boolean,
        hideTextModalButton: Boolean,
        hideLinkModalButton: Boolean,
        hideThumbnailSection: Boolean,
        hideAttachmentsSection: Boolean,
        showAllowCommentsSection: Boolean,
        hideCollapses: Boolean,
        hideHelpButtons: Boolean,
        hideMetadataTypes: Boolean,
        enabledMetadata: Array,
        sentFormHeading: String,
        sentFormMessage: String,
        documentSectionLabel: String,
        thumbnailSectionLabel: String,
        attachmentsSectionLabel: String,
        metadataSectionLabel: String,
        showItemLinkButton: Boolean,
        itemLinkButtonLabel: String,
        helpInfoBellowLabel: Boolean,
        showTermsAgreementCheckbox: Boolean,
        termsAgreementMessage: String,
        isLayoutSteps: Boolean
    },
    data(){
        return {
            collecionAllowsItemSubmission: true,
            isLoading: false,
            isLoadingMetadataSections: false,
            isSubmitting: false,
            isUploading: false,
            metadataCollapses: [],
            metadataSectionCollapses: [],
            collapseAll: true,
            form: {
                collection_id: Number,
                document: '',
                document_type: '',
                comment_status: '',
                attachments: [],
                thumbnail: '',
                document_options: {
                    forced_iframe: false,
                    forced_iframe_width: 600,
                    forced_iframe_height: 450,
                    is_image: false
                }
            },
            formErrorMessage: '',
            hasSentForm: false,
            showThumbnailInput: false,
            couldLoadCollection: true,
            useCaptcha: 'no',
            captchaSiteKey: tainacan_plugin['item_submission_captcha_site_key'],
            linkToCreatedItem: '',
            userHasAgreedToTerms: false,
            metadataElements: {},
            activeSectionStep: 0,
        }
    },
    computed: {
        showSteppedLayout() {
            return this.isLayoutSteps;
        },
        itemSubmission() {
            return this.getItemSubmission();
        },
        itemSubmissionMetadata() {
            return this.getItemSubmissionMetadata();
        },
        itemMetadata() {

            if ( !this.itemSubmissionMetadata || !this.itemSubmissionMetadata.length)
                return [];

            const realItemMetadata = JSON.parse(JSON.stringify(this.getMetadata()));
            const tweakedItemMetadata = realItemMetadata.map((metadatum) => {

                const metadatumValue = this.itemSubmissionMetadata.find((aMetadatum) => aMetadatum.metadatum_id == metadatum.id);

                // We need this because repository level metadata have an array of section IDs
                const metadatumSectionId = metadatum.metadata_section_id;
                if ( !Array.isArray(metadatumSectionId) )
                    return {
                        metadatum: metadatum,
                        item: {},
                        value: metadatumValue && metadatumValue.value ? metadatumValue.value : ''
                    }

                metadatum.metadata_section_id = 'default_section';

                // To find which is the section of this metadatum, we look for an intersection of the existeing sections
                // in this collection and the list of section ids in the repository metadata
                const intersectionOfSections = this.getMetadataSections() // We do not use the computed metadataSections here as we want to include every section, even those hidden by stepped layout
                    .filter((aMetadataSection) => metadatumSectionId.includes("" + aMetadataSection.id) && aMetadataSection.id !== 'default_section');
                     
                if (intersectionOfSections.length === 1)
                    metadatum.metadata_section_id = intersectionOfSections[0].id;                          
                    
                return {
                    metadatum: metadatum,
                    item: {},
                    value: metadatumValue && metadatumValue.value ? metadatumValue.value : ''
                };
               
            });
            return tweakedItemMetadata;
        },
        metadataSections() {
            return this.showSteppedLayout ? this.getMetadataSections().filter(aMetadataSection => !this.isSectionHidden(aMetadataSection.id)) : this.getMetadataSections();
        },
        formErrors() {
           return eventBusItemMetadata && eventBusItemMetadata.errors && eventBusItemMetadata.errors.length ? eventBusItemMetadata.errors : []
        },
        hasMoreThanOneDocumentTypeOption() {
            return [ this.hideFileModalButton, this.hideTextModalButton, this.hideLinkModalButton ].filter((option) => { return option == false }).length > 1;
        },
        documentErrorMessage() {
            const existingError = this.formErrors.find(error => error.metadatum_id == 'document');
            return existingError ? existingError.errors : '';
        },
        attachmentsErrorMessage() {
            const existingError = this.formErrors.find(error => error['attachments'] || error.metadatum_id == 'attachments');
            return existingError ? existingError.errors : '';
        },
        thumbnailErrorMessage() {
            const existingError = this.formErrors.find(error => error.metadatum_id == 'thumbnail');
            return existingError ? existingError.errors : '';
        },
        conditionalSections() {
            return eventBusItemMetadata && eventBusItemMetadata.conditionalSections ? eventBusItemMetadata.conditionalSections : [];
        },
    },
    created() {
        
        // Puts loading on form
        this.isLoading = true;

        // First, check if collections allows this whole thing
        this.fetchCollectionForItemSubmission(this.collectionId)
            .then((collection) => {

                // Gets update info from the collecion in case it has been updated
                this.collecionAllowsItemSubmission = collection.allows_submission == 'yes' ? true : false;
                this.useCaptcha = collection.submission_use_recaptcha;

                // Initialize clear data from store
                this.clearItemSubmission();

                eventBusItemMetadata.clearAllErrors();
                this.formErrorMessage = '';
                this.form.collection_id = this.collectionId;

                // CREATING NEW ITEM SUBMISSION
                this.createNewItem();

                eventBusItemMetadata.$on('hasErrorsOnForm', (hasErrors) => {
                    if (hasErrors) {
                        if (Array.isArray(this.formErrors)) {
                            for (let i = 0; i < this.metadataSectionCollapses.length; i++)
                                this.$set(this.metadataSectionCollapses, i, true);
                        }
                        this.formErrorMessage = this.formErrorMessage ? this.formErrorMessage : this.$i18n.get('info_errors_in_form');
                        this.loadMetadataElements();
                    } else
                        this.formErrorMessage = '';
                });
            })
            .catch(() => {
                this.collecionAllowsItemSubmission = false;
                this.isLoading = false;
            });

        // Loads Metadata Sections
        this.isLoadingMetadataSections = true;
        this.fetchMetadataSections({
                collectionId: this.collectionId
            })
            .then((metadataSections) => {
                this.metadataSectionCollapses = Array(metadataSections.length).fill(true);
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
    },
    mounted() {
        // Checks if only one type of document is allowed. In this case we preset document type
        if (!this.hideFileModalButton && this.hideTextModalButton && this.hideLinkModalButton)
            this.form.document_type = 'attachment';
        else if (this.hideFileModalButton && !this.hideTextModalButton && this.hideLinkModalButton)
            this.form.document_type = 'text';
        else if (this.hideFileModalButton && this.hideTextModalButton && !this.hideLinkModalButton)
            this.form.document_type = 'url';
    },
    beforeDestroy () {
        eventBusItemMetadata.$off('hasErrorsOnForm');
    },
    methods: {
        ...mapActions('item', [
            'setItemSubmission',
            'setItemSubmissionMetadata',
            'submitItemSubmission',
            'finishItemSubmission',
            'clearItemSubmission'
        ]),
        ...mapGetters('item',[
            'getItemSubmission',
            'getItemSubmissionMetadata',
        ]),
        ...mapActions('metadata',[
            'fetchMetadata',
            'fetchMetadataSections'
        ]),
        ...mapGetters('metadata',[
            'getMetadata',
            'getMetadataSections'
        ]),
        ...mapActions('collection',[
            'fetchCollectionForItemSubmission'
        ]),
        hasBeforeHook(location) {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.hasFilter(`tainacan_item_submission_collection_${this.collectionId}_${location}_before`);
            
            return false;
        },
        hasAfterHook(location) {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.hasFilter(`tainacan_item_submission_collection_${this.collectionId}_${location}_after`);

            return false;
        },
        getBeforeHook(location, entity = '') {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.applyFilters(`tainacan_item_submission_collection_${this.collectionId}_${location}_before`, entity);
            
            return '';
        },
        getAfterHook(location, entity = '') {
            if (wp !== undefined && wp.hooks !== undefined)
                return wp.hooks.applyFilters(`tainacan_item_submission_collection_${this.collectionId}_${location}_after`, entity);
            
            return '';
        },
        onSubmit() {

            // Puts loading on Item edit
            this.isSubmitting = true;

            let data = this.form;
            this.fillExtraFormData(data);
            this.updateExtraFormData(data);
            this.setItemSubmission(Object.assign(this.itemSubmission, data));

            // Clear errors so we don't have them duplicated from api
            eventBusItemMetadata.errors = [];

            this.submitItemSubmission({
                    itemSubmission: this.itemSubmission,
                    itemSubmissionMetadata: this.itemSubmissionMetadata,
                    captchaResponse: (this.useCaptcha == 'yes' && grecaptcha && this.captchaSiteKey) ? grecaptcha.getResponse() : null
                })
                .then((fakeItemId) => {

                    this.isUploading = true;
                    this.isSubmitting = false;

                    if (fakeItemId) {
                        this.finishItemSubmission({ itemSubmission: this.itemSubmission, fakeItemId: fakeItemId })
                            .then((item) => {
                                this.hasSentForm = true;
                                this.isUploading = false;

                                this.linkToCreatedItem = item.url;
                            })
                            .catch((errors) => {
                                if (errors.errors) {
                                    for (let error of errors.errors) {
                                        for (let metadatum of Object.keys(error)) {
                                            eventBusItemMetadata.errors.push({
                                                metadatum_id: metadatum,
                                                errors: error[metadatum]
                                            });
                                        }
                                    }
                                }
                                this.formErrorMessage = errors.error_message;

                                this.isSubmitting =  false;
                                this.hasSentForm = false;
                                this.isUploading = false;
                            });
                    }
                })
                .catch((errors) => {
                    if (errors.errors) {
                        for (let error of errors.errors) {
                            for (let metadatum of Object.keys(error)) {
                                eventBusItemMetadata.errors.push({
                                    metadatum_id: metadatum,
                                    errors: error[metadatum]
                                });
                            }
                        }
                    }
                    this.formErrorMessage = errors.error_message;

                    this.isSubmitting =  false;
                    this.hasSentForm = false;
                    this.isUploading = false;
                });
        },
        onDiscard() {
            // Initialize clear data from store
            this.clearItemSubmission();

            eventBusItemMetadata.clearAllErrors();
            this.formErrorMessage = '';
            this.form.collection_id = this.collectionId;

            // CREATING NEW ITEM SUBMISSION
            this.createNewItem();
        },
        createNewItem() {

            // Clear errors so we don't have them duplicated from api
            eventBusItemMetadata.errors = [];

            let data = JSON.parse(JSON.stringify(this.form));

            this.fillExtraFormData(data);
            this.setItemSubmission(data);

            // Loads metadata
            this.loadMetadata();
        },
        loadMetadata() {
            // Obtains Item Metadatum
            this.fetchMetadata({ collectionId: this.collectionId }).then(resp => {
                resp.request.then((metadata) => {
                    this.metadataCollapses = [];

                    for (let i = 0; i < metadata.length; i++) {
                        this.metadataCollapses.push(false);
                        this.metadataCollapses[i] = true;
                    }
                    this.setItemSubmissionMetadata( metadata.map((metadatum) => { return { metadatum_id: metadatum.id, value: null } }) );
                    this.couldLoadCollection = true;

                    // Mounts grecapcha
                    if (this.useCaptcha == 'yes') {
                        try {
                            grecaptcha.render('tainacan-g-recaptcha');
                        } catch(error) {
                            this.$console.log(error);
                        }
                    }

                    this.isLoading = false;
                })
                .catch(() => {
                    this.couldLoadCollection = false;
                    this.isLoading = false;
                });
            });
        },
        toggleCollapseAll() {
            this.collapseAll = !this.collapseAll;

            for (let i = 0; i < this.metadataCollapses.length; i++)
                this.metadataCollapses[i] = this.collapseAll;

            for (let i = 0; i < this.metadataSectionCollapses.length; i++)
                this.$set(this.metadataSectionCollapses, i, this.collapseAll);
        },
        onChangeCollapse(event, index) {
            this.metadataCollapses.splice(index, 1, event);
        },
        toggleMetadataSectionCollapse(sectionIndex) {
            this.$set(this.metadataSectionCollapses, sectionIndex, (this.formErrorMessage ? true : !this.metadataSectionCollapses[sectionIndex]));
        },
        getErrorMessage(errors) {
            let metadatumErrorMessage = '';
            for (let singleError of errors) {
                if (typeof singleError != 'string') {
                    for (let index of Object.keys(singleError))
                        metadatumErrorMessage += singleError[index] + '\n';
                } else {
                    metadatumErrorMessage += singleError;
                }
            }
            return metadatumErrorMessage;
        },
        loadMetadataElements() {
            this.metadataElements = {};
            this.formErrors.map((error) => {
                this.metadataElements[error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : '')] = document.getElementById('tainacan-item-metadatum_id-' + error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : ''));
            });
        },
        goToErrorMetadatum(error) {
            if ( ['thumbnail', 'attachments', 'document'].includes(error.metadatum_id) )
                this.metadataElements[error.metadatum_id].scrollIntoView({ behavior: 'smooth', block: 'center' });
            else if ( this.metadataElements[error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : '')] ) {

                if ( this.showSteppedLayout ) {
                    const stepWhereTheErrorIs = this.metadataSections.findIndex((aMetadataSection) => aMetadataSection.metadata_object_list.findIndex((aMetadatatum) => aMetadatatum.id == error.metadatum_id || aMetadatatum.id == error.parent_meta_id) >= 0);
                    if (stepWhereTheErrorIs >= 0)
                        this.activeSectionStep = stepWhereTheErrorIs;
                }
                this.metadataElements[error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : '')].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
        onPreviousStep() {
            if ( this.$refs['item-submission-steps-layout'] && typeof this.$refs['item-submission-steps-layout'].prev == 'function' )
                this.$refs['item-submission-steps-layout'].prev();
        },
        onNextStep() {
            if ( this.$refs['item-submission-steps-layout'] && typeof this.$refs['item-submission-steps-layout'].next == 'function' )
                this.$refs['item-submission-steps-layout'].next();
        },
        isSectionHidden(sectionId) {
            return this.conditionalSections[sectionId] && this.conditionalSections[sectionId].hide;
        }
    }
}
</script>

<style lang="scss" scoped>

.tainacan-item-submission-form {

    .tainacan-form {
        background-color: var(--tainacan-background-color);
        padding-left: var(--tainacan-one-column);
        padding-right: var(--tainacan-one-column);
        padding-top: 24px;
        padding-bottom: 10px;

        @media screen and (max-width: 769px) {
            max-width: 100%;
        }
        .field:not(:last-child) {
            margin-bottom: 0em;
        }
        .field {
            padding: 12px 0px 12px 34px;
            margin-left: 16px;
        }
        /deep/ input {
            box-sizing: border-box;
        }
        .columns {
            flex-wrap: wrap;
            justify-content: space-between;

            .column {
                padding: 1em 12px 0 12px;
            }
        }
    }

    .collapse-all {
        font-size: 0.75em;
        .icon {
            vertical-align: bottom;
        }
    }

    .metadata-section-hidden {
        opacity: 0.5;
        filter: grayscale(1.0);

        & > {
            pointer-events: none;
        }
    }

    .section-label {
        position: relative;
        margin-top: 14px;
        label {
            font-size: 1em !important;
            font-weight: 500 !important;
            color: var(--tainacan-label-color) !important;
            line-height: 1.2em;
            display: inline-block;
        }
    }

    .section-toggle p {
        font-size: 0.875em;
        margin-bottom: 0;
        padding-left: calc(0.75em - 1px);
    }

    .section-box {
        padding: 0 calc(0.75em - 1px);
        margin-top: 10px;
        margin-bottom: 14px;

        ul {
            padding: 0;
            display: flex;
            flex-wrap: wrap;

            li {
                margin-left: 0.25em;
                margin-right: 1.5em;
                text-align: center;
                button {
                    border-radius: 50px;
                    height: 72px;
                    width: 72px;
                    padding: 0;
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

    .metadatum-description-help-info {
        font-size: 0.75em;
        color: var(--tainacan-info-color);
        margin-bottom: 0.5em;
    }

    .document-field {
        ul {
            list-style: none;
        }
        .document-buttons-row {
            text-align: right;
            top: -21px;
            position: relative;
        }
    }

    .files-list {
        display: flex;
        flex-wrap: wrap;
    }

    .terms-agreement-confirmation-section {
        width: 100%;
        margin-top: 1.25em;

        .field {
            margin-left: 0;
            margin-right: 0;
            padding: 0.75em 0.75em 0.5em 0.75em;
            border: 1px dashed var(--tainacan-input-border-color);

            label {
                margin: 0;
            }
        }
        /deep/ .control-label {
            white-space: normal !important;
            overflow: visible;
        }
    }
    
    .form-error-area {
        font-size: 0.9375em;
        margin-top: 1rem;
        margin-bottom: 1.125rem;
        padding: 0.875em;
        display: flex;
        flex-wrap: nowrap;
        border: 1px solid var(--tainacan-red-2, #a23939);
        color: var(--tainacan-red-2, #a23939);
        background: var(--tainacan-red-1, #eadadc);

        .form-error-area-icon {
            font-size: 2rem;
            padding-right: 0.75rem;
        }

        .form-error-area-messages > p {
            margin-top: 0.25rem;
            margin-bottom: 0.25rem;
        }

        /deep/ ol {
            margin-top: 0;
            margin-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
    }

    .metadata-section-header {
        padding-bottom: 7px;
        border-bottom: 1px solid var(--tainacan-input-border-color);

        .icon {
            margin-left: -0.5rem;
        }
    }

    .form-submission-footer {
        padding: 18px 0;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1em;

        .wp-block-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        @keyframes blink {
            from { color: var(--tainacan-blue5); }
            to { color: var(--tainacan-info-color); }
        }

        .footer-message {
            display: flex;
            align-items: center;
            margin: 12px;
            font-size: 0.875em;
        }
        .update-info-section {
            color: var(--tainacan-info-color);
            margin-right: auto;
        }

        .help {
            display: inline-flex;
            font-size: 1.0em;
            margin-top: 0;
            margin-left: 24px;
        }
    }

    .b-steps {
        border: 1px solid var(--tainacan-input-border-color);
        border-radius: 2px;
        margin-top: 1em;

        /deep/ .steps {

            .step-items {
                margin-top: -1em;
                padding-right: 0px;
                margin-right: 0px;
                padding-left: 0px;
                margin-left: 0px;

                .step-item.is-active .step-title {
                    color: var(--tainacan-secondary);
                }
                .step-item:not(.is-active) .step-title {
                    color: var(--tainacan-label-color);
                }
            }
        }
    }

}
</style>
