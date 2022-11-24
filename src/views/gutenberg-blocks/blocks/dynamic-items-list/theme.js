import Vue from 'vue';
import DynamicItemsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'vue-blurhash';

export default (element) => {

    // Vue Dev Tools!
    Vue.config.devtools = TAINACAN_ENV === 'development';

    function renderTainacanDynamicItemsBlocks() {
    
        // Gets all divs with content created by our block;
        let blocksElements = element ? [ element ] : document.getElementsByClassName('wp-block-tainacan-dynamic-items-list');
        
        if (blocksElements) {
            let blocks = Object.values(blocksElements);

            // Checks if this carousel isn't already mounted
            blocks = blocks.filter((block) => block.classList && !block.classList.contains('has-mounted'));
            const blockIds = Object.values(blocks).map((block) => block.id);
    
            // Creates a new Vue Instance to manage each block isolatelly
            for (let blockId of blockIds) {
    
                // Configure Vue logic before passing it to constructor:
                let vueOptions = {
                    data: {
                        collectionId: '',  
                        showImage: true,
                        showName: true,
                        layout: 'grid',
                        gridMargin: 0,
                        searchURL: '',
                        selectedItems: [],
                        loadStrategy: 'search',
                        maxItemsNumber: 12,
                        mosaicHeight: 40,
                        mosaicDensity: 5,
                        mosaicGridRows: 3,
                        mosaicGridColumns: 3,
                        mosaicItemFocalPointX : 0.5,
                        mosaicItemFocalPointY : 0.5,
                        maxColumnsCount: 4,
                        imageSize: 'tainacan-medium',
                        order: 'asc',
                        orderBy: 'date',
                        orderByMetaKey: '',
                        showSearchBar: false,
                        showCollectionHeader: false,
                        showCollectionLabel: false,
                        collectionBackgroundColor: '#454647',
                        collectionTextColor: '#ffffff',
                        tainacanApiRoot: '',
                        tainacanBaseUrl: '',
                        className: '',
                        style: ''
                    },
                    render(h){ 
                        return h(DynamicItemsListTheme, {
                            props: {
                                collectionId: this.collectionId,  
                                showImage: this.showImage,
                                showName: this.showName,
                                layout: this.layout,
                                gridMargin: this.gridMargin,
                                mosaicDensity: this.mosaicDensity,
                                mosaicHeight: this.mosaicHeight,
                                mosaicGridRows: this.mosaicGridRows,
                                mosaicGridColumns: this.mosaicGridColumns,
                                mosaicItemFocalPointX: this.mosaicItemFocalPointX,
                                mosaicItemFocalPointY: this.mosaicItemFocalPointY,
                                maxColumnsCount: this.maxColumnsCount,
                                imageSize: this.imageSize,
                                searchURL: this.searchURL,
                                selectedItems: this.selectedItems,
                                loadStrategy: this.loadStrategy,
                                maxItemsNumber: this.maxItemsNumber,
                                order: this.order,
                                orderBy: this.orderBy,
                                orderByMetaKey: this.orderByMetaKey,
                                showSearchBar: this.showSearchBar,
                                showCollectionHeader: this.showCollectionHeader,
                                showCollectionLabel: this.showCollectionLabel,
                                collectionBackgroundColor: this.collectionBackgroundColor,
                                collectionTextColor: this.collectionTextColor,
                                tainacanApiRoot: this.tainacanApiRoot,
                                tainacanBaseUrl: this.tainacanBaseUrl,
                                className: this.className,
                                customStyle: this.style    
                            }
                        });
                    },
                    beforeMount () {
                        this.className = this.$el.attributes.class != undefined ? this.$el.attributes.class.value : undefined;
                        this.searchURL = this.$el.attributes['search-url'] != undefined ? this.$el.attributes['search-url'].value : undefined;
                        this.selectedItems = this.$el.attributes['selected-items'] != undefined ? JSON.parse(this.$el.attributes['selected-items'].value) : undefined;
                        this.loadStrategy = this.$el.attributes['load-strategy'] != undefined ? this.$el.attributes['load-strategy'].value : undefined;
                        this.collectionId = this.$el.attributes['collection-id'] != undefined ? this.$el.attributes['collection-id'].value : undefined;
                        this.showImage = this.$el.attributes['show-image'] != undefined ? this.$el.attributes['show-image'].value == 'true' : true;
                        this.showName = this.$el.attributes['show-name'] != undefined ? this.$el.attributes['show-name'].value == 'true' : true;
                        this.layout = this.$el.attributes['layout'] != undefined ? this.$el.attributes['layout'].value : undefined;
                        this.gridMargin = this.$el.attributes['grid-margin'] != undefined ? Number(this.$el.attributes['grid-margin'].value) : undefined;
                        this.mosaicDensity = this.$el.attributes['mosaic-density'] != undefined ? Number(this.$el.attributes['mosaic-density'].value) : undefined;
                        this.mosaicHeight = this.$el.attributes['mosaic-height'] != undefined ? Number(this.$el.attributes['mosaic-height'].value) : undefined;
                        this.mosaicGridRows = this.$el.attributes['mosaic-grid-rows'] != undefined ? Number(this.$el.attributes['mosaic-grid-rows'].value) : undefined;
                        this.mosaicGridColumns = this.$el.attributes['mosaic-grid-columns'] != undefined ? Number(this.$el.attributes['mosaic-grid-columns'].value) : undefined;
                        this.mosaicItemFocalPointX = this.$el.attributes['mosaic-item-focal-point-x'] != undefined ? Number(this.$el.attributes['mosaic-item-focal-point-x'].value) : undefined;
                        this.mosaicItemFocalPointY = this.$el.attributes['mosaic-item-focal-point-y'] != undefined ? Number(this.$el.attributes['mosaic-item-focal-point-y'].value) : undefined;
                        this.maxColumnsCount = this.$el.attributes['max-columns-count'] != undefined ? this.$el.attributes['max-columns-count'].value : 4;
                        this.imageSize = this.$el.attributes['image-size'] != undefined ? this.$el.attributes['image-size'].value : 'tainacan-medium';
                        this.maxItemsNumber = this.$el.attributes['max-items-number'] != undefined ? this.$el.attributes['max-items-number'].value : undefined;
                        this.order = this.$el.attributes['order'] != undefined ? this.$el.attributes['order'].value : undefined;
                        this.orderBy = this.$el.attributes['order-by'] != undefined ? this.$el.attributes['order-by'].value : undefined;
                        this.orderByMetaKey = this.$el.attributes['order-by-meta-key'] != undefined ? this.$el.attributes['order-by-meta-key'].value : undefined;
                        this.showSearchBar = this.$el.attributes['show-search-bar'] != undefined ? this.$el.attributes['show-search-bar'].value == 'true' : false;
                        this.showCollectionHeader = this.$el.attributes['show-collection-header'] != undefined ? this.$el.attributes['show-collection-header'].value == 'true' : false;
                        this.showCollectionLabel = this.$el.attributes['show-collection-label'] != undefined ? this.$el.attributes['show-collection-label'].value == 'true' : false;
                        this.collectionBackgroundColor = this.$el.attributes['collection-background-color'] != undefined ? this.$el.attributes['collection-background-color'].value : undefined;
                        this.collectionTextColor = this.$el.attributes['collection-text-color'] != undefined ? this.$el.attributes['collection-text-color'].value : undefined;
                        this.tainacanApiRoot = this.$el.attributes['tainacan-api-root'] != undefined ? this.$el.attributes['tainacan-api-root'].value : undefined;
                        this.tainacanBaseUrl = this.$el.attributes['tainacan-base-url'] != undefined ? this.$el.attributes['tainacan-base-url'].value : undefined;
                        this.style = this.$el.attributes.style != undefined ? this.$el.attributes.style.value : undefined;
                    },
                    methods: {
                        __(text, domain) {
                            return wp.i18n.__(text, domain);
                        }
                    }
                };
    
                Vue.use(ThumbnailHelperPlugin);
                Vue.use(VueBlurHash);
                new Vue( Object.assign({ el: '#' + blockId }, vueOptions) );
            }
        }
    }

    // This is rendered on the theme side.
    renderTainacanDynamicItemsBlocks();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("TainacanReloadDynamicItemsBlock", () => {
        renderTainacanDynamicItemsBlocks();
    });

}