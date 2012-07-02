<?php

/**
 * A model to facilitate working w/ forms in LaravelPHP.
 *
 * @package    Reformed
 * @author     Scott Travis <scott.w.travis@gmail.com>
 * @link       http://github.com/swt83/laravel-reformed
 * @license    MIT License
 */

class Reformed
{
	public static $data = array();
	public static $rules = array();
	public static $remember = false; // persistant data mode

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
		
		// We're going to fill the data array after every post,
		// that way we can use all the available methods even
		// if the post was invalid.  On the next post, everything
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
			$validation = Validator::make(static::all(), $rules);
			
			// if passes...
			if ($validation->passes())
			{
				// If the post was valid, we're going to save the values.
				// We assume that no two fields on different pages will
				// have the same name, thus each successful post adds more
				// and more values to the session.
				
				// remember
				static::remember();
				
				// return
				return true;
			}
			else
			{
				// The form model is using it's own session to store
				// errors, thus making the redirect with_errors()
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
			// load
			static::$data = unserialize(Session::get(get_called_class()));
		}
	}
	
	/**
	 * Remember data (automatic when using is_valid() method).
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
	 * Forget persistant data array.
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
	 * Flash data array.
	 */
	public static function flash()
	{
		static::forget();
	}
	
	/**
	 * Fill data array w/ values.
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
				static::$data[$field] = trim($input[$field]); // trim just in case
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
	 * Set alert box value after form post.
	 *
	 * @param	string	$string
	 * @param	string	$color
	 * @return	string/void
	 */
	public static function alert($string = null, $color = null)
	{
		// With no arguments, the method is being used
		// to fetch the current alert status.
		
		// if no string...
		if (!$string)
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
				
				// if alert...
				if ($alert)
				{
					// return
					return '<div class="alert '.$alert['color'].'"><p>'.$alert['string'].'</p></div>';
				}
			}
		}
		
		// With arguments, the method is being used to
		// construct a custom alert message.
		
		else
		{			
			Session::flash('alert_'.get_called_class(), array('string' => $string, 'color' => $color));
		}
	}
}