<template>
    <div 
            ref="exposersModal"
            aria-labelledby="exposers-modal-title"
            autofocus
            role="dialog"
            tabindex="-1"
            aria-modal 
            class="tainacan-modal-content"
            :class="{ 'tainacan-repository-level-colors': isNaN(collectionId) || !collectionId }"
            style="width: auto">
        <header class="tainacan-modal-title">
            <h2 
                    v-if="selectedExposer == undefined"
                    id="exposers-modal-title">
                {{ itemId ? $i18n.get('label_urls_for_item_page') : ($i18n.get('label_urls_for_items_list') + (selectedItems && selectedItems.length ? (' (' + selectedItems.length + ' ' + $i18n.get('items') + ')') : '')) }}
            </h2>
            <h2 
                    v-if="selectedExposer != undefined"
                    id="exposers-modal-title">
                {{ (itemId ? $i18n.get('label_urls_for_item_page') : $i18n.get('label_urls_for_items_list')) + " - " + selectedExposer.name }}
            </h2>
            <button         
                    class="button is-medium is-white is-align-self-flex-start"
                    :aria-label="$i18n.get('close')"
                    @click="closeModal()">
                <span 
                        aria-hidden="true"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-close tainacan-icon-1-125em" />
                </span>
            </button>
        </header>
        <div
                class="sr-only"
                role="status"
                aria-live="polite"
                aria-atomic="true">
            {{ copyStatusAnnouncement }}
        </div>
        <section class="tainacan-form">
            <div 
                    v-if="selectedExposer == undefined"
                    class="exposer-types-container">
                <div class="exposer-item-link">
                    <span>
                        <p>
                            {{ itemId ? $i18n.get('label_item_page_on_website') : $i18n.get('label_items_list_on_website') }}
                        </p>
                    </span>
                    <span class="exposer-item-actions">
                        <button 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: $i18n.get('label_copy_link_url'),
                                    autoHide: false,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"         
                                type="button"
                                class="button link-style"
                                :aria-label="$i18n.get('label_copy_link_url')"
                                @click="copyTextToClipboard(itemURL ? itemURL : collectionURL, { skipToast: true }).then(ok => { if (ok) siteLinkCopied = true; })">
                            <span 
                                    aria-hidden="true"
                                    class="icon">
                                <i
                                        class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url"
                                        aria-hidden="true" />
                            </span>
                        </button>
                        <div 
                                v-if="siteLinkCopied == true"
                                class="exposer-copy-popup"
                                aria-hidden="true">
                            <p aria-hidden="true">{{ $i18n.get('info_url_copied') }}</p>
                            <button 
                                    aria-hidden="true"
                                    tabindex="-1"
                                    type="button"
                                    class="exposer-copy-popup-close"
                                    @click="siteLinkCopied = false"
                                    @keydown.enter="siteLinkCopied = false"
                                    @keydown.space="siteLinkCopied = false">
                                <span 
                                        aria-hidden="true"
                                        class="icon has-text-secondary">
                                    <i
                                            class="tainacan-icon tainacan-icon-close"
                                            aria-hidden="true" />
                                </span>
                            </button>
                            <input 
                                    readonly
                                    tabindex="-1"
                                    type="text"
                                    :value="itemURL ? itemURL : collectionURL"
                                    aria-hidden="true">
                        </div>
                        <a 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: $i18n.get('label_open_externally'),
                                    autoHide: false,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }" 
                                target="_blank"
                                :aria-label="$i18n.get('label_open_externally')"
                                :href="itemURL ? itemURL : collectionURL">
                            <span 
                                    aria-hidden="true"
                                    class="icon">
                                <i
                                        class="tainacan-icon tainacan-icon-18px tainacan-icon-openurl"
                                        aria-hidden="true" />
                            </span>
                        </a>
                    </span>
                </div>
                <h3 class="exposer-other-options-heading">
                    {{ itemId ? $i18n.get('info_other_options') : $i18n.get('info_other_item_listing_options') }}
                </h3>
                <div 
                        :role="availableExposers.length > 1 ? 'list' : undefined"
                        class="exposer-types-list tainacan-clickable-cards">
                    <button
                            v-for="(exposerType, index ) in availableExposers"
                            :key="index"
                            class="exposer-type tainacan-clickable-card"
                            :role="availableExposers.length > 1 ? 'listitem' : undefined"
                            type="button"
                            @click="siteLinkCopied = false; selectExposer(exposerType)"
                            @keydown.enter="siteLinkCopied = false; selectExposer(exposerType)"
                            @keydown.space="siteLinkCopied = false; selectExposer(exposerType)">
                        <dl class="exposer-type-definition">
                            <dt class="exposer-type-name">
                                {{ exposerType.name }}
                            </dt>
                            <dd class="exposer-type-description">
                                {{ exposerType.description }}
                            </dd>
                        </dl>
                    </button>
                </div>
            </div>
            
            <div
                    v-if="selectedExposer != undefined && (itemId == undefined || itemId == null)"
                    class="exposed-metadata-control">
                <b-checkbox
                        v-model="shouldRespectFetchOnly" 
                        v-tooltip="{
                            content: $i18n.get('info_expose_only_displayed_metadata'),
                            autoHide: true,
                            placement: 'bottom',
                            popperClass: ['tainacan-tooltip', 'tooltip']
                        }">
                    {{ $i18n.get('label_expose_only_displayed_metadata') }}
                </b-checkbox>
            </div>
            <div 
                    v-if="selectedExposer != undefined"
                    class="exposer-item-container"
                    :role="selectedExposerMappers.length > 1 ? 'list' : undefined">
                <b-field 
                        v-for="(exposerMapper, index) in selectedExposerMappers"
                        :key="index"
                        :addons="false"
                        class="exposer-item"
                        :role="selectedExposerMappers.length > 1 ? 'listitem' : undefined">
                    <span 
                            v-tooltip="{
                                delay: { show: 500, hide: 300 },
                                content: selectedExposer.name + (exposerMapper.name != undefined ? ': ' + exposerMapper.name + ' ' + $i18n.get('label_mapper') : ''),
                                autoHide: false,
                                placement: 'auto-end',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            role="button"
                            tabindex="0"
                            class="collapse-handle"
                            :aria-expanded="!exposerMapper.collapsed"
                            :aria-controls="'exposer-mapper-region-' + index"
                            :aria-label="selectedExposer.name + (exposerMapper.name != undefined ? ': ' + exposerMapper.name + ' ' + $i18n.get('label_mapper') : '')"
                            @click="collapse(index)"
                            @keydown.enter.prevent="collapse(index)"
                            @keydown.space.prevent="collapse(index)">
                        <span 
                                aria-hidden="true"
                                class="icon">
                            <i 
                                    :class="{ 'tainacan-icon-arrowdown' : !exposerMapper.collapsed, 'tainacan-icon-arrowright tainacan-icon-is-rtl-mirrored' : exposerMapper.collapsed }"
                                    class="has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                        </span>
                        <span 
                                aria-hidden="true"
                                class="label">
                            {{ selectedExposer.name + (exposerMapper.name != undefined ? ": " + exposerMapper.name + " " + $i18n.get('label_mapper') : '') }}
                        </span>
                    </span>
                    <transition name="filter-item">
                        <div 
                                v-show="!exposerMapper.collapsed"
                                :id="'exposer-mapper-region-' + index"
                                :role="totalPages > 1 ? 'list' : undefined"
                                class="exposer-item-links-list"
                                :aria-hidden="exposerMapper.collapsed">    
                            <div
                                    v-for="pagedLink in totalPages"
                                    :key="pagedLink"
                                    :role="totalPages > 1 ? 'listitem' : undefined"
                                    class="exposer-item-link">
                                <span>
                                    <p>
                                        {{ getItemPageLabel(pagedLink) }}
                                    </p>
                                </span>
                                <span class="exposer-item-actions">
                                    <button 
                                            v-tooltip="{
                                                delay: {
                                                    show: 500,
                                                    hide: 300,
                                                },
                                                content: $i18n.get('label_copy_link_url'),
                                                autoHide: false,
                                                placement: 'bottom',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            type="button"
                                            class="button link-style"
                                            :aria-label="$i18n.get('label_copy_link_url')"
                                            @click="copyTextToClipboard(getExposerFullURL(pagedLink, exposerMapper), { skipToast: true }).then(ok => { if (ok) exposerMapper.linkCopied = pagedLink; })">
                                        <span 
                                                aria-hidden="true"
                                                class="icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url" />
                                        </span>
                                    </button>
                                    <div 
                                            v-if="exposerMapper.linkCopied == pagedLink"
                                            class="exposer-copy-popup"
                                            aria-hidden="true">
                                        <p aria-hidden="true">{{ $i18n.get('info_url_copied') }}</p>
                                        <button 
                                                aria-hidden="true"
                                                tabindex="-1"
                                                type="button"
                                                class="exposer-copy-popup-close"
                                                @click="exposerMapper.linkCopied = undefined">
                                            <span 
                                                    aria-hidden="true"
                                                    class="icon has-text-secondary">
                                                <i
                                                        class="tainacan-icon tainacan-icon-close"
                                                        aria-hidden="true" />
                                            </span>
                                        </button>
                                        <input 
                                                tabindex="-1"
                                                aria-hidden="true"
                                                readonly
                                                type="text"
                                                :value="getExposerFullURL(pagedLink, exposerMapper)">
                                    </div>
                                    <a 
                                            v-tooltip="{
                                                delay: {
                                                    show: 500,
                                                    hide: 300,
                                                },
                                                content: $i18n.get('label_open_externally'),
                                                autoHide: false,
                                                placement: 'bottom',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            target="_blank"
                                            :aria-label="$i18n.get('label_open_externally')"
                                            :href="getExposerFullURL(pagedLink, exposerMapper)">
                                        <span 
                                                aria-hidden="true"
                                                class="icon">
                                            <i
                                                    class="tainacan-icon tainacan-icon-18px tainacan-icon-openurl"
                                                    aria-hidden="true" />
                                        </span>
                                    </a>
                                </span>  
                            </div>
                        </div>      
                    </transition>
                </b-field>
            </div>

            <b-loading 
                    v-model="isLoading"
                    :is-full-page="false" 
                    :can-cancel="false" />

            <footer class="field is-grouped form-submit">
                <div class="control">
                    <button 
                            class="button is-outlined" 
                            type="button" 
                            @click="closeModal()"
                            @keydown.enter="closeModal()"
                            @keydown.space="closeModal()">
                        {{ $i18n.get('close') }}
                    </button>
                </div>
            </footer>
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import qs from 'qs';

export default {
    name: 'ExposersModal',
    directives: {
        focus: {
            inserted(el) {
                el.focus();
       
                if (el.value != undefined)
                    el.setSelectionRange(0,el.value.length)
            }
        }
    },
    props: {
        collectionId: [String, Number],
        totalItems: Number,
        itemId: [String, Number],
        itemURL: String,
        selectedItems: Array
    },
    emits: [
        'close',
        'beforeClose'
    ],
    data(){
        return {
            isLoading: false,
            siteLinkCopied: false,
            selectedExposer: undefined,
            selectedExposerMappers: [],
            maxItemsPerPage: tainacan_plugin.api_max_items_per_page,
            shouldRespectFetchOnly: false,
            collectionURL: undefined,
            copyStatusAnnouncement: '',
            copyStatusAnnouncementTimeout: null
        }
    },
    computed: {
        totalPages() {
            return this.selectedItems && this.selectedItems.length ? 1 : Math.ceil(Number(this.totalItems)/Number(this.maxItemsPerPage));    
        },
        exposerBaseURL() {
            let baseURL = this.collectionId ? '/collection/' + this.collectionId + '/items/' : '/items/';
            let currentParams = JSON.parse(JSON.stringify(this.$route.query));

            // Removes Fetch Only
            if (currentParams.fetch_only != undefined && this.shouldRespectFetchOnly == false)
                delete currentParams.fetch_only;

            // Removes Fetch Only Meta
            if (currentParams.fetch_only_meta != undefined && this.shouldRespectFetchOnly == false)
                delete currentParams.fetch_only_meta;

            // Removes View Mode
            if (currentParams.view_mode != undefined)
                delete currentParams.view_mode;

            // Removes Admin View Mode
            if (currentParams.admin_view_mode != undefined)
                delete currentParams.admin_view_mode;

            // Handles pagination of this link
            delete currentParams.paged;
            if (this.itemId != null && this.itemId != undefined)
                delete currentParams.perpage;
            else 
                currentParams.perpage = this.maxItemsPerPage;

            // If selectedItems were provided, filters by them
            if (this.selectedItems && this.selectedItems.length) {
                currentParams.postin = this.selectedItems;
                delete currentParams.paged;
                currentParams.perpage = this.maxItemsPerPage;
            }

            return tainacan_plugin.tainacan_api_url + baseURL + '?' + qs.stringify(currentParams);
        },
        availableExposers() {
            let exposers = this.getAvailableExposers();

            let tainacanApiExposerIndex = exposers.findIndex((aExposer) => aExposer.slug == 'tainacan-api');
            if (tainacanApiExposerIndex < 0) {
                exposers.unshift({
                    accept_no_mapper: true,
                    class_name: 'API',
                    mappers: [],
                    name: this.$i18n.get('label_tainacan_api'),
                    description: this.$i18n.get('info_tainacan_api'),
                    slug: 'tainacan-api'   
                });
            }
            return exposers;
        }
    },
    mounted() {
        this.isLoading = true;
        this.fetchAvailableExposers()
            .then(() => {
                this.isLoading = false;
            }).catch((error) => {
                this.$console.log(error);
                this.isLoading = false;
            });

        if (this.collectionId != undefined) {
            this.fetchCollectionForExposer(this.collectionId)
                .then((collection) => this.collectionURL = collection.url);
        } else    
            this.collectionURL = tainacan_plugin.theme_items_list_url;

        if (this.itemId)
            this.shouldRespectFetchOnly = false;

        if (this.$refs.exposersModal)
            this.$refs.exposersModal.focus()
    },
    beforeUnmount() {
        if (this.copyStatusAnnouncementTimeout) {
            clearTimeout(this.copyStatusAnnouncementTimeout);
        }
    },
    methods: {
        ...mapActions('exposer', [
            'fetchAvailableExposers'
        ]),
        ...mapGetters('exposer', [
            'getAvailableExposers'
        ]),
        ...mapActions('collection', [
            'fetchCollectionForExposer'
        ]),
        closeModal() {
            this.$emit('beforeClose');
            this.$emit('close');
        },
        collapse(index) {
            let exposerMapper = this.selectedExposerMappers[index];
            Object.assign( exposerMapper, { 'collapsed': !exposerMapper.collapsed });
            Object.assign( this.selectedExposerMappers, { [index]: exposerMapper });
        },
        selectExposer(exposerType) {
            this.selectedExposer = exposerType;

            this.selectedExposerMappers = [];
            this.selectedExposerMappers.push({
                name: undefined,
                collapsed: false,
                linkCopied: false
            });

            for (let exposerMapper of this.selectedExposer.mappers) {
                this.selectedExposerMappers.push({
                    name: exposerMapper,
                    collapsed: true,
                    linkCopied: false
                });
            }
        },
        getExposerFullURL(pagedLink, exposerMapper) {

            let params = {};

            if (this.selectedExposer.slug != 'tainacan-api')
                params.exposer = this.selectedExposer.slug;
                
            if (exposerMapper.name != undefined)
                params.mapper = exposerMapper.name;

            if (this.itemId != undefined && this.itemId != null)
                params.id = this.itemId;

            if (pagedLink && (this.itemId == undefined || this.itemId == null))
                params.paged = pagedLink;

            if (tainacan_user.nonce)
                params._wpnonce = tainacan_user.nonce;

            return this.exposerBaseURL + '&' + qs.stringify(params);
        },
        getItemPageLabel(pagedLink) {
            if (this.itemId != undefined && this.itemId != null) {
                return this.$i18n.get('label_item_page');
            } else {
                const first = this.getFirstItemNumber(pagedLink);
                const last = this.getLastItemNumber(pagedLink);
                const total = this.totalItems;
                
                return this.$i18n.getWithVariables('info_page_items_range', [pagedLink, first, last, total]);
            }

        },
        fallbackCopyTextToClipboard(text, skipToast = false) {
            let textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            let successful = false;
            try {
                successful = document.execCommand('copy');
                this.$console.log('Fallback: Copying text command was ' + (successful ? 'successful' : 'unsuccessful'));
                if (successful && !skipToast) {
                    this.$buefy.toast.open({
                        duration: 3000,
                        message: this.$i18n.get('info_url_copied'),
                        position: 'is-bottom',
                        type: 'is-primary',
                        queue: true
                    });
                }
            } catch (err) {
                this.$console.error('Fallback: Oops, unable to copy', err);
            }

            document.body.removeChild(textArea);
            return successful;
        },
        announceCopyStatus(message) {
            if (this.copyStatusAnnouncementTimeout) {
                clearTimeout(this.copyStatusAnnouncementTimeout);
            }
            this.copyStatusAnnouncement = '';
            this.$nextTick(() => {
                this.copyStatusAnnouncement = message;
                this.copyStatusAnnouncementTimeout = setTimeout(() => {
                    this.copyStatusAnnouncement = '';
                    this.copyStatusAnnouncementTimeout = null;
                }, 2000);
            });
        },
        copyTextToClipboard(text, options = {}) {
            const skipToast = options.skipToast === true;

            if (!navigator.clipboard) {
                const successful = this.fallbackCopyTextToClipboard(text, skipToast);
                if (!successful) {
                    this.announceCopyStatus(this.$i18n.get('info_copy_to_clipboard_failed'));
                    this.$buefy.toast.open({
                        duration: 4000,
                        message: this.$i18n.get('info_copy_to_clipboard_failed'),
                        position: 'is-bottom',
                        type: 'is-warning',
                        queue: true
                    });
                } else if (skipToast) {
                    this.announceCopyStatus(this.$i18n.get('info_url_copied'));
                }
                return Promise.resolve(successful);
            }

            return navigator.clipboard.writeText(text)
                .then(() => {
                    this.$console.log('Async: Copying to clipboard was successful!');
                    if (skipToast) {
                        this.announceCopyStatus(this.$i18n.get('info_url_copied'));
                    } else {
                        this.$buefy.toast.open({
                            duration: 3000,
                            message: this.$i18n.get('info_url_copied'),
                            position: 'is-bottom',
                            type: 'is-dark',
                            queue: true
                        });
                    }
                    return true;
                })
                .catch((err) => {
                    this.$console.error('Async: Could not copy text: ', err);
                    this.announceCopyStatus(this.$i18n.get('info_copy_to_clipboard_failed'));
                    this.$buefy.toast.open({
                        duration: 4000,
                        message: this.$i18n.get('info_copy_to_clipboard_failed'),
                        position: 'is-bottom',
                        type: 'is-warning',
                        queue: true
                    });
                    return false;
                });
        },
        getLastItemNumber(page) {
            let last = (this.selectedItems && this.selectedItems.length) ? this.selectedItems.length : (Number(this.maxItemsPerPage*(page - 1)) + Number(this.maxItemsPerPage));
            
            return last > this.totalItems ? this.totalItems : last;
        },
        getFirstItemNumber(page){
            if( this.totalItems == 0 )
                return 0;
            return ( this.maxItemsPerPage * ( page - 1 ) + 1)
        },
    }
}
</script>

<style lang="scss" scoped>

    @use '../../scss/_cards.scss';

    .tainacan-modal-title {
        margin-bottom: 24px;

        h2 {
            margin-bottom: 0;
        }
    }

    .exposer-other-options-heading {
        font-size: 1.125em !important;
        font-weight: normal;
        color: var(--tainacan-heading-color);
        display: inline-block;
        margin-inline-start: 0;
        margin-inline-end: auto;
        margin-block-start: 1em;
        margin-block-end: 0.5em;
    }

    .exposer-types-container {

        .exposer-item-link {
            padding-inline-start: 0em;
            
            p {
                padding-inline-start: 0.5em;
            }
        }
    }

    .exposer-item-container {

        .exposer-item {
        
            &:first-child {
                margin-top: 0.75em;
            }
            &:last-child {
                border-bottom: none;
            }
            .collapse-handler:hover {
                cursor: pointer;
                background-color: var(--tainacan-item-hover-background-color);
            }
            .collapse-handle {
                cursor: pointer;
                .label {
                    color: var(--tainacan-label-color);
                    margin: 3px 0.75em 0 0;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    overflow: hidden;
                }
                .has-text-dark {
                    font-size: 0.75em;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    overflow: hidden;
                }
            }
            p {
                padding: 0.5em 0.75em;
            }

            // .exposer-item-links-list {
            //     max-height: 50vh;
            //     overflow: auto;
            // }
        }
    }

    .exposed-metadata-control {
        display: flex;
        justify-content: flex-end;

        .checkbox {
            width: auto;
        }
    }

    .exposer-item-link {
        margin: 0; 
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-inline-start: 0.125em;
        height: 3em;
        border-bottom: 1px solid var(--tainacan-lists-separator-color, var(--tainacan-item-hover-background-color));            

        &:first-of-type {
            margin-top: 0.5em;
        }
        &>span {
            padding: 0.5em 0.75em;
            font-size: 1em;

            p { 
                margin: 0; 
                padding: 0em 0.75em;
                font-size: 0.875em;
            }
        }
        &:hover {
            background-color: var(--tainacan-item-hover-background-color);
            .exposer-item-actions {
                background-color: var(--tainacan-item-hover-background-color);
            }
        }
        .exposer-item-actions {
            display: flex;
            align-items: center;
            position: relative;

            a {
                cursor: pointer;
                margin: 0 0.5em;
                color: var(--tainacan-secondary);
                position: relative;
            }
            .exposer-copy-popup {
                animation-name: appear-from-top-tooltip;
                animation-duration: 0.3s;
                position: absolute;
                box-shadow: 0 0 8px -6px rgba(0, 0, 0, 0.5);
                background: var(--tainacan-item-hover-background-color);
                padding: 0.5em 0.875em 0.75em 0.875em;
                border-radius: 4px;
                top: 44px;
                right: 12px;
                z-index: 99999;
                min-width: 300px;

                .exposer-copy-popup-close {
                    position: absolute;
                    top: 6px;
                    right: 4px;
                    background: none;
                    border: none;
                    padding: 0;
                    cursor: pointer;
                    color: inherit;
                }
                p { padding: 0 0 0.5em 0; }
                input {
                    background-color: var(--tainacan-input-background-color);
                    border: 1px solid var(--tainacan-input-border-color);
                    border-radius: 0;
                    padding: 2px 8px;
                    width: 100%;
                }
                &:before {
                    content: "";
                    display: block;
                    position: absolute;
                    right: 46px;
                    width: 0;
                    height: 0;
                    border-style: solid;
                    border-color: transparent transparent var(--tainacan-item-hover-background-color) transparent;
                    border-right-width: 14px;
                    border-bottom-width: 16px;
                    border-left-width: 14px;
                    top: -15px;
                }
            }
        }
    }

</style>


 
