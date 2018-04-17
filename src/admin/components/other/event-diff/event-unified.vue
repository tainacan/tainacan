<template>
    <div>
        <div class="tile is-ancestor">

            <div class="tile is-parent">
                <article class="tile notification is-child is-light">

                    <div class="content">
                        <div class="title">Changes</div>
                        <div
                                v-for="(diff, key) in event.log_diffs"
                                v-if="diff.old"
                                :key="key">

                            <p/>
                            <div class="has-text-weight-bold is-capitalized">{{ `${key.replace('_', ' ')}:` }}</div>
                            <div
                                    v-if="!Array.isArray(diff.old)"
                                    class="content is-inline">
                                {{ diff.old }}
                            </div>

                            <div
                                    v-else
                                    v-for="(o, ind) in diff.old"
                                    :key="ind">
                                <div
                                        class="content is-inline is-capitalized"
                                        v-for="(o2, ind2) in o"
                                        :key="ind2">
                                    <div class="is-inline is-capitalized">{{ `${ind2.replace('_', ' ')}: ${o2} ` }}</div>
                                </div>
                            </div>
                        </div>

                        <hr class="divider">

                        <div
                                v-for="(diff, key) in event.log_diffs"
                                :key="key">

                            <p/>
                            <div
                                    class="has-text-weight-bold is-capitalized"
                                    :class="{ 'has-text-success': !diff.old, 'back-hlight': !diff.old }">
                                {{ `${key.replace('_', ' ')}:` }}
                            </div>
                            <div
                                    v-for="(d, i) in diff.new"
                                    :key="i"
                                    :class="{ 'back-hlight': diff.diff_with_index.hasOwnProperty(i) }"
                                    class="content is-inline" >

                                <div
                                        class="is-inline"
                                        :class="{ 'back-hlight': diff.diff_with_index.hasOwnProperty(i) }"
                                        v-if="!Array.isArray(d) && d.constructor.name !== 'Object' ">{{ d }}
                                </div>

                                <div
                                        v-else
                                        v-for="(e, i2) in d"
                                        :key="i2"
                                        class="is-capitalized"
                                        :class="{ 'back-hlight': diff.diff_with_index.hasOwnProperty(i) }">
                                    {{ `${i2.replace('_', ' ')}: ${e} ` }}
                                </div>

                            </div>

                        </div>
                    </div>

                </article>
            </div>

        </div>
    </div>
</template>

<script>
    export default {
        name: "EventUnified",
        props: {
            event: Object
        }
    }
</script>

<style scoped>

</style>