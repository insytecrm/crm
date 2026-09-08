let loading = null;

function hasReportDonutRoots(scope) {
    if (!(scope instanceof Element) && scope !== document) {
        return false;
    }

    if (scope instanceof Element && scope.matches('[data-report-donut]')) {
        return true;
    }

    return scope.querySelector('[data-report-donut]') !== null;
}

function loadReportDonutCharts() {
    if (! loading) {
        loading = import('./report-donut-charts').then((module) => {
            module.initReportDonutCharts();
        });
    }

    return loading;
}

function maybeLoad(scope = document) {
    if (! hasReportDonutRoots(scope)) {
        return false;
    }

    void loadReportDonutCharts();

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
