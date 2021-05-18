# Let's Generate !

## What will be generated

These will let you generate
- CRUD **[ Model, Controller, Blade File, Request, Factory, Migration ]** with **Feature Test!**
- API  **[ ApiController, ApiRequest, ApiResource ]** with **Feature Test!**
 > **Note:** Model, Factory, Migration can be also generated for API if needed.


## How To Start
- Clone the repo
- Copy **Commands** Folder and paste it inside app>console

- Copy **Stubs** Folder and paste it inside resources folder 
- type php artisan inside your app terminal you will see two custom commands ie **gen:api** and **gen:crud**
- To generate files type ``gen:crud {ModelName} ``

	``[eg: gen:crud Post or gen:api Comment]``

## Customizations

- You can easily customize everything to your need by simply changing stubs files present in stub folder.
 > **Note:** It will need to add your own migrations fields, validations in request.
