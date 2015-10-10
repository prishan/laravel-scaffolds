<?php

namespace Prishan\LaravelScaffolds\Install;

class InstallScripts {
    public static function postInstall(){
        try
        {
            $contents = file_get_contents(realpath(__DIR__.'/../../../../../config/jsvalidation.php'));
            $contents = preg_replace('/jsvalidation::bootstrap/', 'layout.error_display.JsValidation', $contents);
            file_put_contents(realpath(__DIR__.'/../../../../../config/jsvalidation.php'), $contents);
        }
        catch (Illuminate\Filesystem\FileNotFoundException $exception)
        {
            echo("The file doesn't exist");
        }
    }
}