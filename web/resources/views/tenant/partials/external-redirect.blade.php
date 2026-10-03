@if (session('external_redirect'))
    <div
        x-data
        x-init="
            const url = @js(session('external_redirect'));
            if (url.startsWith('tel:')) {
                window.location.href = url;
            } else {
                window.open(url, '_blank');
            }
        "
        class="hidden"
        aria-hidden="true"
    ></div>
@endif
