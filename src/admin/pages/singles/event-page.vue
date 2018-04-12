<template>
    <div>
        <div class="is-fullheight">
            <div class="page-container primary-page">

                <div class="">
                    <div class="field">
                        <b-switch
                                v-model="comp"
                                true-value="Unified"
                                false-value="Split"
                                class="is-pulled-right">
                            {{ comp }}
                        </b-switch>
                    </div>
                </div>

                <component
                        :is="comp"
                        :event="event" />
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';

    import Split from '../../components/other/event-diff/event-split.vue';
    import Unified from '../../components/other/event-diff/event-unified.vue';

    export default {
        name: 'EventPage',
        data() {
            return {
                eventId: Number,
                comp: 'Split',
            }
        },
        methods: {
            ...mapActions('event', [
                'fetchEvent'
            ]),
            ...mapGetters('event', [
                'getEvent'
            ])
        },
        computed: {
            event() {
                return this.getEvent();
            }
        },
        components: {
          Split,
          Unified
        },
        created() {
            this.eventId = parseInt(this.$route.params.eventId);

            this.fetchEvent(this.eventId);
        }

    }
</script>

<style>
    .back-hlight {
        background-color: rgb(231, 255, 237);
    }
</style>