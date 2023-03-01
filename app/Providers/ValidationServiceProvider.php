<?php

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Opis\JsonSchema\Resolvers\SchemaResolver;
use Opis\JsonSchema\Validator;

class ValidationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Validator::class, function () {
            // Register all schemas under resources/validation
            $resolver = new SchemaResolver();
            $resolver->registerPrefix(
                'https://schema.nethserver.org/',
                resource_path('validation')
            );
            $validator = new Validator();
            $validator->setResolver($resolver);

            return $validator;
        });
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<class-string>
     */
    public function provides(): array
    {
        return [Validator::class];
    }
}
