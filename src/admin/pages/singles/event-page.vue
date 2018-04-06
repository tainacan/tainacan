<template>
    <div>
        <div class="is-fullheight">
            <div class="page-container primary-page">
                <div class="tile is-ancestor">
                    <div class="tile is-parent">
                        <article class="tile notification is-child is-light">
                            <div class="content">
                                <p class="title">Before change</p>
                                <div
                                        v-for="(diff, key) in event.log_diff"
                                        v-if="diff.old"
                                        :key="key">

                                    <div class="has-text-weight-bold is-capitalized">{{ `${key.replace('_', ' ')}:` }}</div>
                                    <div class="content">{{ diff.old }}</div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="tile is-parent">
                        <article class="tile notification is-child is-light">
                            <div class="content">
                                <p class="title">What was changed</p>
                                <div
                                        v-for="(diff, key) in event.log_diff"
                                        :key="key">

                                    <div class="has-text-weight-bold is-capitalized">{{ `${key.replace('_', ' ')}:` }}</div>
                                    <div class="content has-text-primary">{{ diff.new }}</div>
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

</style>