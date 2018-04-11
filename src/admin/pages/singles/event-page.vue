<template>
    <div>
        <div class="is-fullheight">
            <div class="page-container primary-page">
                <div class="tile is-ancestor">
                    <div class="tile is-parent">
                        <article class="tile notification is-child is-light">
                            <div class="content">
                                <div class="title">{{ this.$i18n.get('info_logs_before') }}</div>
                                <div
                                        v-for="(diff, key) in event.log_diff"
                                        v-if="diff.old"
                                        :key="key">

                                    <p/>
                                    <div class="has-text-weight-bold is-capitalized">{{ `${key.replace('_', ' ')}:` }}</div>
                                    <div class="content">{{ diff.old }}</div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="tile is-parent">
                        <article class="tile notification is-child is-light">
                            <div class="content">
                                <div class="title">{{ this.$i18n.get('info_logs_after') }}</div>
                                <div
                                        v-for="(diff, key) in event.log_diff"
                                        :key="key">

                                    <p/>
                                    <div
                                            class="has-text-weight-bold is-capitalized"
                                            :class="{'has-text-success': !diff.old, 'back-hlight': !diff.old }">
                                        {{ `${key.replace('_', ' ')}:` }}
                                    </div>
                                    <div
                                            v-for="(d, i) in diff.new"
                                            :key="i"
                                            :class="{'has-text-success': diff.diff_with_index.hasOwnProperty(i), 'back-hlight': diff.diff_with_index.hasOwnProperty(i) }"
                                            class="content is-inline" >
                                        {{ d }}
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';

    export default {
        name: 'EventPage',
        data() {
            return {
                eventId: Number,
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
        created() {
            this.eventId = parseInt(this.$route.params.eventId);

            this.fetchEvent(this.eventId);
        }

    }
</script>

<style scoped>
    .back-hlight {
        background-color: hsl(0, 0%, 93%);
    }
</style>