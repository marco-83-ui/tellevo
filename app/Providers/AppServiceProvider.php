<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Validation\Factory as Validator;
use Illuminate\Support\Str;
use Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Validator instance.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validator;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Validator $validator)
    {
        $this->validator = $validator;

        Schema::defaultStringLength(191);

        $this->loadCustomValidators();

        Paginator::useBootstrap();

    }

     /**
     * Load the custom validator methods.
     *
     * @return void
     */
    protected function loadCustomValidators()
    {
        $customValidatorClass = 'App\Base\Validators\CustomValidators';

        $this->extendValidator('mobile_number', $customValidatorClass);
        $this->extendValidator('numeric_max', $customValidatorClass);
        $this->extendValidator('numeric_min', $customValidatorClass);
        $this->extendValidator('otp', $customValidatorClass);
        $this->extendValidator('uuid', $customValidatorClass);
        $this->extendValidator('decimal', $customValidatorClass);
        $this->extendValidator('double', $customValidatorClass);
    }

    /**
     * Extend the validator with custom methods.
     *
     * @param string $name
     * @param string $class
     * @return void
     */
    protected function extendValidator($name, $class)
    {
        $method = 'validate' . Str::studly($name);

        $this->validator->extend($name, "{$class}@{$method}");
    }
}
