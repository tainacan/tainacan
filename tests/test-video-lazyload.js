import assert from 'node:assert/strict';
import test from 'node:test';

const listeners = {};
const createdVideos = [];

globalThis.document = {
    readyState: 'loading',
    addEventListener(type, handler) {
        listeners[type] = handler;
    },
    createElement(type) {
        assert.equal(type, 'video');
        const attributes = {};
        const video = {
            attributes,
            src: '',
            setAttribute(name, value) {
                attributes[name] = value;
            },
            play() {
                video.playCalled = true;
                return Promise.resolve();
            }
        };
        createdVideos.push(video);
        return video;
    }
};

const { activateLazyVideo } = await import('../src/views/tainacan-video-lazyload.js');

test('activating a lazy video creates and plays one video element', async () => {
    const attributes = {
        'data-video-src': 'https://example.com/video.mp4',
        'data-video-width': '640',
        'data-video-height': '360'
    };
    const placeholder = {
        getAttribute(name) {
            return attributes[name] || null;
        },
        parentNode: {
            replaceChild(video, oldPlaceholder) {
                this.replacedVideo = video;
                this.replacedPlaceholder = oldPlaceholder;
            }
        }
    };

    activateLazyVideo(placeholder);
    await Promise.resolve();

    assert.equal(createdVideos.length, 1);
    assert.equal(createdVideos[0].src, 'https://example.com/video.mp4');
    assert.equal(createdVideos[0].className, 'tainacan-video-lazyload__video');
    assert.equal(createdVideos[0].attributes.controls, 'controls');
    assert.equal(createdVideos[0].attributes.preload, 'none');
    assert.equal(createdVideos[0].attributes.playsinline, 'playsinline');
    assert.equal(createdVideos[0].attributes.autoplay, 'autoplay');
    assert.equal(createdVideos[0].attributes.width, '640');
    assert.equal(createdVideos[0].attributes.height, '360');
    assert.equal(createdVideos[0].playCalled, true);
    assert.equal(placeholder.parentNode.replacedVideo, createdVideos[0]);
});

test('the module registers delegated DOM readiness handling', () => {
    assert.equal(typeof listeners.DOMContentLoaded, 'function');
});
