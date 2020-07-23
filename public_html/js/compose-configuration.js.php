<?php
use \system\classes\Configuration;
?>

<script type="text/javascript">
  var Configuration = (function () {
    var __data;

    function createInstance() {
      return {
        <?php
        printf('"/core/%s": "%s",', 'TIMEZONE', Configuration::$TIMEZONE);
        printf('"/core/%s": "%s",', 'GMT', Configuration::$GMT);
        printf('"/core/%s": "%s",', 'DEBUG', Configuration::$DEBUG);

        printf('"/core/%s": "%s",', 'BASE', Configuration::$BASE);
        printf('"/core/%s": "%s",', 'PAGE', Configuration::$PAGE);
        printf('"/core/%s": "%s",', 'ACTION', Configuration::$ACTION);
        printf('"/core/%s": "%s",', 'ARG1', Configuration::$ARG1);
        printf('"/core/%s": "%s",', 'ARG2', Configuration::$ARG2);
        printf('"/core/%s": "%s",', 'TOKEN', Configuration::$TOKEN);

        printf('"/core/%s": "%s",', 'IS_MOBILE', Configuration::$IS_MOBILE);

        printf('"/core/%s": "%s",', 'CACHE_SYSTEM', Configuration::$CACHE_SYSTEM);
        printf('"/core/%s": "%s",', 'WEBAPI_VERSION', Configuration::$WEBAPI_VERSION);
        printf('"/core/%s": "%s",', 'ASSETS_STORE_URL', Configuration::$ASSETS_STORE_URL);
        printf('"/core/%s": "%s",', 'ASSETS_STORE_VERSION', Configuration::$ASSETS_STORE_VERSION);
        ?>
      };
    }

    return {
      as_array: function () {
        if (!__data) {
          __data = createInstance();
        }
        return __data;
      },
      get: function (pack, key, default_val) {
        if (!__data) {
          __data = createInstance();
        }
        key = "/{0}/{1}".format(pack, key);
        if (default_val == undefined && __data[key] == undefined) {
          throw "Key {0} not found!".format(key);
        }
        if (__data[key] == undefined) {
          return default_val;
        }
        return __data[key];
      },
      set: function (pack, key, value) {
        if (!__data) {
          __data = createInstance();
        }
        key = "/{0}/{1}".format(pack, key);
        __data[key] = value;
      }
    };
  })();
</script>
