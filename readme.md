# FormModel for LaravelPHP #

This is a extendable form model based around Shawn McCool's [Form Base Model](https://github.com/ShawnMcCool/laravel-form-base-model).  I have found Shawn's form model concept to be extremely helpful and has completely changed how I do forms.  Every form has a model now.

## Install ##

In ``application/bundles.php`` add:

```php
'form' => array('auto' => true),
```

## Example ##

When working w/ forms, I always have three parts:  the ``Route`` or ``Controller``, the ``View``, and the ``FormModel``.  The following is a basic example of how I might make a simple registration form:

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

You could process the form in the controller, but I like to pass things to a ``run()`` method to handle everything inside my model.

### The View ###

```php
<?=RegisterForm::alert();?>
<?=Form::open();?>
<?=Form::label('name', 'Name*');?>
<?=Form::text('name', RegisterForm::populate('name'));?>
<?=Form::submit('Submit');?>
<?=Form::close();?>
```

The ``alert()`` method is a helper function I use w/ my forms to facilitate printing error messages.  I always put it at the top.

### The Model ###

```php
<?php

class RegisterForm extends FormModel
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
				static::alert('<p>Sorry, only Foo Bar is allowed to register.</p>', 'red');
				
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

The ``FormModel`` class is capable of more than I can really explain in a readme, but this example should get you off the ground.  Take a look at the classes and read what the methods do.

You'll know you're using it to it's full potential if you aren't using any ``Input`` class methods at all.