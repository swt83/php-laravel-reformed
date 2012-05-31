# FormModel for LaravelPHP #

This is a extendable form model based around Shawn McCool's [Form Base Model](https://github.com/ShawnMcCool/laravel-form-base-model).  Because forms are so essential to my work, I wanted my own version to modify as needed.  The more I've worked w/ it, the more it has varied from Shawn's original.

### The Goal ###

Make forms easier to work w/ by breaking them into smaller and more manageable parts.  I have found Shawn's form model concept to be extremely helpful and it has completely changed how I do forms.  Every form has a model now.

## Install ##

In ``application/bundles.php`` add:

```php
'form' => array('auto' => true),
```

The bundle contains two classes, a ``FormModel`` class for building forms and an ``Alert`` class for handling error notices.

## Example ##

When working w/ forms, I always have the ``Route/Controller``, the ``View``, and the ``FormModel``.  The following is an example of how I might make a simple registration form.

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

You could process the form in the controller, but I like to pass things to a ``run()`` method to handle everything from inside my ``FormModel``.

### The View ###

```php
<?=Alert::get();?>
<?=Form::open();?>
<?=Form::label('name', 'Name*');?>
<?=Form::text('name', RegisterForm::populate('name'));?>
<?=Form::submit('Submit');?>
<?=Form::close();?>
```

``Alert`` is a helper class I use w/ my form models to facilitate printing error messages.  I always put it at the top.

### Form Model ###

```php
<?php

class RegisterForm extends FormModel()
{
	public static $rules = array(
		'name' => 'required',
	);

	public static function run()
	{
		if (static::is_valid())
		{
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
				Alert::set('<p>Sorry, only Foo Bar is allowed to register.</p>', 'red');
				
				// redirect
				return Redirect::to(URL::current())->with_input();
			}
		}
		else
		{
			// redirect
			return Redirect::to(URL::current())->with_input()->with_errors(static::validation());
		}
	}
}
```

The ``FormModel`` class is capable of more than I can really explain in a readme, but this example should get you off the ground.  Take a look at the class and read what the methods do.  You'll know you're using it to it's full potential if you aren't using any ``Input`` class methods.

## Notes ##

There are several tricky aspects to working with forms.  Building out this form model has been an extremely educational excercise.  Here are a few things I think are worth mentioning:

### How the FormModel Stores Data ###

Before a post, the model has no data at all.  So unless you use a ``fill()`` method to add data, your ``has()``, ``get()``, and ``all()`` methods won't do anything useful.  Generally you'll be working in a controller or a view and the only methods you'll use are ``fill()``, ``populate()``, and ``error()``.

After a post, the model will immediately store all input in the ``static::$data`` array.  You can use any method at this point to get access to your data.  Generally you'll be working in the model itself under a ``run()`` method or something of your choosing, processing the form and ending w/ a returned redirect. 

### Persistant Data Over Multi-Page Forms ###

In your model you can add a variable ``static::$remember`` which will cause the form to remember data between pageloads.  You don't have to add any special method calls to your form processing, the model will just do it.

When it comes time to forget the data, after a final and successful post on the last page, you'll use the ``forget()`` method to erase everything.  The data will be flashed for a final use on a possible "thank you" page.