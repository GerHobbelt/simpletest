<?php
/**
 *  Base include file for SimpleTest.
 *  @package    SimpleTest
 *  @subpackage WebserverDefaults
 *  @version    $Id$
 */

/**
 *    Stores, detects and reports the server URI which is used as default for all web tests (GET, PUT, etc. requests)
 *    @package SimpleTest
 *    @subpackage WebserverDefaults
 */
class WebserverDefaults {
    protected static $default_server_url = null;

    /**
     *    A support method which delivers the URL (with scheme and authority as per RFC3986) to the sample test site, which' location is by default based on the currently running script.
     *
     *    @param string $auth_str      The optional userinfo (cf. RFC3986 section 3.2) part of the URI.
     *    @return string               The URL as currently configured.
     *    @access public
     */
    static function getServerUrl($auth_str = null)
    {
        global $_SERVER;

        if (empty(self::$default_server_url))
        {
            self::setServerUrl($auth_str);
        }
        $url = self::$default_server_url;

        if (!is_null($auth_str))
        {
            $url = self::completeURL($url, null, $auth_str);
        }
        return $url;
    }

    /**
     *    A support method which sets the URL (with scheme and authority as per RFC3986) to the sample test site, which' location is by default based on the currently running script.
     *
     *    @param string $auth_str      The optional userinfo (cf. RFC3986 section 3.2) part of the URI.
     *    @param string $path          Either the optional complete URL to the test site or the (relative to the starting script) path to the test sample site root directory. Default: './site/'
     *    @return string               The URL as currently configured.
     *    @access public
     */
    static function setServerUrl($auth_str = null, $path = 'site/')
    {
        global $_SERVER;

        if (empty(self::$default_server_url))
        {
            $url = parse_url(strtr($path, '\\', '/'));

            // keep path? when it's a relative path and assumed relative to the running script.
            $path_is_relative = (!empty($url['path']) && empty($url['host']) && $url['path']{0} != '/' && substr($path, 0, strlen($url['path'])) == $url['path']);
            // else: path is assumed to have specified a (more or less) fully qualified URL; do NOT append it to the default location as well!

            if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['SCRIPT_NAME']))
            {
                $dflt = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
                $dflt .= '://' . $_SERVER['HTTP_HOST'] . (!empty($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : '');
                $dflt .= strtr(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']), '\\', '/') . ($path_is_relative ? $path : '');
            }
            else
            {
                $dflt = 'http://www.lastcraft.com/test/';
            }
            $url = self::completeURL(($path_is_relative ? $dflt : $path), $dflt, $auth_str);

            self::$default_server_url = $url;
        }
        else 
        {
            $url = self::$default_server_url;
        }
        return $url;
    }

    /**
     *    A support method which delivers the fully qualified URL (with
     *    scheme and authority as per RFC3986) given a path and an optional
     *    authentication string; the base is assumed to be the 'default
     *    server URL' as obtained through @see WebserverDefaults::getServerURL().
     *
     *    @param string $path          A more or less complete URL  or an absolute or relative path.
     *    @param string $dflt_url      A fully qualified URL which is to be used as a basis for completing the given path.
     *    @param string $auth_str      The optional userinfo (cf. RFC3986 section 3.2) part of the URI.
     *    @return string               The completed URL.
     *    @access public
     */
    static function completeURL($path, $dflt_url = null, $auth_str = null) {
        global $_SERVER;

        if (empty($dflt_url))
        {
            if (empty(self::$default_server_url))
            {
                $dflt_url = 'http://www.lastcraft.com/test/';
            }
            else
            {
                $dflt_url = self::$default_server_url;
            }
        }

        if (!empty($path))
        {
            $url = parse_url(strtr($path, '\\', '/'));
        }
        else
        {
            $url = array();
        }

        // keep path? when it's a relative path and assumed relative to the running script.
        $path_is_relative = (!empty($url['path']) && empty($url['host']) && $url['path']{0} != '/' && substr($path, 0, strlen($url['path'])) == $url['path']);

        $dflt = parse_url($dflt_url);

        if (empty($url['scheme'])) {
            $url['scheme'] = $dflt['scheme'];
        }
        if (empty($url['host'])) {
            $url['host'] = $dflt['host'];
        }
        // fix parse_url() hickup for file:// scheme on Windows:
        if ($url['scheme'] == 'file' && isset($_SERVER['WINDIR'])) {
            $url['host'] .= ':';
        }
        if (empty($url['port']) && !empty($dflt['port'])) {
            $url['port'] = $dflt['port'];
        }
        if (empty($url['custom_port']) && !empty($dflt['custom_port'])) {
            $url['custom_port'] = $dflt['custom_port'];
        }
        if (empty($url['path']) || $path_is_relative) {
            $url['path'] = $dflt['path'] . ($path_is_relative ? $url['path'] : '');
        }
        $has_user_cred = false;
        if (empty($url['user']) && !empty($dflt['user'])) {
            $url['user'] = $dflt['user'];
            $has_user_cred = true;
        }
        else if (!empty($url['user'])) {
            $has_user_cred = true;
        }
        if (empty($url['pass']) && !empty($dflt['pass'])) {
            $url['pass'] = $dflt['pass'];
            $has_user_cred = true;
        }
        else if (!empty($url['pass'])) {
            $has_user_cred = true;
        }
        // when auth_str is specifically specified as empty, the caller doesn't want to see any credentials in the result:
        if (!is_null($auth_str) && empty($auth_str))
        {
            $has_user_cred = false;
        }
        else if (!empty($auth_str))
        {
            // override the user/auth values:
            $auth = explode(':', $auth_str, 2);
            $url['user'] = $auth[0];
            $url['pass'] = $auth[1];
            $has_user_cred = true;
        }

        $auth_str = '';
        if ($has_user_cred)
        {
            if (!empty($url['user'])) {
                $auth_str = $url['user'];
            }
            if (!empty($url['pass'])) {
                $auth_str .= ':' . $url['pass'];
            }
            if (!empty($auth_str))
            {
                $auth_str .= '@';
            }
        }

        $rv = $url['scheme'] . '://' . $auth_str . $url['host'] . (!empty($url['custom_port']) ? ':' . $url['custom_port'] : '') . $url['path'];
        if (!empty($url['query'])) {
            $rv .= '?' . $url['query'];
        }
        if (!empty($url['fragment'])) {
            $rv .= '#' . $url['fragment'];
        }

        return $rv;
    }
}
