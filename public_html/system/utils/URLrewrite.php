<?php

namespace system\utils;

use \system\classes\Configuration;
use \system\classes\Core;

class URLrewrite{

    public static function match(){
        $request_uri = $_SERVER['REQUEST_URI'];
        $packages = Core::getPackagesList();
        // temporary vars
        $matched = false;
        $rewritten_uri = "";
        // iterate through packages and their rules
        foreach( $packages as $package_name => $package_settings ){
            if( !in_array('url_rewrite', $package_settings) ) continue;
            // match package-specific rules
            foreach( $package_settings['url_rewrite'] as $rule_id => $rule ){
                $rule_pattern = $rule['pattern'];
                $rule_replace = $rule['replace'];
                // check if the rule matches
                $num_matches = preg_match_all($rule_pattern, $request_uri, $matches, PREG_SET_ORDER);
                if( $num_matches === false )
                    return [
                        'success' => false,
                        'data' => sprintf('URL Rewrite: Error occurred while matching the rule `%s`', $rule_id)
                    ];
                // jump to the next rule if this did not match
                if( $num_matches == 0 ) continue;
                // return if the pattern matches multiple sections the URI
                if( $num_matches != 1 )
                    return [
                        'success' => false,
                        'data' => sprintf('URL Rewrite: The `%s` matches multiple times the URI `%s`', $rule_id, $request_uri)
                    ];
                // replace the placeholders
                $num_groups = count( $matches[0] );
                $rewritten_uri = $rule_replace;
                for( $i = 1; $i < $num_groups; $i++ ){
                    $search = sprintf("$%d", $i);
                    $rewritten_uri = str_replace( $search, $matches[0][$i], $rewritten_uri );
                    $matched = true;
                }

                $redirect_url = sprintf("%s%s", Configuration::$BASE_URL, $rewritten_uri);

                // redirect
                if (ob_get_length()) ob_clean();
                header( "HTTP/1.1 301 Moved Permanently" );
                header(
                    sprintf("Location: %s", $redirect_url),
                    true,
                    301
                );
                exit();
            }
        }
        //
        return [
            'success' => true,
            'data' => null
        ];
    }//match

}

?>
