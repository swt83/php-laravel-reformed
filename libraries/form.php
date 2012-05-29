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
	 * Remember latest input in data array (possibly persistant).
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
			// pull remote data array
			static::$data = unserialize(Session::get(get_called_class()));
		}
	}
	
	/**
	 * Clear all fields from data array.
	 */
	public static function forget()
	{	
		// forget remote data
		Session::forget(get_called_class());
		
		// forget local data
		static::$data = array();
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
	 * Check if field exists in data array.
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
	 * Get field from data array.
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
	 * Get field from old input or data array.
	 *
	 * @param	string	$field
	 * @param	string	$default
	 * @return	string
	 */
	public static function populate($field, $default = null)
	{
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