let loading = null;

function hasDateTimePickerRoots(scope) {
    if (!(scope instanceof Element)) {
        return false;
    }

    if (scope.matches('[data-crm-datetime-picker-root]')) {
        return true;
    }

    return scope.querySelector('[data-crm-datetime-picker-root]') !== null;
}

function loadDateTimePickers() {
    if (! loading) {
        loading = import('./datetime-pickers').then((module) => {
            module.initDateTimePickers();
        });
    }

    return loading;
}

function maybeLoad(scope = document) {
    if (! hasDateTimePickerRoots(scope)) {
        return false;
    }

    void loadDateTimePickers();

    return true;
}

if (! maybeLoad()) {
    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node instanceof Element && maybeLoad(node)) {
                    observer.disconnect();

                    return;
                }
            }
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}
