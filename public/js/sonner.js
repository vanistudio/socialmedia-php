(function (global) {
    const defaultConfig = {
        position: 'bottom-right',
        expand: false,
        visibleToasts: 3,
        duration: 4000
    };
    let userConfig = { ...defaultConfig };
    let toasts = [];
    let toaster = null;
    function initToaster() {
        if (toaster) return;
        toaster = document.createElement('ol');
        toaster.setAttribute('data-sonner-toaster', '');
        toaster.setAttribute('dir', 'auto');
        toaster.setAttribute('tabindex', '-1');
        if (window.SONNER_CONFIG) {
            userConfig = { ...defaultConfig, ...window.SONNER_CONFIG };
        }
        toaster.setAttribute('data-sonner-config-position', userConfig.position);
        toaster.setAttribute('data-expanded', 'false');
        toaster.addEventListener('mouseenter', () => {
            if (userConfig.expand) {
                toaster.setAttribute('data-expanded', 'true');
            }
        });
        toaster.addEventListener('mouseleave', () => {
            toaster.setAttribute('data-expanded', 'false');
        });
        document.body.appendChild(toaster);
    }
    function createToastElement(toast) {
        const li = document.createElement('li');
        li.setAttribute('data-sonner-toast', '');
        li.setAttribute('data-type', toast.type);
        li.setAttribute('data-id', toast.id);
        let iconHtml = '';
        if (toast.type === 'success') {
            iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>';
        } else if (toast.type === 'error') {
            iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>';
        } else if (toast.type === 'loading') {
            iconHtml = '<div class="sonner-loader"></div>';
        }
        li.innerHTML = `
            <div data-sonner-toast-content>
                ${iconHtml ? `<div data-sonner-toast-icon>${iconHtml}</div>` : ''}
                <div data-sonner-toast-text>
                    ${toast.title ? `<div data-sonner-toast-title>${toast.title}</div>` : ''}
                    ${toast.description ? `<div data-sonner-toast-description>${toast.description}</div>` : ''}
                </div>
            </div>
        `;
        return li;
    }

    function removeToast(id) {
        const index = toasts.findIndex(t => t.id === id);
        if (index === -1) return;
        const toast = toasts[index];
        const element = toaster.querySelector(`[data-id="${id}"]`);

        if (element) {
            element.style.opacity = '0';
            element.style.transform = userConfig.position.includes('bottom')
                ? 'translateY(100%) scale(0.9)'
                : 'translateY(-100%) scale(0.9)';

            setTimeout(() => {
                if (element.parentNode) element.parentNode.removeChild(element);
            }, 400);
        }

        toasts.splice(index, 1);
        updatePositions();
    }

    function updatePositions() {
        const isBottom = userConfig.position.includes('bottom');
        const gap = 14;
        const reversedToasts = [...toasts].reverse();
        let cumulativeOffset = 0;
        reversedToasts.forEach((toast, index) => {
            const element = toaster.querySelector(`[data-id="${toast.id}"]`);
            if (!element) return;
            if (index >= userConfig.visibleToasts) {
                element.style.opacity = '0';
                element.style.pointerEvents = 'none';
                return;
            }
            element.setAttribute('data-index', index);
            element.setAttribute('data-mounted', 'true');
            element.style.opacity = '1';
            element.style.pointerEvents = 'auto';
            element.style.zIndex = userConfig.visibleToasts - index;
            const stackScale = 1 - (index * 0.05);
            const stackY = index * gap;
            const transformStackY = isBottom ? -(stackY) : stackY;
            element.style.transform = `translate3d(0, ${transformStackY}px, 0) scale(${stackScale})`;
            const elementHeight = element.offsetHeight;
            const expandY = isBottom ? -(cumulativeOffset) : cumulativeOffset;
            element.style.setProperty('--offset', `${expandY}px`);
            cumulativeOffset += elementHeight + gap;
        });
    }

    function addToast(options) {
        if (!toaster) initToaster();

        const id = Math.random().toString(36).substring(2, 9);
        const toast = {
            id,
            title: options.title || '',
            description: options.description || '',
            type: options.type || 'default',
            duration: options.duration || userConfig.duration
        };

        toasts.push(toast);
        const element = createToastElement(toast);
        const isBottom = userConfig.position.includes('bottom');
        element.style.transform = isBottom ? 'translateY(100%)' : 'translateY(-100%)';
        element.style.opacity = '0';
        toaster.appendChild(element);
        element.offsetHeight;
        requestAnimationFrame(() => {
            updatePositions();
        });

        if (toast.duration !== Infinity && toast.type !== 'loading') {
            setTimeout(() => {
                removeToast(id);
            }, toast.duration);
        }

        return id;
    }

    const toast = {
        message: (title, options = {}) => addToast({ ...options, title }),
        success: (title, options = {}) => addToast({ ...options, title, type: 'success' }),
        error: (title, options = {}) => addToast({ ...options, title, type: 'error' }),
        info: (title, options = {}) => addToast({ ...options, title, type: 'info' }),
        warning: (title, options = {}) => addToast({ ...options, title, type: 'warning' }),
        dismiss: (id) => removeToast(id)
    };

    global.toast = toast;
    document.addEventListener('DOMContentLoaded', initToaster);

})(window);
