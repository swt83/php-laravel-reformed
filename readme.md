# Reformed for LaravelPHP #

A model for working w/ forms in LaravelPHP, based on Shawn McCool's [Form Base Model](https://github.com/ShawnMcCool/laravel-form-base-model).  I have found Shawn's form model concept to be extremely helpful and has completely changed how I do forms.  Every form has a model now.  Reformed is my own implementation, refined from my experiences over the last several months.

## Install ##

In ``application/bundles.php`` add:

```php
'form' => array('auto' => true),
```

## Example ##

When working w/ forms, I always have three parts:  the ``Controller``, the ``View``, and the ``Model``.

### The Controller ###

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

Notice the ``run()`` method.  This could be named anything you want, but the important thing it to process the form directly from the model.  This keeps things nice a tidy and contained in a single place.

### The View ###

```php
<?=RegisterForm::alert();?>
<?=Form::open();?>
<?=Form::label('name', 'Name*');?>
<?=Form::text('name', RegisterForm::populate('name'));?>
<?=RegisterForm::error('name');?>
<?=Form::submit('Submit');?>
<?=Form::close();?>
```

Notice the ``alert()`` method, which prints form responses for the user to see, such as error and success messages.  Also notice the ``populate()`` method, which loads the best value for that field.  Finally, notice the ``error()`` method, which shows any error that field may have had, if there was any.

### The Model ###

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
				static::alert('Sorry, only Foo Bar is allowed to register.', 'red');
				
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