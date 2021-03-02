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
        entityType: String,
        summary: Object
    },
    computed: {
        total() {
            return this.summary.totals[this.entityType].total;
        },
        totalByStatus() {
            return {
                'publish': this.summary.totals[this.entityType].publish,
                'private': this.summary.totals[this.entityType].private,
                'draft': this.summary.totals[this.entityType].draft,
                'trash': this.summary.totals[this.entityType].trash
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