@once
    <script>
        window.manageableDataTableConfigs = window.manageableDataTableConfigs ?? {};
        window.manageableDataTableConfigs['{{ $dataTableKey }}'] = {
            updateUrl: @js(route('tenant.table-preferences.update', $dataTableKey)),
            initial: @js($dataTablePreferences),
            defaults: { columns: @js($dataTableDefaultColumns) },
            requiredColumns: @js($dataTableRequiredColumns),
            listing: @js($dataTableListing),
        };
    </script>
@endonce
