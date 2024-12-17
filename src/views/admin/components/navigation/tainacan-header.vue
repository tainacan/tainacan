<template>
    <div style="position: absolute; top: 0;">
        <processes-popup
                v-if="showProcesses"
                @close-processes-popup="showProcesses = false" />
    </div>
</template>

<script>
    import ProcessesPopup from '../other/processes-popup.vue';

    export default {
        name: 'TainacanHeader',
        components: {
            ProcessesPopup
        },
        data() {
            return {
                showProcesses: false,
                hasNewProcess: false
            }
        },
        created(){
            this.$emitter.on('openProcessesPopup', () => {
                this.showProcesses = true;
            });
        },
        beforeUnmount() {
            this.$emitter.off('openProcessesPopup');
        },
    }
</script>