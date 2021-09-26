<?php

namespace system\classes\database;


abstract class DatabaseEntryAbs {
    
    public abstract function contains( $key );

	public abstract function get( $key, $default=null );

	public abstract function set($key, $val);

	public abstract function commit();

	public abstract function asArray();
    
}
?>