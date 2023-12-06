module.exports = {
    extends: [
        // add more generic rulesets here, such as:
        'eslint:recommended',
        'plugin:vue/recommended'
    ],
    parserOptions: {
        'ecmaVersion': 2018
    },
    rules: {
        // override/add rules settings here, such as:
        'vue/no-unused-vars': 'error',
        'no-console': 'warn',
        'no-unused-vars': 'warn',
        'no-undef': 'warn',
        'vue/no-v-html': 'off',
        'vue/html-indent': 'off',
        'vue/attributes-order': 'off',
        'vue/html-closing-bracket-spacing': 'off',
        'vue/html-closing-bracket-newline': 'off',
        'vue/require-prop-type-constructor': 'off',
        'vue/singleline-html-element-content-newline': 'off',
        'vue/multiline-html-element-content-newline': 'off',
        'vue/prop-name-casing': 'off',
        "vue/no-v-for-template-key": 'off', // In Vue3, this rule is deprecated
        "vue/no-v-model-argument": "off", // ADD
        'vue/multi-word-component-names': 'off',
        'vue/require-default-prop': 'off',
        'vue/no-v-text-v-html-on-component': 'off',
        'vue/no-multiple-template-root': 'off' // In Vue3, this rule is deprecated
    },
    globals: {
        'wp': true,
        'tainacan_plugin': true,
        'tainacan_blocks': true,
        '_': true,
        'jQuery': true,
        'tainacan_extra_components': true,
        'tainacan_extra_plugins': true,
        'grecaptcha': true,
        'webkit': true
    }
}