<?php

use Illuminate\Database\Seeder;

class MenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('menu')->truncate();
        $datas = [
            [
                'name' => '系统管理',
                'route' => null,
                'url' => null,
                'icon' => 'layui-icon-auz',
                'type' => 2,
                'sort' => 1,
                'permission_name' => 'system',
                'child' => [
                    [
                        'name' => '用户管理',
                        'route' => 'system.user',
                        'url' => null,
                        'icon' => 'layui-icon-username',
                        'type' => 1,
                        'permission_name' => 'system.user',
                    ],
                    [
                        'name' => '角色管理',
                        'route' => 'system.role',
                        'url' => null,
                        'icon' => 'layui-icon-group',
                        'type' => 1,
                        'permission_name' => 'system.role',
                    ],
                    [
                        'name' => '权限管理',
                        'route' => 'system.permission',
                        'url' => null,
                        'icon' => 'layui-icon-key',
                        'type' => 1,
                        'permission_name' => 'system.permission',
                    ],
                    [
                        'name' => '菜单管理',
                        'route' => 'system.menu',
                        'url' => null,
                        'icon' => 'layui-icon-menu-fill',
                        'type' => 1,
                        'permission_name' => 'system.menu',
                    ],
                ]
            ],
            [
                'name' => '呼叫中心',
                'route' => null,
                'url' => null,
                'icon' => 'layui-icon-windows',
                'type' => 2,
                'sort' => 2,
                'permission_name' => 'call',
                'child' => [
                    [
                        'name' => '分机管理',
                        'route' => 'call.sip',
                        'url' => null,
                        'icon' => 'layui-icon-cellphone',
                        'type' => 1,
                        'permission_name' => 'call.sip',
                    ],
                    [
                        'name' => '网关管理',
                        'route' => 'call.gateway',
                        'url' => null,
                        'icon' => 'layui-icon-service',
                        'type' => 1,
                        'permission_name' => 'call.gateway',
                    ],
                    [
                        'name' => '拨号计划',
                        'route' => 'call.extension',
                        'url' => null,
                        'icon' => 'layui-icon-chart',
                        'type' => 1,
                        'permission_name' => 'call.extension',
                    ],
                    [
                        'name' => '通话记录',
                        'route' => 'call.cdr',
                        'url' => null,
                        'icon' => 'layui-icon-headset',
                        'type' => 1,
                        'permission_name' => 'call.cdr',
                    ],
                ]
            ],
            [
                'name' => '群呼管理',
                'route' => null,
                'url' => null,
                'icon' => 'layui-icon-group',
                'type' => 2,
                'sort' => 2,
                'permission_name' => 'callcenter',
                'child' => [
                    [
                        'name' => '队列管理',
                        'route' => 'callcenter.queue',
                        'url' => null,
                        'icon' => 'layui-icon-user',
                        'type' => 1,
                        'permission_name' => 'callcenter.queue',
                    ],
                    [
                        'name' => '任务管理',
                        'route' => 'callcenter.task',
                        'url' => null,
                        'icon' => 'layui-icon-template-1',
                        'type' => 1,
                        'permission_name' => 'callcenter.task',
                    ],
                ]
            ],
            [
                'name' => '实时聊天',
                'route' => null,
                'url' => null,
                'icon' => 'layui-icon-cellphone-fine',
                'type' => 2,
                'sort' => 2,
                'permission_name' => 'chat',
                'child' => [
                    [
                        'name' => '消息中心',
                        'route' => 'chat.message',
                        'url' => null,
                        'icon' => 'layui-icon-note',
                        'type' => 1,
                        'permission_name' => 'chat.message',
                    ],
                    [
                        'name' => '语音通话',
                        'route' => 'chat.audio',
                        'url' => null,
                        'icon' => 'layui-icon-service',
                        'type' => 1,
                        'permission_name' => 'chat.audio',
                    ],
                ]
            ],
            [
                'name' => 'CRM管理',
                'route' => null,
                'url' => null,
                'icon' => 'layui-icon-heart-fill',
                'type' => 2,
                'sort' => 2,
                'permission_name' => 'crm',
                'child' => [
                    [
                        'name' => '部门管理',
                        'route' => 'crm.department',
                        'url' => null,
                        'icon' => 'layui-icon-android',
                        'type' => 1,
                        'permission_name' => 'crm.department',
                    ],
                    [
                        'name' => '节点管理',
                        'route' => 'crm.node',
                        'url' => null,
                        'icon' => 'layui-icon-cellphone-fine',
                        'type' => 1,
                        'permission_name' => 'crm.node',
                    ],
                    [
                        'name' => '客户配置',
                        'route' => 'crm.customer_field',
                        'url' => null,
                        'icon' => 'layui-icon-set-fill',
                        'type' => 1,
                        'permission_name' => 'crm.customer_field',
                    ],
                    [
                        'name' => '待分配库',
                        'route' => 'crm.assignment',
                        'url' => null,
                        'icon' => 'layui-icon-transfer',
                        'type' => 1,
                        'permission_name' => 'crm.assignment',
                    ],
                    [
                        'name' => '经理库',
                        'route' => 'crm.business',
                        'url' => null,
                        'icon' => 'layui-icon-dialogue',
                        'type' => 1,
                        'permission_name' => 'crm.business',
                    ],
                    [
                        'name' => '部门抢单',
                        'route' => 'crm.grab',
                        'url' => null,
                        'icon' => 'layui-icon-carousel',
                        'type' => 1,
                        'permission_name' => 'crm.grab',
                    ],
                    [
                        'name' => '公海库',
                        'route' => 'crm.waste',
                        'url' => null,
                        'icon' => 'layui-icon-404',
                        'type' => 1,
                        'permission_name' => 'crm.waste',
                    ],
                    [
                        'name' => '客户管理',
                        'route' => 'crm.customer',
                        'url' => null,
                        'icon' => 'layui-icon-reply-fill',
                        'type' => 1,
                        'permission_name' => 'crm.customer',
                    ],
                ]
            ],
            [
                'name' => '订单模块',
                'route' => null,
                'url' => null,
                'icon' => 'layui-icon-align-left',
                'type' => 2,
                'sort' => 2,
                'permission_name' => 'order',
                'child' => [
                    [
                        'name' => '订单管理',
                        'route' => 'order.order',
                        'url' => null,
                        'icon' => 'layui-icon-form',
                        'type' => 1,
                        'permission_name' => 'order.order',
                    ],
                ]
            ],
            [
                'name' => '账务模块',
                'route' => null,
                'url' => null,
                'icon' => 'layui-icon-diamond',
                'type' => 2,
                'sort' => 2,
                'permission_name' => 'account',
                'child' => [
                    [
                        'name' => '订单付款',
                        'route' => 'account.pay',
                        'url' => null,
                        'icon' => 'layui-icon-dollar',
                        'type' => 1,
                        'permission_name' => 'account.pay',
                    ],
                ]
            ],
            [
                'name' => '数据可视化',
                'route' => null,
                'url' => null,
                'icon' => 'layui-icon-chart-screen',
                'type' => 2,
                'sort' => 2,
                'permission_name' => 0,
                'child' => [
                    [
                        'name' => '通话统计',
                        'route' => 'data_view.cdr',
                        'url' => null,
                        'icon' => 'layui-icon-chart',
                        'type' => 1,
                        'permission_name' => 'data_view.cdr',
                    ],
                ]
            ],
            [
                'name' => '三方接口',
                'route' => null,
                'url' => null,
                'icon' => 'layui-icon-file-b',
                'type' => 2,
                'sort' => 2,
                'permission_name' => 0,
                'child' => [
                    [
                        'name' => '接口文档',
                        'route' => null,
                        'url' => '/apidoc',
                        'icon' => 'layui-icon-form',
                        'type' => 1,
                        'permission_name' => 0,
                    ],
                ]
            ],

        ];
        $permissions = \App\Models\Permission::pluck('id','name')->toArray();
        foreach ($datas as $k1 => $d1){
            $m1 = \App\Models\Menu::create([
                'name' => $d1['name'],
                'route' => $d1['route'],
                'url' => $d1['url'],
                'icon' => $d1['icon'],
                'type' => $d1['type'],
                'sort' => $k1+1,
                'permission_id' => \Illuminate\Support\Arr::get($permissions,$d1['permission_name'],null),
            ]);
            if (isset($d1['child'])&&!empty($d1['child'])){
                foreach ($d1['child'] as $k2 => $d2){
                    $m2 = \App\Models\Menu::create([
                        'name' => $d2['name'],
                        'route' => $d2['route'],
                        'url' => $d2['url'],
                        'icon' => $d2['icon'],
                        'type' => $d2['type'],
                        'sort' => $k2+1,
                        'parent_id' => $m1->id,
                        'permission_id' => \Illuminate\Support\Arr::get($permissions,$d2['permission_name'],null),
                    ]);
                }
            }
        }
    }
}
