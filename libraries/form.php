<?php

/**
 * A LaravelPHP package for working w/ form models.
 *
 * @package    FormModel
 * @author     Shawn McCool
 * @author     Scott Travis <scott.w.travis@gmail.com>
 * @link       http://github.com/swt83/laravel-form
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
	
	protected static function serialize()
	{
		Session::put(get_called_class(), Crypter::encrypt(serialize(static::$data)));
	}
	
	protected static function unserialize()
	{
		if (Session::has(get_called_class()))
		{
			static::$data = unserialize(Crypter::decrypt(Session::get(get_called_class())));
		}
	}
	
	public static function remember($fields = null, $input = null)
	{
		// check error
		if (!is_array($fields) and !is_null($fields))
		{
			return false;
		}
		
		// get fields
		if (is_null($fields))
		{
			$fields = array_keys(Input::all());
		}

		// get values
		if (is_null($input))
		{
			$input = Input::all();
		}

		// load existing
		if (empty(static::$data))
		{
			static::unserialize();
		}

		// save values
		foreach ($fields as $field)
		{
			if (Input::has($field))
			{
				static::$data[$field] = Input::get($field);
			}
			else
			{
				static::$data[$field] = '';
			}
		}

		// serialize
		static::serialize();
	}
	
	public static function forget()
	{
		Session::forget(get_called_class());
	}
	
	public static function has($field)
	{
		return isset(static::$data[$field]) and !empty(static::$data[$field]);
	}

	public static function get($field, $default = null)
	{
		if (empty(static::$data))
		{
			static::unserialize();
		}
		
		return static::has($field) ? static::$data[$field] : $default;
	}
	
	public static function all()
	{
		return static::$data;
	}
	
	public static function populate($field, $default = null)
	{
		if (empty(static::$data))
		{
			static::unserialize();
		}

		return Input::old($field, static::get($field, $default));
	}
	
	public static function validation()
	{
		return static::$validation;
	}
	
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