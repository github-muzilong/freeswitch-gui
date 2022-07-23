<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderPay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_pay', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->decimal('money',10,2)->comment('金额');
            $table->tinyInteger('pay_type')->comment('1现金|2对公账户|3支付宝|4微信|5其它');
            $table->text('content')->comment('备注');
            $table->tinyInteger('status')->default(0)->comment('审核状态,0待审核，1审核通过，2审核不通过');
            $table->unsignedBigInteger('check_user_id')->default(0)->comment('审核人ID');
            $table->string('check_user_nickname')->nullable()->comment('审核人昵称');
            $table->text('check_result')->nullable()->comment('审核备注');
            $table->text('check_time')->nullable()->comment('审核时间');
            $table->unsignedBigInteger('created_user_id')->default(0)->comment('操作人ID');
            $table->string('created_user_nickname')->nullable()->comment('操作人昵称');
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
        Schema::dropIfExists('order_pay');
    }
}
