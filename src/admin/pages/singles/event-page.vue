<template>
    <div>
        <div class="is-fullheight">
            <div class="page-container primary-page">
                <tainacan-title/>
                <h1 class="event-titles">{{ event.description }}</h1>
                <div
                        class="level"
                        v-if="event.title !== undefined && event.title.includes('updated')">
                    <div class="level-left"/>
                    <div class="level-right">
                        <div class="level-item">
                            <div class="field has-addons is-pulled-right">
                                <p class="control">
                                    <a
                                            @click="comp = 'Split'"
                                            :class="{'is-selected': comp === 'Split', 'is-focused': comp === 'Split'}"
                                            class="button">
                                        <b-icon
                                                icon="pause"
                                                size="is-small"/>
                                        <span>{{ $i18n.get('split') }}</span>
                                    </a>
                                </p>
                                <p class="control">
                                    <a
                                            @click="comp = 'Unified'"
                                            :class="{'is-selected': comp === 'Unified', 'is-focused': comp === 'Unified'}"
                                            class="button">
                                        <b-icon
                                                icon="minus"
                                                size="is-small"/>
                                        <span>{{ $i18n.get('unified') }}</span>
                                    </a>
                                </p>
                            </div>
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
                    <no-diff :event="event"/>
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

    .event-titles {
        font-size: 20px;
        font-weight: 500;
        color: #01295c;
        display: inline-block;
    }

    .field.has-addons .control:first-child .button {
        border-bottom-right-radius: 0 !important;
        border-top-right-radius: 0 !important;
    }

    .field.has-addons .control:last-child .button {
        border-bottom-left-radius: 0 !important;
        border-top-left-radius: 0 !important;
    }
</style>