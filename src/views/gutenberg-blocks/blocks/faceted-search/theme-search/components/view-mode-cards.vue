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
                        :key="item"
                        v-for="item in 12"
                        class="skeleton tainacan-card" />
            </div>

            <!-- CARDS VIEW MODE -->
            <div 
                    role="list"
                    v-if="!isLoading && items.length > 0"
                    class="tainacan-cards-container">
                <div 
                        role="listitem"
                        :key="index"
                        :aria-setsize="totalItems"
                        :aria-posinset="getPosInSet(index)"
                        :data-tainacan-item-id="item.id"
                        v-for="(item, index) of items">        

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
                                            shown: 500,
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
                                            shown: 500,
                                            hide: 100,
                                        },
                                        content: $i18n.get('label_see_on_fullscreen'),
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                    }"          
                                    @click.prevent="starSlideshowFromHere(index)"
                                    class="icon slideshow-icon">
                                <i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"/>
                            </span>
                        </div>

                        <!-- Remaining metadata -->
                        <div class="media">
                            <div 
                                    v-if="!shouldHideItemsThumbnail"
                                    class="card-thumbnail">
                            <blur-hash-image
                                    v-if="item.thumbnail != undefined"
                                    :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium', 120)"
                                    :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium', 120)"
                                    :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium')"
                                    :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium', item.document_mimetype)"
                                    :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium', item.document_mimetype)"
                                    :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                    :transition-duration="500"
                            />
                            </div>
                            
                            <div class="list-metadata media-body">
                            <!-- Description -->
                                <p 
                                        v-tooltip="{
                                            delay: {
                                                shown: 500,
                                                hide: 300,
                                            },
                                            content: item.title != undefined ? item.title : '',
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"   
                                        class="metadata-description"
                                        v-html="item.description != undefined && item.description != '' ? getLimitedDescription(item.description) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_description_not_provided') + `</span>`" />                                                        
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
    data() {
        return {
            shouldHideItemsThumbnail: this.$root.hideItemsThumbnail
        }
    },
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

    // Grid mixin for display: grid compatibility
    @mixin display-grid {
        flex-wrap: wrap;
        display: flex;
        display: -ms-grid;
        display: grid;
    }

    @import "../../../../../admin/scss/_view-mode-cards.scss";
    
    .tainacan-cards-container .tainacan-card .metadata-title {
        padding: 0.6em 0.75em;
    }
</style>


