import  tainacanRegisterBlockType from '../../js/compatibility/tainacan-blocks-compat-register.js';

import metadata from './block.json';
import icon from './icon.js';
import edit from './edit.js';
import save from './save.js';
import deprecated from './deprecated.js';
import transforms from './transforms.js';

tainacanRegisterBlockType({
    metadata,
    icon,
    edit,
    save,
    deprecated,
    transforms
});
