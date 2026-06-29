<template>
    <div class="table-container">
        <div class="table-wrapper">

            <!-- Empty result placeholder, rendered in the parent component -->
            <slot />

            <!-- SKELETON LOADING -->
            <div
                    v-if="isLoading"
                    class="tainacan-cards-container">
                <div 
                        v-for="item in 12"
                        :key="item"
                        class="skeleton tainacan-card" />
            </div>

            <!-- CARDS VIEW MODE -->
            <div 
                    v-if="!isLoading && items.length > 0"
                    :role="items.length > 1 ? 'list' : undefined"
                    class="tainacan-cards-container">
                <div 
                        v-for="(item, index) of items"
                        :key="index"
                        :role="items.length > 1 ? 'listitem' : undefined"
                        :aria-setsize="totalItems"
                        :aria-posinset="getPosInSet(index)"
                        :data-tainacan-item-id="item.id">        

                    <a
                            class="tainacan-card"
                            :href="getItemLink(item.url, index)">     
                      
                        <!-- JS-side hook for extra content -->
                        <div 
                                v-if="hasBeforeHook()"
                                class="faceted-search-hook faceted-search-hook-item-before"
                                v-html="getBeforeHook(item)" />

                        <!-- Title -->
                        <div class="metadata-title">
                            <p 
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: item.title != undefined ? item.title : '',
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                    }"
                                    v-html="item.title != undefined ? item.title : ''" />                
                            <span 
                                    v-if="isSlideshowViewModeEnabled"
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 100,
                                        },
                                        content: $i18n.get('label_see_on_fullscreen'),
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                    }"
                                    role="button"
                                    tabindex="0"
                                    :aria-label="$i18n.get('label_see_on_fullscreen')"
                                    class="icon slideshow-icon"
                                    @click.prevent="starSlideshowFromHere(index)"
                                    @keydown.enter.prevent="starSlideshowFromHere(index)"
                                    @keydown.space.prevent="starSlideshowFromHere(index)">
                                <i
                                        class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"
                                        aria-hidden="true" />
                            </span>
                        </div>

                        <!-- Remaining metadata -->
                        <div class="media">
                            <div 
                                    v-if="!shouldHideItemsThumbnail && item['thumbnail']"
                                    class="card-thumbnail">
                                <blur-hash-image
                                        :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium', 120)"
                                        :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium', 120)"
                                        :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium')"
                                        :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium', item.document_mimetype)"
                                        :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium', item.document_mimetype)"
                                        :alt="item.thumbnail_alt ? item.thumbnail_alt : ''"
                                        :transition-duration="500"
                                    />
                            </div>
                            
                            <div class="list-metadata media-body">
                                <!-- Description -->
                                <p 
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: item.title != undefined ? item.title : '',
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"   
                                        class="metadata-description"
                                        v-html="item.description != undefined && item.description != '' ? getLimitedDescription(item.description) : `<span class='has-text-grey is-italic'>` + $i18n.get('label_description_not_provided') + `</span>`" />                                                        
                                <br>

                            </div>
                        </div>
                  
                        <!-- JS-side hook for extra content -->
                        <div 
                                v-if="hasAfterHook()"
                                class="faceted-search-hook faceted-search-hook-item-after"
                                v-html="getAfterHook(item)" />
                                
                    </a>
                </div>
            </div>
        </div> 
    </div>
</template>

<script>
import { viewModesMixin } from '../js/view-modes-mixin.js';

export default {
    name: 'ViewModeCards',
    mixins: [
        viewModesMixin
    ],
    computed: {
        descriptionMaxCharacter() {
            return (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= 480 ? (this.shouldHideItemsThumbnail ? 185 : 155) : (this.shouldHideItemsThumbnail ? 480 : 330);
        }
    },
    methods: {
        getLimitedDescription(description) {
            return description.length > this.descriptionMaxCharacter ? description.substring(0, this.descriptionMaxCharacter - 3) + '...' : description;
        }
    }
}
</script>

<style lang="scss" scoped>
    @use "../../../../../admin/scss/_view-mode-cards.scss";
    
    .tainacan-cards-container .tainacan-card .metadata-title {
        padding: 0.6em 0.75em;
    }
</style>


