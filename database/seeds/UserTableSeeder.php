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
            [
                'name' => 'call',
                'display_name' => '呼叫中心',
                'child' => [
                    [
                        'name' => 'call.gateway',
                        'display_name' => '网关管理',
                        'child' => [
                            ['name' => 'call.gateway.create', 'display_name' => '添加'],
                            ['name' => 'call.gateway.edit', 'display_name' => '编辑'],
                            ['name' => 'call.gateway.destroy', 'display_name' => '删除'],
                            ['name' => 'call.gateway.updateXml', 'display_name' => '更新配置'],
                        ]
                    ],
                    [
                        'name' => 'call.sip',
                        'display_name' => '分机管理',
                        'child' => [
                            ['name' => 'call.sip.create', 'display_name' => '添加'],
                            ['name' => 'call.sip.create_list', 'display_name' => '批量添加'],
                            ['name' => 'call.sip.edit', 'display_name' => '编辑'],
                            ['name' => 'call.sip.destroy', 'display_name' => '删除'],
                            ['name' => 'call.sip.updateXml', 'display_name' => '更新配置'],
                        ]
                    ],
                    [
                        'name' => 'call.extension',
                        'display_name' => '拨号计划',
                        'child' => [
                            ['name' => 'call.extension.show', 'display_name' => '详情'],
                            ['name' => 'call.extension.create', 'display_name' => '添加'],
                            ['name' => 'call.extension.edit', 'display_name' => '编辑'],
                            ['name' => 'call.extension.destroy', 'display_name' => '删除'],
                            ['name' => 'call.extension.updateXml', 'display_name' => '更新配置'],
                        ]
                    ],
                    [
                        'name' => 'call.cdr',
                        'display_name' => '通话记录',
                        'child' => [

                        ]
                    ],
                ],
            ],
            [
                'name' => 'callcenter',
                'display_name' => '群呼管理',
                'child' => [
                    [
                        'name' => 'callcenter.queue',
                        'display_name' => '队列管理',
                        'child' => [
                            ['name' => 'callcenter.queue.create', 'display_name' => '添加'],
                            ['name' => 'callcenter.queue.edit', 'display_name' => '编辑'],
                            ['name' => 'callcenter.queue.destroy', 'display_name' => '删除'],
                            ['name' => 'callcenter.queue.updateXml', 'display_name' => '更新配置'],
                        ]
                    ],
                    [
                        'name' => 'callcenter.task',
                        'display_name' => '任务管理',
                        'child' => [
                            ['name' => 'callcenter.task.create', 'display_name' => '添加'],
                            ['name' => 'callcenter.task.show', 'display_name' => '详情'],
                            ['name' => 'callcenter.task.edit', 'display_name' => '编辑'],
                            ['name' => 'callcenter.task.destroy', 'display_name' => '删除'],
                            ['name' => 'callcenter.task.importCall', 'display_name' => '导入号码'],
                            ['name' => 'callcenter.task.setStatus', 'display_name' => '设置状态'],
                        ]
                    ],
                ],
            ],
            [
                'name' => 'crm',
                'display_name' => 'CRM管理',
                'child' => [
                    [
                        'name' => 'crm.department',
                        'display_name' => '部门管理',
                        'child' => [
                            ['name' => 'crm.department.create', 'display_name' => '添加'],
                            ['name' => 'crm.department.edit', 'display_name' => '编辑'],
                            ['name' => 'crm.department.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'crm.node',
                        'display_name' => '节点管理',
                        'child' => [
                            ['name' => 'crm.node.create', 'display_name' => '添加'],
                            ['name' => 'crm.node.edit', 'display_name' => '编辑'],
                            ['name' => 'crm.node.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'crm.customer_field',
                        'display_name' => '客户配置',
                        'child' => [
                            ['name' => 'crm.customer_field.create', 'display_name' => '添加'],
                            ['name' => 'crm.customer_field.edit', 'display_name' => '编辑'],
                            ['name' => 'crm.customer_field.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'crm.assignment',
                        'display_name' => '待分配库',
                        'child' => [
                            ['name' => 'crm.assignment.create', 'display_name' => '添加'],
                            ['name' => 'crm.assignment.edit', 'display_name' => '编辑'],
                            ['name' => 'crm.assignment.import', 'display_name' => '导入'],
                            ['name' => 'crm.assignment.to', 'display_name' => '分配'],
                            ['name' => 'crm.assignment.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'crm.business',
                        'display_name' => '经理库',
                        'child' => [
                            ['name' => 'crm.business.to', 'display_name' => '分配'],
                        ]
                    ],
                    [
                        'name' => 'crm.grab',
                        'display_name' => '部门抢单',
                        'child' => [
                            ['name' => 'crm.grab.store', 'display_name' => '抢单'],
                            ['name' => 'crm.grab.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'crm.waste',
                        'display_name' => '公海库',
                        'child' => [
                            ['name' => 'crm.waste.retrieve', 'display_name' => '拾回'],
                            ['name' => 'crm.waste.show', 'display_name' => '详情'],
                            ['name' => 'crm.waste.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'crm.customer',
                        'display_name' => '客户管理',
                        'route' => 'crm.customer',
                        'child' => [
                            ['name' => 'crm.customer.create', 'display_name' => '添加'],
                            ['name' => 'crm.customer.edit', 'display_name' => '编辑'],
                            ['name' => 'crm.customer.destroy', 'display_name' => '删除'],
                            ['name' => 'crm.customer.show', 'display_name' => '详情'],
                            ['name' => 'crm.customer.remark', 'display_name' => '备注跟进'],
                            ['name' => 'crm.customer.list_all', 'display_name' => '查看所有客户'],
                            ['name' => 'crm.customer.list_department', 'display_name' => '查看本部门客户'],
                            ['name' => 'crm.customer.transfer', 'display_name' => '移交'],
                            ['name' => 'crm.customer.remove', 'display_name' => '剔除'],
                        ]
                    ],
                ],
            ],
            [
                'name' => 'chat',
                'display_name' => '实时聊天',
                'child' => [
                    [
                        'name' => 'chat.message',
                        'display_name' => '消息中心',
                        'child' => [
                            ['name' => 'chat.message.create', 'display_name' => '发送消息'],
                            ['name' => 'chat.message.show', 'display_name' => '详情'],
                            ['name' => 'chat.message.read', 'display_name' => '已读'],
                            ['name' => 'chat.message.destroy', 'display_name' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'chat.audio',
                        'display_name' => '语音通话',
                        'child' => [

                        ]
                    ],

                ],
            ],
            [
                'name' => 'order',
                'display_name' => '订单模块',
                'child' => [
                    [
                        'name' => 'order.order',
                        'display_name' => '订单管理',
                        'child' => [
                            ['name' => 'order.order.create', 'display_name' => '下单'],
                            ['name' => 'order.order.pay', 'display_name' => '付款'],
                            ['name' => 'order.order.remark', 'display_name' => '跟进'],
                            ['name' => 'order.order.destroy', 'display_name' => '删除'],
                            ['name' => 'order.order.list_all', 'display_name' => '查看所有订单'],
                            ['name' => 'order.order.list_department', 'display_name' => '查看本部门订单'],
                        ]
                    ],
                ],
            ],
            [
                'name' => 'account',
                'display_name' => '财务模块',
                'child' => [
                    [
                        'name' => 'account.pay',
                        'display_name' => '订单付款',
                        'child' => [
                            ['name' => 'account.pay.check', 'display_name' => '审核'],
                            ['name' => 'account.pay.show', 'display_name' => '详情'],
                        ]
                    ],
                ],
            ],
            [
                'name' => 'data_view',
                'display_name' => '数据可视化',
                'child' => [
                    [
                        'name' => 'data_view.cdr',
                        'display_name' => '通话统计',
                        'child' => [

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
