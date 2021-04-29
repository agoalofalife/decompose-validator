# Decompose laravel validation
Package helps you decompose validation in you project Laravel

- [What is it?](#what)
- [Installation](#installation)
- [Create Validator Value](#create)
- [How Do I can use in Form Request](#useInFormRequest)
- [How Do I use in simple way validation](#useJustWay)
- [Article](#article)

<a name="what"></a>
## What is it?
Package give you a chance split validation fields. Each filed and dataset rules is  independent  class.
It gives freedom in action and centralization validation in one place.
For more understanding reading my article in [medium](https://agoalofalife.medium.com/decompose-form-request-in-laravel-5997997f1f1) .

<a name="installation"></a>
## Installation

```bash
composer require agoalofalife/decompose-validator
```
| Laravel version | Package Version |
|-----------------|-----------------|
| 7               | 1               |
| 8               | 1               |


<a name="create"></a>
## Create Validator Value
Let's have a look email validation example.
Should implementation ValidatorValue.
Describe rules and extra messages.
I prefer `$attribute` set in contstruct with value by default, but you can return just string in method.

```php

use agoalofalife\DecomposeValidator\ValidatorValue;

class ConsumerEmail implements ValidatorValue
{
    /**
     * @var string
     */
    private $attribute;
    
    /**
     * @var int
     */
    private $exceptConsumerId;
   
    public function __construct(
        int $consumerId,
        string $attribute = 'email'
    ) {
        $this->exceptConsumerId = $consumerId;
        $this->attribute = $attribute;
    }


    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function getRules(): array
    {
        return [
            'required',
            'email',
            'unique:users,email,'.$this->exceptConsumerId,
        ];
    }

    public function getMessages(): array
    {
        return [
            "{$this->attribute}.email"         => 'Please field should be email',
            "{$this->attribute}.required"      => 'Please email is required field',
            "{$this->attribute}.unique"        => 'Email has registered already',
            "{$this->attribute}.email_checker" => 'Email does not exist',
        ];
    }
}

```

<a name="useInFormRequest"></a>
## Use in Form Request

Pay attention parent name class. You should extend of it.

```php
use agoalofalife\DecomposeValidator\FormRequestDecompose;

class UpdateUserProfile extends FormRequestDecompose
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            new ConsumerEmail(),
            'name'       => ['required', 'alpha'],
            'age'        => ['integer', 'max:120'],
        ];
    }
}
```

<a name="useJustWay"></a>
## Use in Simple Validation(controller or facade)
Also we have a chance use simple validation.

```php
/**
 * Store a new blog post.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
public function store(Request $request)
{
    $validatorValue = new ConsumerEmail(request->auth()->id);
    $validated = $request->validate([
         $validatorObject->getAttribute() => $validatorObject->getRules()
    ]);
    
   //...
}
```


<a name="article"></a>
## Article
Also I wrote the aritcle in [medium](https://agoalofalife.medium.com/decompose-form-request-in-laravel-5997997f1f1) 
