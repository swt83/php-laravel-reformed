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
     * @var $data   array
     */
    public static $data = array();
    
    /**
     * Rules to govern fields.
     *
     * @var $rules  array
     */
    public static $rules = array();
    
    /**
     * Customized error messages.
     *
     * @var $messages   array
     */
    public static $messages = array();
    
    /**
     * Persistant data mode.
     *
     * @var $remember   boolean
     */
    public static $remember = false;
    
    /**
     * Validates form, sets all input to data array.
     *
     * @param   array   $fields
     * @return  bool
     */
    public static function is_valid($fields = null)
    {
        // capture input
        static::capture();
        
        // The user may only want to validate certain fields,
        // in which case we will only load the corresponding rules.

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
            // validate input
            $validation = Validator::make(static::all(), $rules, static::$messages);
            
            // if passes...
            if ($validation->passes())
            {
                // remember
                static::remember();
                
                // return
                return true;
            }
            else
            {
                // We're going to store errors in our own session so that
                // the get_alert() method can retreive them automatically
                // later.  We don't need to use with_errors() on redirects.
                
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
     * Capture input and store in object.
     *
     * @return  void
     */
    public static function capture()
    {
        static::fill(Input::all());
    }

    /**
     * Load session and store in object (only directly used w/out is_valid() method).
     *
     * @return  void
     */
    public static function recall()
    {
        // if session exists...
        if (Session::has(get_called_class()))
        {
            // unpack from session
            static::$data = unserialize(Crypter::decrypt(Session::get(get_called_class())));
        }
    }
    
    /**
     * Take object data and store in session.
     *
     * @return  void
     */
    public static function remember()
    {   
        // if remember mode...
        if (static::$remember)
        {   
            // grab current values
            $existing = static::all();
        
            // recall previous values
            static::recall();
            
            // merge previous and current
            static::fill($existing);

            // save to session
            Session::put(get_called_class(), Crypter::encrypt(serialize(static::$data)));
        }
    }
    
    /**
     * Save object data, in session, for single pageload.
     *
     * @return  void
     */
    public static function flash()
    {   
        // forget
        static::forget();
        
        // flash
        Session::flash(get_called_class(), Crypter::encrypt(serialize(static::$data)));
    }
    
    /**
     * Delete the session data.
     *
     * @return  void
     */
    public static function forget()
    {
        // erase session
        Session::forget(get_called_class());
    }
    
    /**
     * Fill data array w/ values.
     *
     * @param   array   $input
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
     * @return  array
     */
    public static function all()
    {   
        return static::$data;
    }
    
    /**
     * Set field value.
     *
     * @param   string  $field
     * @param   string  $value
     * @return  void
     */
    public static function set($field, $value)
    {
        static::$data[$field] = $value;
    }
    
    /**
     * Check if field value exists.
     *
     * @param   string  $field
     * @return  bool
     */
    public static function has($field)
    {
        // recall
        if (empty(static::$data)) static::recall();
    
        // return
        return isset(static::$data[$field]) and !empty(static::$data[$field]);
    }
    
    /**
     * Get field value from data array.
     *
     * @param   string  $field
     * @param   string  $default
     * @return  string
     */
    public static function get($field, $default = null)
    {
        return static::has($field) ? static::$data[$field] : $default;
    }
    
    /**
     * Get true/false for array value from data array.
     *
     * @param   string  $field
     * @param   string  $option
     * @return  boolean
     */
    public static function get_array($field, $option)
    {    
        // get input value
        $input = static::get($field, array());
        
        // catch blank
        if ($input === '') $input = array();
        
        // check in_array
        return in_array($option, $input) ? true : false;
    }

    /**
     * Get array value from data array.
     *
     * @param   string  $field
     * @param   string  $default
     * @return  boolean
     */
    public static function get_array_value($field, $default = null)
    {
        $pass = false;
        $value = static::get($field);
        if (is_array($value))
        {
            if (isset($value[0]))
            {
                $pass = true;
                $value = $value[0];
            }
        }

        return $pass ? $value : $default;
    }
    
    /**
     * Get field value from input array.
     *
     * @param   string  $field
     * @param   string  $default
     * @return  string
     */
    public static function populate($field, $default = null)
    {
        return Input::old($field, static::get($field, $default));
    }
    
    /**
     * Get true/false for array value from input array.
     *
     * @param   string  $field
     * @param   string  $option
     * @return  boolean
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
     * Get array value from data array.
     *
     * @param   string  $field
     * @param   string  $default
     * @return  boolean
     */
    public static function populate_array_value($field, $default = null)
    {    
        $pass = false;
        $value = static::populate($field);
        if (is_array($value))
        {
            if (isset($value[0]))
            {
                $pass = true;
                $value = $value[0];
            }
        }

        return $pass ? $value : $default;
    }
    
    /**
     * Get field error from validation object.
     *
     * @param   string  $field
     * @param   string  $default
     * @return  string
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
     * @param   string  $string
     * @param   string  $color
     * @return  void
     */
    public static function set_alert($string = null, $color = 'red')
    {
        Session::flash('alert_'.get_called_class(), array('string' => $string, 'color' => $color));
    }
    
    /**
     * Print alert box (and override current value).
     *
     * @param   string  $string
     * @param   string  $color
     * @return  string
     */
    public static function get_alert($string = null, $color = 'red')
    {
        // Regardless of what alert may have been set,
        // this method will return the list of errors if
        // there were any.
        
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
    
}