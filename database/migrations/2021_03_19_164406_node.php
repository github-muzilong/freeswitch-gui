<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Node extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('节点名称');
            $table->tinyInteger('sort')->default(10)->comment('排序');
            $table->tinyInteger('type')->default(1)->comment('默认1，1表示公共，2客户跟进，3订单生产，4财务付款');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('node');
    }
}
