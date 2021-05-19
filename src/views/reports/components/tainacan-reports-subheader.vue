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
                        <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-see"/>
                    </span>
                    <span>
                        {{ $i18n.get('label_open_access') }}
                    </span>
                </li>
                <li>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-private"/>
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
