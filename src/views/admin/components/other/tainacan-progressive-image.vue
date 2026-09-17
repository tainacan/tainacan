<template>
    <div
            class="parent tainacan-progressive-image"
            :class="[
                {
                    'is-loaded': imageLoaded,
                    'is-instant': isInstant
                },
                $attrs.class
            ]"
            :style="wrapperStyle">
        <canvas
                v-if="isBlurhashEnabled"
                v-show="showCanvas"
                ref="canvas"
                class="child"
                :width="decodeWidth"
                :height="decodeHeight"
            />
        <img
                ref="image"
                class="child"
                v-bind="imageAttrs"
                :width="width"
                :height="height"
                :src="src"
                :srcset="srcset"
                @load="onImageLoad"
            >
    </div>
</template>

<script>
import { decode } from 'blurhash';

const BLURHASH_DECODE_MAX_EDGE = 32;

/**
 * Canvas decode/createImageData require integers. Fractional SVG sizes
 * (e.g. 363.9×330.1) make decode() emit more pixels than the canvas bitmap.
 */
function toPositiveInt(value, fallback = 1) {
    const parsed = Math.round(Number(value));
    return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
}

function getBlurhashCanvasSize(width, height, maxEdge = BLURHASH_DECODE_MAX_EDGE) {
    const parsedWidth = toPositiveInt(width);
    const parsedHeight = toPositiveInt(height);
    const longestEdge = Math.max(parsedWidth, parsedHeight);
    const targetEdge = toPositiveInt(maxEdge, BLURHASH_DECODE_MAX_EDGE);

    if (longestEdge <= targetEdge) {
        return {
            width: parsedWidth,
            height: parsedHeight
        };
    }

    const scale = targetEdge / longestEdge;

    return {
        width: toPositiveInt(parsedWidth * scale),
        height: toPositiveInt(parsedHeight * scale)
    };
}

function prefersReducedMotion() {
    return typeof window !== 'undefined' &&
        window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function isImageBlurhashEnabled() {
    if (typeof tainacan_plugin === 'undefined' || typeof tainacan_plugin.tainacan_enable_image_blurhash === 'undefined')
        return true;

    const enabled = tainacan_plugin.tainacan_enable_image_blurhash;
    return enabled !== false && enabled !== 0 && enabled !== '0' && enabled !== '';
}

export default {
    name: 'TainacanProgressiveImage',
    inheritAttrs: false,
    props: {
        hash: {
            type: String,
            required: true
        },
        src: {
            type: String,
            required: true
        },
        srcset: {
            type: String,
            default: undefined
        },
        width: {
            type: [Number, String],
            default: 128
        },
        height: {
            type: [Number, String],
            default: 128
        },
        punch: {
            type: Number,
            default: 1
        },
        transitionDuration: {
            type: Number,
            default: 200
        }
    },
    data() {
        const blurhashEnabled = isImageBlurhashEnabled();
        return {
            imageLoaded: !blurhashEnabled,
            isInstant: !blurhashEnabled,
            showCanvas: blurhashEnabled,
            hideCanvasTimeout: null,
            hasMounted: false
        }
    },
    computed: {
        isBlurhashEnabled() {
            return isImageBlurhashEnabled();
        },
        fadeDuration() {
            return Math.max(0, Number(this.transitionDuration) || 0);
        },
        decodeSize() {
            return getBlurhashCanvasSize(this.width, this.height);
        },
        decodeWidth() {
            return this.decodeSize.width;
        },
        decodeHeight() {
            return this.decodeSize.height;
        },
        wrapperStyle() {
            return [
                {
                    '--tainacan-progressive-image-duration': this.fadeDuration + 'ms'
                },
                this.$attrs.style
            ];
        },
        imageAttrs() {
            const attrs = { ...this.$attrs };
            delete attrs.class;
            delete attrs.style;
            return attrs;
        }
    },
    watch: {
        hash: 'drawBlurhash',
        decodeWidth: 'drawBlurhash',
        decodeHeight: 'drawBlurhash',
        punch: 'drawBlurhash',
        src() {
            this.resetImageState();
        }
    },
    mounted() {
        this.hasMounted = true;
        this.drawBlurhash();
        this.syncIfAlreadyComplete();
    },
    beforeUnmount() {
        this.clearHideCanvasTimeout();
    },
    methods: {
        resetImageState() {
            this.clearHideCanvasTimeout();
            this.imageLoaded = !this.isBlurhashEnabled;
            this.isInstant = !this.isBlurhashEnabled;
            this.showCanvas = this.isBlurhashEnabled;
            this.$nextTick(() => {
                this.drawBlurhash();
                this.syncIfAlreadyComplete();
            });
        },
        syncIfAlreadyComplete() {
            const image = this.$refs.image;
            if (image && image.complete && image.naturalWidth)
                this.markImageLoaded(true);
        },
        onImageLoad() {
            this.markImageLoaded(!this.hasMounted);
        },
        markImageLoaded(instant) {
            if (this.imageLoaded)
                return;

            this.isInstant = instant || prefersReducedMotion() || this.fadeDuration === 0;
            this.imageLoaded = true;

            if (this.isInstant) {
                this.showCanvas = false;
                return;
            }

            this.hideCanvasTimeout = window.setTimeout(() => {
                this.showCanvas = false;
                this.hideCanvasTimeout = null;
            }, this.fadeDuration);
        },
        clearHideCanvasTimeout() {
            if (this.hideCanvasTimeout) {
                window.clearTimeout(this.hideCanvasTimeout);
                this.hideCanvasTimeout = null;
            }
        },
        drawBlurhash() {
            if (!this.isBlurhashEnabled || !this.$refs.canvas || !this.hash)
                return;

            try {
                const width = toPositiveInt(this.decodeWidth);
                const height = toPositiveInt(this.decodeHeight);
                const pixels = decode(this.hash, width, height, this.punch);
                const context = this.$refs.canvas.getContext('2d');
                const imageData = context.createImageData(width, height);
                imageData.data.set(pixels);
                context.putImageData(imageData, 0, 0);
            } catch {
                // Invalid hashes should not block the real image.
            }
        }
    }
}
</script>

<style lang="scss" scoped>
    .parent {
        display: grid;
        grid-template: 1fr / 1fr;
        --tainacan-progressive-image-duration: 200ms;
    }
    .child {
        grid-area: 1 / 1 / 2 / 2;
        max-width: 100%;
        transition: opacity var(--tainacan-progressive-image-duration) ease;
    }
    canvas.child {
        width: 100%;
        height: 100%;
        object-fit: contain;
        opacity: 1;
    }
    img.child {
        opacity: 0;
    }
    .is-loaded {
        canvas.child {
            opacity: 0;
        }
        img.child {
            opacity: 1;
        }
    }
    .is-instant .child {
        transition-duration: 0s;
    }
    @media (prefers-reduced-motion: reduce) {
        .child {
            transition-duration: 0s;
        }
    }
</style>
