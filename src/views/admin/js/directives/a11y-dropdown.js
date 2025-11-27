/**
 * Accessibility directive for Buefy dropdowns
 * 
 * Adds arrow key navigation to dropdowns, making them behave like native <select> elements.
 * Apply this directive to b-dropdown components: v-a11y-dropdown
 * 
 * For append-to-body dropdowns, use: v-a11y-dropdown="{ appendToBody: true }"
 * 
 * Features:
 * - Arrow Up/Down: Navigate between items
 * - Home/End: Jump to first/last item
 * - Enter/Space: Select focused item
 * - Escape: Focus trigger to close dropdown
 */
export default {
    mounted(el, binding) {
        // el is the root element of b-dropdown (the .dropdown div)
        // binding.value can be an object with { appendToBody: true } or just true/false
        const appendToBody = binding.value && (binding.value.appendToBody === true || binding.value === true);
        
        let currentFocusedIndex = -1;
        let dropdownMenu = null;
        
        /**
         * Find the dropdown menu - handles both normal and append-to-body cases
         */
        const findDropdownMenu = () => {
            // First try to find it as a child (normal case)
            let menu = el.querySelector('.dropdown-menu');
            
            // If found as child, verify it's visible
            if (menu) {
                const isVisible = menu.offsetParent !== null && 
                                 menu.style.display !== 'none' &&
                                 menu.getAttribute('aria-hidden') !== 'true';
                if (isVisible) {
                    return menu;
                }
                // If not visible, might be append-to-body case
                menu = null;
            }
            
            // If append-to-body or not found as child, search in document body
            if (appendToBody || !menu) {
                // Get the trigger element to help identify the correct menu
                const trigger = el.querySelector('.dropdown-trigger');
                const triggerRect = trigger ? trigger.getBoundingClientRect() : null;
                
                // Get all dropdown menus in body
                const bodyMenus = Array.from(document.body.querySelectorAll('.dropdown-menu'));
                
                // Find the one that's visible and likely associated with this dropdown
                menu = bodyMenus.find(menuEl => {
                    const isVisible = menuEl.offsetParent !== null && 
                                     menuEl.style.display !== 'none' &&
                                     menuEl.getAttribute('aria-hidden') !== 'true';
                    
                    if (!isVisible) return false;
                    
                    // If we have trigger position, prefer menu that's near the trigger
                    if (triggerRect) {
                        const menuRect = menuEl.getBoundingClientRect();
                        // Check if menu is positioned near the trigger (within reasonable distance)
                        const isNearTrigger = Math.abs(menuRect.top - triggerRect.bottom) < 500 ||
                                            Math.abs(menuRect.bottom - triggerRect.top) < 500 ||
                                            Math.abs(menuRect.left - triggerRect.left) < 200;
                        return isNearTrigger;
                    }
                    
                    // If no trigger position, return first visible menu
                    return true;
                });
            }
            
            return menu;
        };
        
        const getDropdownItems = () => {
            dropdownMenu = findDropdownMenu();
            if (!dropdownMenu) return [];
            
            // Get all focusable items, filtering out disabled and separators
            const regularItems = Array.from(dropdownMenu.querySelectorAll('.dropdown-item'))
                .filter(item => {
                    const isDisabled = item.classList.contains('is-disabled') || 
                                     item.hasAttribute('disabled') ||
                                     item.closest('.is-disabled');
                    const isSeparator = item.classList.contains('dropdown-divider');
                    const isVisible = item.offsetParent !== null;
                    return !isDisabled && !isSeparator && isVisible;
                });
            
            // Also include special action containers (like dropdown-item-apply) that contain buttons
            const actionContainers = Array.from(dropdownMenu.querySelectorAll('.dropdown-item-apply, .dropdown-action, [class*="dropdown-item-action"]'))
                .filter(container => {
                    const isVisible = container.offsetParent !== null;
                    const hasFocusableElement = container.querySelector('button, a, [tabindex]:not([tabindex="-1"]), [role="button"]');
                    return isVisible && hasFocusableElement;
                });
            
            // Combine regular items and action containers
            return [...regularItems, ...actionContainers];
        };

        const focusItem = (index) => {
            const items = getDropdownItems();
            if (items.length === 0) return;
            
            // Clamp index
            if (index < 0) index = 0;
            if (index >= items.length) index = items.length - 1;
            
            currentFocusedIndex = index;
            const item = items[index];
            
            if (item) {
                // For action containers (like dropdown-item-apply), focus the button directly
                if (item.classList.contains('dropdown-item-apply') || 
                    item.classList.contains('dropdown-action') ||
                    item.className.includes('dropdown-item-action')) {
                    const button = item.querySelector('button, [role="button"]');
                    if (button && typeof button.focus === 'function') {
                        button.focus();
                        return;
                    }
                }
                
                // For regular dropdown items, find focusable element within item - prioritize interactive elements
                const focusableElement = item.querySelector('button, a, input, [tabindex]:not([tabindex="-1"]), [role="button"], [role="link"]') || 
                                       (item.hasAttribute('tabindex') || item.hasAttribute('role') ? item : null);
                
                if (focusableElement && typeof focusableElement.focus === 'function') {
                    focusableElement.focus();
                }
            }
        };

        const handleKeydown = (e) => {
            // Check if dropdown is active
            const isActive = el.classList.contains('is-active');
            if (!isActive) {
                return;
            }
            
            // Find dropdown menu (handles both normal and append-to-body)
            dropdownMenu = findDropdownMenu();
            if (!dropdownMenu || dropdownMenu.style.display === 'none' || dropdownMenu.getAttribute('aria-hidden') === 'true') {
                return;
            }

            const items = getDropdownItems();
            if (items.length === 0) return;

            // Find currently focused item
            const activeElement = document.activeElement;
            
            // Verify the event is related to this dropdown
            // Allow if: active element is in dropdown, or dropdown is active and we're handling arrow keys
            const isInDropdown = el.contains(activeElement) || 
                                dropdownMenu.contains(activeElement) ||
                                items.some(item => item === activeElement || item.contains(activeElement));
            
            // For arrow keys, also allow if dropdown is active (even if focus is on trigger)
            const isArrowKey = ['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(e.key);
            if (!isInDropdown && !isArrowKey) {
                return;
            }
            
            const currentIndex = items.findIndex(item => 
                item === activeElement || item.contains(activeElement)
            );

            // Update current focused index
            if (currentIndex >= 0) {
                currentFocusedIndex = currentIndex;
            } else if (currentFocusedIndex < 0) {
                // If no item is focused, start at first item
                currentFocusedIndex = 0;
            }

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    e.stopPropagation();
                    focusItem(currentFocusedIndex + 1);
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    e.stopPropagation();
                    focusItem(currentFocusedIndex - 1);
                    break;
                    
                case 'Home':
                    e.preventDefault();
                    e.stopPropagation();
                    focusItem(0);
                    break;
                    
                case 'End':
                    e.preventDefault();
                    e.stopPropagation();
                    focusItem(items.length - 1);
                    break;
                    
                case 'Enter':
                    // Only handle if we're on a dropdown item (not in input/textarea)
                    if (activeElement.tagName !== 'INPUT' && 
                        activeElement.tagName !== 'TEXTAREA' &&
                        currentFocusedIndex >= 0 && 
                        currentFocusedIndex < items.length) {
                        const item = items[currentFocusedIndex];
                        // Find clickable element - check for button, link, or elements with role="button" or role="link"
                        const clickableElement = item.querySelector('button, a, [role="button"], [role="link"]') || 
                                               (item.hasAttribute('role') && (item.getAttribute('role') === 'button' || item.getAttribute('role') === 'link') ? item : null);
                        if (clickableElement && typeof clickableElement.click === 'function') {
                            e.preventDefault();
                            e.stopPropagation();
                            clickableElement.click();
                        }
                    }
                    break;
                    
                case ' ':
                    // Same as Enter, but only if not in input/textarea
                    if (activeElement.tagName !== 'INPUT' && 
                        activeElement.tagName !== 'TEXTAREA' &&
                        currentFocusedIndex >= 0 && 
                        currentFocusedIndex < items.length) {
                        const item = items[currentFocusedIndex];
                        // Find clickable element - check for button, link, or elements with role="button" or role="link"
                        const clickableElement = item.querySelector('button, a, [role="button"], [role="link"]') || 
                                               (item.hasAttribute('role') && (item.getAttribute('role') === 'button' || item.getAttribute('role') === 'link') ? item : null);
                        if (clickableElement && typeof clickableElement.click === 'function') {
                            e.preventDefault();
                            e.stopPropagation();
                            clickableElement.click();
                        }
                    }
                    break;
                    
                case 'Escape':
                    // Focus the trigger button to close dropdown
                    const trigger = el.querySelector('.dropdown-trigger');
                    if (trigger) {
                        const triggerButton = trigger.querySelector('button, [tabindex]:not([tabindex="-1"])');
                        if (triggerButton) {
                            e.preventDefault();
                            e.stopPropagation();
                            triggerButton.focus();
                            // Buefy will handle closing via focus management
                        }
                    }
                    break;
            }
        };

        // Always use document listener - works for both cases
        document.addEventListener('keydown', handleKeydown, true);
        
        // Store handler for cleanup
        el._a11yKeydownHandler = handleKeydown;
        
        // Focus first item when dropdown opens
        const observer = new MutationObserver(() => {
            const isActive = el.classList.contains('is-active');
            
            if (isActive) {
                // Give Buefy a moment to render the menu first
                setTimeout(() => {
                    // Focus first item
                    const items = getDropdownItems();
                    if (items.length > 0 && currentFocusedIndex < 0) {
                        focusItem(0);
                    }
                }, appendToBody ? 100 : 50);
            } else {
                currentFocusedIndex = -1; // Reset when closed
                dropdownMenu = null; // Clear reference
            }
        });
        
        observer.observe(el, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        el._a11yObserver = observer;
    },
    
    unmounted(el) {
        // Cleanup
        if (el._a11yKeydownHandler) {
            document.removeEventListener('keydown', el._a11yKeydownHandler, true);
            delete el._a11yKeydownHandler;
        }
        
        if (el._a11yObserver) {
            el._a11yObserver.disconnect();
            delete el._a11yObserver;
        }
    }
};
