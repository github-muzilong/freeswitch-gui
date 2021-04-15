<?php

use Illuminate\Database\Seeder;

class BackendPermission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $guard = config('freeswitch.backend_guard');
        //清空表
        \App\Models\Admin::truncate();
        \App\Models\Permission::where('guard_name',$guard)->delete();
        \App\Models\Role::where('guard_name',$guard)->delete();
        //后台用户
        $user = \App\Models\Admin::create([
            'username' => 'root',
            'phone' => '18999999999',
            'nickname' => '超级管理员',
            'password' => bcrypt('123456'),
            'api_token' => hash('sha256', Str::random(60)),
        ]);
        //后台角色
        $role = \App\Models\Role::create([
            'name' => 'root',
            'display_name' => '超级管理员',
            'guard_name' => $guard,
        ]);
        //后台权限
        $permissions = [
            [
                'name' => 'backend.system',
                'display_name' => '系统管理',
                'child' => [
                    [
                        'name' => 'backend.system.admin',
                        'display_name' => '用户管理',
                        'child' => [
                            ['name' => 'backend.system.admin.create', 'display_name' => '添加用户'],
                            ['name' => 'backend.system.admin.edit', 'display_name' => '编辑用户'],
                            ['name' => 'backend.system.admin.resetPassword', 'display_name' => '重置密码'],
                            ['name' => 'backend.system.admin.destroy', 'display_name' => '删除用户'],
                            ['name' => 'backend.system.admin.role', 'display_name' => '分配角色'],
                            ['name' => 'backend.system.admin.permission', 'display_name' => '分配权限'],
                        ]
                    ],
                    [
                        'name' => 'backend.system.role',
                        'display_name' => '角色管理',
                        'child' => [
                            ['name' => 'backend.system.role.create', 'display_name' => '添加角色'],
                            ['name' => 'backend.system.role.edit', 'display_name' => '编辑角色'],
                            ['name' => 'backend.system.role.destroy', 'display_name' => '删除角色'],
                            ['name' => 'backend.system.role.permission', 'display_name' => '分配权限'],
                        ]
                    ],
                    [
                        'name' => 'backend.system.permission',
                        'display_name' => '权限管理',
                        'child' => [
                            ['name' => 'backend.system.permission.create', 'display_name' => '添加权限'],
                            ['name' => 'backend.system.permission.edit', 'display_name' => '编辑权限'],
                            ['name' => 'backend.system.permission.destroy', 'display_name' => '删除权限'],
                        ]
                    ],
                    [
                        'name' => 'backend.system.menu',
                        'display_name' => '菜单管理',
                        'child' => [
                            ['name' => 'backend.system.menu.create', 'display_name' => '添加'],
                            ['name' => 'backend.system.menu.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.system.menu.destroy', 'display_name' => '删除'],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'backend.call',
                'display_name' => '呼叫中心',
                'child' => [
                    [
                        'name' => 'backend.call.freeswitch',
                        'display_name' => 'FS管理',
                        'child' => [
                            ['name' => 'backend.call.freeswitch.create', 'display_name' => '添加'],
                            ['name' => 'backend.call.freeswitch.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.call.freeswitch.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'backend.call.extension',
                        'display_name' => '拨号计划',
                        'child' => [
                            ['name' => 'backend.call.extension.create', 'display_name' => '添加'],
                            ['name' => 'backend.call.extension.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.call.extension.destroy', 'display_name' => '删除'],
                            ['name' => 'backend.call.extension.show', 'display_name' => '详情'],
                            ['name' => 'backend.call.extension.updateXml', 'display_name' => '更新配置'],
                        ]
                    ],
                    [
                        'name' => 'backend.call.gateway',
                        'display_name' => '网关管理',
                        'child' => [
                            ['name' => 'backend.call.gateway.create', 'display_name' => '添加'],
                            ['name' => 'backend.call.gateway.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.call.gateway.destroy', 'display_name' => '删除'],
                            ['name' => 'backend.call.gateway.updateXml', 'display_name' => '更新配置'],
                        ]
                    ],
                    [
                        'name' => 'backend.call.sip',
                        'display_name' => '分机管理',
                        'child' => [
                            ['name' => 'backend.call.sip.create', 'display_name' => '添加'],
                            ['name' => 'backend.call.sip.createList', 'display_name' => '批量添加'],
                            ['name' => 'backend.call.sip.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.call.sip.destroy', 'display_name' => '删除'],
                            ['name' => 'backend.call.sip.updateXml', 'display_name' => '更新配置'],
                        ]
                    ],
                    [
                        'name' => 'backend.call.rate',
                        'display_name' => '费率管理',
                        'child' => [
                            ['name' => 'backend.call.rate.create', 'display_name' => '添加'],
                            ['name' => 'backend.call.rate.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.call.rate.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'backend.call.cdr',
                        'display_name' => '通话记录',
                        'child' => [
                            ['name' => 'backend.call.cdr.destroy', 'display_name' => '删除'],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'backend.platform',
                'display_name' => '平台管理',
                'child' => [
                    [
                        'name' => 'backend.platform.merchant',
                        'display_name' => '商户管理',
                        'child' => [
                            ['name' => 'backend.platform.merchant.create', 'display_name' => '添加'],
                            ['name' => 'backend.platform.merchant.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.platform.merchant.destroy', 'display_name' => '删除'],
                            ['name' => 'backend.platform.merchant.bill', 'display_name' => '帐单'],
                        ]
                    ],
                    [
                        'name' => 'backend.platform.staff',
                        'display_name' => '员工管理',
                        'child' => [
                            ['name' => 'backend.platform.staff.create', 'display_name' => '添加'],
                            ['name' => 'backend.platform.staff.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.platform.staff.destroy', 'display_name' => '删除'],
                            ['name' => 'backend.platform.staff.role', 'display_name' => '角色'],
                            ['name' => 'backend.platform.staff.resetPassword', 'display_name' => '重置密码'],
                        ]
                    ],
                    [
                        'name' => 'backend.platform.bill',
                        'display_name' => '帐单管理',
                        'child' => [
                            ['name' => 'backend.platform.bill.create', 'display_name' => '添加'],
                        ]
                    ],
                    [
                        'name' => 'backend.platform.staff_permission',
                        'display_name' => '权限管理',
                        'child' => [
                            ['name' => 'backend.platform.staff_permission.create', 'display_name' => '添加权限'],
                            ['name' => 'backend.platform.staff_permission.edit', 'display_name' => '编辑权限'],
                            ['name' => 'backend.platform.staff_permission.destroy', 'display_name' => '删除权限'],
                        ]
                    ],
                    [
                        'name' => 'backend.platform.staff_role',
                        'display_name' => '角色管理',
                        'child' => [
                            ['name' => 'backend.platform.staff_role.create', 'display_name' => '添加角色'],
                            ['name' => 'backend.platform.staff_role.edit', 'display_name' => '编辑角色'],
                            ['name' => 'backend.platform.staff_role.destroy', 'display_name' => '删除角色'],
                            ['name' => 'backend.platform.staff_role.permission', 'display_name' => '分配权限'],
                        ]
                    ],
                    [
                        'name' => 'backend.platform.staff_menu',
                        'display_name' => '菜单管理',
                        'child' => [
                            ['name' => 'backend.platform.staff_menu.create', 'display_name' => '添加'],
                            ['name' => 'backend.platform.staff_menu.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.platform.staff_menu.destroy', 'display_name' => '删除'],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'backend.crm',
                'display_name' => 'CRM管理',
                'child' => [
                    [
                        'name' => 'backend.crm.department',
                        'display_name' => '部门管理',
                        'child' => [
                            ['name' => 'backend.crm.department.create', 'display_name' => '添加'],
                            ['name' => 'backend.crm.department.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.crm.department.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'backend.crm.node',
                        'display_name' => '节点管理',
                        'child' => [
                            ['name' => 'backend.crm.node.create', 'display_name' => '添加'],
                            ['name' => 'backend.crm.node.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.crm.node.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'backend.crm.project-design',
                        'display_name' => '客户配置',
                        'child' => [
                            ['name' => 'backend.crm.project-design.create', 'display_name' => '添加'],
                            ['name' => 'backend.crm.project-design.edit', 'display_name' => '编辑'],
                            ['name' => 'backend.crm.project-design.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'backend.crm.assignment',
                        'display_name' => '待分配',
                        'child' => [
                            ['name' => 'backend.crm.assignment.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'backend.crm.project',
                        'display_name' => '客户管理',
                        'child' => [
                            ['name' => 'backend.crm.project.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'backend.crm.waste',
                        'display_name' => '公海库',
                        'child' => [
                            ['name' => 'backend.crm.waste.destroy', 'display_name' => '删除'],
                            ['name' => 'backend.crm.waste.show', 'display_name' => '跟进记录'],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($permissions as $pem1) {
            //生成一级权限
            $p1 = \App\Models\Permission::create([
                'guard_name' => $guard,
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
                        'guard_name' => $guard,
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
                                'guard_name' => $guard,
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
        //为用户添加角色
        $user->assignRole($role);

    }
}
