<template>
    <div>
        <p class="title is-2">
            <i-count-up
                    :delay="750"
                    :end-val="total" />
        </p>
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
                <span class="value">
                    <i-count-up
                            :delay="750"
                            :end-val="totalByStatus[statusOption.slug]" />
                    &nbsp;
                </span>
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
import ICountUp from 'vue-countup-v2';

export default {
    components: {
        ICountUp
    },
    props: {
        sourceCollection: String,
        entityType: String,
        summary: Object
    },
    computed: {
        total() {
            return this.summary && this.summary.totals && this.summary.totals[this.entityType] ? this.summary.totals[this.entityType].total : 0;
        },
        totalByStatus() {
            return {
                'publish': this.summary && this.summary.totals && this.summary.totals[this.entityType] ? this.summary.totals[this.entityType].publish : 0,
                'private': this.summary && this.summary.totals && this.summary.totals[this.entityType] ?  this.summary.totals[this.entityType].private : 0,
                'draft': this.summary && this.summary.totals && this.summary.totals[this.entityType] ?  this.summary.totals[this.entityType].draft : 0,
                'trash': this.summary && this.summary.totals && this.summary.totals[this.entityType] ?  this.summary.totals[this.entityType].trash : 0
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