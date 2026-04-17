<template>
    <div class="tabs">
        <ul 
                v-a11y-tabs
                role="tablist"
                data-orientation="horizontal">               
            <li 
                    :tabindex="-1"
                    :class="{ 'is-active': status == undefined || status == '' || status == 'publish,private,pending,draft' }">
                <a
                        id="items-status-tab-all"
                        v-tooltip="{
                            content: $i18n.get('info_items_tab_all'),
                            autoHide: true,
                            placement: 'auto',
                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                        }"
                        role="tab"
                        :aria-selected="status == undefined || status == '' || status == 'publish,private,pending,draft'"
                        :tabindex="(status == undefined || status == '' || status == 'publish,private,pending,draft') ? 0 : -1"
                        style="font-weight: bold;"
                        @click="onChangeTab('')">
                    {{ $i18n.get('label_all_items') }}
                    <span 
                            v-if="!$adminOptions.hideItemsListStatusTabsTotalItems && !$route.query.authorid"
                            class="has-text-dark">
                        &nbsp;{{ (isRepositoryLevel && repositoryTotalItems) ? ` (${ $statusHelper.sumTotalItemsByStatus(repositoryTotalItems) })` : (collection && collection.total_items ? ` (${$statusHelper.sumTotalItemsByStatus(collection.total_items)})` : '') }}
                    </span>
                </a>
            </li>
            <template
                    v-for="(statusOption, index) of $statusHelper.getStatuses()"
                    :key="index">
                <li 
                        :tabindex="-1"
                        :class="{ 'is-active': status == statusOption.slug}"
                        :style="{ marginInlineEnd: statusOption.slug == 'draft' ? 'auto' : '', marginInlineStart: statusOption.slug == 'trash' ? 'auto' : '' }">
                    <a
                            :id="'items-status-tab-' + statusOption.slug"
                            v-tooltip="{
                                content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('items')]),
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                            }"
                            role="tab"
                            :aria-selected="status == statusOption.slug"
                            :tabindex="status == statusOption.slug ? 0 : -1"
                            @click="onChangeTab(statusOption.slug)">
                        <span 
                                v-if="$statusHelper.hasIcon(statusOption.slug)"
                                aria-hidden="true"
                                class="icon has-text-dark">
                            <i 
                                    class="tainacan-icon tainacan-icon-1-125em"
                                    :class="$statusHelper.getIcon(statusOption.slug)"
                                    aria-hidden="true" />
                        </span>
                        {{ statusOption.name }}
                        <span 
                                v-if="!$adminOptions.hideItemsListStatusTabsTotalItems && !$route.query.authorid"
                                class="has-text-dark">
                            &nbsp;{{ (isRepositoryLevel && repositoryTotalItems) ? ` (${ repositoryTotalItems[statusOption.slug] })` : (collection && collection.total_items ? ` (${collection.total_items[statusOption.slug]})` : '') }}
                        </span>
                    </a>
                </li>
            </template>
        </ul>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    props: {
        isRepositoryLevel: Boolean
    },
    computed: {
        ...mapGetters('search', {
            'status': 'getStatus'
        }),
        ...mapGetters('collection', {
            'collection': 'getCollection'
        }),
        repositoryTotalItems() {

            if (!this.$adminOptions.hideItemsListStatusTabsTotalItems) {
                const collections = this.getCollections();

                const total_items = {};

                for (const collection of collections) {
                    if (!collection.total_items) {
                        continue;
                    }
                    for (const [slug, count] of Object.entries(collection.total_items)) {
                        total_items[slug] = (total_items[slug] || 0) + Number(count);
                    }
                }

                return total_items;
            } else {
                return '';
            }
        }
    },
    methods: {
        ...mapGetters('collection', [
            'getCollections'
        ]),
        onChangeTab(status) {
            this.$eventBusSearch.resetPageOnStore();
            this.$eventBusSearch.setStatus(status);
        }
    }
}
</script>