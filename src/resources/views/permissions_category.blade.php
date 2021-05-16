{!! '<?php' !!}

use Illuminate\Database\Migrations\Migration;

class {{ Str::studly($resource) }}PermissionsCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions_categories')->insert([
            'name'         => '{{ $resource }}',
            'display_name' => '{{ $name }}::permissions.{{ $resource }}.category',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions_categories')->where('name', '{{ $resource }}')->delete();
    }
}
