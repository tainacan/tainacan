<template>
    <div class="tainacan-reports-subheader">
        <div class="tainacan-reports-subheader-box">
            <div class="tainacan-reports-subheader-box__title">
                {{ $i18n.get('label_status') }}
            </div>
            <ul class="tainacan-reports-subheader-box__content tainacan-reports-subheader-box__content--two-columns">
                <li
                        v-for="(statusOption, index) of $statusHelper.getStatuses()"
                        :key="index">
                    <span 
                        v-if="$statusHelper.hasIcon(statusOption.slug)"
                        class="icon has-text-gray">
                    <i 
                            class="tainacan-icon tainacan-icon-1-125em"
                            :class="$statusHelper.getIcon(statusOption.slug)"
                            />
                    </span>
                    <span>
                        {{ statusOption.name }}
                    </span>
                </li>
            </ul>
        </div>

        <div class="tainacan-reports-subheader-box">
            <div class="tainacan-reports-subheader-box__title">
                {{ $i18n.get('label_visibility') }}
            </div>
            <ul class="tainacan-reports-subheader-box__content">
                <li>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-see"/>
                    </span>
                    <span>
                        {{ $i18n.get('label_open_access') }}
                    </span>
                </li>
                <li>
                    <span class="icon">
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
                    </span>
                    <span>
                        {{ $i18n.get('label_restrict_access') }}
                    </span>
                </li>
            </ul>
        </div>

        <div class="tainacan-reports-subheader-box">
            <div class="tainacan-reports-subheader-box__title">
                {{ $i18n.get('label_chart_controls') }}
            </div>
            <ul class="tainacan-reports-subheader-box__content tainacan-reports-subheader-box__content--three-columns">
                <li>
                    <span class="icon">
                        <div
                                class="apexcharts-zoomin-icon"
                                :title="$i18n.get('label_increase_zoom')">
                            <svg 
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24">
                                <path
                                        d="M0 0h24v24H0z"
                                        fill="none"/>
                                <path d="M13 7h-2v4H7v2h4v4h2v-4h4v-2h-4V7zm-1-5C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
                            </svg>
                        </div>
                    </span>
                    <span>
                        {{ $i18n.get('label_increase_zoom') }}
                    </span>
                </li>
                <li>
                    <span class="icon">
                        <div
                                class="apexcharts-zoomout-icon"
                                :title="$i18n.get('label_decrease_zoom')">
                            <svg 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    width="24" 
                                    height="24"
                                    viewBox="0 0 24 24">
                                <path 
                                        d="M0 0h24v24H0z" 
                                        fill="none" />
                                <path d="M7 11v2h10v-2H7zm5-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
                            </svg>
                        </div>
                    </span>
                    <span>
                        {{ $i18n.get('label_decrease_zoom') }}
                    </span>
                </li>
                <li>
                    <span class="icon">
                        <div
                                class="apexcharts-zoom-icon"
                                :title="$i18n.get('label_zoom_by_selection')">
                            <svg 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    height="24"
                                    viewBox="0 0 24 24"
                                    width="24">
                                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
                                <path 
                                        d="M0 0h24v24H0V0z" 
                                        fill="none" />
                                <path d="M12 10h-2v2H9v-2H7V9h2V7h1v2h2v1z" />
                            </svg>
                        </div>
                    </span>
                    <span>
                        {{ $i18n.get('label_zoom_by_selection') }}
                    </span>
                </li>
                <li>
                    <span class="icon">
                        <div 
                                class="apexcharts-pan-icon" 
                                :title="$i18n.get('label_zoom_by_selection')">
                            <svg 
                                    xmlns="http://www.w3.org/2000/svg"
                                    height="24" 
                                    viewBox="0 0 24 24" 
                                    width="24">
                                <defs>
                                    <path 
                                            d="M0 0h24v24H0z"
                                            id="a" />
                                </defs>
                                <clipPath id="b">
                                    <use 
                                            overflow="visible" 
                                            xlink:href="#a" />
                                </clipPath>
                                <path 
                                        clip-path="url(#b)" 
                                        d="M23 5.5V20c0 2.2-1.8 4-4 4h-7.3c-1.08 0-2.1-.43-2.85-1.19L1 14.83s1.26-1.23 1.3-1.25c.22-.19.49-.29.79-.29.22 0 .42.06.6.16.04.01 4.31 2.46 4.31 2.46V4c0-.83.67-1.5 1.5-1.5S11 3.17 11 4v7h1V1.5c0-.83.67-1.5 1.5-1.5S15 .67 15 1.5V11h1V2.5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5V11h1V5.5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5z" />
                            </svg>
                        </div>
                    </span>
                    <span>
                        {{ $i18n.get('label_pan_selection') }}
                    </span>
                </li>
                <li>
                    <span class="icon">
                        <div 
                                class="apexcharts-reset-icon" 
                                :title="$i18n.get('label_reset_zoom')">
                            <svg 
                                    height="24" 
                                    viewBox="0 0 24 24" 
                                    width="24" 
                                    xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                                <path 
                                        d="M0 0h24v24H0z"
                                        fill="none" />
                            </svg>
                        </div>
                    </span>
                    <span>
                        {{ $i18n.get('label_reset_zoom') }}
                    </span>
                </li>
                <li>
                    <span class="icon">
                        <div
                                class="apexcharts-menu-icon"
                                :title="$i18n.get('label_chart_export_options')">
                            <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24">
                                <path
                                        fill="none"
                                        d="M0 0h24v24H0V0z" />
                                <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" />
                            </svg>
                        </div>
                    </span>
                    <span>
                        {{ $i18n.get('label_chart_export_options') }}
                    </span>
                </li>
            </ul>
        </div>

    </div>
</template>

<script>
export default {
    
}
</script>
