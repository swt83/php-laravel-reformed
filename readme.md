# Reformed

A Laravel PHP library for working w/ forms.

Based on Shawn McCool's [Form Base Model](https://github.com/ShawnMcCool/laravel-form-base-model).

## Install

Normal install via Composer.

## Example

When working w/ forms, I always have three parts:  the ``Controller``, the ``View``, and the ``Model``.

### The Controller

```php
<?php

class Example extends Base_Controller
{
    public function get_register()
    {
        return View::make('forms/register');
    }

    public function post_register()
    {
        return RegisterForm::run();
    }
}
```

Notice the ``run()`` method.  This could be named anything you want, but the important thing is to process the form directly from the model.  This keeps things nice and tidy and contained in a single place.

### The View

```php
<?php

$alert = RegisterForm::get_alert(); // array with 'msg' and 'level', for printing
if ($alert['msg']) echo '<div class="'.$alert['level'].'">'.$alert['msg'].'</div>';

echo Form::open();
echo Form::label('name', 'Name *');
echo Form::text('name', RegisterForm::populate('name'));
echo RegisterForm::error('name');
echo Form::submit('Submit');
echo Form::close();
```

Notice the ``get_alert()`` method, which returns form errors for the user to see, or success messages.  Also notice the ``populate()`` method, which loads the best value for that field.  Finally, notice the ``error()`` method, which shows any error that field may have had.

### The Model

```php
<?php

class RegisterForm extends Reformed
{
    // field rules
    public static $rules = array(
        'name' => 'required',
    );

    // persistant data mode
    public static $remember = false;

    // process the form
    public static function run()
    {
        // if passes...
        if (static::is_valid())
        {
            // test...
            if ($name === 'Foo Bar')
            {
                // save
                $record = new Registration();
                $record->name = static::get('name');
                $record->save();
            }
            else
            {
                // alert
                static::set_alert('Sorry, only Foo Bar is allowed to register.', 'red');

                // redirect
                return Redirect::to(URL::current())->with_input();
            }
        }
        else
        {
            // redirect
            return Redirect::to(URL::current())->with_input(); // note with_errors() isn't needed
        }
    }
}
```

The model allows you a single place to control the form processing behavior.  Take a look at the library itself and review the methods, and then take a look at this example model.  Notice that redirects don't require ``with_errors()`` as the model tracks errors by itself via sessions.  Also note that when using a model in "remember mode", returning redirects ``with_input()`` is also unnecessary.