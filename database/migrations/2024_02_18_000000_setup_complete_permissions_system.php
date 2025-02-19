<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Eliminar tablas existentes en el orden correcto
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');

        Schema::enableForeignKeyConstraints();

        // Crear tablas nuevas
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });

        // Insertar permisos básicos
        $permissions = $this->getPermissions();
        DB::table('permissions')->insert($permissions);

        // Insertar roles
        $roles = $this->getRoles();
        DB::table('roles')->insert($roles);

        // Asignar permisos a roles
        $rolePermissions = $this->getRolePermissions();
        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = DB::table('roles')->where('name', $roleName)->first();

            if ($roleName === 'admin') {
                // Para el admin, asignar todos los permisos
                $permissions = DB::table('permissions')->get();
            } else {
                $permissions = DB::table('permissions')
                    ->whereIn('name', $permissionNames)
                    ->get();
            }

            foreach ($permissions as $permission) {
                DB::table('role_has_permissions')->insert([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id
                ]);
            }
        }

        // Asignar rol admin al primer usuario
        $user = DB::table('users')->first();
        if ($user) {
            $adminRole = DB::table('roles')->where('name', 'admin')->first();
            if ($adminRole) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $adminRole->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $user->id
                ]);
            }
        }
    }

    private function getPermissions()
    {
        $now = now();
        $permissions = [];

        // Permisos para modelos principales
        $models = ['users', 'equipment', 'surgeries', 'instituciones', 'medicos', 'lines', 'roles', 'permissions'];
        $actions = ['view', 'create', 'edit', 'delete', 'restore'];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $permissions[] = [
                    'name' => "{$action}_{$model}",
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        // Permisos adicionales
        $additionalPermissions = [
            'access_admin_panel',
            'manage_settings',
            'view_dashboard',
            'view_reports',
            'export_data',
            'import_data',
            'manage_maintenance',
            'view_audit_logs',
        ];

        foreach ($additionalPermissions as $permission) {
            $permissions[] = [
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        return $permissions;
    }

    private function getRoles()
    {
        $now = now();
        return [
            [
                'name' => 'admin',
                'guard_name' => 'web',
                'description' => 'Administrador del Sistema',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'gerente',
                'guard_name' => 'web',
                'description' => 'Gerente',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'supervisor',
                'guard_name' => 'web',
                'description' => 'Supervisor',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'vendedor',
                'guard_name' => 'web',
                'description' => 'Vendedor',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'tecnico',
                'guard_name' => 'web',
                'description' => 'Técnico',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];
    }

    private function getRolePermissions()
    {
        return [
            'admin' => ['*'], // Admin tiene todos los permisos
            'gerente' => [
                'access_admin_panel',
                'view_dashboard',
                'view_reports',
                'view_users',
                'view_equipment',
                'view_surgeries',
                'view_instituciones',
                'view_medicos',
                'view_lines',
                'create_surgeries',
                'edit_surgeries',
                'export_data',
                'view_audit_logs',
            ],
            'supervisor' => [
                'access_admin_panel',
                'view_dashboard',
                'view_equipment',
                'view_surgeries',
                'create_surgeries',
                'edit_surgeries',
                'view_instituciones',
                'view_medicos',
            ],
            'vendedor' => [
                'access_admin_panel',
                'view_dashboard',
                'view_surgeries',
                'create_surgeries',
                'view_instituciones',
                'view_medicos',
            ],
            'tecnico' => [
                'access_admin_panel',
                'view_dashboard',
                'view_equipment',
                'edit_equipment',
                'manage_maintenance',
            ],
        ];
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');

        Schema::enableForeignKeyConstraints();
    }
};
