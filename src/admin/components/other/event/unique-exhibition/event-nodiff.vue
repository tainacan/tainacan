<template>
    <div>
        <div
                class="tile is-ancestor"
                v-if="event.log_diffs.constructor === Object &&
                 Object.keys(event.log_diffs).length > 0 ||
                  event.log_diffs.length > 0">
            <div class="tile is-parent">
                <article class="tile box is-child">
                    <div class="content">
                        <div
                                v-for="(diff, key) in event.log_diffs"
                                :key="key">

                            <p/>
                            <div class="has-text-weight-bold is-capitalized">
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
                                        class="is-capitalized">
                                    {{ `ID: ${d.id} Enabled: ${d.enabled}` }}
                                </div>

                                <div
                                        class="is-inline"
                                        v-else-if="!Array.isArray(d) && d.constructor.name !== 'Object' ">{{ d }}
                                </div>

                                <div
                                        v-else
                                        v-for="(e, i2) in d"
                                        :key="i2"
                                        class="is-inline">

                                    <div class="is-capitalized">
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
        name: "EventNoDiff",
        props: {
            event: Object
        }
    }
</script>

<style scoped>

</style>