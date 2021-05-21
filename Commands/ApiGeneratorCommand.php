<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ApiGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gen:api {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Basic Laravel Api :D';

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
        $this->api_controller_stub($name);
        $this->resource_stub($name);
        $this->api_request_stub($name);

        if ($this->confirm('Do you wish to generate Model, Migrations and Factory?')) {
            $this->model_stub($name);
            $this->factory_stub($name);
            Artisan::call('make:migration create_' . Str::plural(Str::snake($name)) . '_table --create=' . Str::plural(Str::snake($name)));
        }

        if ($this->confirm('Do you wish to generate Tests for Api?')) {
            $this->api_feature_test_stub($name);
        }

        //add api resource controller in api.php
        File::append(base_path('routes/api.php'),
            'Route::apiResource(\'' . Str::plural(Str::kebab($name)) . "',\\App\Http\Controllers\Api\\"  . $name . "ApiController::class);".PHP_EOL);

        $this->info($name . ' api was generated successfully !!');
    }

    protected function getStub($type)
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    protected function getApiStub($type)
    {
        return file_get_contents(resource_path("stubs/api_stubs/$type.stub"));
    }

    protected function api_controller_stub($name)
    {
        //gives model with replaced placeholder
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
            ],

            [
                $name,
                Str::snake($name),
            ],

            $this->getApiStub('api_controller')
        );

        //create folder if it doesnot exist
        if (!file_exists($path = app_path("/Http/Controllers/Api"))) {
            mkdir($path, 0777, true);
        }

        //update placeholder_model with valued Model
        file_put_contents(app_path("/Http/Controllers/Api/{$name}ApiController.php"), $template);
    }

    protected function resource_stub($name)
    {
        //gives model with replaced placeholder
        $template = str_replace(
            ['{{modelName}}'], [$name],

            $this->getApiStub('resource')
        );

        //create folder if it doesnot exist
        if (!file_exists($path = app_path("/Http/Resources"))) {
            mkdir($path, 0777, true);
        }

        //update placeholder_model with valued Model
        file_put_contents(app_path("/Http/Resources/{$name}Resource.php"), $template);
    }

    protected function api_request_stub($name)
    {
        //gives model with replaced placeholder
        $template = str_replace(
            ['{{modelName}}'], [$name], //name comes from command
            $this->getApiStub('api_request')
        );

        //create file dir if it doesnot exist
        if (!file_exists($path = app_path("/Http/Requests"))) {
            mkdir($path, 0777, true);
        }

        //update placeholder_model with valued Model
        file_put_contents(app_path("/Http/Requests/{$name}ApiRequest.php"), $template);

        $api_form_request = $this->getApiStub('ApiFormRequest');

        if (!file_exists(app_path("/Http/Requests/ApiFormRequest.php"))) {
            file_put_contents(app_path("/Http/Requests/ApiFormRequest.php"), $api_form_request);
        }
    }

    //FOR TEST
    protected function api_feature_test_stub($name)
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
                Str::plural(Str::snake($name))
            ],
            $this->getApiStub('api_feature_test')
        );

        //update placeholder_model with valued Model
        file_put_contents(base_path("/tests/Feature/{$name}ApiTest.php"), $template);
    }


    //  FOR ADDITIONAL GENERATION

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
}
