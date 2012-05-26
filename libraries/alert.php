<?php

/**
 * A simple class for handling form alerts.
 *
 * @package    Alert
 * @author     Scott Travis <scott.w.travis@gmail.com>
 * @link       http://github.com/swt83/laravel-form
 * @license    MIT License
 */

class Alert
{
	public static function set($string, $color)
	{
		Session::flash('alert', static::build($string, $color));
	}
	
	public static function get()
	{
		// load errors
		$errors = Session::get('errors');
		
		// if errors...
		if ($errors)
		{
	    	return static::build('<p>Form Errors:</p>'.HTML::ul($errors), 'red');
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
	
	protected static function build($content, $color)
	{
		return '<div class="alert '.$color.'">'.$content.'</div>';
	}
}