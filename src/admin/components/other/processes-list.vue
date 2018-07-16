<template>
    <div class="processes-popup">
        <div class="popup-header">
            <span class="header-title">{{ bgProcesses.length }}</span>
            <a @click="showProcessesList = !showProcessesList">
                <span class="icon has-text-tertiary">
                    <i 
                            :class="{ 'mdi-chevron-up': showProcessesList,  
                                      'mdi-chevron-down': !showProcessesList }"
                            class="mdi"/>
                </span>
            </a>    
            <a @click="$emit('closeProcessesPopup')">
                <span class="icon has-text-tertiary">
                    <i class="mdi mdi-close"/>
                </span>
            </a>       
        </div>
        <div 
                v-if="showProcessesList"
                class="popup-list">
            <ul>
                <li     
                        v-if="bgProcess.progress_label" 
                        :key="index"
                        v-for="(bgProcess, index) of bgProcesses">
                    <div class="progress-label">{{ bgProcess.progress_label }}</div>
                    <span 
                            v-if="bgProcess.done > 0"
                            class="icon has-text-success">
                        <i class="mdi mdi-24px mdi-checkbox-marked-circle"/>
                    </span>
                    <span 
                            v-if="bgProcess.done <= 0"
                            class="icon has-text-success">
                        <div class="control has-icons-right is-loading is-clearfix" />
                    </span>
                </li>
            </ul>
        </div>
        <div class="popup-footer"/>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'ProcessesList',
    data() {
        return {
            showProcessesList: false
        }
    },
    computed: {
        bgProcesses() {
            return this.getProcesses();
        }
    },
    methods: {
        ...mapActions('bgprocess', [
            'fetchProcesses'
        ]),
        ...mapGetters('bgprocess', [
            'getProcesses',
        ])
    },
    mounted() {    
        this.fetchProcesses();
    }
}
</script>

<style lang="scss">
    @import "../../scss/_variables.scss";

    @keyframes appear {
        from { 
            top: 24px;
            opacity: 0; 
        }
        to { 
            top: 48px;
            opacity: 1; 
        }
    }

    .processes-popup{
        background-color: #c1dae0;
        width: 320px;
        max-width: 100%;
        position: absolute;
        top: 48px;
        border-radius: 5px;
        box-shadow: 0px 0px 10px -8px #222;
        animation-name: appear;
        animation-duration: 0.3s;

        .update-warning {
            color: $tertiary;
            animation-name: blink;
            animation-duration: 0.5s;
            animation-delay: 0.5s;
            align-items: center;
            display: flex;
        }

        .popup-header {
            display: flex;
            justify-content: space-between;
            color: $tertiary;
            padding: 12px;

            .header-title {
                margin-right: auto;
            }
        }

        .popup-list {
            background-color: white;
            color: black;

            li {
                padding: 6px 12px;
                display: flex;
                justify-content: space-between;

                .progress-label {
                    margin-right: auto;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    overflow: hidden;
                    max-width: calc(100% - 40px);
                }

                .control.is-loading::after {
                    position: relative !important;
                    right: 0;
                    top: 0;
                }
            }
            li:hover {
                background-color: $tainacan-input-background;
            }
            
        }

        &:before {
            content: "";
            display: block;
            position: absolute;
            right: 47px;
            width: 0;
            height: 0;
            border-style: solid;
        }
        &:before {
            border-color: transparent transparent $primary-light transparent;
            border-right-width: 8px;
            border-bottom-width: 8px;
            border-left-width: 8px;
            top: -10px;
        }
    }

</style>