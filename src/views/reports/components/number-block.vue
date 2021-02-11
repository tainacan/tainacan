<template>
    <div>
        <p class="title is-2">{{ total }}</p>
        <p class="subtitle is-3">
            <span class="icon has-text-gray">
                <i 
                        class="tainacan-icon tainacan-icon-1-125em"
                        :class="'tainacan-icon-' + entityType" />
            </span>
            &nbsp;{{ $i18n.get(entityType) }}
        </p>
        <ul class="has-text-gray status-list">
            <li 
                    v-for="(statusOption, index) of $statusHelper.getStatuses()"
                    :key="index"
                    v-if="(statusOption.slug != 'private' || (statusOption.slug == 'private' && $userCaps.hasCapability('tnc_rep_read_private_collections')) && totalByStatus[statusOption.slug])">
                <span class="value">{{ totalByStatus[statusOption.slug] }}&nbsp;</span>
                <span 
                        v-if="$statusHelper.hasIcon(statusOption.slug)"
                        class="icon has-text-gray">
                    <i 
                            class="tainacan-icon tainacan-icon-1-125em"
                            :class="$statusHelper.getIcon(statusOption.slug)"
                            />
                </span>
                <!-- {{ statusOption.name }} -->
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        sourceCollection: String,
        entityType: String
    },
    data() {
        return {
            total: 0,
            totalByStatus: {}
        }
    },
    mounted() {
        // Fake data until we fetch and load this from store
        if (this.entityType === 'items') {
            this.total = 2344;
            this.totalByStatus = {
                'publish': 2326,
                'private': 8,
                'draft': 9,
                'trash': 1
            }
        } else if (this.entityType === 'collections') {
            this.total = 23;
            this.totalByStatus = {
                'publish': 18,
                'private': 2,
                'trash': 3
            }
        } else if (this.entityType === 'taxonomies') {
            this.total = 8;
            this.totalByStatus = {
                'publish': 5,
                'private': 0,
                'draft': 1,
                'trash': 1
            }
        }
    }
}
</script>

<style lang="scss" scoped>
    .title {
        margin-top: 0.25em;
    }
    .status-list {
        display: flex;
        justify-content: center;

        li {
            margin: 0 1em;
        }
    }
</style>