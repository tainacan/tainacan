const { __, _x, _n, _nx } = wp.i18n;

/**
 * I18N plugin: exposes WordPress translation functions so Vue templates and scripts
 * can use __('string', 'tainacan') directly. The built JS then contains literal
 * strings that wp i18n make-pot can extract.
 */
export const I18NPlugin = {};
I18NPlugin.install = function (app) {
    const domain = 'tainacan';
    app.config.globalProperties.__ = (key, d = domain) => __(key, d);
    app.config.globalProperties._x = (key, context, d = domain) => _x(key, context, d);
    app.config.globalProperties._n = (single, plural, number, d = domain) => _n(single, plural, number, d);
    app.config.globalProperties._nx = (single, plural, number, context, d = domain) => _nx(single, plural, number, context, d);
};