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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('developer_name')->nullable()->after('id');
            $table->string('project_name')->nullable()->after('developer_name');
            $table->string('project_location')->nullable()->after('project_name');
            $table->string('rera_number')->nullable()->after('project_location');
            $table->string('project_status')->nullable()->after('property_type');
            $table->string('possession_date', 7)->nullable()->after('project_status');
        });

        if (Schema::hasColumn('properties', 'name')) {
            DB::table('properties')->update([
                'project_name' => DB::raw('name'),
                'project_location' => DB::raw('location'),
            ]);
        }

        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'status')) {
                $table->dropIndex(['status']);
            }

            if (Schema::hasColumn('properties', 'location')) {
                $table->dropIndex(['location']);
            }
        });

        Schema::table('properties', function (Blueprint $table) {
            $columnsToDrop = array_filter([
                Schema::hasColumn('properties', 'name') ? 'name' : null,
                Schema::hasColumn('properties', 'location') ? 'location' : null,
                Schema::hasColumn('properties', 'price') ? 'price' : null,
                Schema::hasColumn('properties', 'status') ? 'status' : null,
                Schema::hasColumn('properties', 'description') ? 'description' : null,
            ]);

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }

            $table->index('project_status');
            $table->index('project_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('location')->nullable();
            $table->unsignedBigInteger('price')->nullable();
            $table->string('status')->default('available');
            $table->text('description')->nullable();
        });

        DB::table('properties')->update([
            'name' => DB::raw('project_name'),
            'location' => DB::raw('project_location'),
        ]);

        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['project_status']);
            $table->dropIndex(['project_location']);

            $table->dropColumn([
                'developer_name',
                'project_name',
                'project_location',
                'rera_number',
                'project_status',
                'possession_date',
            ]);

            $table->index('status');
            $table->index('location');
        });
    }
};
