<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GatewayOutbound extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gateway_outbound', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('gateway_id')->nullable()->comment('网关ID');
            $table->string('number')->nullable()->comment('出局号码');
            $table->integer('status')->default(1)->nullable()->comment('状态，1启用，2禁用，默认1');
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
        Schema::dropIfExists('gateway_outbound');
    }
}
