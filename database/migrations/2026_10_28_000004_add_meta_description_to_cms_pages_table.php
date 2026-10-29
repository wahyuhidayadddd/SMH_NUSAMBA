<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cms_pages', 'meta_description')) {
            Schema::table('cms_pages', function (Blueprint $table) {
                $table->string('meta_description', 500)->nullable()->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cms_pages', 'meta_description')) {
            Schema::table('cms_pages', function (Blueprint $table) {
                $table->dropColumn('meta_description');
            });
        }
    }
};
