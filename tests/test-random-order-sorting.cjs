// Run with: node --test tests/test-random-order-sorting.cjs
const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');
const qs = require('qs');

// Load the actual Vue script, leaving DOM-only carousel dependencies out of
// these request tests. Parsing and serializing queries uses the project's qs.
function loadTheme(block) {
    const filename = path.join(__dirname, '../src/views/gutenberg-blocks/blocks', block, 'theme.vue');
    const { descriptor } = parse(readFileSync(filename, 'utf8'));
    const { code } = transformSync(descriptor.script.content, {
        babelrc: false,
        configFile: false,
        presets: [['@babel/preset-env', { targets: { node: 'current' }, modules: 'commonjs' }]]
    });
    const exports = {};
    vm.runInNewContext(code, {
        exports,
        tainacan_blocks: { registered_view_modes: {} },
        require(name) {
            if (name === 'vue') return { nextTick: () => {} };
            if (name === 'swiper' || name === 'swiper/modules') return {};
            return require(name);
        }
    }, { filename });
    return exports.default;
}

function requestQuery(component, overrides = {}) {
    const requests = [];
    const context = {
        ...component.data(),
        collectionId: '10',
        loadStrategy: 'search',
        searchURL: '/admin/#/collections/10/items?orderby=meta_value_num&metakey=126&order=asc&search=flower',
        orderBy: 'rand',
        orderByMetaKey: '126',
        localOrder: 'desc',
        searchString: 'flower',
        selectedItems: [],
        ...overrides,
        tainacanAxios: {
            get(endpoint) {
                requests.push(endpoint);
                return Promise.resolve({ data: { items: [] }, headers: { 'x-wp-total': '0' } });
            }
        }
    };
    component.methods.fetchItems.call(context);
    assert.equal(requests.length, 1);
    assert.equal(requests[0].split('?')[0], '/collection/10/items');
    return qs.parse(requests[0].split('?')[1]);
}

for (const block of ['carousel-items-list', 'dynamic-items-list']) {
    const component = loadTheme(block);

    test(`${block}: random sorting removes direction and metadata key, preserving the search`, () => {
        const query = requestQuery(component);
        assert.equal(query.orderby, 'rand');
        assert.equal(Object.hasOwn(query, 'order'), false);
        assert.equal(Object.hasOwn(query, 'metakey'), false);
        assert.equal(query.search, 'flower');
    });

    test(`${block}: non-random sorting preserves metadata ordering`, () => {
        const query = requestQuery(component, {
            // The carousel keeps sorting from searchURL; the dynamic list uses props.
            orderBy: block === 'carousel-items-list' ? 'date' : 'meta_value_num',
            localOrder: 'asc'
        });
        assert.equal(query.orderby, 'meta_value_num');
        assert.equal(query.metakey, '126');
        assert.equal(query.order, 'asc');
    });

    test(`${block}: manual selection retains its explicit item order`, () => {
        const query = requestQuery(component, {
            loadStrategy: 'selection',
            selectedItems: [30, 10, 20]
        });
        assert.equal(query.orderby, 'post__in');
        assert.equal(query.order, 'ASC');
        assert.deepEqual(query.postin, ['30', '10', '20']);
        assert.equal(query.perpage, '3');
    });
}
