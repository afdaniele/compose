<?php

namespace system\classes;

// fast cache system
require_once __DIR__ . '/phpfastcache/lib/Phpfastcache/Autoload/Autoload.php';

use Exception;
use Phpfastcache\CacheManager;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Entities\DriverStatistic;


abstract class Cache {
    
    // private/protected attributes
    protected static ExtendedCacheItemPoolInterface|null $cache = null;
    
    
    // public methods
    
    public static function init() {
        // enable cache
        try {
            self::$cache = CacheManager::getInstance(Configuration::$CACHE_SYSTEM);
        } catch (Exception) {
            try {
                self::$cache = CacheManager::getInstance('files');
            } catch (Exception) {}
        }
    }//init
    
    public static function enabled(): bool {
        return !is_null(self::$cache);
    }//enabled
    
    public static function get(string $key, mixed $default = null): mixed {
        if (self::enabled()) {
            $cache_item = self::$cache->getItem($key);
            if ($cache_item->isHit()) {
                return $cache_item->get();
            }
        }
        return $default;
    }//get
    
    public static function has(string $key): bool {
        if (self::enabled()) {
            return self::$cache->hasItem($key);
        }
        return false;
    }//has
    
    public static function delete(string $key): bool {
        if (self::enabled()) {
            return self::$cache->deleteItem($key);
        }
    }//delete
    
    public static function getStats(): DriverStatistic|null {
        if (self::enabled()) {
            return self::$cache->getStats();
        }
        return null;
    }//getStats
    
    public static function clearAll(): bool {
        if (self::enabled()) {
            return self::$cache->clear();
        }
        return false;
    }//clearAll
    
    
    // protected methods
    
    protected static function getCacheItem($key): mixed {
        if (self::enabled()) {
            return self::$cache->getItem($key);
        }
        return null;
    }//getCacheItem
    
}//Cache


class CacheProxy extends Cache {
    
    // private/protected attributes
    private string $group_name;
    
    // constructor
    public function __construct($group_name) {
        $this->group_name = $group_name;
    }//__construct
    
    
    // public methods
    
    public function set(string $key, mixed $value, int $ttl = null): bool {
        if (self::enabled()) {
            $cache_item = Cache::getCacheItem($key);
            $cache_item->set($value)->addTag($this->group_name)->expiresAfter($ttl);
            return Cache::$cache->save($cache_item);
        }
        return false;
    }//set
    
    public function clear(): bool {
        if (self::enabled()) {
            return Cache::$cache->deleteItemsByTag($this->group_name);
        }
        return false;
    }//clear
    
}//CacheProxy
