<template>
    <div class="tabs">
        <ul>               
            <li 
                    @click="onChangeTab('')"
                    :class="{ 'is-active': status == undefined || status == ''}"
                    v-tooltip="{
                        content: $i18n.get('info_items_tab_all'),
                        autoHide: true,
                        placement: 'auto',
                    }">
                <a :style="{ fontWeight: 'bold', color: '#454647 !important', lineHeight: '1.5rem' }">
                    {{ $i18n.get('label_all_published_items') }}
                    <span class="has-text-gray">&nbsp;{{ collection && collection.total_items ? ` (${Number(collection.total_items.private) + Number(collection.total_items.publish)})` : (isRepositoryLevel && repositoryTotalItems) ? ` (${ repositoryTotalItems.private + repositoryTotalItems.publish })` : '' }}</span>
                </a>
            </li>
            <li 
                    v-for="(statusOption, index) of $statusHelper.getStatuses()"
                    v-if="(isRepositoryLevel || statusOption.slug != 'private') || (statusOption.slug == 'private' && collection && collection.current_user_can_read_private_items)"
                    :key="index"
                    @click="onChangeTab(statusOption.slug)"
                    :class="{ 'is-active': status == statusOption.slug}"
                    :style="{ marginRight: statusOption.slug == 'private' ? 'auto' : '', marginLeft: statusOption.slug == 'draft' ? 'auto' : '' }"
                    v-tooltip="{
                        content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('items')]),
                        autoHide: true,
                        placement: 'auto',
                    }">
                <a>
                    <span 
                            v-if="$statusHelper.hasIcon(statusOption.slug)"
                            class="icon has-text-gray">
                        <i 
                                class="tainacan-icon tainacan-icon-18px"
                                :class="$statusHelper.getIcon(statusOption.slug)"
                                />
                    </span>
                    {{ statusOption.name }}
                    <span class="has-text-gray">&nbsp;{{ collection && collection.total_items ? ` (${collection.total_items[statusOption.slug]})` : (isRepositoryLevel && repositoryTotalItems) ? ` (${ repositoryTotalItems[statusOption.slug] })` : '' }}</span>
                </a>
            </li>
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
        status() {
            return this.getStatus();
        },
        collection() {
            return this.getCollection();
        },
        repositoryTotalItems() {
            let collections = this.getCollections();

            let total_items = {
                trash: 0,
                publish: 0,
                draft: 0,
                private: 0
            };

            for(let collection of collections){
                total_items.trash += Number(collection.total_items.trash);
                total_items.draft += Number(collection.total_items.draft);
                total_items.publish += Number(collection.total_items.publish);
                total_items.private += Number(collection.total_items.private);
            }

            return total_items;
        }
    },
    methods: {
        ...mapGetters('search', [
            'getStatus'
        ]),
        ...mapGetters('collection', [
            'getCollection',
            'getCollections'
        ]),
        onChangeTab(status) {
            this.$eventBusSearch.setStatus(status);
        }
    }
}
</script>