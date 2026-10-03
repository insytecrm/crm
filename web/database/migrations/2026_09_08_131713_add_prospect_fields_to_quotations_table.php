<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('number');
            $table->string('owner_name')->nullable()->after('company_name');
            $table->string('email')->nullable()->after('owner_name');
            $table->string('phone', 30)->nullable()->after('email');
            $table->timestamp('onboarded_at')->nullable()->after('partner_subscription_id');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->change();
        });

        $tenants = DB::table('tenants')->get()->keyBy('id');

        DB::table('quotations')->orderBy('id')->each(function (object $row) use ($tenants): void {
            if ($row->tenant_id === null || $row->tenant_id === '') {
                return;
            }

            $tenant = $tenants->get($row->tenant_id);
            if ($tenant === null) {
                return;
            }

            $data = json_decode((string) ($tenant->data ?? '{}'), true);
            if (! is_array($data)) {
                $data = [];
            }

            DB::table('quotations')->where('id', $row->id)->update([
                'company_name' => $row->company_name ?: $tenant->name,
                'owner_name' => $row->owner_name ?: ($data['owner_name'] ?? null),
                'email' => $row->email ?: $tenant->email,
                'phone' => $row->phone ?: ($data['phone'] ?? null),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'owner_name', 'email', 'phone', 'onboarded_at']);
        });
    }
};
