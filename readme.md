# Laravel 5 Scaffold Generator


Hi, this is a scaffold generator for Laravel 5. Inspired by laraviet/l5scaffold.



## Usage

### Step 1: Install Through Composer

Update composer.json file with below content 

```
"require-dev": {
    "prishan/laravel-scaffolds": "dev-master"
},
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/prishan/laravel-scaffolds"
    }
],
"scripts": {
    "post-update-cmd": [
        "Prishan\\LaravelScaffolds\\Install\\InstallScripts::postInstall"
    ]
}
```

run 
```
composer update
```

### Step 2: Add the Service Provider

Open `config/app.php` and, to your **providers** array at the bottom, add:

```
"Prishan\Laravel-scaffolds\GeneratorsServiceProvider"
```

### Step 3: Run Artisan!

You're all set. Run `php artisan` from the console, and you'll see the new commands `make:scaffold`.

#### Examples


```
php artisan make:scaffold Tweet --schema="title:string:default('Tweet #1'), body:text"
```
This command will generate:

```
app/Tweet.php
app/Http/Requests/TweetRequest.php
app/Http/Controllers/Admin/TweetController.php
app/Libs/ErrorDisplay.php
app/Libs/ValueHelper.php
database/migrations/2015_04_23_234422_create_tweets_table.php
database/seeds/TweetTableSeeder.php
resources/views/layout/admin.blade.php
resources/views/layout/error_display/all_errors.blade.php
resources/views/layout/error_display/field_errors.blade.php
resources/views/admin/tweets/index.blade.php
resources/views/admin/tweets/show.blade.php
resources/views/admin/tweets/edit.blade.php
resources/views/admintweets/create.blade.php
```
And don't forget to run:

```
php artisan migrate
```

### Step 4: Add Validation Rules!

Open `app/Http/Requests/TweetRequest.php` and add validation rules at rules() method

```
public function rules()
{
    return [
    	"title" => "required",
    	"body" => "required"
    ];
}
```

### Step 5: Customize How Validation Error Message Display!

Open `config/error_display.php` and update config:

```
"box" => false, //Display all validation error message in the top of page as box
"line" => true, //Display error message line by line
```

Views location for error message: `resources/views/layout/error_display/`

## Scaffold Screenshot

comming soon
