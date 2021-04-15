<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Asr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asr', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid')->nullable()->comment('通话记录UUID');
            $table->string('leg_uuid')->nullable()->comment('当前说话leg的UUID');
            $table->string('record_file')->nullable()->comment('录音语音');
            $table->string('text')->nullable()->comment('录音文本');
            $table->dateTime('start_at')->nullable()->comment('说话开始时间');
            $table->dateTime('end_at')->nullable()->comment('说话结束时间');
            $table->tinyInteger('billsec')->default(0)->comment('时长');
            $table->text('keywords')->nullable()->comment('匹配到的敏感词');
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
        Schema::dropIfExists('cdr');
    }
}
