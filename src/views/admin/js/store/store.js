import { createStore } from 'vuex';

import item from './modules/item';
import collection from './modules/collection';
import metadata from './modules/metadata';
import filter from './modules/filter';
import search from './modules/search';
import taxonomy from './modules/taxonomy';
import activity from './modules/activity';
import importer from './modules/importer';
import bgprocess from './modules/bgprocess';
import bulkedition from './modules/bulk-edition';
import exporter from './modules/exporter';
import exposer from './modules/exposer';
import capability from './modules/capability';
import report from './modules/report';

export default createStore({
    modules: {
        item,
        collection,
        metadata,
        filter,
        search,
        taxonomy,
        activity,
        importer,
        bgprocess,
        bulkedition,
        exporter,
        exposer,
        capability,
        report
    }
})