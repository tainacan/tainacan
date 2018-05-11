<template>
    <div>
        <div class="is-fullheight">
            <div class="page-container primary-page">
                <tainacan-title />
                <div class="title">{{ event.description }}</div>
                <div
                        class="level"
                        v-if="event.title !== undefined && event.title.includes('updated')">
                    <div class="level-left"/>
                    <div class="level-right">
                        <div class="level-item">
                            <b-switch
                                    v-model="comp"
                                    true-value="Unified"
                                    false-value="Split"
                                    class="is-pulled-right">
                                {{ comp }}
                            </b-switch>
                        </div>
                    </div>
                </div>

                <hr class="divider">

                <div v-if="event.title !== undefined && event.title.includes('updated')">
                    <component
                            :is="comp"
                            :event="event"/>
                </div>

                <div v-else-if="event.title !== undefined">
                    <no-diff :event="event" />
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';

    import Split from '../../components/other/event/diff-exhibition/event-split.vue';
    import Unified from '../../components/other/event/diff-exhibition/event-unified.vue';
    import NoDiff from '../../components/other/event/unique-exhibition/event-nodiff.vue';
    import TainacanTitle from '../../components/navigation/tainacan-title.vue';


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
            Unified,
            NoDiff,
            TainacanTitle,
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

    .bottom-space-tainacan {
        margin-bottom: 0.2rem;
    }
</style>