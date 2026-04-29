/**
 * Accessibility directive for Buefy autocomplete/taginput.
 *
 * Adds combobox/listbox semantics:
 * - input: aria-controls, aria-expanded, aria-activedescendant
 * - popup: role="listbox"
 * - options: role="option" + aria-selected
 *
 * Apply to b-autocomplete or b-taginput: v-a11y-autocomplete
 * For append-to-body: v-a11y-autocomplete="{ appendToBody: true }"
 *
 * Avoids double screen-reader announcement on focus by: (1) collapsing Buefy's
 * select-all on focus; (2) suppressing input ARIA updates briefly after focus
 * when parent @focus handlers (e.g. filter performSearch) trigger DOM changes.
 */
let a11yAutocompleteCounter = 0;

export default {
    mounted(el, binding) {
        const appendToBody = binding.value && (binding.value.appendToBody === true || binding.value === true);
        let trackedDropdown = null;
        let trackedInput = null;
        let idBase = '';
        let listboxId = '';
        let rootObserver = null;
        let dropdownObserver = null;
        let syncRafId = null;
        let lastInputFocusTime = 0;
        const POST_FOCUS_ARIA_SUPPRESS_MS = 280;

        const isVisible = (node) => {
            return !!node &&
                node.offsetParent !== null &&
                node.style.display !== 'none' &&
                node.getAttribute('aria-hidden') !== 'true';
        };

        const normalizeId = (rawId) => {
            return String(rawId).trim().replace(/[^A-Za-z0-9\-_:.]/g, '-');
        };

        const getAutocompleteRoot = () => {
            if (el.classList && el.classList.contains('autocomplete'))
                return el;
            return el.querySelector('.autocomplete');
        };

        const getInput = () => {
            const autocompleteRoot = getAutocompleteRoot();
            return autocompleteRoot ? autocompleteRoot.querySelector('input') : null;
        };

        const ensureIds = () => {
            if (idBase && listboxId)
                return;

            const autocompleteRoot = getAutocompleteRoot();
            const input = getInput();
            const preferredId = (input && input.id) || (autocompleteRoot && autocompleteRoot.id) || el.id;

            if (preferredId) {
                const safeId = normalizeId(preferredId);
                idBase = `tainacan-a11y-autocomplete-${safeId}`;
            } else {
                const instanceId = ++a11yAutocompleteCounter;
                idBase = `tainacan-a11y-autocomplete-${instanceId}`;
            }

            listboxId = `${idBase}-listbox`;
        };

        const teardownDropdownTracking = () => {
            if (dropdownObserver) {
                dropdownObserver.disconnect();
                dropdownObserver = null;
            }

            if (trackedDropdown) {
                trackedDropdown.removeEventListener('mousemove', scheduleSync, true);
                trackedDropdown.removeEventListener('click', scheduleSync, true);
                trackedDropdown = null;
            }
        };

        const setupDropdownTracking = (dropdown) => {
            if (trackedDropdown === dropdown)
                return;

            teardownDropdownTracking();

            if (!dropdown)
                return;

            trackedDropdown = dropdown;
            trackedDropdown.addEventListener('mousemove', scheduleSync, true);
            trackedDropdown.addEventListener('click', scheduleSync, true);

            dropdownObserver = new MutationObserver(() => scheduleSync());
            dropdownObserver.observe(trackedDropdown, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'style', 'aria-hidden']
            });
        };

        const collapseSelectionOnFocus = () => {
            const input = getInput();
            if (!input || document.activeElement !== input) return;
            lastInputFocusTime = Date.now();
            setTimeout(() => {
                if (document.activeElement === input) {
                    const len = (input.value || '').length;
                    input.setSelectionRange(len, len);
                }
            }, 0);
        };

        const teardownInputTracking = () => {
            if (!trackedInput)
                return;

            trackedInput.removeEventListener('keydown', scheduleSync, true);
            trackedInput.removeEventListener('keyup', scheduleSync, true);
            trackedInput.removeEventListener('input', scheduleSync, true);
            trackedInput.removeEventListener('focus', collapseSelectionOnFocus, true);
            trackedInput.removeEventListener('blur', scheduleSync, true);
            trackedInput.removeEventListener('click', scheduleSync, true);
            trackedInput = null;
        };

        const setupInputTracking = () => {
            const input = getInput();
            if (!input || trackedInput === input)
                return;

            teardownInputTracking();
            trackedInput = input;

            trackedInput.addEventListener('keydown', scheduleSync, true);
            trackedInput.addEventListener('keyup', scheduleSync, true);
            trackedInput.addEventListener('input', scheduleSync, true);
            trackedInput.addEventListener('focus', collapseSelectionOnFocus, true);
            trackedInput.addEventListener('blur', scheduleSync, true);
            trackedInput.addEventListener('click', scheduleSync, true);
        };

        const findDropdownMenu = () => {
            const autocompleteRoot = getAutocompleteRoot();
            const input = getInput();
            if (!autocompleteRoot || !input)
                return null;

            const localMenu = autocompleteRoot.querySelector('.dropdown.dropdown-menu');
            if (isVisible(localMenu))
                return localMenu;

            if (!appendToBody)
                return null;

            const inputRect = input.getBoundingClientRect();
            const bodyMenus = Array.from(document.body.querySelectorAll('.dropdown.dropdown-menu'));
            const visibleMenus = bodyMenus.filter((menu) => isVisible(menu));

            if (!visibleMenus.length)
                return null;

            let bestMenu = null;
            let bestDistance = Number.POSITIVE_INFINITY;

            visibleMenus.forEach((menu) => {
                const menuRect = menu.getBoundingClientRect();
                const verticalDistance = Math.min(
                    Math.abs(menuRect.top - inputRect.bottom),
                    Math.abs(menuRect.bottom - inputRect.top)
                );
                const horizontalDistance = Math.abs(menuRect.left - inputRect.left);
                const score = verticalDistance + horizontalDistance;
                if (score < bestDistance) {
                    bestDistance = score;
                    bestMenu = menu;
                }
            });

            return bestMenu;
        };

        const getDropdownContent = (menu) => {
            if (!menu)
                return null;
            return menu.querySelector('.dropdown-content') || menu;
        };

        const getOptionItems = (menu) => {
            if (!menu)
                return [];
            return Array.from(menu.querySelectorAll('.dropdown-item'))
                .filter((item) => {
                    const isHeader = item.classList.contains('dropdown-header');
                    const isFooter = item.classList.contains('dropdown-footer');
                    const isDisabled = item.classList.contains('is-disabled') || item.hasAttribute('disabled');
                    const isGroupLabel = item.querySelector('.has-text-weight-bold') !== null &&
                        item.querySelector('a, button, [role="button"], [role="option"]') === null;
                    return !isHeader && !isFooter && !isDisabled && !isGroupLabel;
                });
        };

        const setInputAttrIfChanged = (node, name, value) => {
            if (value == null || value === '') {
                if (node.hasAttribute(name))
                    node.removeAttribute(name);
                return;
            }
            const current = node.getAttribute(name);
            if (current !== value)
                node.setAttribute(name, value);
        };

        function syncAutocompleteA11y() {
            syncRafId = null;
            const input = getInput();
            const dropdown = findDropdownMenu();
            const content = getDropdownContent(dropdown);

            if (!input)
                return;

            ensureIds();
            setupInputTracking();
            setupDropdownTracking(dropdown);

            const dropdownClosed = !dropdown || !content || !isVisible(dropdown);
            const inputFocused = document.activeElement === input;
            const withinPostFocusSuppress = (Date.now() - lastInputFocusTime) < POST_FOCUS_ARIA_SUPPRESS_MS;
            if (inputFocused && (dropdownClosed || withinPostFocusSuppress)) {
                return;
            }

            if (!dropdown || !content) {
                setInputAttrIfChanged(input, 'aria-expanded', null);
                setInputAttrIfChanged(input, 'aria-controls', null);
                setInputAttrIfChanged(input, 'aria-activedescendant', null);
                return;
            }

            const options = getOptionItems(dropdown);
            const isOpen = isVisible(dropdown);

            setInputAttrIfChanged(input, 'aria-expanded', isOpen ? 'true' : null);
            setInputAttrIfChanged(input, 'aria-controls', isOpen ? listboxId : null);
            content.setAttribute('id', listboxId);
            content.setAttribute('role', 'listbox');

            let activeDescendantId = null;
            options.forEach((option, index) => {
                const optionId = `${idBase}-option-${index}`;
                option.setAttribute('id', optionId);
                option.setAttribute('role', 'option');
                const isSelected = option.classList.contains('is-hovered');
                option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                if (isSelected)
                    activeDescendantId = optionId;
            });

            setInputAttrIfChanged(input, 'aria-activedescendant', activeDescendantId || null);
        }

        function scheduleSync() {
            if (syncRafId !== null)
                return;
            syncRafId = window.requestAnimationFrame(syncAutocompleteA11y);
        };

        rootObserver = new MutationObserver(() => scheduleSync());
        rootObserver.observe(el, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style', 'aria-hidden']
        });

        el._a11yAutocompleteState = {
            get syncRafId() { return syncRafId; },
            set syncRafId(value) { syncRafId = value; },
            teardownInputTracking,
            teardownDropdownTracking,
            rootObserver
        };

        scheduleSync();
    },

    unmounted(el) {
        const state = el._a11yAutocompleteState;
        if (!state)
            return;

        if (state.syncRafId !== null)
            window.cancelAnimationFrame(state.syncRafId);

        if (state.rootObserver)
            state.rootObserver.disconnect();

        if (typeof state.teardownInputTracking === 'function')
            state.teardownInputTracking();

        if (typeof state.teardownDropdownTracking === 'function')
            state.teardownDropdownTracking();

        delete el._a11yAutocompleteState;
    }
};
