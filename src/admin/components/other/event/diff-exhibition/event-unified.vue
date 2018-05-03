<template>
    <div>
        <div class="tile is-ancestor">

            <div class="tile is-parent">
                <article class="tile box is-child">

                    <div class="content">
                        <div class="title">{{ this.$i18n.get('info_changes') }}</div>
                        <div
                                v-for="(diff, key) in event.log_diffs"
                                v-if="diff.old"
                                :key="key">

                            <p/>
                            <div class="has-text-weight-bold is-capitalized">{{ `${key.replace('_', ' ')}:` }}</div>
                            <div v-if="key === 'featured_image'">
                                <div class="image is-128x128">
                                    <img :src="diff.old">
                                </div>
                            </div>
                            <div
                                    v-else-if="!Array.isArray(diff.old)"
                                    class="content is-inline">
                                {{ diff.old }}
                            </div>

                            <div
                                    v-else
                                    v-for="(o, ind) in diff.old"
                                    :key="ind">

                                <div v-if="o.hasOwnProperty('mime_type') && o.mime_type.includes('image') && key === 'attachments'">


                                    <article class="media">
                                        <div class="media-left bottom-space-tainacan">
                                            <p class="image is-64x64"><img :src="o.url"></p>
                                        </div>
                                        <div class="media-content">
                                            <div class="content">
                                                <p>
                                                    <strong class="is-capitalized">{{ o.title }}</strong> <small class="tag is-light">{{ o.mime_type }}</small>
                                                    <br>
                                                    {{ o.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </article>

                                </div>

                                <div
                                        v-else-if="key === 'fields_order' || key === 'filters_order'"
                                        class="is-capitalized"
                                        :class="{ 'back-hlight': diff.diff_with_index.hasOwnProperty(i) }">
                                    {{ `ID: ${o.id} Enabled: ${o.enabled ? o.enabled : 'false'}` }}
                                </div>

                                <div
                                        v-else
                                        class="content is-inline is-capitalized"
                                        v-for="(o2, ind2) in o"
                                        :key="ind2">
                                    <div class="is-inline is-capitalized">{{ `${ind2 ? ind2.replace('_', ' ')+':' : ''} ${o2} ` }}</div>
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
                            <div v-if="key === 'featured_image'">
                                <div class="image is-128x128">
                                    <img :src="diff.new">
                                </div>
                            </div>
                            <div
                                    v-else
                                    v-for="(d, i) in diff.new"
                                    :key="i"
                                    :class="{ 'back-hlight': diff.diff_with_index.hasOwnProperty(i) }"
                                    class="content is-inline" >

                                <div v-if="d.hasOwnProperty('mime_type') && d.mime_type.includes('image') && key === 'attachments'">


                                    <article class="media">
                                        <div class="media-left">
                                            <p class="image is-64x64"><img :src="d.url"></p>
                                        </div>
                                        <div class="media-content">
                                            <div class="content">
                                                <p>
                                                    <strong class="is-capitalized">{{ d.title }}</strong> <small class="tag is-light">{{ d.mime_type }}</small>
                                                    <br>
                                                    {{ d.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </article>

                                </div>

                                <div
                                        v-else-if="key === 'fields_order' || key === 'filters_order'"
                                        class="is-capitalized"
                                        :class="{ 'back-hlight': diff.diff_with_index.hasOwnProperty(i) }">
                                    {{ `ID: ${d.id} Enabled: ${d.enabled ? d.enabled : 'false'}` }}
                                </div>

                                <div
                                        class="is-inline"
                                        :class="{ 'back-hlight': diff.diff_with_index.hasOwnProperty(i) }"
                                        v-else-if="!Array.isArray(d) && d.constructor.name !== 'Object' ">{{ d }}
                                </div>


                                <div
                                        v-else
                                        v-for="(e, i2) in d"
                                        :key="i2"
                                        class="is-inline">

                                    <div
                                            class="is-capitalized"
                                            :class="{ 'back-hlight': diff.diff_with_index.hasOwnProperty(i) }">
                                        {{ `${i2.replace('_', ' ')}: ${e} ` }}
                                    </div>

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