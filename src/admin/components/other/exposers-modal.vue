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
                                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-url"/>
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
                                    class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
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
                                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-url"/>
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
    props: {
        collectionId: Number,
        totalItems: Number,
        itemId: Number,
        itemURL: String
    },
    directives: {
        focus: {
            inserted(el) {
                el.focus();
       
                if (el.value != undefined)
                    el.setSelectionRange(0,el.value.length)
            }
        }
    },
    data(){
        return {
            isLoading: false,
            siteLinkCopied: false,
            selectedExposer: undefined,
            selectedExposerMappers: [],
            maxItemsPerPage: tainacan_plugin.api_max_items_per_page,
            shouldRespectFetchOnly: false
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
        collectionName() {
            return this.getCollectionName();
        },
        collectionURL() {
            if (this.collectionId != undefined)
                return this.getCollectionURL();
            else    
                return tainacan_plugin.theme_items_list_url;
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
    methods: {
        ...mapActions('exposer', [
            'fetchAvailableExposers'
        ]),
        ...mapGetters('exposer', [
            'getAvailableExposers'
        ]),
        ...mapActions('collection', [
            'fetchCollectionNameAndURL'
        ]),
        ...mapGetters('collection', [
            'getCollectionName',
            'getCollectionURL'
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
            this.fetchCollectionNameAndURL(this.collectionId);
        }

        if (this.itemId)
            this.shouldRespectFetchOnly = false;

        if (this.$refs.exposersModal)
            this.$refs.exposersModal.focus()
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
            color: $secondary;
            cursor: pointer;
        }
    }

    .exposer-types-container {

        .exposer-item-link {
            padding-left: 0rem;
            p {
                padding-left: 0.5rem;
            }
        }
        
        p {
            font-size: 0.875rem;
            color: $gray5;
            padding: 0rem 1.25rem;
            margin-top: 0.75rem;
            margin-bottom: 0;
        }

        .exposer-types-list {
            display: flex;
            flex-wrap: wrap;
        
            .exposer-type {
                border: 1px solid $gray2;
                padding: 15px;
                margin: 24px 12px;
                cursor: pointer;
                max-width: 50%;
                flex-grow: 1;
                transition: border 0.3s ease;

                h4 {
                    font-size: 1rem;
                    font-weight: 500;
                    padding: 0rem 0.5rem;
                }
                p {
                    font-size: 0.75rem;
                    color: $gray5;
                    padding: 0rem 0.5rem;
                    margin-bottom: 0;
                }

                &:hover {
                    border: 1px solid $gray3;
                }
            }
        }
    }

    .exposer-item-container {

        .exposer-item {
        
            &:first-child {
                margin-top: 0.75rem;
            }
            &:last-child {
                border-bottom: none;
            }
            .collapse-handler:hover {
                cursor: pointer;
                background-color: $gray1;
            }
            .collapse-handle {
                cursor: pointer;
                .label {
                    margin: 3px 0.75rem 0 0;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    overflow: hidden;
                }
                .has-text-gray {
                    font-size: 0.75rem;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    overflow: hidden;
                }
            }
            p {
                padding: 0.5rem 0.75rem;
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
        padding-left: 0.125rem;
        height: 42px;

        &:first-of-type {
            margin-top: 0.5rem;
        }
        &>span {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;

            p { 
                margin: 0; 
                padding: 0rem 0.75rem;
            }
        }
        &:hover {
            background-color: $gray1;
            .exposer-item-actions {
                background-color: $gray2;
            }
        }
        .exposer-item-actions {
            display: flex;
            align-items: center;
            position: relative;

            a {
                cursor: pointer;
                margin: 0 0.5rem;
                color: $secondary;
                position: relative;
            }
            .exposer-copy-popup {
                animation-name: appear-from-top;
                animation-duration: 0.3s;
                position: absolute;
                background: $gray1;
                padding: 0.5rem 0.875rem 0.75rem 0.875rem;
                border-radius: 4px;
                top: 44px;
                right: 12px;
                z-index: 99999;

                .exposer-copy-popup-close {
                    position: absolute;
                    top: 6px;
                    right: 4px;
                }
                p { padding: 0 0 0.5rem 0; }
                input {
                    background-color: white;
                    border: 1px solid $gray2;
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
                    border-color: transparent transparent $gray1 transparent;
                    border-right-width: 14px;
                    border-bottom-width: 16px;
                    border-left-width: 14px;
                    top: -15px;
                }
            }
        }
    }

</style>


 
