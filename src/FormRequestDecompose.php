<?php

declare(strict_types=1);

namespace agoalofalife\DecomposeValidator;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;

/**
 * Looks like a usually FormRequest from Laravel, but has extra logic for
 * handle rules
 * Class FormRequestDecompose
 * @package agoalofalife\DecomposeValidator
 */
abstract class FormRequestDecompose extends Request implements ValidatesWhenResolved
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * The redirector instance.
     *
     * @var \Illuminate\Routing\Redirector
     */
    protected $redirector;

    /**
     * The URI to redirect to if validation fails.
     *
     * @var string
     */
    protected $redirect;

    /**
     * The route to redirect to if validation fails.
     *
     * @var string
     */
    protected $redirectRoute;

    /**
     * The controller action to redirect to if validation fails.
     *
     * @var string
     */
    protected $redirectAction;


    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'default';
    /**
     * @var array
     */
    protected $composeMessages = [];
    /**
     * @var array
     */
    protected $composeRules = [];
    /**
     * The validator instance.
     *
     * @var \Illuminate\Contracts\Validation\Validator
     */
    protected $validator;


    public function validateResolved()
    {
        $this->prepareForValidation();

        $instance = $this->getValidatorInstance();

        if ($instance->fails()) {
            $this->failedValidation($instance);
        }

        $this->passedValidation();
    }


    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        //
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Validation\Validator
     * @throws BindingResolutionException
     */
    protected function getValidatorInstance()
    {
        $factory = $this->container->make(ValidationFactory::class);

        if (method_exists($this, 'validator')) {
            $validator = $this->container->call(
                [$this, 'validator'],
                compact('factory')
            );
        } else {
            $validator = $this->createDefaultValidator($factory);
        }

        if (method_exists($this, 'withValidator')) {
            $this->withValidator($validator);
        }

        $this->setValidator($validator);

        return $this->validator;
    }

    /**
     * Create the default validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Factory  $factory
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        $this->compose();

        return $factory->make(
            $this->validationData(),
            $this->composeRules,
            $this->composeMessages,
            $this->attributes()
        );
    }

    private function compose()
    {
        $this->composeRules = $this->container->call([$this, 'rules']);
        $attributes = [];
        $rules = [];
        foreach ($this->composeRules as $attributeOrIndex => $candidate) {
            if (is_object($candidate) && $candidate instanceof ValidatorValue) {
                $this->composeRules[$attributeOrIndex] = $candidate->getRules();
                $attributes[] = $candidate->getAttribute();
                $rules[] = $candidate->getRules();
                $this->composeMessages = array_merge(
                    $candidate->getMessages(),
                    $this->composeMessages
                );
            } else {
                $attributes[] = $attributeOrIndex;
                $rules[] = $candidate;
            }
        }
        $this->composeRules = array_combine($attributes, $rules);
        $this->composeMessages = array_merge(
            $this->messages(),
            $this->composeMessages
        );
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }


    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        return $this->request->all();
    }

    /**
     * Handle a passed validation attempt.
     *
     * @return void
     */
    protected function passedValidation()
    {
        //
    }

    /**
     * Set the Validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Set the Redirector instance.
     *
     * @param  \Illuminate\Routing\Redirector  $redirector
     * @return $this
     */
    public function setRedirector(Redirector $redirector)
    {
        $this->redirector = $redirector;

        return $this;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(
        \Illuminate\Contracts\Validation\Validator $validator
    ) {
        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }

    /**
     * Get the URL to redirect to on a validation error.
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();

        if ($this->redirect) {
            return $url->to($this->redirect);
        } elseif ($this->redirectRoute) {
            return $url->route($this->redirectRoute);
        } elseif ($this->redirectAction) {
            return $url->action($this->redirectAction);
        }

        return $url->previous();
    }

    /**
     * Set the container implementation.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return $this
     */
    public function setContainer(
        \Illuminate\Contracts\Container\Container $container
    ) {
        $this->container = $container;

        return $this;
    }
}
