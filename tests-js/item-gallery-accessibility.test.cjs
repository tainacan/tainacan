const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const sourcePath = path.join(
    __dirname,
    '..',
    'src',
    'views',
    'gutenberg-blocks',
    'blocks',
    'item-gallery',
    'theme.js'
);

const createElement = (selectors = {}, links = []) => {
    const attributes = new Map();

    return {
        attributes,
        childNodes: [],
        classList: {
            contains: (className) => className === 'swiper-slide'
        },
        nodeType: 1,
        querySelector: (selector) => selectors[selector] || null,
        querySelectorAll: (selector) => selector === 'a[href]' ? links : [],
        removeAttribute: (name) => attributes.delete(name),
        setAttribute: (name, value) => attributes.set(name, value),
        style: {}
    };
};

const loadGalleryClass = () => {
    const source = fs.readFileSync(sourcePath, 'utf8')
        .replace(/^import .*;\n/gm, '')
        .split('/* Loads and instantiates media components passed to the global variable */')[0];
    const tainacanPlugin = { classes: {} };
    const context = {
        PhotoSwipe: class {},
        PhotoSwipeLightbox: class {},
        Swiper: class {},
        Navigation: {},
        A11y: {},
        Thumbs: {},
        Pagination: {},
        tainacan_plugin: tainacanPlugin,
        window: { tainacan_plugin: tainacanPlugin },
        wp: { i18n: { __: (text) => text } }
    };

    vm.runInNewContext(source, context, { filename: sourcePath });

    return tainacanPlugin.classes.TainacanMediaGallery;
};

test('labels a native video slide with no descendant image without throwing', () => {
    const video = {
        getAttribute: () => null
    };
    const slideContent = createElement({
        img: null,
        video,
        iframe: null,
        audio: null,
        figure: null,
        '.swiper-slide-metadata': null
    });
    const slide = createElement({
        '.swiper-slide-content': slideContent,
        '.swiper-slide-metadata__name': null
    });
    const gallery = { childNodes: [slide] };
    const Gallery = loadGalleryClass();

    assert.doesNotThrow(() => {
        Gallery.prototype.enhanceLinksForAccessibility(gallery);
    });
    assert.equal(slideContent.attributes.get('role'), 'button');
    assert.equal(slideContent.attributes.get('aria-label'), 'Video');
});
