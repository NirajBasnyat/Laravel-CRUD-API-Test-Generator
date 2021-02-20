<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CrudGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "gen:crud {name}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Basic Laravel Crud :)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $this->model_stub($name);
        $this->request_stub($name);
        $this->controller_stub($name);
        $this->factory_stub($name);
        $this->blade_stub($name);

        //add resource controller in web.php
        File::append(base_path('routes/web.php'),
            'Route::resource(\'' . Str::plural(strtolower($name)) . "',\\App\Http\Controllers\\"  . $name . "Controller::class);".PHP_EOL);

        //add in Database seederFile
        $current_contents = file_get_contents(base_path("database/seeders/DatabaseSeeder.php"));
        $factory_name = "\\App\Models\\" . $name . "::factory(5)->create();";
        $replacement = str_replace('//here', $factory_name, $current_contents);
        file_put_contents(base_path("database/seeders/DatabaseSeeder.php"), $replacement);
        //  File::append(base_path('database/seeders/DatabaseSeeder.php'), "\\" . "App\Models\\" . $name . "::factory(5)->create();");

        //make migration
        Artisan::call('make:migration create_' . Str::plural(strtolower($name)) . '_table --create=' . Str::plural(strtolower($name)));

        //to generate test
        if ($this->confirm('Do you wish to generate Test?')) {
            $this->feature_test_stub($name);
            $this->info($name . ' Test was generated successfully !!');
        }
        $this->info('Yaay ' . $name . ' crud was generated successfully !!');
    }

    protected function getStub($type)
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    protected function getBladeStub($type)
    {
        return file_get_contents(resource_path("stubs/blade/$type.stub"));
    }

    protected function model_stub($name)
    {
        //gives model with replaced placeholder
        $template = str_replace(
            ['{{modelName}}'], [$name], //name comes from command
            $this->getStub('model')
        );

        //create file dir if it doesnot exist
        if (!file_exists($path = app_path("/Models"))) {
            mkdir($path, 0777, true);
        }

        //update placeholder_model with valued Model
        file_put_contents(app_path("/Models/{$name}.php"), $template);
    }

    protected function request_stub($name)
    {
        //gives model with replaced placeholder
        $template = str_replace(
            ['{{modelName}}'], [$name], //name comes from command
            $this->getStub('request')
        );

        //create file dir if it doesnot exist
        if (!file_exists($path = app_path("/Http/Requests"))) {
            mkdir($path, 0777, true);
        }

        //update placeholder_model with valued Model
        file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $template);
    }

    protected function controller_stub($name)
    {
        //gives model with replaced placeholder
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralLowerCase}}'
            ],

            [
                $name,
                strtolower($name),
                strtolower(Str::plural($name))
            ],

            $this->getStub('controller')
        );

        //update placeholder_model with valued Model
        file_put_contents(app_path("/Http/Controllers/{$name}Controller.php"), $template);
    }

    protected function factory_stub($name)
    {
        //gives model with replaced placeholder
        $template = str_replace(
            ['{{modelName}}'], [$name], //name comes from command
            $this->getStub('factory')
        );

        //create file dir if it doesnot exist
        if (!file_exists($path = base_path("/database/factories"))) {
            mkdir($path, 0777, true);
        }

        //update placeholder_model with valued Model
        file_put_contents(base_path("/database/factories/{$name}Factory.php"), $template);
    }

    protected function blade_stub($name)
    {
        $template1 = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralLowerCase}}'
            ],

            [
                $name,
                strtolower($name),
                strtolower(Str::plural($name))
            ],
            $this->getBladeStub('index_blade')
        );

        $template2 = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
            ],

            [
                $name,
                strtolower($name),
            ],
            $this->getBladeStub('create_blade')
        );

        $template3 = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
            ],

            [
                $name,
                strtolower($name),
            ],
            $this->getBladeStub('edit_blade')
        );

        $template4 = $this->getBladeStub('show_blade');

        //create folder if it doesnot exist
        if (!file_exists($path = base_path("/resources/views/" . strtolower($name)))) {
            mkdir($path, 0777, true);
        }

        //create file
        file_put_contents(base_path("/resources/views/" . strtolower($name) . "/index.blade.php"), $template1);
        file_put_contents(base_path("/resources/views/" . strtolower($name) . "/create.blade.php"), $template2);
        file_put_contents(base_path("/resources/views/" . strtolower($name) . "/edit.blade.php"), $template3);
        file_put_contents(base_path("/resources/views/" . strtolower($name) . "/show.blade.php"), $template4);
    }

    protected function feature_test_stub($name)
    {
        //gives model with replaced placeholder
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralLowerCase}}'
            ],

            [
                $name,
                strtolower($name),
                strtolower(Str::plural($name))
            ],
            $this->getStub('feature_test')
        );

        //update placeholder_model with valued Model
        file_put_contents(base_path("/tests/Feature/{$name}Test.php"), $template);
    }
}
