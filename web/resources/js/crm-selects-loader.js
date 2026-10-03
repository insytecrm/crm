let loading = null;

function hasCrmSelectRoots(scope) {
    if (!(scope instanceof Element)) {
        return false;
    }

    if (scope.matches('[data-crm-select-root]')) {
        return true;
    }

    return scope.querySelector('[data-crm-select-root]') !== null;
}

function loadCrmSelects() {
    if (! loading) {
        loading = import('./crm-selects').then((module) => {
            module.initCrmSelects();
        });
    }

    return loading;
}

function maybeLoad(scope = document) {
    if (! hasCrmSelectRoots(scope)) {
        return false;
    }

    void loadCrmSelects();

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
