<template>
    <div 
            :class="isRepositoryLevel ? 'number-block--' + entityType : ''"
            class="number-block">
        <p class="title is-2">
            <i-count-up
                    :delay="750"
                    :end-val="total"
                    :options="{ separator: ' ' }" />
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
                    @mouseenter="currentHoveredStatus = statusOption.slug"
                    @mouseleave="currentHoveredStatus = ''"
                    v-if="(statusOption.slug != 'draft' || entityType != 'collections') && totalByStatus[statusOption.slug]">
                <span class="value">
                    <i-count-up
                            :delay="750"
                            :end-val="totalByStatus[statusOption.slug]"
                            :options="{ separator: ' ' }" />
                    &nbsp;
                </span>
                <span 
                        v-if="$statusHelper.hasIcon(statusOption.slug)"
                        class="icon has-text-gray">
                    <i 
                            :style="(isRepositoryLevel && entityType === 'items') ? 'color: var(--tainacan-block-primary, #298596);' : ''"
                            class="tainacan-icon tainacan-icon-1-125em"
                            :class="$statusHelper.getIcon(statusOption.slug)" />
                </span>
                <!-- {{ statusOption.name }} -->
            </li>
        </ul>
        <p 
                v-if="summary.totals && summary.totals[entityType] && entityType == 'taxonomies'"
                class="terms-used-info subtitle is-6">
            {{ $i18n.get('label_used') + ': ' + summary.totals[entityType].used + ' | ' + $i18n.get('label_not_used') + ': ' + summary.totals[entityType].not_used }}
        </p>
        <div 
                class="visibility-charts"
                v-if="entityType === 'items' && !isBuildingChart && isRepositoryLevel">
            <div :style="'margin-right: 6px; background-color: ' + ((currentHoveredStatus != '' && currentHoveredStatus != 'publish') ? '#acacac' : ';' ) + '; width: ' + visibilityChartOpenWidth + '%'">
                <span class="icon has-text-gray">
                    <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-see" />
                    &nbsp;{{ totalByVisibility['not_restrict'] }}
                </span>
            </div>
            <div :style="'background-color: ' + ((currentHoveredStatus != '' && currentHoveredStatus != 'publish') ? '#acacac' : ';' ) + '; width: ' + visibilityChartRestrictWidth + '%'">
                <span class="icon has-text-gray">
                    <i
                            class="tainacan-icon tainacan-icon-svg"
                            style="display: flex;">
                        <svg 
                                xmlns:svg="http://www.w3.org/2000/svg"
                                xmlns="http://www.w3.org/2000/svg"
                                width="24px"
                                height="24px"
                                viewBox="-2 -1 8 9">
                            <g
                                    id="layer1"
                                    transform="translate(-71.664352,-160.89128)">
                                <path
                                        style="fill:var(--tainacan-block-gray4, #555758);;fill-opacity:1;stroke:none;stroke-width:0.332731"
                                        d="m 74.839398,162.85685 c 0.09358,0 0.181945,0.0178 0.265146,0.052 0.08321,0.0356 0.153355,0.0831 0.213173,0.14544 0.06238,0.0624 0.110471,0.13511 0.145584,0.21852 0.03768,0.0806 0.05718,0.16896 0.05718,0.26522 0,0.0973 -0.0196,0.18714 -0.05718,0.2702 -0.03494,0.0806 -0.08321,0.14936 -0.145584,0.20783 -0.05978,0.0599 -0.129971,0.10801 -0.213173,0.14544 -0.08321,0.0356 -0.171572,0.052 -0.265146,0.052 -0.09358,0 -0.181945,-0.0179 -0.265146,-0.052 -0.08061,-0.0378 -0.150755,-0.0859 -0.213173,-0.14544 -0.06238,-0.0585 -0.11179,-0.12726 -0.145585,-0.20783 -0.03494,-0.083 -0.05198,-0.17289 -0.05198,-0.2702 0,-0.0962 0.01675,-0.18466 0.05198,-0.26522 0.03386,-0.0831 0.08321,-0.15578 0.145585,-0.21852 0.06238,-0.0624 0.132573,-0.11051 0.213173,-0.14544 0.08321,-0.0321 0.171571,-0.052 0.265146,-0.052 z"
                                        id="path5508" />
                                <path
                                        id="path5461"
                                        style="fill:var(--tainacan-block-gray4, #555758);;fill-opacity:1;stroke:none;stroke-width:0.332732"
                                        d="m 74.840268,161.8152 c -0.284646,0 -0.556123,0.0421 -0.816062,0.12511 -0.257339,0.0834 -0.494574,0.20141 -0.712908,0.35362 -0.215775,0.15329 -0.404697,0.33189 -0.571064,0.53975 -0.16601,0.20497 -0.301577,0.43572 -0.401586,0.68713 0.100026,0.24634 0.235559,0.47502 0.401586,0.68527 0.166367,0.20817 0.355335,0.38752 0.571064,0.53977 0.218341,0.14936 0.45556,0.267 0.712908,0.35006 0.259942,0.083 0.531423,0.12333 0.816062,0.12333 h 0.123411 c 0.04156,0 0.08074,-0.004 0.11974,-0.0107 -0.01034,-0.0732 -0.01461,-0.14473 -0.01461,-0.21745 0.0027,-0.0378 0.0053,-0.0752 0.0053,-0.11408 0.0028,-0.0417 0.0068,-0.0854 0.01462,-0.13083 -0.07667,0.0143 -0.158987,0.0214 -0.248677,0.0214 -0.211852,0 -0.418798,-0.0285 -0.618955,-0.0884 -0.19756,-0.0584 -0.385072,-0.14258 -0.561853,-0.2506 -0.174173,-0.10658 -0.33177,-0.23526 -0.473425,-0.3887 -0.139059,-0.15578 -0.254594,-0.33009 -0.348169,-0.52131 0.09618,-0.20034 0.216239,-0.37725 0.35921,-0.53053 0.145587,-0.15222 0.304788,-0.28054 0.478964,-0.38867 0.176742,-0.1105 0.362449,-0.19464 0.56001,-0.2506 0.197559,-0.0545 0.401772,-0.0828 0.609743,-0.0828 0.210571,0 0.415875,0.0285 0.613431,0.0884 0.200161,0.0599 0.388758,0.14545 0.565538,0.25596 0.176775,0.10765 0.334374,0.23812 0.473428,0.39422 0.137741,0.15329 0.254594,0.32617 0.348169,0.51583 -0.01426,0.0285 -0.02923,0.0524 -0.04605,0.0774 -0.01426,0.025 -0.03101,0.047 -0.04792,0.072 0.155958,0.0356 0.298689,0.10016 0.427371,0.19357 0.06238,-0.11694 0.114785,-0.23065 0.160271,-0.34259 -0.09749,-0.25201 -0.227859,-0.4818 -0.394219,-0.68713 -0.166368,-0.20818 -0.360082,-0.38646 -0.578427,-0.53975 -0.218343,-0.15221 -0.456649,-0.27056 -0.716588,-0.35362 -0.25734,-0.0831 -0.527216,-0.12512 -0.810547,-0.12512 z m 1.541874,2.02267 c -0.08238,0 -0.158347,0.0179 -0.230248,0.0478 -0.07073,0.0321 -0.131931,0.073 -0.186045,0.12726 -0.0529,0.0528 -0.09635,0.11586 -0.127119,0.18786 -0.03137,0.0706 -0.04605,0.1465 -0.04605,0.2285 v 0.23776 h -0.117885 c -0.0658,0 -0.122129,0.025 -0.167652,0.0699 -0.04613,0.0442 -0.06816,0.0987 -0.06816,0.16397 v 1.18268 c 0,0.0639 0.0221,0.11978 0.06816,0.16576 0.04552,0.0456 0.101846,0.0681 0.167652,0.0681 h 1.416601 c 0.06521,0 0.121522,-0.0214 0.16765,-0.0681 0.04549,-0.0442 0.06816,-0.10051 0.06816,-0.16576 v -1.18268 c 0,-0.0652 -0.02281,-0.11977 -0.06816,-0.16397 -0.04613,-0.0456 -0.102451,-0.0699 -0.16765,-0.0699 h -0.117888 v -0.23776 c 0,-0.0816 -0.01533,-0.15757 -0.04605,-0.2285 -0.03137,-0.072 -0.07483,-0.13511 -0.128937,-0.18786 -0.05286,-0.0542 -0.114108,-0.0959 -0.186045,-0.12726 -0.07073,-0.0321 -0.148473,-0.0478 -0.230283,-0.0478 z m 0,0.23777 c 0.0504,0 0.09881,0.007 0.141841,0.025 0.04242,0.018 0.07917,0.0449 0.110544,0.0773 0.03244,0.0321 0.05707,0.0681 0.07554,0.11051 0.01782,0.0431 0.0278,0.0895 0.0278,0.14011 v 0.23775 h -0.70922 v -0.23775 c 0,-0.0503 0.0093,-0.097 0.02745,-0.14011 0.01782,-0.0424 0.04231,-0.0791 0.07368,-0.11051 0.03244,-0.0321 0.06933,-0.0589 0.112361,-0.0773 0.04242,-0.0178 0.08898,-0.025 0.139987,-0.025 z m 0,1.17897 c 0.06517,0 0.121522,0.025 0.16765,0.0699 0.04552,0.046 0.07002,0.1023 0.07002,0.16755 0,0.0659 -0.02462,0.12226 -0.07002,0.16968 -0.04613,0.046 -0.102451,0.0681 -0.16765,0.0681 -0.0658,0 -0.120276,-0.0214 -0.165798,-0.0681 -0.04613,-0.0474 -0.07002,-0.10374 -0.07002,-0.16968 0,-0.0652 0.02388,-0.12157 0.07002,-0.16755 0.04549,-0.0456 0.09999,-0.0699 0.165798,-0.0699 z" />
                                <g 
                                        id="use1344"
                                        transform="matrix(0.157413,0,0,0.157413,74.965914,165.96635)"
                                        style="fill:var(--tainacan-block-gray4, #555758);;fill-opacity:1" />
                            </g>
                        </svg>
                    </i>
                    &nbsp;{{ totalByVisibility['restrict'] }}
                </span>
            </div>
            <div :style="'background-color: ' + ((currentHoveredStatus != '' && currentHoveredStatus != 'private') ? '#acacac' : ';' ) + '; width: ' + visibilityChartPrivateWidth + '%'" />
            <div :style="'background-color: ' + ((currentHoveredStatus != '' && currentHoveredStatus != 'draft') ? '#acacac' : ';' ) + '; width: ' + visibilityChartDraftWidth + '%'" />
            <div :style="'background-color: ' + ((currentHoveredStatus != '' && currentHoveredStatus != 'trash') ? '#acacac' : ';' ) + '; width: ' + visibilityChartTrashWidth + '%'" />
        </div>
        <!-- <ul 
                v-if="entityType == 'items' && (totalByVisibility['not_restrict'] || totalByVisibility['restrict'])"
                class="has-text-gray status-list">
            <li>
                <span class="value">
                    <i-count-up
                            :delay="750"
                            :end-val="totalByVisibility['not_restrict']"
                            :options="{ separator: ' ' }" />
                    &nbsp;
                </span>
                <span class="icon has-text-gray">
                    <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-see" />
                </span>
            </li>
             <li>
                <span class="value">
                    <i-count-up
                            :delay="750"
                            :end-val="totalByVisibility['restrict']"
                            :options="{ separator: ' ' }" />
                    &nbsp;
                </span>
                <span class="icon has-text-gray">
                    <i
                            class="tainacan-icon tainacan-icon-svg"
                            style="display: flex;">
                        <svg 
                                xmlns:svg="http://www.w3.org/2000/svg"
                                xmlns="http://www.w3.org/2000/svg"
                                width="24px"
                                height="24px"
                                viewBox="-1 -2 8 9">
                            <g
                                    id="layer1"
                                    transform="translate(-71.664352,-160.89128)">
                                <path
                                        style="fill:var(--tainacan-block-gray4, #555758);;fill-opacity:1;stroke:none;stroke-width:0.332731"
                                        d="m 74.839398,162.85685 c 0.09358,0 0.181945,0.0178 0.265146,0.052 0.08321,0.0356 0.153355,0.0831 0.213173,0.14544 0.06238,0.0624 0.110471,0.13511 0.145584,0.21852 0.03768,0.0806 0.05718,0.16896 0.05718,0.26522 0,0.0973 -0.0196,0.18714 -0.05718,0.2702 -0.03494,0.0806 -0.08321,0.14936 -0.145584,0.20783 -0.05978,0.0599 -0.129971,0.10801 -0.213173,0.14544 -0.08321,0.0356 -0.171572,0.052 -0.265146,0.052 -0.09358,0 -0.181945,-0.0179 -0.265146,-0.052 -0.08061,-0.0378 -0.150755,-0.0859 -0.213173,-0.14544 -0.06238,-0.0585 -0.11179,-0.12726 -0.145585,-0.20783 -0.03494,-0.083 -0.05198,-0.17289 -0.05198,-0.2702 0,-0.0962 0.01675,-0.18466 0.05198,-0.26522 0.03386,-0.0831 0.08321,-0.15578 0.145585,-0.21852 0.06238,-0.0624 0.132573,-0.11051 0.213173,-0.14544 0.08321,-0.0321 0.171571,-0.052 0.265146,-0.052 z"
                                        id="path5508" />
                                <path
                                        id="path5461"
                                        style="fill:var(--tainacan-block-gray4, #555758);;fill-opacity:1;stroke:none;stroke-width:0.332732"
                                        d="m 74.840268,161.8152 c -0.284646,0 -0.556123,0.0421 -0.816062,0.12511 -0.257339,0.0834 -0.494574,0.20141 -0.712908,0.35362 -0.215775,0.15329 -0.404697,0.33189 -0.571064,0.53975 -0.16601,0.20497 -0.301577,0.43572 -0.401586,0.68713 0.100026,0.24634 0.235559,0.47502 0.401586,0.68527 0.166367,0.20817 0.355335,0.38752 0.571064,0.53977 0.218341,0.14936 0.45556,0.267 0.712908,0.35006 0.259942,0.083 0.531423,0.12333 0.816062,0.12333 h 0.123411 c 0.04156,0 0.08074,-0.004 0.11974,-0.0107 -0.01034,-0.0732 -0.01461,-0.14473 -0.01461,-0.21745 0.0027,-0.0378 0.0053,-0.0752 0.0053,-0.11408 0.0028,-0.0417 0.0068,-0.0854 0.01462,-0.13083 -0.07667,0.0143 -0.158987,0.0214 -0.248677,0.0214 -0.211852,0 -0.418798,-0.0285 -0.618955,-0.0884 -0.19756,-0.0584 -0.385072,-0.14258 -0.561853,-0.2506 -0.174173,-0.10658 -0.33177,-0.23526 -0.473425,-0.3887 -0.139059,-0.15578 -0.254594,-0.33009 -0.348169,-0.52131 0.09618,-0.20034 0.216239,-0.37725 0.35921,-0.53053 0.145587,-0.15222 0.304788,-0.28054 0.478964,-0.38867 0.176742,-0.1105 0.362449,-0.19464 0.56001,-0.2506 0.197559,-0.0545 0.401772,-0.0828 0.609743,-0.0828 0.210571,0 0.415875,0.0285 0.613431,0.0884 0.200161,0.0599 0.388758,0.14545 0.565538,0.25596 0.176775,0.10765 0.334374,0.23812 0.473428,0.39422 0.137741,0.15329 0.254594,0.32617 0.348169,0.51583 -0.01426,0.0285 -0.02923,0.0524 -0.04605,0.0774 -0.01426,0.025 -0.03101,0.047 -0.04792,0.072 0.155958,0.0356 0.298689,0.10016 0.427371,0.19357 0.06238,-0.11694 0.114785,-0.23065 0.160271,-0.34259 -0.09749,-0.25201 -0.227859,-0.4818 -0.394219,-0.68713 -0.166368,-0.20818 -0.360082,-0.38646 -0.578427,-0.53975 -0.218343,-0.15221 -0.456649,-0.27056 -0.716588,-0.35362 -0.25734,-0.0831 -0.527216,-0.12512 -0.810547,-0.12512 z m 1.541874,2.02267 c -0.08238,0 -0.158347,0.0179 -0.230248,0.0478 -0.07073,0.0321 -0.131931,0.073 -0.186045,0.12726 -0.0529,0.0528 -0.09635,0.11586 -0.127119,0.18786 -0.03137,0.0706 -0.04605,0.1465 -0.04605,0.2285 v 0.23776 h -0.117885 c -0.0658,0 -0.122129,0.025 -0.167652,0.0699 -0.04613,0.0442 -0.06816,0.0987 -0.06816,0.16397 v 1.18268 c 0,0.0639 0.0221,0.11978 0.06816,0.16576 0.04552,0.0456 0.101846,0.0681 0.167652,0.0681 h 1.416601 c 0.06521,0 0.121522,-0.0214 0.16765,-0.0681 0.04549,-0.0442 0.06816,-0.10051 0.06816,-0.16576 v -1.18268 c 0,-0.0652 -0.02281,-0.11977 -0.06816,-0.16397 -0.04613,-0.0456 -0.102451,-0.0699 -0.16765,-0.0699 h -0.117888 v -0.23776 c 0,-0.0816 -0.01533,-0.15757 -0.04605,-0.2285 -0.03137,-0.072 -0.07483,-0.13511 -0.128937,-0.18786 -0.05286,-0.0542 -0.114108,-0.0959 -0.186045,-0.12726 -0.07073,-0.0321 -0.148473,-0.0478 -0.230283,-0.0478 z m 0,0.23777 c 0.0504,0 0.09881,0.007 0.141841,0.025 0.04242,0.018 0.07917,0.0449 0.110544,0.0773 0.03244,0.0321 0.05707,0.0681 0.07554,0.11051 0.01782,0.0431 0.0278,0.0895 0.0278,0.14011 v 0.23775 h -0.70922 v -0.23775 c 0,-0.0503 0.0093,-0.097 0.02745,-0.14011 0.01782,-0.0424 0.04231,-0.0791 0.07368,-0.11051 0.03244,-0.0321 0.06933,-0.0589 0.112361,-0.0773 0.04242,-0.0178 0.08898,-0.025 0.139987,-0.025 z m 0,1.17897 c 0.06517,0 0.121522,0.025 0.16765,0.0699 0.04552,0.046 0.07002,0.1023 0.07002,0.16755 0,0.0659 -0.02462,0.12226 -0.07002,0.16968 -0.04613,0.046 -0.102451,0.0681 -0.16765,0.0681 -0.0658,0 -0.120276,-0.0214 -0.165798,-0.0681 -0.04613,-0.0474 -0.07002,-0.10374 -0.07002,-0.16968 0,-0.0652 0.02388,-0.12157 0.07002,-0.16755 0.04549,-0.0456 0.09999,-0.0699 0.165798,-0.0699 z" />
                                <g 
                                        id="use1344"
                                        transform="matrix(0.157413,0,0,0.157413,74.965914,165.96635)"
                                        style="fill:var(--tainacan-block-gray4, #555758);;fill-opacity:1" />
                            </g>
                        </svg>
                    </i>
                </span>
            </li>
        </ul> -->
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import ICountUp from 'vue-countup-v2';
import { reportsChartMixin } from '../js/reports-mixin';

export default {
    components: {
        ICountUp
    },
    mixins: [ reportsChartMixin ],
    props: {
        entityType: String,
        summary: Object,
        isRepositoryLevel: Boolean
    },
    data() {
        return {
            visibilityChartOpenWidth: 20,
            visibilityChartRestrictWidth: 20,
            visibilityChartPrivateWidth: 20,
            visibilityChartDraftWidth: 20,
            visibilityChartTrashWidth: 20,
            currentHoveredStatus: ''
        }
    },
    computed: {
        total() {
            return this.summary && this.summary.totals && this.summary.totals[this.entityType] && this.summary.totals[this.entityType].total ? this.summary.totals[this.entityType].total : 0;
        },
        totalByStatus() {
            return {
                'publish': this.summary && this.summary.totals && this.summary.totals[this.entityType] && this.summary.totals[this.entityType].publish ? this.summary.totals[this.entityType].publish : 0,
                'private': this.summary && this.summary.totals && this.summary.totals[this.entityType] && this.summary.totals[this.entityType].private ? this.summary.totals[this.entityType].private : 0,
                'draft': this.summary && this.summary.totals && this.summary.totals[this.entityType] && this.summary.totals[this.entityType].draft ? this.summary.totals[this.entityType].draft : 0,
                'trash': this.summary && this.summary.totals && this.summary.totals[this.entityType] && this.summary.totals[this.entityType].trash ? this.summary.totals[this.entityType].trash : 0,
            }
        },
        totalByVisibility() {
            return {
                'not_restrict': this.summary && this.summary.totals && this.summary.totals[this.entityType] && this.summary.totals[this.entityType].not_restrict ? this.summary.totals[this.entityType].not_restrict : 0,
                'restrict': this.summary && this.summary.totals && this.summary.totals[this.entityType] && this.summary.totals[this.entityType].restrict ? this.summary.totals[this.entityType].restrict : 0,
            }
        },
        ...mapGetters('report', {
            visibilityHorizontalBarChartOptions: 'getVisibilityHorizontalBarChartOptions',
        })
    },
    watch: {
        totalByStatus() {
            this.buildVisibilityChart();
        }
    },
    methods: {
        buildVisibilityChart() {
            if (this.entityType === 'items' && this.isRepositoryLevel) {
                this.isBuildingChart = true;
 
                const totalVisibility = this.summary.totals['items']['total'];
                this.visibilityChartOpenWidth = (this.totalByVisibility['not_restrict']*100)/totalVisibility;
                this.visibilityChartRestrictWidth = ((this.totalByStatus['publish'] - this.totalByVisibility['not_restrict'])*100)/totalVisibility;
                this.visibilityChartPrivateWidth = (this.totalByStatus['private']*100)/totalVisibility;
                this.visibilityChartDraftWidth = (this.totalByStatus['draft']*100)/totalVisibility;
                this.visibilityChartTrashWidth = (this.totalByStatus['trash']*100)/totalVisibility;           
                this.isBuildingChart = false;
           }
        },
    }
}
</script>

<style lang="scss" scoped>
.number-block {
    min-height: 210px !important;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-content: center;

    .title {
        margin-top: 0.25em;
    }
    .subtitle {
        padding-left: 0;
        padding-right: 0;
    }
    .subtitle.is-6 {
        margin-bottom: 0px;
    }
    .status-list {
        display: flex;
        justify-content: center;
        font-size: 0.875rem;

        li {
            margin: 0 1em;
        }
        .value {
            text-align: center;
            display: inline-flex;
        }
    }
    &.number-block--items .status-list {
        .tainacan-icon-private {
            color: #01295c !important; 
        }
        .tainacan-icon-draft {
            color: #25a189 !important;
        }
        .tainacan-icon-delete {
            color: #bb7700 !important;
        }
    }
    &.number-block--items,
    &.number-block--taxonomies {
        .subtitle {
            margin-bottom: 1.25rem;
        }
        .status-list {
            margin: 0.25em 0 0.1em 0;
        }
    }
}
.terms-used-info {
    border-top: 1px solid #cbcbcb;
    margin-top: 6px;
    margin-bottom: 0px !important;
    padding: 12px;
}
.visibility-charts {
    display: flex;
    width: calc(100% - 26px);
    margin-top: 6px;
    padding: 12px;
    border-top: 1px solid #cbcbcb;
    
    &>div {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-height: 18px;
        color: white;
        font-weight: bold;
        position: relative;
        background-color: var(--tainacan-turquoise6, #226f7d);
        transition: width 0.2s ease, background-color 0.2s ease;

        &:nth-child(3) {
            background-color: #01295c;
        }
        &:nth-child(4) {
            background-color: #25a189;
        }
        &:nth-child(5) {
            background-color: #bb7700;
        }
        &:nth-child(6) {
            opacity: 0.35;
        }
        &:nth-child(7) {
            opacity: 0.25;
        }

        .icon {
            position: absolute;
            top: 20px;
        }
    }
}
</style>