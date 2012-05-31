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
	public static $remember = false; // persistant data mode
	public static $validation = null;

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
			static::$validation = Validator::make(Input::all(), $rules);
			
			// if passes...
			if (static::$validation->passes())
			{
				// remember
				static::remember();
				
				// return
				return true;
			}
			else
			{
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
	 * Get validation object.
	 *
	 * @return	object
	 */
	public static function validation()
	{
		return static::$validation;
	}
	
	/**
	 * Plant a cookie prior to post, to see if cookies are disabled.
	 */
	public static function plant_cookie()
	{
		// In rare cases, people disable cookies which prevents you
		// from being able to store persistant data.  We can detect
		// these people by planting a cookie before the post and
		// then attempting to harvest it after.  Handle as needed.
	
		// plant test cookie
		Session::put(md5(get_called_class()), true);
		
	}
	
	/**
	 * Harvest a cookie after a post, to see if cookies are disabled.
	 */
	public static function harvest_cookie()
	{
		return Session::get(md5(get_called_class()), false);
	}
	
	/**
	 * Store latest input in data array (possibly persistant).
	 */
	protected static function remember()
	{
		// if remember...
		if (static::$remember)
		{
			// pull
			static::pull();
			
			// update
			static::fill(Input::all());
			
			// push
			static::push();
		}
		else
		{
			// update
			static::fill(Input::all());			
		}
	}
	
	/**
	 * Serialize data array and store in session.
	 */
	protected static function push()
	{
		// set remote data array
		Session::put(get_called_class(), serialize(static::$data));
	}
	
	/**
	 * Unserialize session and populate data array.
	 */
	protected static function pull()
	{
		// if session...
		if (Session::has(get_called_class()))
		{
			// get remote data array
			static::$data = unserialize(Session::get(get_called_class()));
		}
	}
	
	/**
	 * Forget persistant data array.
	 */
	public static function forget()
	{	
		// delete
		Session::forget(get_called_class());
		
		// Sometimes you'll want to forget the persistant data,
		// but then use it again on the final thank you page.
		// What we'll do here is flash for a final use.
		
		// flash (for one last use)
		Session::flash(get_called_class(), serialize(static::$data));
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
				static::$data[$field] = $input[$field];
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
		
		// pull
		if (empty(static::$data) and static::$remember) static::pull();
	
		// return
		return Input::old($field, static::get($field, $default));
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
	 * Get field error from validation object.
	 *
	 * @param	string	$field
	 * @param	string	$default
	 * @return	string
	 */
	public static function error($field, $default = null)
	{
		// Because this is based on a flashed validation object,
		// this method is only useful in a pre-post context.
	
		// load session
		$errors = Session::get('errors');
		
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
}