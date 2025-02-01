<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class GoogleClientService extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            $client = new Client();

            $client->setApplicationName(config('google.application_name'));
            $client->setClientId(config('google.client_id'));
            $client->setClientSecret(config('google.client_secret'));
            $client->setRedirectUri(config('google.redirect_uri'));
            $client->setAccessType(config('google.access_type'));
            $client->setApprovalPrompt(config('google.approval_prompt'));

            foreach (config('google.scopes') as $scope) {
                $client->addScope($scope);
            }

            return $client;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
