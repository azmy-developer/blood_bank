<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClientsTable extends Migration {

	public function up()
	{
		Schema::create('clients', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->string('name');
			$table->string('email');
			$table->string('password');
			$table->string('phone');
			$table->string('date_of_birth');
			$table->integer('blood_type_id')->unsigned();
			$table->string('last_donation_date');
			$table->integer('city_id')->unsigned();
			$table->integer('rest_code_password')->nullable();
			$table->boolean('is_active')->default(1);
			$table->string('api_token')->unique()->nullable();

		});
	}

	public function down()
	{
		Schema::drop('clients');
	}
}