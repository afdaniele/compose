<?php
/**
 * PHPColor
 *
 * An simple colour class for PHP. Note: in documentation the 'colour' spelling is intentional.
 *
 * @package PHPColor
 * @author Rowan Manning
 * @copyright Copyright (c) 2010, Rowan Manning
 * @license Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
 * @link http://www.rowanmanning.co.uk/
 * @filesource
 */


namespace system\classes;



/**
 * PHPColor Color Class
 *
 * This is the base class used in all colour manipulations.
 *
 * @package PHPColor
 * @subpackage Classes
 * @category Classes
 * @author Rowan Manning
 * @link http://www.rowanmanning.co.uk/
 */
class Color {
    
    /**
     * The red value of the colour.
     *
     * @access protected.
     * @var integer
     */
    protected $red = 0;
    
    /**
     * The green value of the colour.
     *
     * @access protected.
     * @var integer
     */
    protected $green = 0;
    
    /**
     * The blue value of the colour.
     *
     * @access protected.
     * @var integer
     */
    protected $blue = 0;
    
    //============================================================
    // PROTECTED AND PRIVATE STATIC FUNCTIONS
    //============================================================
    
    /**
     * Fix a colour value (round and keep between 0 and 255).
     *
     * @access protected
     * @param integer $value The value to fix.
     * @return mixed
     */
    protected static function fix_rgb_value($value) {
        // returned fixed value
        return max(min(round((int)$value), 255), 0);
    }
    
    //============================================================
    // PUBLIC STATIC FUNCTIONS
    //============================================================
    
    /**
     * Convert red, green and blue values to a HEX code.
     *
     * @access public
     * @param $red The red value of the colour (between 0 and 255).
     * @param $green The green value of the colour (between 0 and 255).
     * @param $blue The blue value of the colour (between 0 and 255).
     * @return string Returns the HEX code representing the values given.
     */
    public static function rgb_to_hex($red, $green, $blue) {
        // convert rgb to hex
        $red = str_pad(dechex(self::fix_rgb_value($red)), 2, '0', STR_PAD_LEFT);
        $green = str_pad(dechex(self::fix_rgb_value($green)), 2, '0', STR_PAD_LEFT);
        $blue = str_pad(dechex(self::fix_rgb_value($blue)), 2, '0', STR_PAD_LEFT);
        
        // concat and return
        return $red . $green . $blue;
    }
    
    /**
     * Convert a HEX code into RGB.
     *
     * @access public
     * @param string $hex The HEX code to convert.
     * @return array Returns an associative array of values. The array will have 'red', 'green' and 'blue' keys.
     */
    public static function hex_to_rgb($hex) {
        // trim the '#' character
        $hex = ltrim((string)$hex, '#');
        
        // what kind of code do we have?
        if (strlen($hex) == 6) {
            
            // parse 6-character code into array
            $hex = array(
                'red' => $hex[0] . $hex[1],
                'green' => $hex[2] . $hex[3],
                'blue' => $hex[4] . $hex[5]
            );
            
        }
        else {
            if (strlen($hex) == 3) {
                
                // parse 3 character code into array
                $hex = array(
                    'red' => $hex[0] . $hex[0],
                    'green' => $hex[1] . $hex[1],
                    'blue' => $hex[2] . $hex[2]
                );
                
            }
            else {
                
                // invalid code... oops
                $hex = array(
                    'red' => 0,
                    'green' => 0,
                    'blue' => 0,
                );
                
            }
        }
        
        // set values
        $hex['red'] = self::fix_rgb_value(hexdec($hex['red']));
        $hex['green'] = self::fix_rgb_value(hexdec($hex['green']));
        $hex['blue'] = self::fix_rgb_value(hexdec($hex['blue']));
        
        // we're ok!
        return $hex;
    }
    
    /**
     * Calculate and return a range of colours between a start and end colour.
     *
     * @access public
     * @param Color|string $start The start colour. This can be a Color object or a HEX code as a string.
     * @param Color|string $end The end colour. This can be a Color object or a HEX code as a string.
     * @param integer $steps The number of colours to return (including start and end colours).
     * @return array Returns an array of Color objects.
     */
    public static function range($start, $end, $steps = 10) {
        // do we have a start colour?
        if (!is_a($start, 'Color')) {
            $startc = new Color();
            $startc->set_hex($start);
            $start = $startc;
        }
        
        // do we have an end colour?
        if (!is_a($end, 'Color')) {
            $endc = new Color();
            $endc->set_hex($end);
            $end = $endc;
        }
        
        // minus start and end from the steps
        $steps -= 2;
        
        // get the two colours
        $start_rgb = $start->get_array();
        $end_rgb = $end->get_array();
        
        // amount to increment on each step
        if ($start_rgb['red'] <= $end_rgb['red']) {
            $red_increment = -($start_rgb['red'] - $end_rgb['red']) / ($steps + 1);
        }
        else {
            $red_increment = ($end_rgb['red'] - $start_rgb['red']) / ($steps + 1);
        }
        if ($start_rgb['green'] <= $end_rgb['green']) {
            $green_increment = -($start_rgb['green'] - $end_rgb['green']) / ($steps + 1);
        }
        else {
            $green_increment = ($end_rgb['green'] - $start_rgb['green']) / ($steps + 1);
        }
        if ($start_rgb['blue'] <= $end_rgb['blue']) {
            $blue_increment = -($start_rgb['blue'] - $end_rgb['blue']) / ($steps + 1);
        }
        else {
            $blue_increment = ($end_rgb['blue'] - $start_rgb['blue']) / ($steps + 1);
        }
        
        // add the start colour to the array
        $range = array(
            $start
        );
        
        // get the range
        $steps_taken = 0;
        while ($steps_taken < $steps) {
            $steps_taken++;
            $start_rgb['red'] += $red_increment;
            $start_rgb['green'] += $green_increment;
            $start_rgb['blue'] += $blue_increment;
            $range[] = new Color($start_rgb['red'], $start_rgb['green'], $start_rgb['blue']);
        }
        
        // add the end colour to the array
        $range[] = $end;
        
        // return
        return $range;
    }
    
    /**
     * Mix two colours together.
     *
     * @access public
     * @param Color|string $color1 The first colour. This can be a Color object or a HEX code as a string.
     * @param Color|string $color2 The second colour. This can be a Color object or a HEX code as a string.
     * @return Color Returns the result of the mix as a new Color object.
     */
    public static function mix($color1, $color2) {
        // do we have a colour 1?
        if (!is_a($color1, 'Color')) {
            $color1c = new Color();
            $color1c->set_hex($color1);
            $color1 = $color1c;
        }
        
        // do we have a colour 2?
        if (!is_a($color2, 'Color')) {
            $color2c = new Color();
            $color2c->set_hex($color2);
            $color2 = $color2c;
        }
        
        // get arrays of colour values
        $rgb1 = $color1->get_array();
        $rgb2 = $color2->get_array();
        
        // mix colours into a new colour and return
        return new Color($rgb1['red'] + $rgb2['red'], $rgb1['green'] + $rgb2['green'], $rgb1['blue'] + $rgb2['blue']);
    }
    
    /**
     * Create Color from HEX string.
     *
     * @access public
     * @param string $hex The HEX string representing the colour.
     * @return Color Returns the resulting Color object.
     */
    public static function from_hex($hex) {
        $rgb = self::hex_to_rgb($hex);
        return new Color($rgb['red'], $rgb['green'], $rgb['blue']);
    }
    
    
    //============================================================
    // PUBLIC FUNCTIONS
    //============================================================
    
    /**
     * Class constructor.
     *
     * @access public
     * @param integer $red [optional] The red value of the colour (between 0 and 255). Default value is 0.
     * @param integer $green [optional] The green value of the colour (between 0 and 255). Default value is 0.
     * @param integer $blue [optional] The blue value of the colour (between 0 and 255). Default value is 0.
     */
    public function __construct($red = 0, $green = 0, $blue = 0) {
        // add values
        $this->red = self::fix_rgb_value($red);
        $this->green = self::fix_rgb_value($green);
        $this->blue = self::fix_rgb_value($blue);
    }
    
    /**
     * Set the colour's red, green and blue values
     *
     * @access public
     * @param integer $red [optional] The red value of the colour (between 0 and 255). Default value is 0.
     * @param integer $green [optional] The green value of the colour (between 0 and 255). Default value is 0.
     * @param integer $blue [optional] The blue value of the colour (between 0 and 255). Default value is 0.
     */
    public function set($red = 0, $green = 0, $blue = 0) {
        // add values
        $this->red = self::fix_rgb_value($red);
        $this->green = self::fix_rgb_value($green);
        $this->blue = self::fix_rgb_value($blue);
    }
    
    /**
     * Modify the colour's red, green and blue values.
     *
     * @access public
     * @param integer $red [optional] The amount to modify the red value of the colour (between -255 and 255). Default value is 0.
     * @param integer $green [optional] The amount to modify the green value of the colour (between -255 and 255). Default value is 0.
     * @param integer $blue [optional] The amount to modify the blue value of the colour (between -255 and 255). Default value is 0.
     */
    public function modify($red = 0, $green = 0, $blue = 0) {
        // add values
        $this->red = self::fix_rgb_value($this->red + (int)$red);
        $this->green = self::fix_rgb_value($this->green + (int)$green);
        $this->blue = self::fix_rgb_value($this->blue + (int)$blue);
    }
    
    /**
     * Randomise red, green and blue values of the colour.
     *
     * @access public
     */
    public function randomise() {
        // randomise values
        $this->red = rand(0, 255);
        $this->green = rand(0, 255);
        $this->blue = rand(0, 255);
    }
    
    /**
     * Set the red, green and blue values of the colour with a HEX code.
     *
     * @access public
     * @param string $hex The HEX code to set. This must be 3 or 6 characters in length and can optionally start with a '#'.
     * @return boolean Returns TRUE on success.
     */
    public function set_hex($hex) {
        // get the rgb values
        $rgb = self::hex_to_rgb($hex);
        
        // set values
        $this->red = $rgb['red'];
        $this->green = $rgb['green'];
        $this->blue = $rgb['blue'];
        
        // we're ok!
        return true;
    }
    
    /**
     * Get the red, green and blue values of the colour as an array.
     *
     * @access public
     * @return array Returns an associative array of values. The array will have 'red', 'green' and 'blue' keys.
     */
    public function get_array() {
        // return the array
        return array(
            'red' => $this->red,
            'green' => $this->green,
            'blue' => $this->blue
        );
    }
    
    /**
     * Get the HEX code that represents the colour.
     *
     * @access public
     * @param boolean $hash Whether to prepend the HEX code with a '#' character. Default value is TRUE.
     * @return string Returns the HEX code.
     */
    public function get_hex($hash = true) {
        return ($hash ? '#' : '') . self::rgb_to_hex($this->red, $this->green, $this->blue);
    }
    
    /**
     * Scales the color uniformly across the channels.
     *
     * @access public
     * @param float $alpha Scaling factor to apply.
     * @return Color Returns the scaled Color.
     */
    public function scale($alpha) {
        $red = self::fix_rgb_value(intval($this->red * $alpha));
        $green = self::fix_rgb_value(intval($this->green * $alpha));
        $blue = self::fix_rgb_value(intval($this->blue * $alpha));
        // wrap result in a Color object
        return new Color($red, $green, $blue);
    }
    
    /**
     * Darkens the color.
     *
     * @access public
     * @param float $alpha Intensity of the darken filter between 0 and 1.
     * @return Color Returns the darkened Color.
     */
    public function darken($alpha) {
        return $this->scale(1.0 - $alpha);
    }
    
    /**
     * Lightens the color.
     *
     * @access public
     * @param float $alpha Intensity of the lighten filter between 0 and 1.
     * @return Color Returns the lightened Color.
     */
    public function lighten($alpha) {
        return $this->scale(1.0 + $alpha);
    }
    
    //============================================================
    // MAGIC METHODS
    //============================================================
    
    /**
     * get magic method.
     *
     * @param string $name The name of the variable to get.
     * @return mixed Returns the value of the requested variable.
     */
    public function __get($name) {
        // get variable
        switch ($name) {
            case 'red':
                return $this->red;
                break;
            case 'green':
                return $this->green;
                break;
            case 'blue':
                return $this->blue;
                break;
            case 'hex':
                return $this->get_hex(true);
                break;
            case 'rgb':
                return $this->get_array();
                break;
        }
        
        // error
        $trace = debug_backtrace();
        trigger_error('Undefined property: ' . __CLASS__ . '::$' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'] . ',', E_USER_NOTICE);
        return null;
    }
    
    /**
     * set magic method.
     *
     * @param string $name The name of the variable to set.
     * @param mixed $value The value to set the variable to.
     */
    public function __set($name, $value) {
        // get variable
        switch ($name) {
            case 'red':
                $this->red = self::fix_rgb_value($value);
                return;
                break;
            case 'green':
                $this->green = self::fix_rgb_value($value);
                return;
                break;
            case 'blue':
                $this->blue = self::fix_rgb_value($value);
                return;
                break;
            case 'hex':
                $this->set_hex($value);
                return;
                break;
        }
        
        // error
        $trace = debug_backtrace();
        trigger_error('Undefined property: ' . __CLASS__ . '::$' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'] . ',', E_USER_NOTICE);
        return;
    }
    
    /**
     * toString magic method.
     *
     * @return string Returns the HEX code that represents the colour.
     */
    public function __toString() {
        return $this->get_hex(true);
    }
    
}
