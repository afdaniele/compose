<?php

namespace system\utils;

use exceptions\URLRewriteException;
use \system\classes\Configuration;
use \system\classes\Core;

class URLrewrite {
    
    /**
     * @return bool
     * @throws URLRewriteException
     */
    public static function match(): bool {
        $request_uri = $_SERVER['REQUEST_URI'];
        $packages = Core::getPackagesList();
        // iterate through packages and their rules
        foreach ($packages as $package_name => $package_settings) {
            if (!array_key_exists('url_rewrite', $package_settings)) {
                continue;
            }
            // match package-specific rules
            foreach ($package_settings['url_rewrite'] as $rule_id => $rule) {
                $rule_pattern = $rule['pattern'];
                $rule_replace = $rule['replace'];
                // check if the rule matches
                $num_matches = preg_match_all($rule_pattern, $request_uri, $matches, PREG_SET_ORDER);
                if ($num_matches === false) {
                    throw new URLRewriteException("Error occurred while matching the rule '{$rule_id}'.");
                }
                // jump to the next rule if this did not match
                if ($num_matches == 0) {
                    continue;
                }
                // return if the pattern matches multiple sections the URI
                if ($num_matches != 1) {
                    throw new URLRewriteException("Rule '$rule_id' matches multiple times the URI '{$request_uri}'.");
                }
                // replace the placeholders
                $num_groups = count($matches[0]);
                $rewritten_uri = $rule_replace;
                for ($i = 1; $i < $num_groups; $i++) {
                    $search = sprintf("$%d", $i);
                    $rewritten_uri = str_replace($search, $matches[0][$i], $rewritten_uri);
                }
                
                $redirect_url = sprintf("%s%s", Configuration::$BASE, $rewritten_uri);
                
                // redirect
                if (ob_get_length()) {
                    ob_clean();
                }
                header("HTTP/1.1 301 Moved Permanently");
                header(
                    sprintf("Location: %s", $redirect_url),
                    true,
                    301
                );
                exit();
            }
        }
        //
        return true;
    }//match
    
}
