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
	public static $validation = false;

	public static function is_valid($fields = null, $input = null)
	{
		// check error
		if (!is_array($fields) and !is_null($fields))
		{
			return false;
		}

		// get input
		if (is_null($input))
		{
			$input = Input::all();
		}

		// get rules
		if (is_array($fields))
		{
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
			$rules = static::$rules;
		}

		// catch error
		if (empty($rules))
		{
			return true;
		}
	
		// validate
		static::$validation = Validator::make(Input::all(), static::$rules);
		
		// return
		return static::$validation->passes();
	}
	
	/**
	 * Serialize data and store in session.
	 */
	protected static function push()
	{
		Session::put(get_called_class(), serialize(static::$data));
	}
	
	/**
	 * Unserialize session and populate data array.
	 */
	protected static function pull()
	{
		if (Session::has(get_called_class()))
		{
			static::$data = unserialize(Session::get(get_called_class()));
		}
	}
	
	/**
	 * Save data array.
	 */
	public static function remember()
	{
		// remember data
		static::push();
	}
	
	/**
	 * Unsave data array.
	 */
	public static function forget()
	{
		// forget data
		Session::forget(get_called_class());
	}
	
	/**
	 * Fill data fields.
	 */
	public static function fill($array)
	{
		foreach ($array as $field => $value)
		{
			static::$data[$field] = $value;
		}
	}
	
	/**
	 * Set data field.
	 */
	public static function set($field, $value)
	{
		// set new value
		static::$data[$field] = $value;
	}
	
	/**
	 * Check for data field.
	 */
	public static function has($field)
	{
		// return has field
		return isset(static::$data[$field]) and !empty(static::$data[$field]);
	}
	
	/**
	 * Get data field.
	 */
	public static function get($field, $default = null)
	{
		// return value
		return static::has($field) ? static::$data[$field] : $default;
	}
	
	/**
	 * Get all data fields.
	 */
	public static function all()
	{
		// return data array
		return static::$data;
	}
	
	/**
	 * Get best field value.
	 */
	public static function populate($field, $default = null)
	{
		// return best value
		return Input::old($field, static::get($field, $default));
	}
	
	/**
	 * Get validation object.
	 */
	public static function validation()
	{
		return static::$validation;
	}
	
	/**
	 * Get errors array.
	 */
	public static function errors()
	{
		$errors = Session::get('errors');
		
		if ($errors)
		{
			return $errors->all();
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Get field error.
	 */
	public static function error($field, $default = null)
	{
		$errors = Session::get('errors');
		
		if ($errors)
		{
			return $errors->has($field) ? $errors->first($field) : $default;
		}
		else
		{
			return $default;
		}
	}
}