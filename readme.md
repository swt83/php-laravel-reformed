# FormModel for LaravelPHP #

This is a extendable form model based around Shawn McCool's [Form Base Model](https://github.com/ShawnMcCool/laravel-form-base-model).  Because forms are so essential to my work, I wanted my own version to modify as needed.  The more I've worked w/ it, the more it has varied from Shawn's original.

### The Goal ###

Make forms easier to work w/ by breaking them into smaller and more managable parts.  I think this tool accomplishes that quite nicely.  You might find yourself not using the ``Input`` class at all anymore.

## Install ##

In ``application/bundles.php`` add:

```php
'form' => array('auto' => true),
```

## Usage ##

When working w/ forms, I always have view and model files.  The following is an example of how I might make a simple registration form.

### Form View ###

```php
<?=Form::open();?>
<?=Alert::get();?>
<?=Form::label('name', 'Name*');?>
<?=Form::text('name', RegisterForm::get('name', 'What is your name, dude?'));?>
<?=Form::close();?>
```

Notice the alert field.  ``Alert`` is a helper class I use w/ my form models to facilitate printing error messages.

### Form Model ###

```php
class RegisterForm extends FormModel()
{
	public static function run()
	{
		if (static::is_valid())
		{
		
		}
		else
		{
			// redirect
			return Redirect::to(URL::current())->with_input()->with_errors(static::validation());
		}
	}
}
```

A few things worth mentioning.  First notice the 

### Form Controller ###

```php
public function get_register()
{
	return View::make('forms/register');
}

public function post_register()
{
	return RegisterForm::run();
}
```

Then you
