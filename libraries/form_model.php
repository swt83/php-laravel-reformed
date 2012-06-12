<?php

/**
 * A model to facilitate working w/ forms in LaravelPHP.
 *
 * @package    FormModel
 * @author     Scott Travis <scott.w.travis@gmail.com>
 * @link       http://github.com/swt83/laravel-form-model
 * @license    MIT License
 */

class FormModel
{
	public static $data = array();
	public static $rules = array();
	public static $validation = null;
	public static $errors = array();
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
			static::$validation = Validator::make(static::$data, $rules);
			
			// if passes...
			if (static::$validation->passes())
			{
				// When a form passes validation, let's take the
				// liberty of automatically remembering the values.
			
				// remember
				static::remember();
				
				// return
				return true;
			}
			else
			{
				// flash errors
				Session::flash('errors_'.get_called_class(), static::$validation->errors);
			
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
	 * Load data array from session.
	 */
	public static function pull()
	{
		// if session...
		if (Session::has(get_called_class()))
		{
			// load
			static::$data = unserialize(Session::get(get_called_class()));
		}
	}
	
	/**
	 * Remember data array (automatic when using is_valid() method).
	 */
	public static function remember()
	{
		// There is a big difference between pulling and remembering.
		// When you pull, you're just loading what has already been
		// saved.  When you remember, you are splicing together what
		// has been saved w/ what is new.
	
		// if remember...
		if (static::$remember)
		{	
			// old
			$existing = static::all();
		
			// pull
			static::pull();
			
			// fill
			static::fill($existing);
			
			// push
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
	 * Flash data array (alias of forget() method).
	 */
	public static function flash()
	{
		// alias
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
				// set field value
				static::$data[$field] = trim($input[$field]); // trim just in case
			}
		}
	}
	
	/**
	 * Set field value in data array.
	 *
	 * @param	string	$field
	 * @param	string	$value
	 */
	public static function set($field, $value)
	{
		// set new value
		static::$data[$field] = $value;
	}
	
	/**
	 * Check if field value exists in data array.
	 *
	 * @param	string	$field
	 * @return	bool
	 */
	public static function has($field)
	{
		// return
		return isset(static::$data[$field]) and !empty(static::$data[$field]);
	}
	
	/**
	 * Get field value from data array.
	 *
	 * @param	string	$field
	 * @param	string	$default
	 * @return	string
	 */
	public static function get($field, $default = null)
	{
		// return
		return static::has($field) ? static::$data[$field] : $default;
	}
	
	/**
	 * Get all fields from data array.
	 *
	 * @return	array
	 */
	public static function all()
	{	
		// return
		return static::$data;
	}
	
	/**
	 * Get field value from old input or data array.
	 *
	 * @param	string	$field
	 * @param	string	$default
	 * @return	string
	 */
	public static function populate($field, $default = null)
	{
		// The question of when to pull is tricky.  All data is stored
		// after the post, so the need to pull only applies to pre-post
		// situations.  In a pre-post context, assume either a fill()
		// was used to build the data array or a pull is necessary.
		
		// remember
		if (empty(static::$data)) static::pull();
		
		// return
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
	public static function alert($string = null, $color = 'red')
	{
		// Regardless of what alert may have been set,
		// if the error array is set, we need to show
		// the list of errors.
		
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
	    	return static::alert_build('<p>Form Errors:</p>'.HTML::ul($clean_errors), 'red');
    	}
		
		// With no arguments, the method is being used
		// to fetch the current alert status.
		
		// if string...
		if (!$string)
		{	
			// load alert
			$alert = Session::get('alert_'.get_called_class());
			
			// if alert...
			if ($alert)
			{
				// return
				return static::alert_build('<p>'.$alert['string'].'</p>', $alert['color']);
			}
			else
			{
				// If an alert was not found in the session, it might be
				// found in the GET vars.  Assumede coding is necessary.
				// This would only be used if cookies are disabled.
			
				// load alert
				$alert = Input::get('alert');
				
				// if warning...
				if ($alert)
				{
					// return
					return static::alert_build('<p>'.static::url_decode($alert).'</p>', 'red');
				}
				else
				{
					// return
					return null;
				}
			}
		}
		
		// With arguments, the method is being used to
		// construct the alert HTML and save to session.
		
		else
		{			
			Session::flash('alert_'.get_called_class(), array('string' => $string, 'color' => $color));
		}
	}
	
	/**
	 * Build HTML for alert box.
	 *
	 * @param	string	$string
	 * @param	string	$color
	 * @return	string
	 */
	private static function alert_build($string, $color = null)
	{
		return '<div class="alert '.$color.'">'.$string.'</div>';
	}
	
	/**
	 * Helper for encoding alerts through the URL.
	 *
	 * @param	string	$string
	 * @return	string
	 */
	public static function url_encode($string)
	{
		return urlencode(base64_encode($string));
	}

	/**
	 * Helper for decoding alerts through the URL.
	 *
	 * @param	string	$string
	 * @return	string
	 */	
	public static function url_decode($string)
	{
		return base64_decode(urldecode($string));
	}
	
	/**
	 * Plant a cookie prior to post, to see if cookies are disabled.
	 */
	public static function plant()
	{
		// In rare cases, people disable cookies which prevents you
		// from being able to store persistant data.  We can detect
		// these people by planting a cookie before the post and
		// then attempting to harvest it after.  Handle as needed.
		
		Session::put('cookie_'.get_called_class(), true);
	}
	
	/**
	 * Harvest a cookie after a post, to see if cookies are disabled.
	 *
	 * @return	bool
	 */
	public static function harvest()
	{
		return Session::get('cookie_'.get_called_class(), false);
	}
}