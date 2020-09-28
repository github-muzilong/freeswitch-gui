<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Project extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company_name')->nullable()->comment('公司名称');
            $table->string('name')->nullable()->comment('姓名');
            $table->string('phone')->nullable()->comment('电话');
            $table->unsignedBigInteger('node_id')->default(0)->comment('当前节点ID');
            $table->timestamp('follow_at')->nullable()->comment('最近跟进时间');
            $table->unsignedBigInteger('follow_user_id')->nullable()->comment('最近跟进人ID');
            $table->timestamp('next_follow_at')->nullable()->comment('下次跟进时间');
            $table->unsignedBigInteger('created_user_id')->nullable()->comment('创建人ID');
            $table->unsignedBigInteger('updated_user_id')->nullable()->comment('更新人ID');
            $table->unsignedBigInteger('deleted_user_id')->nullable()->comment('删除人ID');
            $table->bigInteger('owner_user_id')->nullable()->comment('拥有人ID，-1公海库，0待分配，>0用户ID');
            $table->tinyInteger('is_end')->default(0)->comment('是否成单，0未成单，1成单');
            $table->timestamp('end_time')->nullable()->comment('成单时间');
            $table->softDeletes();
            $table->timestamps();
        });
        \DB::statement("ALTER TABLE `project` comment '客户表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project');
    }
}
