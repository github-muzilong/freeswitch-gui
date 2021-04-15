<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //清空表
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \Illuminate\Support\Facades\DB::table('model_has_permissions')->truncate();
        \Illuminate\Support\Facades\DB::table('model_has_roles')->truncate();
        \Illuminate\Support\Facades\DB::table('role_has_permissions')->truncate();
        \Illuminate\Support\Facades\DB::table('user')->truncate();
        \Illuminate\Support\Facades\DB::table('role')->truncate();
        \Illuminate\Support\Facades\DB::table('permission')->truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $user = \App\Models\User::create([
            'name' => 'root',
            'password' => bcrypt('123456'),
            'phone' => '18908221080',
            'nickname' => 'root',
        ]);
        $role = \App\Models\Role::create([
            'name' => 'root',
            'display_name' => '超级管理员',
        ]);
        $user->assignRole($role);
        $permissions = [
            [
                'name' => 'system',
                'display_name' => '系统管理',
                'child' => [
                    [
                        'name' => 'system.permission',
                        'display_name' => '权限管理',
                        'child' => [
                            ['name' => 'system.permission.create', 'display_name' => '添加'],
                            ['name' => 'system.permission.edit', 'display_name' => '编辑'],
                            ['name' => 'system.permission.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'system.role',
                        'display_name' => '角色管理',
                        'child' => [
                            ['name' => 'system.role.create', 'display_name' => '添加'],
                            ['name' => 'system.role.edit', 'display_name' => '编辑'],
                            ['name' => 'system.role.destroy', 'display_name' => '删除'],
                            ['name' => 'system.role.permission', 'display_name' => '分配权限'],
                        ]
                    ],
                    [
                        'name' => 'system.user',
                        'display_name' => '用户管理',
                        'child' => [
                            ['name' => 'system.user.create', 'display_name' => '添加'],
                            ['name' => 'system.user.edit', 'display_name' => '编辑'],
                            ['name' => 'system.user.resetPassword', 'display_name' => '重置密码'],
                            ['name' => 'system.user.status', 'display_name' => '启用/禁用'],
                            ['name' => 'system.user.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'system.menu',
                        'display_name' => '菜单管理',
                        'child' => [
                            ['name' => 'system.menu.create', 'display_name' => '添加'],
                            ['name' => 'system.menu.edit', 'display_name' => '编辑'],
                            ['name' => 'system.menu.destroy', 'display_name' => '删除'],
                        ]
                    ],
                ],
            ],
        ];
        foreach ($permissions as $pem1) {
            //生成一级权限
            $p1 = \App\Models\Permission::create([
                'name' => $pem1['name'],
                'display_name' => $pem1['display_name'],
                'parent_id' => 0,
            ]);
            //为角色添加权限
            $role->givePermissionTo($p1);
            //为用户添加权限
            $user->givePermissionTo($p1);
            if (isset($pem1['child'])) {
                foreach ($pem1['child'] as $pem2) {
                    //生成二级权限
                    $p2 = \App\Models\Permission::create([
                        'name' => $pem2['name'],
                        'display_name' => $pem2['display_name'],
                        'parent_id' => $p1->id,
                    ]);
                    //为角色添加权限
                    $role->givePermissionTo($p2);
                    //为用户添加权限
                    $user->givePermissionTo($p2);
                    if (isset($pem2['child'])) {
                        foreach ($pem2['child'] as $pem3) {
                            //生成三级权限
                            $p3 = \App\Models\Permission::create([
                                'name' => $pem3['name'],
                                'display_name' => $pem3['display_name'],
                                'parent_id' => $p2->id,
                            ]);
                            //为角色添加权限
                            $role->givePermissionTo($p3);
                            //为用户添加权限
                            $user->givePermissionTo($p3);
                        }
                    }
                }
            }
        }


    }
}
