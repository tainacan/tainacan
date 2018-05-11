<template>
    <div>
        <b-loading
                :active.sync="isLoading"
                :can-cancel="false"/>
        <tainacan-title />
        <div class="content">

            <router-link
                    class="button is-secondary"
                    :to="{ path: $routerHelper.getItemEditPath(collectionId, itemId)}">
                {{ $i18n.getFrom('items','edit_item') }}
            </router-link>
            <a
                    class="button is-success is-pulled-right"
                    :href="item.url">
                {{ $i18n.getFrom('items', 'view_item') }}
            </a>
            <br>

            <div
                    class="card-image"
                    v-if="item.document">
                <figure
                            class="image"
                        v-html="item.document_as_html" />
            </div>
            <br>

            <div
                v-if="item.thumbnail"
                class="media">
                <figure
                    class="media-left" >
                    <p class="image is-128x128">
                    <img :src="item.thumbnail">
                    </p>
                </figure>
                <div class="media-content">
                    {{ $i18n.get('label_thumbnail') }}
                </div>
            </div>

            <div
                v-for="(metadata, index) in item.metadata"
                :key="index"
                class="box">

                <p
                    v-if="metadata.value_as_html"
                    class="is-size-3"
                    v-html="metadata.value_as_html"/>
                <p
                    v-else>--</p>

                <p>
                    <i>
                    {{ metadata.name }}
                    </i>
                </p>
            </div>

            <div
                class="box">

                <div
                    v-if="attachments && attachments.length > 0">
                    <span
                        v-for="(attachment, index) in attachments"
                        :key="index"
                        >
                        <a
                            target="blank"
                            :href="attachment.guid.rendered">{{ attachment.guid.rendered }}</a>
                            <br>
                    </span>
                </div>
                <p v-else>--</p>

                <p>
                    <i>
                    {{ $i18n.get('label_attachments') }}
                    </i>
                </p>
            </div>

        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'ItemPage',
    data(){
        return {
            collectionId: Number,
            itemId: Number,
            isLoading: false
        }
    },
    methods: {
        ...mapActions('item', [
            'fetchItem',
            'fetchAttachments'
        ]),
        ...mapGetters('item', [
            'getItem',
            'getAttachments'
        ]),
    },
    computed: {
        item(){
            return this.getItem();
        },
        attachments(){
            return this.getAttachments();
        }
    },
    created(){
        // Obtains item and collection ID
        this.collectionId = this.$route.params.collectionId;
        this.itemId = this.$route.params.itemId;

        // Puts loading on Item Loading
        this.isLoading = true;
        let loadingInstance = this;

        // Obtains Item
        this.fetchItem(this.itemId).then(() => {
            loadingInstance.isLoading = false;
        });

        // Get attachments
        this.fetchAttachments(this.itemId);
    }

}
</script>
