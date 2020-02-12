
const { __, _x, _n, _nx } = wp.i18n;

/** 
 I18N PLUGIN - Allows access to Wordpress translation functions.
    __( '__', 'my-domain' );
    _x( '_x', '_x_context', 'my-domain' );
    _n( '_n_single', '_n_plural', number, 'my-domain' );
    _nx( '_nx_single', '_nx_plural', number, '_nx_context', 'my-domain' );
**/
export const I18NPlugin = {};
I18NPlugin.install = function (Vue, options = {}) {
    
    Vue.prototype.$i18n = {
        get(key) {
            return __(key, 'tainacan');
        },
        getWithContext(key, keyContext) {
            return _x(key, keyContext, 'tainacan');
        },
        getWithNumber(keySingle, keyPlural, number) {
            return _n(keySingle, keyPlural, number, 'tainacan');
        },
        getWithNumberAndContext(keySingle, keyPlural, number, keyContext) {
            return _nx(keySingle, keyPlural, number, keyContext, 'tainacan');
        },
    }

};