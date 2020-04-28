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
        'vue/return-in-computed-property': 'off',
        'vue/singleline-html-element-content-newline': 'off',
        'vue/multiline-html-element-content-newline': 'off',
        'vue/prop-name-casing': 'off',
        'vue/no-confusing-v-for-v-if': 'off',
        'vue/no-use-v-if-with-v-for': 'off',
        'vue/no-template-shadow': 'off',
        'vue/require-default-prop': 'off' // https://github.com/vuejs/eslint-plugin-vue/blob/master/docs/rules/require-default-prop.md
    },
    globals: {
        'tainacan_plugin': true,
        '_': true,
        'jQuery': true,
        'tainacan_extra_components': true,
        'tainacan_extra_plugins': true
    }
}