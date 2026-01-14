<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 建立權限相關資料表。此遷移是基於 `spatie/laravel-permission` 套件自動生成，
     * 用於實現 Laravel 應用程式中的角色與權限管理。
     * 包含權限表、角色表以及多對多關聯表。
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(empty($tableNames), Exception::class, 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        throw_if($teams && empty($columnNames['team_foreign_key'] ?? null), Exception::class, 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        // permissions 資料表：儲存所有系統中定義的權限
        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            $table->bigIncrements('id')->comment('權限唯一識別碼');
            $table->string('name')->comment('權限名稱 (例如: "edit articles")');
            $table->string('guard_name')->comment('Guard 名稱，定義該權限屬於哪個認證 Guard (例如: "web", "api")');
            $table->timestamps();

            $table->unique(['name', 'guard_name'])->comment('權限名稱與 Guard 名稱的組合必須唯一');
        });
        DB::statement("ALTER TABLE `{$tableNames['permissions']}` comment '系統權限定義表'");

        // roles 資料表：儲存所有系統中定義的角色
        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id')->comment('角色唯一識別碼');
            if ($teams || config('permission.testing')) {
                // 如果開啟了多團隊功能，則角色會綁定到特定的團隊
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable()->comment('所屬團隊 ID');
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name')->comment('角色名稱 (例如: "admin", "writer")');
            $table->string('guard_name')->comment('Guard 名稱，定義該角色屬於哪個認證 Guard');
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name'])->comment('團隊、角色名稱與 Guard 名稱的組合必須唯一');
            } else {
                $table->unique(['name', 'guard_name'])->comment('角色名稱與 Guard 名稱的組合必須唯一');
            }
        });
        DB::statement("ALTER TABLE `{$tableNames['roles']}` comment '系統角色定義表'");

        // model_has_permissions 資料表：定義模型 (通常是 User) 擁有的直接權限
        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission)->comment('權限 ID');

            // 多態關聯，指向擁有權限的模型 (例如: App\Models\User)
            $table->string('model_type')->comment('模型類別名稱');
            $table->unsignedBigInteger($columnNames['model_morph_key'])->comment('模型 ID');
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->comment('所屬團隊 ID');
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }
        });
        DB::statement("ALTER TABLE `{$tableNames['model_has_permissions']}` comment '模型直接權限關聯表'");

        // model_has_roles 資料表：定義模型 (通常是 User) 擁有的角色
        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole)->comment('角色 ID');

            // 多態關聯，指向擁有角色的模型 (例如: App\Models\User)
            $table->string('model_type')->comment('模型類別名稱');
            $table->unsignedBigInteger($columnNames['model_morph_key'])->comment('模型 ID');
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->comment('所屬團隊 ID');
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });
        DB::statement("ALTER TABLE `{$tableNames['model_has_roles']}` comment '模型角色關聯表'");

        // role_has_permissions 資料表：定義角色擁有的權限 (角色-權限多對多關聯)
        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission)->comment('權限 ID');
            $table->unsignedBigInteger($pivotRole)->comment('角色 ID');

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });
        DB::statement("ALTER TABLE `{$tableNames['role_has_permissions']}` comment '角色權限關聯表'");

        // 清除權限快取，確保新的權限設定生效
        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(empty($tableNames), Exception::class, 'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
