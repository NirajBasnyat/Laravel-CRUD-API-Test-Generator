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
        $this->factory_stub($name);

        //add in Database seederFile
        $current_contents = file_get_contents(base_path("database/seeders/DatabaseSeeder.php"));
        $factory_name = "\\App\Models\\" . $name . "::factory(5)->create();";
        $replacement = str_replace('//here', $factory_name, $current_contents);
        file_put_contents(base_path("database/seeders/DatabaseSeeder.php"), $replacement);

        //make migration
        Artisan::call('make:migration create_' . Str::plural(Str::snake($name)) . '_table --create=' . Str::plural(Str::snake($name)));


        if ($this->confirm('Do you want to add controllers in specific folder ?')) {

            $folder_name = $this->anticipate('Folder name is', ['Auth', 'Admin', 'User']);

            //add named resource controller in web.php
            File::append(
                base_path('routes/web.php'),
                'Route::resource(\'' . Str::plural(Str::kebab($name)) . "',\\App\Http\Controllers\\" . $folder_name . "\\" . $name . "Controller::class);" . PHP_EOL
            );

            $this->named_controller_stub($name, $folder_name);

            $this->named_blade_stub($name, $folder_name);
        } else {

            //add resource controller in web.php
            File::append(
                base_path('routes/web.php'),
                'Route::resource(\'' . Str::plural(Str::kebab($name)) . "',\\App\Http\Controllers\\"  . $name . "Controller::class);" . PHP_EOL
            );

            $this->controller_stub($name);

            $this->blade_stub($name);
        }

        //to generate test
        if ($this->confirm('Do you wish to generate Test?')) {
            $this->feature_test_stub($name);
            $this->line($name . ' Test was generated successfully !!');
        }

        $this->info($name . ' crud was generated successfully !!');
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
            ['{{modelName}}'],
            [$name], //name comes from command
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
            ['{{modelName}}'],
            [$name], //name comes from command
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
                '{{modelNamePluralLowerCase}}',
                '{{modelNamePluralKebabCase}}'
            ],

            [
                $name,
                Str::snake($name),
                Str::plural(Str::snake($name)),
                Str::plural(Str::kebab($name)),
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
            ['{{modelName}}'],
            [$name], //name comes from command
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
                '{{modelNamePluralLowerCase}}',
                '{{modelNamePluralKebabCase}}',
            ],

            [
                $name,
                Str::snake($name),
                Str::plural(Str::snake($name)),
                Str::plural(Str::kebab($name)),
            ],
            $this->getBladeStub('index_blade')
        );

        $template2 = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralKebabCase}}'
            ],

            [
                $name,
                Str::snake($name),
                Str::plural(Str::kebab($name)),
            ],
            $this->getBladeStub('create_blade')
        );

        $template3 = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralKebabCase}}',
            ],

            [
                $name,
                Str::plural(Str::kebab($name))
            ],
            $this->getBladeStub('edit_blade')
        );

        $template4 = $this->getBladeStub('show_blade');

        //create folder if it doesnot exist
        if (!file_exists($path = base_path("/resources/views/" . Str::snake($name)))) {
            mkdir($path, 0777, true);
        }

        //create file
        file_put_contents(base_path("/resources/views/" . Str::snake($name) . "/index.blade.php"), $template1);
        file_put_contents(base_path("/resources/views/" . Str::snake($name) . "/create.blade.php"), $template2);
        file_put_contents(base_path("/resources/views/" . Str::snake($name) . "/edit.blade.php"), $template3);
        file_put_contents(base_path("/resources/views/" . Str::snake($name) . "/show.blade.php"), $template4);
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
                Str::snake($name),
                Str::plural(Str::snake($name)),
            ],
            $this->getStub('feature_test')
        );

        //update placeholder_model with valued Model
        file_put_contents(base_path("/tests/Feature/{$name}Test.php"), $template);
    }


    //FOR FOLDER SPECIFIC

    protected function named_controller_stub($name, $folder_name)
    {
        //gives model with replaced placeholder
        $template = str_replace(
            [
                '{{folderName}}',
                '{{folderNameSnakeCase}}',
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNamePluralKebabCase}}'
            ],

            [
                $folder_name,
                Str::snake($folder_name),
                $name,
                Str::snake($name),
                Str::plural(Str::snake($name)),
                Str::plural(Str::kebab($name)),
            ],

            $this->getStub('named_controller')
        );

        //create folder if it doesnot exist
        if (!file_exists($path = app_path("/Http/Controllers/{$folder_name}"))) {
            mkdir($path, 0777, true);
        }

        //update placeholder_model with valued Model
        file_put_contents(app_path("/Http/Controllers/{$folder_name}/{$name}Controller.php"), $template);
    }

    protected function named_blade_stub($name, $folder_name)
    {
        $template1 = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNamePluralKebabCase}}',
            ],

            [
                $name,
                Str::snake($name),
                Str::plural(Str::snake($name)),
                Str::plural(Str::kebab($name)),
            ],
            $this->getBladeStub('index_blade')
        );

        $template2 = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralKebabCase}}'
            ],

            [
                $name,
                Str::snake($name),
                Str::plural(Str::kebab($name)),
            ],
            $this->getBladeStub('create_blade')
        );

        $template3 = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralKebabCase}}',
            ],

            [
                $name,
                Str::plural(Str::kebab($name))
            ],
            $this->getBladeStub('edit_blade')
        );

        $template4 = $this->getBladeStub('show_blade');

        //create folder if it doesnot exist
        if (!file_exists($path = base_path("/resources/views/" . Str::snake($folder_name) . "/" . Str::snake($name)))) {
            mkdir($path, 0777, true);
        }

        //create file
        file_put_contents(base_path("/resources/views/" . Str::snake($folder_name) . "/" . Str::snake($name) . "/index.blade.php"), $template1);
        file_put_contents(base_path("/resources/views/" . Str::snake($folder_name) . "/" . Str::snake($name) . "/create.blade.php"), $template2);
        file_put_contents(base_path("/resources/views/" . Str::snake($folder_name) . "/" . Str::snake($name) . "/edit.blade.php"), $template3);
        file_put_contents(base_path("/resources/views/" . Str::snake($folder_name) . "/" . Str::snake($name) . "/show.blade.php"), $template4);
    }
}
