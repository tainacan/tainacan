<template>
    <div>
        <div class="is-fullheight">
            <div 
                    class="page-container"
                    :class="{ 'repository-level-page': $route.params.collectionId == undefined }">
                <tainacan-title
                        :bread-crumb-items="[
                                { path: $routerHelper.getActivitiesPath(), label: $i18n.get('activities') },
                                { path: '', label: (activity != undefined && activity.title != undefined) ? activity.title : $i18n.get('activity') }
                            ]"/>
                <h1 class="activity-titles">{{ activity.description }}</h1>
                <div
                        class="level"
                        v-if="activity.title !== undefined && activity.title.includes('updated')">
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

                <div v-if="activity.title !== undefined && activity.title.includes('updated')">
                    <component
                            :is="comp"
                            :activity="activity"/>
                </div>

                <div v-else-if="activity.title !== undefined">
                    <no-diff :activity="activity"/>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';

    import Split from '../../components/other/activity/diff-exhibition/activity-split.vue';
    import Unified from '../../components/other/activity/diff-exhibition/activity-unified.vue';
    import NoDiff from '../../components/other/activity/unique-exhibition/activity-nodiff.vue';
    import TainacanTitle from '../../components/navigation/tainacan-title.vue';


    export default {
        name: 'ActivityPage',
        data() {
            return {
                activityId: Number,
                comp: 'Split',
            }
        },
        methods: {
            ...mapActions('activity', [
                'fetchActivity'
            ]),
            ...mapGetters('activity', [
                'getActivity'
            ])
        },
        computed: {
            activity() {
                return this.getActivity();
            }
        },
        components: {
            Split,
            Unified,
            NoDiff,
            TainacanTitle,
        },
        created() {
            this.activityId = parseInt(this.$route.params.activityId);

            this.fetchActivity(this.activityId).then(() => {

                if (this.$route.params.collectionId != undefined)
                    this.$root.$emit('onCollectionBreadCrumbUpdate', [
                        { path: this.$routerHelper.getCollectionActivitiesPath(this.$route.params.collectionId), label: this.$i18n.get('activities') },
                        { path: '', label: this.activity.title}
                    ]);
            });
        }

    }
</script>

<style>
    .activity-titles {
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