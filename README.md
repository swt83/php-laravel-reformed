# Reformed

A Laravel PHP form model, assisting in the PRG form pattern.

## Install

Normal install via Composer.

## Usage

Build new form models as an extension of this base model:

```php
use Travis\Reformed;

class ContactForm extends Reformed
{
	public static $rules = [
		'first' => 'required',
		'last' => 'required',
		'email' => 'required|email',
		'subject' => 'required',
		'message' => 'required',
	];

	public static function run()
	{
		// if validates...
		if (static::is_valid())
		{
			// capture
			$first = static::get('first');
			$last = static::get('last');
			$email = static::get('email');
			$subject = static::get('subject');
			$message = static::get('message');

			// do something

			// set alert
			static::set_alert('Message sent.', 'green');

			// return
			return \Redirect::to(\URL::current()); // do not return input, clear the fields
		}

		// return w/ errors
		return \Redirect::to(\URL::current())->withInput();
	}
}
```

In your routes or controllers, implement the form model for processing:

```php
Route::get('contact', function()
{
	// return view w/ form
	return View::make('pages.contact');
});

Route::post('contact', function()
{
	// process and redirect
	return ContactForm::run();
});
```

In your views, use the form model to populate your fields and get error messages:

```html
<?php
$alert = ContactForm::get_alert();
?>
{{ Form::open() }}
{{ if (sizeof($alert['msg'])) }}
    <div class="alert {{ $alert['level'] }}">
        <ul>
        {{ foreach $alert['msg'] as $m }}
            <li>{{ $m }}</li>
        {{ endforeach }}
        </ul>
    </div>
{{ endif }}
{{ Form::label('first', 'First *') }}
{{ Form::text('first', ContactForm::populate('first')) }}
{{ Form::label('last', 'Last *') }}
{{ Form::text('last', ContactForm::populate('last')) }}
{{ Form::label('email', 'Email *') }}
{{ Form::text('email', ContactForm::populate('email')) }}
{{ Form::label('subject', 'Subject *') }}
{{ Form::text('subject', ContactForm::populate('subject')) }}
{{ Form::label('message', 'Message *') }}
{{ Form::textarea('message', ContactForm::populate('message')) }}
{{ Form::submit('Submit') }}
{{ Form::close() }}
```

The above methods make form handing in Laravel super easy.