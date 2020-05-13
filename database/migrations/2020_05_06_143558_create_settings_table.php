<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSettingsTable extends Migration {

	public function up()
	{
		Schema::create('settings', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->text('text_notification');
			$table->text('about_app');
			$table->string('phone_app');
			$table->string('email_app');
			$table->string('fb_link');
			$table->string('tw_link');
			$table->string('you_app');
			$table->string('inst_link');
		});
	}

	public function down()
	{
		Schema::drop('settings');
	}
}