(function () {
    'use strict';
    function getStoredTheme() {
        return localStorage.getItem('theme') || 'light';
    }
    function setStoredTheme(theme) {
        localStorage.setItem('theme', theme);
    }
    function applyTheme(theme) {
        const html = document.documentElement;
        if (theme === 'dark') {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
        setStoredTheme(theme);
    }
    function initTheme() {
        const storedTheme = getStoredTheme();
        applyTheme(storedTheme);
    }
    function toggleTheme() {
        const html = document.documentElement;
        const currentTheme = html.classList.contains('dark') ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
        updateThemeToggleUI(newTheme);
    }
    function updateThemeToggleUI(theme) {
        const themeToggle = document.getElementById('theme-toggle');
        if (!themeToggle) return;
        const toggle = themeToggle.querySelector('div > div');
        const icon = toggle?.querySelector('iconify-icon');
        if (toggle && icon) {
            if (theme === 'dark') {
                toggle.classList.remove('translate-x-0');
                toggle.classList.add('translate-x-6');
                icon.setAttribute('icon', 'solar:sun-2-linear');
            } else {
                toggle.classList.remove('translate-x-6');
                toggle.classList.add('translate-x-0');
                icon.setAttribute('icon', 'solar:moon-linear');
            }
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
    if (typeof jQuery !== 'undefined') {
        function getCsrfToken() {
            return window.CSRF_TOKEN || '';
        }
        function addCsrfToken(data) {
            if (data === null || data === undefined) {
                return { csrf_token: getCsrfToken() };
            }
            if (Array.isArray(data)) {
                const hasToken = data.some(item => item && (item.name === 'csrf_token' || item === 'csrf_token'));
                if (!hasToken) {
                    data.push({ name: 'csrf_token', value: getCsrfToken() });
                }
                return data;
            }
            if (data instanceof FormData) {
                if (!data.has('csrf_token')) {
                    data.append('csrf_token', getCsrfToken());
                }
                return data;
            }
            if (typeof data === 'object') {
                if (data.nodeType || data.jquery) {
                    return data;
                }
                if (!data.hasOwnProperty('csrf_token')) {
                    data.csrf_token = getCsrfToken();
                }
                return data;
            }
            if (typeof data === 'string') {
                if (data.includes('csrf_token=')) {
                    return data; 
                }
                try {
                    const params = new URLSearchParams(data);
                    params.append('csrf_token', getCsrfToken());
                    return params.toString();
                } catch (e) {
                    return data + (data.includes('?') ? '&' : '?') + 'csrf_token=' + encodeURIComponent(getCsrfToken());
                }
            }
            
            return data;
        }
        const originalPost = jQuery.post;
        jQuery.post = function(url, data, success, dataType) {
            if (typeof url === 'string' && typeof data === 'function') {
                return originalPost.call(this, url, addCsrfToken({}), data, success);
            }
            if (data !== undefined && data !== null) {
                data = addCsrfToken(data);
            } else {
                data = addCsrfToken({});
            }
            return originalPost.call(this, url, data, success, dataType);
        };
        const originalAjax = jQuery.ajax;
        jQuery.ajax = function(options) {
            if (options && options.data !== undefined) {
                options.data = addCsrfToken(options.data);
            } else if (options && !options.data) {
                options.data = { csrf_token: getCsrfToken() };
            }
            return originalAjax.call(this, options);
        };
        jQuery(document).on('submit', 'form', function(e) {
            const $form = jQuery(this);
            if ($form.find('input[name="csrf_token"]').length === 0) {
                $form.append('<input type="hidden" name="csrf_token" value="' + getCsrfToken() + '">');
            }
        });
    }
    window.toggleTheme = toggleTheme;
    document.addEventListener('click', function (e) {
        const themeToggle = e.target.closest('#theme-toggle');
        if (themeToggle) {
            e.preventDefault();
            toggleTheme();
        }
    });
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                updateThemeToggleUI(theme);
            }
        });
    });
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
})();
(function () {
    'use strict';

    function initSidebar() {
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        if (!sidebarToggle || !sidebar) return;
        function toggleSidebar() {
            if (window.innerWidth >= 1024) return;
            const isOpen = sidebar.classList.contains('translate-x-0');
            if (isOpen) {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                sidebarToggle.setAttribute('aria-expanded', 'false');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.add('hidden');
                }
            } else {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                sidebarToggle.setAttribute('aria-expanded', 'true');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('hidden');
                }
            }
        }

        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            toggleSidebar();
        });

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function () {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                sidebarToggle.setAttribute('aria-expanded', 'false');
                sidebarOverlay.classList.add('hidden');
            });
        }

        document.addEventListener('click', function (e) {
            if (window.innerWidth < 1024) {
                if (!sidebar.contains(e.target) &&
                    !sidebarToggle.contains(e.target) &&
                    !sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                    sidebarToggle.setAttribute('aria-expanded', 'false');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.add('hidden');
                    }
                }
            }
        });

        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('translate-x-0', '-translate-x-full');
                    sidebarToggle.setAttribute('aria-expanded', 'false');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.add('hidden');
                    }
                } else {
                    if (!sidebar.classList.contains('translate-x-0')) {
                        sidebar.classList.add('-translate-x-full');
                        sidebar.classList.remove('translate-x-0');
                        sidebarToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            }, 100);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }
})();

(function () {
    'use strict';

    function initMenuToggle() {
        document.querySelectorAll('[data-menu-toggle]').forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const menuId = this.getAttribute('data-menu-toggle');
                const menu = document.querySelector('[data-menu="' + menuId + '"]');
                const icon = document.querySelector('[data-menu-icon="' + menuId + '"]');

                if (!menu) return;

                const isOpen = !menu.classList.contains('hidden') && menu.style.maxHeight !== '0px';

                if (isOpen) {
                    menu.style.maxHeight = menu.scrollHeight + 'px';
                    menu.style.overflow = 'visible';
                    menu.offsetHeight;
                    menu.style.maxHeight = '0px';
                    menu.style.opacity = '0';
                    menu.style.overflow = 'hidden';

                    setTimeout(function () {
                        menu.classList.add('hidden');
                    }, 300);

                    if (icon) {
                        icon.classList.remove('rotate-180');
                    }
                } else {
                    menu.classList.remove('hidden');
                    menu.style.maxHeight = '0px';
                    menu.style.opacity = '0';
                    menu.style.overflow = 'hidden';

                    menu.offsetHeight;

                    const targetHeight = menu.scrollHeight;
                    menu.style.maxHeight = targetHeight + 'px';
                    menu.style.opacity = '1';
                    menu.style.overflow = 'visible';

                    if (icon) {
                        icon.classList.add('rotate-180');
                    }
                }
            });
        });
        window.addEventListener('resize', function () {
            document.querySelectorAll('[data-menu]').forEach(function (menu) {
                if (!menu.classList.contains('hidden') && menu.style.maxHeight !== '0px') {
                    menu.style.maxHeight = menu.scrollHeight + 'px';
                    menu.style.overflow = 'visible';
                }
            });
        });
        function updateActiveMenuItem() {
            const currentPath = window.location.pathname;

            document.querySelectorAll('[data-menu]').forEach(function (menuContainer) {
                const allLinks = Array.from(menuContainer.querySelectorAll('a[href]'));
                let activeIndex = -1;

                allLinks.forEach(function (link, index) {
                    const linkPath = link.getAttribute('href');

                    const normalizePath = function (path) {
                        return path.replace(/\/$/, '') || '/';
                    };

                    const normalizedCurrent = normalizePath(currentPath);
                    const normalizedLink = normalizePath(linkPath);

                    const isExactMatch = normalizedCurrent === normalizedLink;
                    const isParentMatch = normalizedCurrent.startsWith(normalizedLink + '/');
                    const isActive = isExactMatch || isParentMatch;

                    if (isActive) {
                        activeIndex = index;
                    }
                });

                allLinks.forEach(function (link, index) {
                    const listItem = link.closest('li');
                    if (!listItem) return;

                    const dot = link.querySelector('span[class*="absolute left-[-15px]"]');
                    const verticalLineActive = listItem.querySelector('div.bg-vanixjnk[style*="scaleY"]');
                    const allListItems = Array.from(listItem.parentElement.querySelectorAll('li'));
                    const isLastItem = listItem === allListItems[allListItems.length - 1];
                    const isActive = index === activeIndex;
                    const isBeforeActive = activeIndex !== -1 && index < activeIndex;

                    if (isActive) {
                        link.classList.remove('text-muted-foreground', 'hover:text-foreground', 'hover:bg-gray-100');
                        link.classList.add('bg-vanixjnk/10', 'text-vanixjnk');

                        if (dot) {
                            dot.classList.remove('bg-gray-100', 'dark:bg-[#23272f]', 'bg-vanixjnk', 'scale-125');
                            dot.classList.add('bg-vanixjnk', 'scale-125', 'shadow-[0_0_8px_rgba(255,34,94,0.5)]');
                        }

                        if (verticalLineActive && !isLastItem) {
                            verticalLineActive.style.transform = 'scaleY(0)';
                        }

                        const parentMenu = link.closest('[data-menu]');
                        if (parentMenu && parentMenu.classList.contains('hidden')) {
                            const menuId = parentMenu.getAttribute('data-menu');
                            const toggleButton = document.querySelector('[data-menu-toggle="' + menuId + '"]');
                            if (toggleButton) {
                                toggleButton.click();
                            }
                        }
                    } else if (isBeforeActive) {
                        link.classList.remove('bg-vanixjnk/10', 'text-vanixjnk');
                        link.classList.add('text-muted-foreground', 'hover:text-foreground', 'hover:bg-gray-100');

                        if (dot) {
                            dot.classList.remove('bg-gray-100', 'dark:bg-[#23272f]', 'scale-125', 'shadow-[0_0_8px_rgba(255,34,94,0.5)]');
                            dot.classList.add('bg-vanixjnk', 'scale-125');
                        }

                        if (verticalLineActive && !isLastItem) {
                            verticalLineActive.style.transform = 'scaleY(1)';
                        }
                    } else {
                        link.classList.remove('bg-vanixjnk/10', 'text-vanixjnk');
                        link.classList.add('text-muted-foreground', 'hover:text-foreground', 'hover:bg-gray-100');

                        if (dot) {
                            dot.classList.remove('bg-vanixjnk', 'scale-125', 'shadow-[0_0_8px_rgba(255,34,94,0.5)]');
                            dot.classList.add('bg-gray-100', 'dark:bg-[#23272f]', 'scale-125');
                        }

                        if (verticalLineActive && !isLastItem) {
                            verticalLineActive.style.transform = 'scaleY(0)';
                        }
                    }
                });
            });
        }

        updateActiveMenuItem();
        window.addEventListener('popstate', updateActiveMenuItem);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMenuToggle);
    } else {
        initMenuToggle();
    }
})();
(function () {
    'use strict';
    function initDialog(dialogId) {
        const dialog = document.getElementById(dialogId);
        if (!dialog) return null;
        const backdrop = dialog.querySelector('[data-dialog-backdrop]');
        const closeButtons = dialog.querySelectorAll('[data-dialog-close]');
        let isOpen = false;
        function openDialog() {
            if (isOpen) return;
            isOpen = true;
            dialog.classList.remove('hidden');
            dialog.classList.add('flex');
            void dialog.offsetWidth;
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    dialog.setAttribute('data-state', 'open');
                });
            });
            document.body.style.overflow = 'hidden';
        }

        function closeDialog() {
            if (!isOpen) return;
            dialog.setAttribute('data-state', 'closed');
            setTimeout(() => {
                dialog.classList.add('hidden');
                dialog.classList.remove('flex');
                isOpen = false;
                document.body.style.overflow = '';
            }, 200);
        }
        closeButtons.forEach(btn => {
            btn.addEventListener('click', closeDialog);
        });

        if (backdrop) {
            backdrop.addEventListener('click', (e) => {
                if (e.target === backdrop) {
                    closeDialog();
                }
            });
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isOpen && dialog === document.activeElement.closest('[data-dialog]')) {
                closeDialog();
            }
        });

        return {
            open: openDialog,
            close: closeDialog,
            isOpen: function () { return isOpen; }
        };
    }
    function initAllDialogs() {
        document.querySelectorAll('[data-dialog]').forEach(function (dialog) {
            const dialogId = dialog.id;
            if (dialogId) {
                initDialog(dialogId);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllDialogs);
    } else {
        initAllDialogs();
    }
    window.initDialog = initDialog;
})();

window.vanixjnkdev = {
    toggleTheme: window.toggleTheme,
    getTheme: function () {
        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    },
    setTheme: function (theme) {
        if (theme === 'dark' || theme === 'light') {
            const html = document.documentElement;
            if (theme === 'dark') {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }
            localStorage.setItem('theme', theme);
        }
    }
};

(function () {
    'use strict';
    function initCustomSelects() {
        const selects = document.querySelectorAll('.custom-select-container');

        selects.forEach(selectContainer => {
            if (selectContainer.dataset.initialized === 'true') return;
            selectContainer.dataset.initialized = 'true';
            const trigger = selectContainer.querySelector('.custom-select-trigger');
            const content = selectContainer.querySelector('.custom-select-content');
            const input = selectContainer.querySelector('input[type="hidden"]');
            const options = selectContainer.querySelectorAll('.custom-select-item');
            const chevron = trigger.querySelector('.fa-chevron-down') || trigger.querySelector('.chevron-icon');
            const selectedText = trigger.querySelector('.selected-text');
            let initialSelected = null;
            if (input && input.value) {
                initialSelected = Array.from(options).find(opt => opt.dataset.value === input.value);
            }
            if (!initialSelected && options.length > 0) {
                initialSelected = options[0];
            }

            if (initialSelected) {
                options.forEach(opt => {
                    opt.dataset.state = 'unchecked';
                    const checkIcon = opt.querySelector('.check-icon');
                    if (checkIcon) {
                        checkIcon.classList.remove('opacity-100');
                        checkIcon.classList.add('opacity-0');
                    }
                });
                initialSelected.dataset.state = 'checked';
                const selectedCheckIcon = initialSelected.querySelector('.check-icon');
                if (selectedCheckIcon) {
                    selectedCheckIcon.classList.remove('opacity-0');
                    selectedCheckIcon.classList.add('opacity-100');
                }
                if (input) input.value = initialSelected.dataset.value;
                if (selectedText) selectedText.textContent = initialSelected.dataset.label || initialSelected.textContent.trim();
            }
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = content.dataset.state === 'open';
                document.querySelectorAll('.custom-select-content[data-state="open"]').forEach(otherContent => {
                    if (otherContent !== content) {
                        closeSelect(otherContent.closest('.custom-select-container'));
                    }
                });

                if (isOpen) {
                    closeSelect(selectContainer);
                } else {
                    openSelect(selectContainer);
                }
            });
            options.forEach(option => {
                option.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const value = this.dataset.value;
                    const label = this.dataset.label || this.textContent.trim();
                    if (input) input.value = value;
                    if (selectedText) selectedText.textContent = label;
                    options.forEach(opt => {
                        opt.dataset.state = 'unchecked';
                        const checkIcon = opt.querySelector('.check-icon');
                        if (checkIcon) {
                            checkIcon.classList.remove('opacity-100');
                            checkIcon.classList.add('opacity-0');
                        }
                    });
                    this.dataset.state = 'checked';
                    const currentCheckIcon = this.querySelector('.check-icon');
                    if (currentCheckIcon) {
                        currentCheckIcon.classList.remove('opacity-0');
                        currentCheckIcon.classList.add('opacity-100');
                    }
                    if (input) {
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    closeSelect(selectContainer);
                });
            });
        });

        if (!window._customSelectDocClickAdded) {
            window._customSelectDocClickAdded = true;
            document.addEventListener('click', function (e) {
                document.querySelectorAll('.custom-select-container').forEach(container => {
                    if (!container.contains(e.target)) {
                        closeSelect(container);
                    }
                });
            });
        }

        function openSelect(container) {
            const content = container.querySelector('.custom-select-content');
            const trigger = container.querySelector('.custom-select-trigger');
            const chevron = container.querySelector('.fa-chevron-down') || container.querySelector('.chevron-icon');

            content.classList.remove('hidden');
            
            const isInsideDialog = container.closest('[data-dialog]') !== null;
            
            if (isInsideDialog) {
                content.style.position = 'absolute';
                content.style.top = '100%';
                content.style.left = '0';
                content.style.width = '100%';
                content.style.marginTop = '4px';
                content.style.maxHeight = '200px';
            } else {
                const rect = trigger.getBoundingClientRect();
                content.style.position = 'fixed';
                content.style.top = (rect.bottom + 4) + 'px';
                content.style.left = rect.left + 'px';
                content.style.width = rect.width + 'px';
                content.style.maxHeight = '200px';
                const dropdownHeight = content.scrollHeight;
                const viewportHeight = window.innerHeight;
                if (rect.bottom + dropdownHeight + 8 > viewportHeight) {
                    content.style.top = (rect.top - dropdownHeight - 4) + 'px';
                }
            }
            
            requestAnimationFrame(() => {
                content.dataset.state = 'open';
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            });
        }
        function closeSelect(container) {
            const content = container.querySelector('.custom-select-content');
            const chevron = container.querySelector('.fa-chevron-down') || container.querySelector('.chevron-icon');
            content.dataset.state = 'closed';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
            setTimeout(() => {
                if (content.dataset.state === 'closed') {
                    content.classList.add('hidden');
                    content.style.position = '';
                    content.style.top = '';
                    content.style.left = '';
                    content.style.width = '';
                    content.style.marginTop = '';
                }
            }, 100);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCustomSelects);
    } else {
        initCustomSelects();
    }
    window.initCustomSelects = initCustomSelects;
    let scrollTimer;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function() {
            document.querySelectorAll('.custom-select-content[data-state="open"]').forEach(content => {
                const container = content.closest('.custom-select-container');
                if (container) {
                    const chevron = container.querySelector('.fa-chevron-down') || container.querySelector('.chevron-icon');
                    content.dataset.state = 'closed';
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                    setTimeout(() => {
                        if (content.dataset.state === 'closed') {
                            content.classList.add('hidden');
                            content.style.position = '';
                            content.style.top = '';
                            content.style.left = '';
                            content.style.width = '';
                        }
                    }, 100);
                }
            });
        }, 50);
    }, true);
    window.addEventListener('resize', function() {
        document.querySelectorAll('.custom-select-content[data-state="open"]').forEach(content => {
            const container = content.closest('.custom-select-container');
            if (container) {
                const chevron = container.querySelector('.fa-chevron-down') || container.querySelector('.chevron-icon');
                content.dataset.state = 'closed';
                if (chevron) chevron.style.transform = 'rotate(0deg)';
                setTimeout(() => {
                    if (content.dataset.state === 'closed') {
                        content.classList.add('hidden');
                        content.style.position = '';
                        content.style.top = '';
                        content.style.left = '';
                        content.style.width = '';
                    }
                }, 100);
            }
        });
    });

})();

(function () {
    'use strict';
    window.toggleDropdown = function (id, trigger) {
        const dropdown = document.getElementById(id);
        const allDropdowns = document.querySelectorAll('.dropdown-menu');
        allDropdowns.forEach(d => {
            if (d.id !== id && d.dataset.state === 'open') {
                d.dataset.state = 'closed';
                setTimeout(() => {
                    d.classList.add('hidden');
                }, 100);
            }
        });

        if (dropdown) {
            if (dropdown.dataset.state === 'open') {
                dropdown.dataset.state = 'closed';
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 100);
            } else {
                dropdown.classList.remove('hidden');
                if (trigger) {
                    const rect = trigger.getBoundingClientRect();
                    const dropdownWidth = 224;
                    let top = rect.bottom + 8;
                    let left = rect.right - dropdownWidth;
                    if (top + dropdown.offsetHeight > window.innerHeight) {
                        top = rect.top - dropdown.offsetHeight - 8;
                    }

                    dropdown.style.top = `${top}px`;
                    dropdown.style.left = `${left}px`;
                }
                void dropdown.offsetWidth;
                dropdown.dataset.state = 'open';
            }
        }
    };
    window.addEventListener('scroll', function () {
        document.querySelectorAll('.dropdown-menu[data-state="open"]').forEach(d => {
            d.dataset.state = 'closed';
            d.classList.add('hidden');
        });
    }, { passive: true });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.dropdown-container')) {
            document.querySelectorAll('.dropdown-menu[data-state="open"]').forEach(d => {
                d.dataset.state = 'closed';
                setTimeout(() => {
                    d.classList.add('hidden');
                }, 100);
            });
        }
    });
})();