<?php

declare(strict_types=1);

namespace agoalofalife\DecomposeValidator\Providers;

use agoalofalife\DecomposeValidator\FormRequestDecompose;
use Illuminate\Routing\Redirector;
use Illuminate\Support\ServiceProvider;

class FormRequestDecomposeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->resolving(FormRequestDecompose::class, function (FormRequestDecompose $requestDecompose, $app) {
                $request = FormRequestDecompose::createFrom($app['request'], $requestDecompose);
                $request->setContainer($app)->setRedirector($app->make(Redirector::class));
        });
    }
}
