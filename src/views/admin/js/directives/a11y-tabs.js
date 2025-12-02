/**
 * Accessibility directive for custom tab components
 * 
 * Implements WAI-ARIA manual activation pattern for tabs.
 * Apply this directive to the element with role="tablist": v-a11y-tabs
 * 
 * Note: Buefy tabs already have built-in keyboard navigation, so this directive
 * only targets custom tabs where role="tab" is on the <a> element.
 * 
 * Features:
 * - Arrow Left/Right: Navigate between tabs (horizontal orientation)
 * - Arrow Up/Down: Navigate between tabs (vertical orientation)
 * - Home/End: Jump to first/last tab
 * - Enter/Space: Activate focused tab (manual activation, no auto-switching)
 */
export default {
    mounted(el) {
        // el should be the element with role="tablist" (typically a <ul>)
        // Only process if it's a custom tablist (not Buefy)
        if (!el.getAttribute('role') || el.getAttribute('role') !== 'tablist') {
            // If not a tablist, try to find one inside
            const tablist = el.querySelector('[role="tablist"]');
            if (!tablist) return;
            el = tablist;
        }
        
        /**
         * Get all custom tabs (where role="tab" is on <a> element, not <li>)
         * @returns {Array<HTMLElement>} Array of tab <a> elements
         */
        const getTabs = () => {
            return Array.from(el.querySelectorAll('a[role="tab"]'))
                .filter(tab => {
                    // Only include visible, enabled tabs
                    const isVisible = tab.offsetParent !== null;
                    const isDisabled = tab.hasAttribute('disabled') || 
                                    tab.getAttribute('aria-disabled') === 'true' ||
                                    tab.closest('[aria-disabled="true"]');
                    return isVisible && !isDisabled;
                });
        };
        
        /**
         * Get the index of a tab in the tabs array
         * @param {HTMLElement} tab - The tab element
         * @returns {number} Index of the tab, or -1 if not found
         */
        const getTabIndex = (tab) => {
            const tabs = getTabs();
            return tabs.indexOf(tab);
        };
        
        /**
         * Find the currently focused tab
         * @returns {HTMLElement|null} The focused tab element, or null if not found
         */
        const findCurrentTab = () => {
            const tabs = getTabs();
            const activeElement = document.activeElement;
            
            if (!activeElement) return null;
            
            // Check if activeElement is a tab itself
            if (tabs.includes(activeElement)) {
                return activeElement;
            }
            
            // Check if activeElement is inside a tab (e.g., a span or icon inside the <a>)
            for (const tab of tabs) {
                if (tab.contains(activeElement)) {
                    return tab;
                }
            }
            
            return null;
        };
        
        /**
         * Move focus to a specific tab
         * @param {number} index - Index of the tab to focus
         * @param {boolean} wrap - Whether to wrap around at boundaries
         */
        const focusTab = (index, wrap = true) => {
            const tabs = getTabs();
            if (tabs.length === 0) return;
            
            // Wrap around or clamp index
            if (wrap) {
                if (index < 0) index = tabs.length - 1;
                if (index >= tabs.length) index = 0;
            } else {
                if (index < 0) index = 0;
                if (index >= tabs.length) index = tabs.length - 1;
            }
            
            const tab = tabs[index];
            if (!tab) return;
            
            // Temporarily set tabindex="0" if needed to make it focusable
            const currentTabindex = tab.getAttribute('tabindex');
            if (currentTabindex === '-1') {
                tab.setAttribute('tabindex', '0');
            }
            
            // Focus the element
            if (typeof tab.focus === 'function') {
                tab.focus({ preventScroll: true });
            }
        };
        
        /**
         * Activate a tab by clicking it
         * @param {HTMLElement} tab - The tab element to activate
         */
        const activateTab = (tab) => {
            if (!tab) return;
            
            // Trigger click event
            if (tab.click && typeof tab.click === 'function') {
                tab.click();
            } else {
                // Fallback: dispatch click event
                const clickEvent = new MouseEvent('click', {
                    bubbles: true,
                    cancelable: true,
                    view: window
                });
                tab.dispatchEvent(clickEvent);
            }
        };
        
        /**
         * Handle keyboard events
         * @param {KeyboardEvent} e - The keyboard event
         */
        const handleKeydown = (e) => {
            // Don't interfere with Tab key - let browser handle it naturally
            if (e.key === 'Tab') {
                return;
            }
            
            const tabs = getTabs();
            if (tabs.length === 0) return;
            
            // Find the currently focused tab
            const currentTab = findCurrentTab();
            if (!currentTab) {
                // If no tab is focused, try to focus the active tab first
                const activeTab = tabs.find(tab => 
                    tab.getAttribute('aria-selected') === 'true' || 
                    tab.classList.contains('is-active') ||
                    tab.closest('li.is-active')
                );
                if (activeTab) {
                    const activeIndex = getTabIndex(activeTab);
                    if (activeIndex >= 0) {
                        focusTab(activeIndex, false);
                    }
                }
                return;
            }
            
            const currentIndex = getTabIndex(currentTab);
            if (currentIndex < 0) return;
            
            // Get orientation (default to horizontal)
            const orientation = el.getAttribute('data-orientation') || 
                               el.getAttribute('aria-orientation') || 
                               'horizontal';
            const isHorizontal = orientation === 'horizontal';
            
            // Handle arrow keys and navigation keys
            if (e.key === 'ArrowLeft' || e.key === 'ArrowRight' || 
                e.key === 'ArrowUp' || e.key === 'ArrowDown' || 
                e.key === 'Home' || e.key === 'End') {
                e.preventDefault();
                e.stopPropagation();
            }
            
            switch (e.key) {
                case 'ArrowLeft':
                    if (isHorizontal) {
                        focusTab(currentIndex - 1);
                    }
                    break;
                    
                case 'ArrowRight':
                    if (isHorizontal) {
                        focusTab(currentIndex + 1);
                    }
                    break;
                    
                case 'ArrowUp':
                    if (!isHorizontal) {
                        focusTab(currentIndex - 1);
                    }
                    break;
                    
                case 'ArrowDown':
                    if (!isHorizontal) {
                        focusTab(currentIndex + 1);
                    }
                    break;
                    
                case 'Home':
                    focusTab(0, false);
                    break;
                    
                case 'End':
                    focusTab(tabs.length - 1, false);
                    break;
                    
                case 'Enter':
                case ' ':
                    // Activate the currently focused tab
                    if (e.key === ' ') {
                        e.preventDefault();
                    }
                    e.stopPropagation();
                    
                    const focusedTab = findCurrentTab();
                    if (focusedTab) {
                        activateTab(focusedTab);
                    }
                    break;
            }
        };
        
        // Add event listener
        el.addEventListener('keydown', handleKeydown);
        
        // Store handler for cleanup
        el._a11yTabsKeydownHandler = handleKeydown;
    },
    
    unmounted(el) {
        // Remove event listener
        if (el._a11yTabsKeydownHandler) {
            el.removeEventListener('keydown', el._a11yTabsKeydownHandler);
            delete el._a11yTabsKeydownHandler;
        }
    }
};
