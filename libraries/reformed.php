<?php

/**
 * A model to facilitate working w/ forms in LaravelPHP.
 *
 * @package    Reformed
 * @author     Scott Travis <scott.w.travis@gmail.com>
 * @link       http://github.com/swt83/laravel-reformed
 * @license    MIT License
 */

abstract class Reformed {

	/**
	 * Data input storage.
	 *
	 * @var	$data	array
	 */
	public static $data = array();
	
	/**
	 * Rules to govern fields.
	 *
	 * @var	$rules	array
	 */
	public static $rules = array();
	
	/**
	 * Customized error messages.
	 *
	 * @var	$messages	array
	 */
	public static $messages = array();
	
	/**
	 * Persistant data mode.
	 *
	 * @var	$remember	boolean
	 */
	public static $remember = false; // persistant data mode
	
	/**
	 * Scaffolding to construct form.
	 *
	 * @var	$scaffold	array
	 */
	public static $scaffold = array();
	
	/**
	 * Validates form, sets all input to data array.
	 *
	 * @param	array	$fields
	 * @return	bool
	 */
	public static function is_valid($fields = null)
	{
		// check error
		if (!is_array($fields) and !is_null($fields))
		{
			return false;
		}
		
		// We're going to fill the data array after every post
		// so we can use all the available data methods.  Even
		// if the post was invalid, on the next post everything
		// will get overwritten anyway.
		
		// fill data array
		static::fill(Input::all());
		
		// if fields...
		if (is_array($fields))
		{
			// filter rules...
			$rules = array();
			foreach ($fields as $field)
			{
				if (array_key_exists($field, static::$rules))
				{
					$rules[$field] = static::$rules[$field];
				}
			}
		}
		else
		{
			// use all rules
			$rules = static::$rules;
		}
		
		// if rules...
		if (!empty($rules))
		{
			// validate
			$validation = Validator::make(static::all(), $rules, static::$messages);
			
			// if passes...
			if ($validation->passes())
			{
				// If the post was valid, we're going to remember the values.
				// We assume that no two fields on different pages will have
				// the same name, thus each successful post just adds more
				// and more values to the session.
				
				// remember
				static::remember();
				
				// return
				return true;
			}
			else
			{
				// The form model is using it's own session to store
				// errors, thus making a redirect with_errors() is
				// no longer necessary.
			
				// flash errors
				Session::flash('errors_'.get_called_class(), $validation->errors);
			
				// return
				return false;
			}
		}
		else
		{
			// return
			return true;
		}
	}
	
	/**
	 * Load data from session.
	 *
	 * @return	void
	 */
	public static function refresh()
	{
		// There is a big difference between "refreshing" and
		// "remembering".  When you "refresh", you're just
		// loading what has already been remembered.  When you
		// "remember", you're adding what is new to what was
		// already remembered previously.
	
		// if session...
		if (Session::has(get_called_class()))
		{
			// pull data
			static::$data = unserialize(Session::get(get_called_class()));
		}
	}
	
	/**
	 * Save data to session.
	 *
	 * @return	void
	 */
	public static function remember()
	{	
		// if remember mode...
		if (static::$remember)
		{	
			// grab existing values
			$existing = static::all();
		
			// pull previous values
			static::refresh();
			
			// merge together
			static::fill($existing);
			
			// push merged values
			Session::put(get_called_class(), serialize(static::$data));
		}
	}
	
	/**
	 * Forget data.
	 *
	 * @return	void
	 */
	public static function forget()
	{	
		// forget
		Session::forget(get_called_class());
		
		// Sometimes you'll want to forget the persistant data,
		// but then use it again on the final thank you page.
		// What we'll do here is flash for a final use.
		
		// flash
		Session::flash(get_called_class(), serialize(static::$data));
	}
	
	/**
	 * Flash data.
	 *
	 * @return	void
	 */
	public static function flash()
	{
		static::forget(); // alias method
	}
	
	/**
	 * Fill data w/ values.
	 *
	 * @param	array	$input
	 */
	public static function fill($input)
	{
		// if array...
		if (is_array($input))
		{
			// spin input...
			foreach ($input as $field => $value)
			{
				// set value
				static::$data[$field] = $input[$field];
			}
		}
	}
	
	/**
	 * Get all field values as array.
	 *
	 * @return	array
	 */
	public static function all()
	{	
		return static::$data;
	}
	
	/**
	 * Set field value.
	 *
	 * @param	string	$field
	 * @param	string	$value
	 * @return	void
	 */
	public static function set($field, $value)
	{
		static::$data[$field] = $value;
	}
	
	/**
	 * Check if field value exists.
	 *
	 * @param	string	$field
	 * @return	bool
	 */
	public static function has($field)
	{
		// refresh memory
		if (empty(static::$data)) static::refresh();
	
		// return
		return isset(static::$data[$field]) and !empty(static::$data[$field]);
	}
	
	/**
	 * Get field value.
	 *
	 * @param	string	$field
	 * @param	string	$default
	 * @return	string
	 */
	public static function get($field, $default = null)
	{
		return static::has($field) ? static::$data[$field] : $default;
	}
	
	/**
	 * Get field value from input array.
	 *
	 * @param	string	$field
	 * @param	string	$option
	 * @return	boolean
	 */
	public static function get_array($field, $option)
	{
		// Sometimes in PHP we use input arrays to store values,
		// usually with checkboxes.  This allows us to see
		// if a specific option has been selected or not.
		// TIP: use Form::hidden() to clear this input array
		// when no option is selected.
	
		// get input value
		$input = static::get($field, array());
		
		// catch blank
		if ($input === '') $input = array();
		
		// check in_array
		return in_array($option, $input) ? true : false;
	}
	
	/**
	 * Get field value.
	 *
	 * @param	string	$field
	 * @param	string	$default
	 * @return	string
	 */
	public static function populate($field, $default = null)
	{
		return Input::old($field, static::get($field, $default));
	}
	
	/**
	 * Get field value from input array.
	 *
	 * @param	string	$field
	 * @param	string	$option
	 * @return	boolean
	 */
	public static function populate_array($field, $option)
	{
		// get input value
		$input = Input::old($field, static::get($field, array()));
		
		// catch blank
		if (!is_array($input)) $input = array();
		
		// check in_array
		return in_array($option, $input) ? true : false;
	}
	
	/**
	 * Get field error from validation object.
	 *
	 * @param	string	$field
	 * @param	string	$default
	 * @return	string
	 */
	public static function error($field, $default = null)
	{
		// load session
		$errors = Session::get('errors_'.get_called_class());
		
		// if errors...
		if ($errors)
		{
			// return
			return $errors->has($field) ? $errors->first($field) : $default;
		}
		else
		{
			// return
			return $default;
		}
	}
	
	/**
	 * Set alert box value.
	 *
	 * @param	string	$string
	 * @param	string	$color
	 * @return	void
	 */
	public static function set_alert($string = null, $color = 'red')
	{
		Session::flash('alert_'.get_called_class(), array('string' => $string, 'color' => $color));
	}
	
	/**
	 * Print alert box (and override current value).
	 *
	 * @param	string	$string
	 * @param	string	$color
	 * @return	string
	 */
	public static function get_alert($string = null, $color = 'red')
	{
		// Regardless of what alert may have been set,
		// if the error array is set, we need to show
		// the list of errors.
		
		// load errors
		$errors = Session::get('errors_'.get_called_class());
		
		// if errors...
		if ($errors)
		{
			// build friendly array
			$clean_errors = array();
			foreach ($errors->messages as $error)
			{
				$clean_errors[] = $error[0];
			}
			
			// return
			return '<div class="alert red"><p>Form Errors:</p>'.HTML::ul($clean_errors).'</div>';
		}
		else
		{
			// load alert
			$alert = Session::get('alert_'.get_called_class());
			
			// if NOT found...
			if (!$alert)
			{
				// if override...
				if ($string)
				{
					// set manual values
					$alert = array(
						'color' => $color,
						'string' => $string,
					);
				}
			}
			
			// if alert...
			if ($alert)
			{
				// return
				return '<div class="alert '.$alert['color'].'"><p>'.$alert['string'].'</p></div>';
			}
		}
	}
	
	/**
	 * Build an HTML form based on the scaffold.
	 *
	 * @return	string
	 */
	public static function form($populate = array())
	{
		// convert object to array...
		if (is_object($populate))
		{
			if (is_a($populate, 'Eloquent')) $populate = $populate->to_array();
		}
		
		// build view
		return View::make('reformed::form')
			->with('reformed', get_called_class())
			->with('scaffold', static::$scaffold)
			->with('populate', $populate)
			->render();
	}
	
	/**
	 * Build an HTML table based on input (not abstract, just helpful).
	 *
	 * @return	string
	 */
	public static function table($data, $headers, $closure)
	{
		return View::make('reformed::table')
			->with('data', $data)
			->with('headers', $headers)
			->with('closure', $closure)
			->render();
	}
	
}