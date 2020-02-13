<template>
    <div 
            aria-labelledby="exposers-modal-title"
            autofocus
            role="dialog"
            tabindex="-1"
            aria-modal
            class="tainacan-modal-content" 
            style="width: auto"
            ref="exposersModal">
        <header class="tainacan-modal-title">
            <h2 
                    id="exposers-modal-title"
                    v-if="selectedExposer == undefined">
                {{ itemId ? $i18n.get('label_urls_for_item_page') : $i18n.get('label_urls_for_items_list') }}
            </h2>
            <h2 
                    id="exposers-modal-title"
                    v-if="selectedExposer != undefined">
                {{ (itemId ? $i18n.get('label_urls_for_item_page') : $i18n.get('label_urls_for_items_list')) + " - " + selectedExposer.name }}
            </h2>
            <a 
                    @click="selectedExposerMappers = []; selectedExposer = undefined;"
                    v-if="selectedExposer != undefined"
                    class="back-link">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </header>
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
                        <a 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: $i18n.get('label_copy_link_url'),
                                    autoHide: false,
                                    placement: 'bottom'
                                }" 
                                target="_blank"
                                @click="siteLinkCopied = true; copyTextToClipboard(itemURL ? itemURL : collectionURL)">
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url"/>
                            </span>
                        </a>
                        <div 
                                v-if="siteLinkCopied == true"
                                class="exposer-copy-popup">
                            <p>{{ $i18n.get('info_url_copied') }}</p>
                            <a 
                                    class="exposer-copy-popup-close"
                                    @click="siteLinkCopied = false">
                                <span class="icon has-text-secondary">
                                    <i class="tainacan-icon tainacan-icon-close" />
                                </span>
                            </a>
                            <input 
                                    readonly
                                    autofocus
                                    type="text"
                                    :value="itemURL ? itemURL : collectionURL">
                        </div>
                        <a 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: $i18n.get('label_open_externally'),
                                    autoHide: false,
                                    placement: 'bottom'
                                }" 
                                target="_blank"
                                :href="itemURL ? itemURL : collectionURL">
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-openurl"/>
                            </span>
                        </a>
                    </span>
                </div>
                <p>{{ itemId ? $i18n.get('info_other_options') : $i18n.get('info_other_item_listing_options') }}</p>
                <div 
                        role="list"
                        class="exposer-types-list">
                    <div
                            class="exposer-type"
                            role="listitem"
                            v-for="(exposerType, index ) in availableExposers"
                            :key="index"
                            @click="siteLinkCopied = false; selectExposer(exposerType)">
                        <h4>{{ exposerType.name }}</h4>
                        <p>{{ exposerType.description }}</p>            
                    </div>
                </div>
            </div>
            
            <div 
                    v-if="selectedExposer != undefined"
                    class="exposer-item-container"
                    role="list">
                <div
                        v-if="itemId == undefined || itemId == null"
                        class="exposed-metadata-control">
                    <b-checkbox
                            v-tooltip="{
                                content: $i18n.get('info_expose_only_displayed_metadata'),
                                autoHide: true,
                                placement: 'bottom'
                            }" 
                            v-model="shouldRespectFetchOnly">{{ $i18n.get('label_expose_only_displayed_metadata') }}</b-checkbox>
                </div>
                <b-field 
                        :addons="false"
                        class="exposer-item"
                        role="listitem"
                        v-for="(exposerMapper, index) in selectedExposerMappers"
                        :key="index">
                    <span 
                            @click="collapse(index)"
                            class="collapse-handle">
                        <span class="icon">
                            <i 
                                    :class="{ 'tainacan-icon-arrowdown' : !exposerMapper.collapsed, 'tainacan-icon-arrowright' : exposerMapper.collapsed }"
                                    class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
                        </span>
                        <label 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: selectedExposer.name + (exposerMapper.name != undefined ? ': ' + exposerMapper.name + ' ' + $i18n.get('label_mapper') : ''),
                                    autoHide: false,
                                    placement: 'auto-end'
                                }" 
                                class="label">
                            {{ selectedExposer.name + (exposerMapper.name != undefined ? ": " + exposerMapper.name + " " + $i18n.get('label_mapper') : '') }}
                        </label>
                    </span>
                    <transition name="filter-item">
                        <div 
                                role="list"
                                class="exposer-item-links-list"
                                v-show="!exposerMapper.collapsed">    
                            <div
                                    role="listitem"
                                    :key="pagedLink"
                                    v-for="pagedLink in totalPages"
                                    class="exposer-item-link">
                                <span>
                                    <p>
                                        {{ getItemPageLabel(pagedLink) }}
                                    </p>
                                </span>
                                <span class="exposer-item-actions">
                                    <a 
                                            v-tooltip="{
                                                delay: {
                                                    show: 500,
                                                    hide: 300,
                                                },
                                                content: $i18n.get('label_copy_link_url'),
                                                autoHide: false,
                                                placement: 'bottom'
                                            }"
                                            @click="exposerMapper.linkCopied = pagedLink; copyTextToClipboard(getExposerFullURL(pagedLink, exposerMapper))">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url"/>
                                        </span>
                                    </a>
                                    <div 
                                            v-if="exposerMapper.linkCopied == pagedLink"
                                            class="exposer-copy-popup">
                                        <p>{{ $i18n.get('info_url_copied') }}</p>
                                        <a 
                                                class="exposer-copy-popup-close"
                                                @click="exposerMapper.linkCopied = undefined">
                                            <span class="icon has-text-secondary">
                                                <i class="tainacan-icon tainacan-icon-close" />
                                            </span>
                                        </a>
                                        <input 
                                                v-focus
                                                readonly
                                                autofocus
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
                                                placement: 'bottom'
                                            }"
                                            target="_blank" 
                                            :href="getExposerFullURL(pagedLink, exposerMapper)">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-openurl"/>
                                        </span>
                                    </a>
                                </span>  
                            </div>
                        </div>      
                    </transition>
                </b-field>
            </div>

            <b-loading 
                    :is-full-page="false"
                    :active.sync="isLoading" 
                    :can-cancel="false"/>

            <footer class="field is-grouped form-submit">
                <div class="control">
                    <button 
                            class="button is-outlined" 
                            type="button" 
                            @click="$parent.close()">Close</button>
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
        collectionId: Number,
        totalItems: Number,
        itemId: Number,
        itemURL: String
    },
    data(){
        return {
            isLoading: false,
            siteLinkCopied: false,
            selectedExposer: undefined,
            selectedExposerMappers: [],
            maxItemsPerPage: tainacan_plugin.api_max_items_per_page,
            shouldRespectFetchOnly: false,
            collectionURL: undefined
        }
    },
    computed: {
        totalPages() {
            return Math.ceil(Number(this.totalItems)/Number(this.maxItemsPerPage));    
        },
        exposerBaseURL() {
            let baseURL = this.collectionId != undefined ? '/collection/' + this.collectionId + '/items/' : '/items/';
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
        collapse(index) {
            let exposerMapper = this.selectedExposerMappers[index];
            this.$set(exposerMapper, 'collapsed', !exposerMapper.collapsed);
            this.$set(this.selectedExposerMappers, index, exposerMapper);
        },
        selectExposer(exposerType) {
            this.selectedExposer = exposerType;

            this.selectedExposerMappers = [];
            this.selectedExposerMappers.push({
                name: undefined,
                collapsed: true,
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

            if (tainacan_plugin.nonce)
                params._wpnonce = tainacan_plugin.nonce;

            return this.exposerBaseURL + '&' + qs.stringify(params);
        },
        getItemPageLabel(pagedLink) {
            if (this.itemId != undefined && this.itemId != null) {
                return this.$i18n.get('label_item_page');
            } else {
                return  this.$i18n.get('label_page') + " " + pagedLink + " (" + 
                        this.$i18n.get('items') + " " + this.getFirstItemNumber(pagedLink) + " " +
                        this.$i18n.get('info_to') + " " + this.getLastItemNumber(pagedLink) + " " + 
                        this.$i18n.get('info_of') + " " + this.totalItems + ")";
            }

        },
        fallbackCopyTextToClipboard(text) {
            let textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                let successful = document.execCommand('copy');
                let msg = successful ? 'successful' : 'unsuccessful';
                this.$console.log('Fallback: Copying text command was ' + msg);
                if (msg == 'sucessful') {
                    this.$buefy.toast.open({
                        duration: 3000,
                        message: this.$i18n.get('info_url_copied'),
                        position: 'is-bottom',
                        type: 'is-secondary',
                        queue: true
                    });
                }
            } catch (err) {
                this.$console.error('Fallback: Oops, unable to copy', err);
            }

            document.body.removeChild(textArea);
        },
        copyTextToClipboard(text) {

            if (!navigator.clipboard) {
                this.fallbackCopyTextToClipboard(text);
                return;
            }
            
            navigator.clipboard.writeText(text)
                .then(() => {
                    this.$console.log('Async: Copying to clipboard was successful!');
                    this.$buefy.toast.open({
                        duration: 3000,
                        message: this.$i18n.get('info_url_copied'),
                        position: 'is-bottom',
                        type: 'is-secondary',
                        queue: true
                    });
                }, 
                (err) => {
                    this.$console.error('Async: Could not copy text: ', err);
                });
        },
        getLastItemNumber(page) {
            let last = (Number(this.maxItemsPerPage*(page - 1)) + Number(this.maxItemsPerPage));
            
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

    @import "../../scss/_variables.scss";

    .tainacan-modal-title {
        margin-bottom: 24px;

        h2 {
            margin-bottom: 0;
        }
        .back-link {
            color: var(--tainacan-secondary);
            cursor: pointer;
        }
    }

    .exposer-types-container {

        .exposer-item-link {
            padding-left: 0em;
            p {
                padding-left: 0.5em;
            }
        }
        
        p {
            font-size: 1em;
            color: var(--tainacan-gray5);
            padding: 0em 1.25em;
            margin-top: 0.75em;
            margin-bottom: 0;
        }

        .exposer-types-list {
            display: flex;
            flex-wrap: wrap;
        
            .exposer-type {
                border: 1px solid var(--tainacan-input-border-color);
                padding: 15px;
                margin: 24px 12px;
                cursor: pointer;
                max-width: 50%;
                flex-grow: 1;
                font-size: 1em;
                transition: border 0.3s ease;

                h4 {
                    color: var(--tainacan-heading-color);
                    font-size: 1em;
                    font-weight: 500;
                    padding: 0em 0.5em;
                    margin: 0;
                }
                p {
                    font-size: 0.75em;
                    color: var(--tainacan-gray5);
                    padding: 0em 0.5em;
                    margin-bottom: 0;
                }

                &:hover {
                    border: 1px solid var(--tainacan-gray3);
                }
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
                background-color: var(--tainacan-gray1);
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
                .has-text-gray {
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
        padding-left: 0.125em;
        height: 42px;

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
            background-color: var(--tainacan-gray1);
            .exposer-item-actions {
                background-color: var(--tainacan-gray2);
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
                animation-name: appear-from-top;
                animation-duration: 0.3s;
                position: absolute;
                background: var(--tainacan-gray1);
                padding: 0.5em 0.875em 0.75em 0.875em;
                border-radius: 4px;
                top: 44px;
                right: 12px;
                z-index: 99999;

                .exposer-copy-popup-close {
                    position: absolute;
                    top: 6px;
                    right: 4px;
                }
                p { padding: 0 0 0.5em 0; }
                input {
                    background-color: var(--tainacan-white);
                    border: 1px solid var(--tainacan-gray2);
                    border-radius: 0;
                    padding: 2px 8px;
                }
                &:before {
                    content: "";
                    display: block;
                    position: absolute;
                    right: 46px;
                    width: 0;
                    height: 0;
                    border-style: solid;
                    border-color: transparent transparent var(--tainacan-gray1) transparent;
                    border-right-width: 14px;
                    border-bottom-width: 16px;
                    border-left-width: 14px;
                    top: -15px;
                }
            }
        }
    }

</style>


 
