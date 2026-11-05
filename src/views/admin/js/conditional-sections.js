/**
 * Ancestor term ids already seen in this page, keyed by term id.
 * Filled from term and facet payloads, which carry an `ancestors` list.
 * Private to this module: screens do not render from it.
 */
const ancestorsByTermId = new Map();

/**
 * Remember the ancestor chain of a term object or a taxonomy facet value.
 *
 * @param {object} term Term (`id`) or facet value (`value`) that may include `ancestors`.
 */
export function rememberTermAncestors(term) {
    if (!term || typeof term !== 'object' || !Array.isArray(term.ancestors))
        return;

    const id = term.id != null ? term.id : term.value;
    if (id == null || id === '')
        return;

    ancestorsByTermId.set(String(id), term.ancestors);
}

/**
 * @param {Array} terms
 */
export function rememberTerms(terms) {
    if (!Array.isArray(terms))
        return;

    terms.forEach(rememberTermAncestors);
}

function ancestorIdsFor(value) {
    if (value && typeof value === 'object' && Array.isArray(value.ancestors))
        return value.ancestors;

    const id = value && typeof value === 'object'
        ? (value.id != null ? value.id : value.value)
        : value;

    if (id == null || id === '')
        return [];

    return ancestorsByTermId.get(String(id)) || [];
}

function valueMatchesRule(value, rules) {
    const id = value && typeof value === 'object'
        ? (value.id != null ? value.id : value.value)
        : value;

    if (rules.some(rule => rule == id))
        return true;

    return ancestorIdsFor(value).some(ancestor => rules.some(rule => rule == ancestor));
}

/**
 * Stored values of one metadatum on an item metadata list.
 *
 * @param {Array} itemMetadata
 * @param {number|string} metadatumId
 * @return {Array}
 */
function storedValuesForMetadatum(itemMetadata, metadatumId) {
    const current = Array.isArray(itemMetadata)
        ? itemMetadata.find(anItemMetadatum => anItemMetadatum && anItemMetadatum.metadatum && anItemMetadatum.metadatum.id == metadatumId)
        : undefined;

    if (!current)
        return [];

    return Array.isArray(current.value) ? current.value : [current.value];
}

/**
 * Whether a conditional section stays hidden for the given metadatum values.
 * A term matches a rule term when it is that term or a descendant of it.
 *
 * @param {Array|*} values     Current metadatum values. Term objects or ids.
 * @param {Array|*} ruleValues Values configured on the section.
 * @return {boolean}
 */
export function conditionalSectionShouldHide(values, ruleValues) {
    const rules = (Array.isArray(ruleValues) ? ruleValues : [ruleValues])
        .filter(rule => rule !== undefined && rule !== null && rule !== '');
    const list = Array.isArray(values) ? values : [values];
    const present = list.filter(value => value !== undefined && value !== null && value !== '' && value !== false);

    if (!present.length)
        return true;

    return present.every(value => !valueMatchesRule(value, rules));
}

/**
 * Form records for the conditional sections in a sections payload.
 * Each record watches the first rule. Sections start hidden.
 *
 * @param {Array} metadataSections
 * @return {Object}
 */
export function conditionalSectionsFrom(metadataSections) {
    const conditionalSections = {};

    if (!Array.isArray(metadataSections))
        return conditionalSections;

    metadataSections.forEach(metadataSection => {
        if (!metadataSection || metadataSection.is_conditional_section != 'yes')
            return;

        const rules = metadataSection.conditional_section_rules;
        if (!rules || typeof rules !== 'object' || !Object.keys(rules).length)
            return;

        const metadatumId = Object.keys(rules)[0];
        conditionalSections[metadataSection.id] = {
            sectionId: metadataSection.id,
            metadatumId: metadatumId,
            metadatumValues: rules[metadatumId],
            hide: true
        };
    });

    return conditionalSections;
}

/**
 * Whether the section is hidden for the item's current metadata.
 * A section that is not conditional stays visible.
 *
 * @param {object} metadataSection
 * @param {Array} itemMetadata
 * @return {boolean}
 */
export function conditionalSectionIsHidden(metadataSection, itemMetadata) {
    if (!metadataSection || metadataSection.is_conditional_section != 'yes')
        return false;

    const rules = metadataSection.conditional_section_rules;
    if (!rules || typeof rules !== 'object' || !Object.keys(rules).length)
        return false;

    const metadatumId = Object.keys(rules)[0];
    return conditionalSectionShouldHide(storedValuesForMetadatum(itemMetadata, metadatumId), rules[metadatumId]);
}

/**
 * Copy the form records with hide set from the item metadata.
 * Term objects in those values are remembered for later id-only checks.
 *
 * @param {Object} conditionalSections
 * @param {Array} itemMetadata
 * @return {Object}
 */
export function conditionalSectionsForItemMetadata(conditionalSections, itemMetadata) {
    const next = {};

    Object.keys(conditionalSections || {}).forEach(sectionId => {
        const record = conditionalSections[sectionId];
        const values = storedValuesForMetadatum(itemMetadata, record.metadatumId);
        rememberTerms(values);
        next[sectionId] = Object.assign({}, record, {
            hide: conditionalSectionShouldHide(values, record.metadatumValues)
        });
    });

    return next;
}
