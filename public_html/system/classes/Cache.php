<?php

namespace system\classes;

// fast cache system
require_once __DIR__.'/phpfastcache/lib/Phpfastcache/Autoload/Autoload.php';

use Phpfastcache\CacheManager;

abstract class Cache{

    // private/protected attributes
    protected static $cache = null;


    // public methods

    public static function init(){
        // enable cache
    	try{
    		self::$cache = CacheManager::getInstance(Configuration::$CACHE_SYSTEM);
    	}catch(Exception $e){
    		self::$cache = null;
    	}
    }//init

    public static function enabled(){
        return !is_null(self::$cache);
    }//enabled

    public function get($key, $default=null){
        if( self::enabled() ){
            $cache_item = self::$cache->getItem($key);
            if( $cache_item->isHit() )
                return $cache_item->get();
        }
        return $default;
    }//get

    public function has($key){
        if( self::enabled() )
            return self::$cache->hasItem($key);
        return false;
    }//has

    public function delete($key){
        if( self::enabled() )
            return self::$cache->deleteItem($key);
    }//delete

    public function getStats(){
        if( self::enabled() ){
            return self::$cache->getStats();
        }
        return [];
    }//getStats

    public function clearAll(){
        if( self::enabled() )
            return self::$cache->clear();
        return false;
    }//clearAll


    // protected methods

    protected function getCacheItem($key){
        if( self::enabled() )
            return self::$cache->getItem($key);
        return null;
    }//getCacheItem

}//Cache


class CacheProxy extends Cache{

    // private/protected attributes
    private $group_name = null;

    // constructor
    public function __construct( $group_name ){
        $this->group_name = $group_name;
    }//__construct


    // public methods

    public function set($key, $value, $ttl=null){
        if( self::enabled() ){
            $cache_item = Cache::getCacheItem( $key );
            $cache_item->set( $value )->addTag( $this->group_name )->expiresAfter( $ttl );
            return Cache::$cache->save( $cache_item );
        }
        return false;
    }//set

    public function clear(){
        if( self::enabled() ){
            return Cache::$cache->deleteItemsByTag( $this->group_name );
        }
        return false;
    }//clear

}//CacheProxy

?>
