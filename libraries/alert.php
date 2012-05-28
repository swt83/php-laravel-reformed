<?php

/**
 * A simple class for handling alerts w/ forms.
 *
 * @package    Alert
 * @author     Scott Travis <scott.w.travis@gmail.com>
 * @link       http://github.com/swt83/laravel-form
 * @license    MIT License
 */

class Alert
{
	/**
	 * Set alert box value.
	 */
	public static function set($string, $color)
	{
		Session::flash('alert', static::build($string, $color));
	}
	
	/**
	 * Get alert box value.
	 */
	public static function get()
	{
		// load errors
		$errors = Session::get('errors');
		
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
	    	return static::build('<p>Form Errors:</p>'.HTML::ul($clean_errors), 'red');
    	}
    	
    	// if not errors, but existing alert...
    	elseif (Session::get('alert'))
    	{
    		// return
    		return Session::get('alert');
    	}
    	
    	// if nothing...
    	else
    	{
    		// return
    		return null;
    	}
	}
	
	/**
	 * Helper function.
	 */
	protected static function build($content, $color)
	{
		return '<div class="alert '.$color.'">'.$content.'</div>';
	}
}