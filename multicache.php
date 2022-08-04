<?php
/**
 *
 * @version 1.0.1.5
 * @package com_multicache
 * @copyright Copyright (C) Multicache.org 2015. All rights reserved.
 * @license GNU GENERAL PUBLIC LICENSE see LICENSE.txt - 
 * @author Wayne DSouza <consulting@OnlineMarketingConsultants.in> - http://OnlineMarketingConsultants.in
 */
// no direct access
defined('_JEXEC') or die();
$JVersion = new JVersion();
$version = $JVersion->getShortVersion();
!defined('MULTICACHEJOOMLAVERSION') && define('MULTICACHEJOOMLAVERSION' , $version);
// jimport('joomla.plugin.plugin');

// JLoader::register('JCache', JPATH_ROOT . '/administrator/components/com_multicache/lib/cache.php',true);
JLoader::register('JCacheControllerPage', JPATH_ROOT . '/administrator/components/com_multicache/lib/page.php', true);
//trial feb13 2016
JLoader::register('MulticacheCSSOptimize', JPATH_ROOT . '/administrator/components/com_multicache/lib/compression_libs/multicachecssoptimize.php');
JLoader::register('MulticacheJSOptimize', JPATH_ROOT . '/administrator/components/com_multicache/lib/compression_libs/multicachejsoptimize.php');
//endtrial
JLoader::register('JsStrategy', JPATH_ROOT . '/administrator/components/com_multicache/lib/jscachestrategy.php');
JLoader::register('MulticacheHtmlMinify', JPATH_ROOT . '/administrator/components/com_multicache/lib/compression_libs/multicachehtmlminify.php');
JLoader::register('JsStrategySimControl', JPATH_ROOT . '/administrator/components/com_multicache/lib/jscachestrategy_simcontrol.php');
//customization hacks
if(file_exists(dirname(__FILE__) .'/multicache_mobilestrategy.php'))
{
	define('MULTICACHEEXTRAORDINARYMOBILE' ,true);
	require_once (dirname(__FILE__) .'/multicache_mobilestrategy.php');
}
if(file_exists(dirname(__FILE__) .'/multicache_extraordinary.php'))
{
	define('MULTICACHEEXTRAORDINARY' ,true);
	require_once (dirname(__FILE__) .'/multicache_extraordinary.php');
}
if(file_exists(dirname(__FILE__) .'/multicache_extraordinary_image.php'))
{
	define('MULTICACHEEXTRAORDINARYIMAGE' ,true);
	require_once (dirname(__FILE__) .'/multicache_extraordinary_image.php');
}
if(file_exists(dirname(__FILE__) .'/multicache_mobile_nolazy.php'))
{
	define('MULTICACHEIMAGENOLAZY' ,true);
	require_once (dirname(__FILE__) .'/multicache_mobile_nolazy.php');
}
//detox start
//js start
if(file_exists(dirname(__FILE__) .'/multicache_detox_orphanedjs.php'))
{
	define('MULTICACHEDETOXORPHANEDJS' ,true);
	require_once (dirname(__FILE__) .'/multicache_detox_orphanedjs.php');
}
//js end

//css start
if(file_exists(dirname(__FILE__) .'/multicache_detox_orphanedcss.php'))
{
	define('MULTICACHEDETOXORPHANEDCSS' ,true);
	require_once (dirname(__FILE__) .'/multicache_detox_orphanedcss.php');
}
//css end
//detox end

// require_once (JPATH_ROOT . '/administrator/components/com_multicache/lib/phpquerymulticache.php');/*abandoned due to memory leaks*/
/*
 * JLog::addLogger(array(
 * 'text_file' => 'errors.php'
 * ), JLog::ALL, array(
 * 'error'
 * ));
 */
class PlgSystemMulticache extends JPlugin
{

	protected $uri = null;
	
	protected $current = null;
	
	protected $css_groupsasync = null;
	
    protected static $_signatures = null;

    protected static $_signatures_css = null;

    protected static $_loadsections = null;

    protected static $_loadsections_css = null;

    protected static $css_switch = null;

    protected static $js_switch = null;

    protected static $dontmove_js_p = null;

    protected static $dontmove_urls_js_p = null;

    protected static $allow_multiple_orphaned = null;
    
    protected static $mobile_strategy_replace_inlinestyle = null;
    
    protected static $master_inlinecss_buffer_mobile = null;
    
    protected static $_img_urlstringexclude = null;

    protected $js_simulation = null;

    protected $js_advanced = null;

    protected $_debug_mode = null;

    protected $_minify_html = null;

    protected $_img_tweaks = null;

    protected static $_js_loadinstruction = null;

    protected static $_js_orphaned = null;

    protected static $_css_orphaned = null;

    protected static $_js_orphaned_buffer = null;

    protected static $_css_orphaned_buffer = null;

    protected static $_js_orphan_sig = null;

    protected static $_css_orphan_sig = null;

    protected static $_jscomments = null;

    protected static $_css_comments = null;
    
  

    // protected static $_compress_js = null;
    // is it used
    // protected static $_compress_css = null;
    // not used
    protected static $_js_replace = null;

    protected static $_img_tweak_params = null;

    protected static $_cando = null;
    
    protected static $_js_inlineorphaned_defer = null;
    
    protected static $_multicache_extraordinary_nolazy = null;
    //detox start
    protected static $_detox_orphaned_js_src = null;
    protected static $_detox_orphaned_js_code = null;
    protected static $_detox_orphaned_css_link = null;
    protected static $_detox_orphaned_css_code = null;

    // protected static $_replace_script_css = null;

    /**
     * Constructor.
     *
     * @param
     *        $subject
     * @param array $config
     */
    function __construct(&$subject, $config = array())
    {

        parent::__construct($subject, $config);
        error_reporting(0);
        self::$js_switch = $this->params->get('js_switch', 0);
        self::$css_switch = $this->params->get('css_switch', 0);
        $this->css_groupsasync = $this->params->get('css_groupsasync');
        $this->js_simulation = $this->params->get('js_simulation', 0);
        $this->js_advanced = $this->params->get('js_advanced');
        $this->_debug_mode = $this->params->get('debug_mode');
        $this->_minify_html = $this->params->get('minify_html');
        $this->_img_tweaks = $this->params->get('img_tweaks');

        if (isset($this->_debug_mode))
        {
            define('MULTICACHE_PLUGIN_DEBUG', true);
        }
        $this->conduit_switch = $this->params->get('conduit_switch', 0);
        $this->testing_switch = $this->params->get('testing_switch', 0);
        
        self::$_jscomments = $this->params->get('js_comments', 1);
        self::$_css_comments = $this->params->get('css_comments', 1);
        
        self::$_js_loadinstruction = $this->params->get('js_loadinstruction', null);
        self::$_js_orphaned = $this->params->get('js_orphaned');
        self::$_css_orphaned = $this->params->get('orphaned_styles_loading');
        //inline  orphaned defer begin
        self::$_js_inlineorphaned_defer = array(
        		'mau' => 0,
        		'switch' =>0,
        		'jQuery'=> 'jQuery',
        		'time' => 30,
        		'count' => 100,
        		'ifjqdefined' => 0,
        		'head_loaded' => false,
        		'compress' => true,
        		'buffer' => array()
        );
        //inline orphan defer end
        // self::$_compress_css = $this->params->get('compress_css', 1);
        $this->uri = JURI::getInstance();
        $this->current = JURI::current();

        if (! empty(self::$_js_orphaned))
        {
            self::$_js_orphaned_buffer = '';
        }
        if (! empty(self::$_css_orphaned))
        {
            self::$_css_orphaned_buffer = '';
        }

    }

    /*
     * FAILED CSRF TEST
     */
    /*
     *
     * public function onAfterInitialise()
     * {
     * // placing post raw as this is in onafterinitialize and could effect site speed
     * $app = JFactory::getApplication();
     * if ($app->input->getMethod() != 'POST')
     * {
     * Return;
     * }
     *
     * if ($app->isAdmin())
     * {
     * return;
     * }
     * $user = JFactory::getUser();
     * if (! ($user->get('guest')))
     * {
     * Return;
     * }
     * // if this is a login attempt check whether our cookie is valid
     * // if not define : JMULTICACHE_PASS and set cookie jmulticachep with about 5 minutes
     * $u_name = $app->input->post->get('username');
     * $submit_isset = $app->input->post->get('Submit');
     * $task = $app->input->post->get('task');
     * if (! (isset($u_name) && isset($submit_isset) && isset($task) && $task = 'user.login'))
     * {
     * Return;
     * }
     * $multicache_signature = substr(md5(md5($app->get('secret') . '-multicache-' . $app->get('cookie_domain') . '-' . JURI::getInstance()->getHost())), 2, 25);
     * if (! $app->input->post->get($multicache_signature))
     * {
     * // not signed by multicache exit
     * Return;
     * }
     * // set the token
     * $jmulticachehash = $app->get('jmulticache_hash');
     * $cookieName = isset($jmulticachehash) ? '_jmulticache_' . $jmulticachehash : null;
     * if (! isset($cookieName))
     * {
     * Return;
     * }
     * $jmulticache_cookie = $app->input->cookie->get($cookieName);
     * if (! isset($jmulticache_cookie))
     * {
     * Return; // not in our context
     * }
     * if (preg_match('~^[A-Fa-f0-9]{32}$~', $jmulticache_cookie))
     * {
     *
     * $key = preg_replace('~[^A-Fa-f0-9]~', '', $jmulticache_cookie);
     * // repeat of advanced cache we dont want this set twice
     * $post_key = $app->input->post->get($key);
     * if (! isset($post_key))
     * {
     * $app->input->post->set($key, true);
     * }
     * }
     * else
     * {
     * Return;
     * }
     *
     * $date_token = md5(date('Y-m-d')); // heurestic:this can get as deep as possible
     * $cookieName = 'jmulticachep_' . $jmulticachehash;
     * $check_cookie = $app->input->cookie->get($cookieName);
     * if (isset($check_cookie) && $check_cookie == $date_token)
     * {
     * Return;
     * }
     * $token = JSession::getFormToken();
     * // check if token isset
     * $token_ok = $app->input->post->get($token, '', 'alnum');
     * if (! $token_ok)
     * {
     * if (! defined('JMULTICACHE_PASS'))
     * {
     * define('JMULTICACHE_PASS', true); // passes precache
     * }
     * $lifetime = 5 * 60;
     *
     * // check value of cookie
     *
     * $session = JFactory::getSession();
     * $id = $session->getId();
     * $name = $session->getName();
     * $cookie_val = md5($id . '-' . $name);
     * $session_val = $session->get('multicache_login_' . $cookie_val);
     * // check for tampering
     * if ($session_val >= 2 && ! isset($check_cookie))
     * {
     * Return;
     * }
     * elseif ($session_val >= 12)
     * {
     * $cookie_block = $date_token; // flooding attacks
     * $lifetime = 4 * 60 * 60;
     * $app->input->cookie->set($cookieName, $cookie_block, time() + $lifetime, $app->get('cookie_path', '/'), $app->get('cookie_domain'), $app->isSSLConnection());
     * Return;
     * }
     * $session_val = isset($session_val) ? ++ $session_val : 1;
     * $session->set('multicache_login_' . $cookie_val, $session_val);
     * $app->input->cookie->set($cookieName, $cookie_val, time() + $lifetime, $app->get('cookie_path', '/'), $app->get('cookie_domain'), $app->isSSLConnection());
     * if ($session_val <= 5)
     * {
     * // check for invalid token
     *
     * if (! $session->isNew())
     * {
     * $app->enqueueMessage(JText::_('PLG_MULTICACHE_ENVIRONMENT_SESSION_EXPIRED'), 'warning');
     * $app->redirect(JRoute::_('index.php'));
     * }
     * }
     * }
     *
     * }
     */
     
     protected static function scriptInlineCodeWrap($code , $compress = false)
     {
     	if($compress)
     	{
     		$code = MulticacheJSOptimize::process($code);
     	}
     	Return '<script>' . $code . '</script>';
     }
    protected function performJstweaks($page)
    {

        if (null !== ($simflag = JFactory::getApplication()->input->get('multicachesimulation', null)) || isset(self::$_js_loadinstruction) && ! empty($this->testing_switch))
        {

            if (! class_exists('JsStrategySimControl'))
            {
                $emessage = "COM_MULTICACHE_SIMCONTROL_CLASS_JSCACHESTRATEGYSIMCONTROL_DOES_NOT_EXIST";
                // JLog::add(JText::_($emessage) . ' req-' . $simflag . ' ' . self::$_js_loadinstruction, JLog::ERROR);
                $this->loaderrorlogger($emessage, 'error', ' req-' . $simflag . '	' . self::$_js_loadinstruction);
                Return $page;
            }

            /*
             * check for excludes and return
             * begin
             */
            if (! $this->canDoOp('JST', 'JsStrategySimControl'))
            {
                Return $page;
            }
            /*
             * $clude_settings = null;
             * if (property_exists('JsStrategySimControl', 'JSTsetting'))
             * {
             * $clude_settings = JsStrategySimControl::$JSTsetting;
             * }
             * if (property_exists('JsStrategySimControl', 'JSTCludeUrl') && isset($clude_settings))
             * {
             * $JSTcludeurls = JsStrategySimControl::$JSTCludeUrl;
             * if (($clude_settings['url_switch'] == 1 && ! isset($JSTcludeurls[JURI::Current()])) || ($clude_settings['url_switch'] == 2 && isset($JSTcludeurls[JURI::Current()])))
             * {
             * // exclude these pages
             * Return $page;
             * }
             * }
             * if (property_exists('JsStrategySimControl', 'JSTCludeQuery') && isset($clude_settings))
             * {
             * $query_params = JURI::getInstance()->getQuery(true);
             * $JSTcludequeries = JsStrategySimControl::$JSTCludeQuery;
             * if ($clude_settings['query_switch'] == 1)
             * {
             * // include these pages
             * $include_page = false;
             * foreach ($query_params as $key => $value)
             * {
             * if (isset($JSTcludequeries[$key][$value]) || (isset($JSTcludequeries[$key]) && $JSTcludequeries[$key][true] == 1))
             * {
             * $include_page = true;
             * break;
             * }
             * }
             * if (! $include_page)
             * {
             * Return $page;
             * }
             * }
             * if ($clude_settings['query_switch'] == 2)
             * {
             * // exclude these pages
             * foreach ($query_params as $key => $value)
             * {
             * if (isset($JSTcludequeries[$key][$value]) || (isset($JSTcludequeries[$key]) && $JSTcludequeries[$key][true] == 1))
             * {
             * Return $page;
             * }
             * }
             * }
             * }
             * if (property_exists('JsStrategySimControl', 'JSTexcluded_components'))
             * {
             * // $excluded_comp = JsStrategy::$JSTexcluded_components;
             * $option = JFactory::getApplication()->input->get('option', null);
             * if (isset(JsStrategySimControl::$JSTexcluded_components[$option]))
             * {
             * Return $page;
             * }
             * }
             * if (property_exists('JsStrategySimControl', 'JSTurl_strings'))
             * {
             * $urlstrings = JsStrategySimControl::$JSTurl_strings;
             * $current = JURI::getInstance()->toString();
             * foreach ($urlstrings as $string)
             * {
             * if (stristr($current, $string))
             * {
             * Return $page;
             * }
             * }
             * }
             * // end jst excludes
             * // begin
             */

            if (self::$_js_loadinstruction === JsStrategySimControl::$simulation_id || $simflag === JsStrategySimControl::$simulation_id)
            {
                define("PLG_MULTICACHE_SIMCONTROL", true);
                if (defined('MULTICACHE_PLUGIN_DEBUG') && MULTICACHE_PLUGIN_DEBUG)
                {
                    $page = isset(self::$_js_loadinstruction) ? '<h1 style="font-size:6em;margin-top:0.5em;letter-spacing: 0.2em;line-height:2em;">LoadInstruction 	' . self::$_js_loadinstruction . '</h1>' . $page : $page;
                    $page = isset($simflag) ? '<h1 style="font-size:6em;margin-top:0.5em;letter-spacing: 0.5em;line-height: 2em;">Simflag 	' . $simflag . '</h1>' . $page : $page;
                }
                self::$_signatures = JsStrategySimControl::getJsSignature();
                $loadsections = JsStrategySimControl::getLoadSection();
            }
            else
            {

                $emessage = "COM_MULTICACHE_SIMCONTROL_PLUGIN_SIMULATION_ID_MISMATCH";
                // JLog::add(JText::_($emessage) . ' req-' . $simflag . ' ' . self::$_js_loadinstruction . ' JsCacheready-' . JsStrategySimControl::$simulation_id, JLog::ERROR);
                $extra_message = ' req-' . $simflag . '	' . self::$_js_loadinstruction . '	JsCacheready-' . JsStrategySimControl::$simulation_id;
                $this->loaderrorlogger($emessage, 'error', $extra_message);
                Return $page;
            }
            // end
        }
        else
        {
            /*
             * check for excludes and return
             * begin
             */

            if (! $this->canDoOp('JST'))
            {
                Return $page;
            }
            /*
             * $clude_settings = null;
             * if (property_exists('JsStrategy', 'JSTsetting'))
             * {
             * $clude_settings = JsStrategy::$JSTsetting;
             * }
             * if (property_exists('JsStrategy', 'JSTCludeUrl') && isset($clude_settings))
             * {
             * $JSTcludeurls = JsStrategy::$JSTCludeUrl;
             * if (($clude_settings['url_switch'] == 1 && ! isset($JSTcludeurls[JURI::Current()])) || ($clude_settings['url_switch'] == 2 && isset($JSTcludeurls[JURI::Current()])))
             * {
             * // exclude these pages
             * Return $page;
             * }
             * }
             * if (property_exists('JsStrategy', 'JSTCludeQuery') && isset($clude_settings))
             * {
             * $query_params = JURI::getInstance()->getQuery(true);
             * $JSTcludequeries = JsStrategy::$JSTCludeQuery;
             * if ($clude_settings['query_switch'] == 1)
             * {
             * // include these pages
             * $include_page = false;
             * foreach ($query_params as $key => $value)
             * {
             * if (isset($JSTcludequeries[$key][$value]) || (isset($JSTcludequeries[$key]) && $JSTcludequeries[$key][true] == 1))
             * {
             * $include_page = true;
             * break;
             * }
             * }
             * if (! $include_page)
             * {
             * Return $page;
             * }
             * }
             * if ($clude_settings['query_switch'] == 2)
             * {
             * // exclude these pages
             * foreach ($query_params as $key => $value)
             * {
             * if (isset($JSTcludequeries[$key][$value]) || (isset($JSTcludequeries[$key]) && $JSTcludequeries[$key][true] == 1))
             * {
             * Return $page;
             * }
             * }
             * }
             * }
             *
             * if (property_exists('JsStrategy', 'JSTexcluded_components'))
             * {
             * // $excluded_comp = JsStrategy::$JSTexcluded_components;
             * $option = JFactory::getApplication()->input->get('option', null);
             * if (isset(JsStrategy::$JSTexcluded_components[$option]))
             * {
             * Return $page;
             * }
             * }
             * if (property_exists('JsStrategy', 'JSTurl_strings'))
             * {
             * $urlstrings = JsStrategy::$JSTurl_strings;
             * $current = JURI::getInstance()->toString();
             * foreach ($urlstrings as $string)
             * {
             * if (stristr($current, $string))
             * {
             * Return $page;
             * }
             * }
             * }
             */
            // end jst excludes

            define("PLG_MULTICACHE_STRATEGY", true);
            self::$_signatures = JsStrategy::getJsSignature();
            $loadsections = JsStrategy::getLoadSection();
            if (empty($loadsections))
            {
                Return $page;
            }
        }

        // $search = "~<script((?s:(?!</script).)*)<\/script>~";
        /* $search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<script(?= (?> [^\\s>]*+[\\s] (?(?=type= )type=["\']?(?:text|application)/javascript ) )*+ [^\\s>]*+> )(?:(?> [^\\s>]*+\\s )+? (?>src)=["\']?)?[^>]*+> (?> <?[^<]*+ )*? </script>)|\\K$)~six'; */
        // transport from WP src enhanced for positional rejection
        // init dontmove vars
        if (defined('PLG_MULTICACHE_STRATEGY'))
        {
            self::$dontmove_js_p = property_exists('JsStrategy', 'dontmove_js') ? JsStrategy::$dontmove_js : null;
            self::$dontmove_urls_js_p = property_exists('JsStrategy', 'dontmove_urls_js') ? JsStrategy::$dontmove_urls_js : null;
            self::$allow_multiple_orphaned = property_exists('JsStrategy', 'allow_multiple_orphaned') ? JsStrategy::$allow_multiple_orphaned : null;
            ;
        }
        elseif (defined('PLG_MULTICACHE_SIMCONTROL'))
        {
            self::$dontmove_js_p = property_exists('JsStrategySimControl', 'dontmove_js') ? JsStrategySimControl::$dontmove_js : null;
            self::$dontmove_urls_js_p = property_exists('JsStrategySimControl', 'dontmove_urls_js') ? JsStrategySimControl::$dontmove_urls_js : null;
            self::$allow_multiple_orphaned = property_exists('JsStrategySimControl', 'allow_multiple_orphaned') ? JsStrategySimControl::$allow_multiple_orphaned : null;
        }
        //detox orphaned begin
        if(defined('MULTICACHEDETOXORPHANEDJS'))
        {
        	self::$_detox_orphaned_js_src = property_exists('MulticacheDetoxOrphanedJS' ,'_js_src') ? MulticacheDetoxOrphanedJS::$_js_src : null;
        	self::$_detox_orphaned_js_code = property_exists('MulticacheDetoxOrphanedJS' ,'_js_code') ? MulticacheDetoxOrphanedJS::$_js_code : null;
        }
        //detox orphaned end
        $search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<script(?= (?> [^\\s>]*+[\\s] (?(?=type= )type=["\']?(?:text|application)/javascript ) )*+ [^\\s>]*+> )(?:(?> [^\\s>]*+\\s )+? (?>src)=["\']?( (?<!["\']) [^\\s>]*+| (?<!\') [^"]*+ | [^\']*+ ))?[^>]*+>( (?> <?[^<]*+ )*? )</script>)|\\K$)~six';

        $tweaks = preg_replace_callback($search, 'self::matchJsSignature', $page);
        $tweaks = isset($tweaks) ? $tweaks : $page;

        $multicache_name = (isset($simflag) || isset(self::$_js_loadinstruction)) ? 'MulticacheSimControl' : 'MulticachePlugin';
        // now lets load up the tweaks
        $load_js = array(); // a carry over var for combining js to css
                            // load the footer
                            // var_dump(self::$_loadsections[3]);
                            // $footesection_search = "</body>";
                            // correction from WP
        /*
         * if ( defined("PLG_MULTICACHE_STRATEGY") && property_exists('JsStrategy', 'stubs'))
         * {
         * $stub = unserialize(JsStrategy::$stubs);
         * $preheadsection_search = $stub["head_open"];
         * $headsection_search = $stub["head"];
         * $bodysection_search = $stub["body"];
         * $footersection_search = $stub["footer"];
         * }
         * elseif (defined("PLG_MULTICACHE_SIMCONTROL") && property_exists('JsStrategySimControl', 'stubs'))
         * {
         * $stub = unserialize(JsStrategySimControl::$stubs);
         * $preheadsection_search = $stub["head_open"];
         * $headsection_search = $stub["head"];
         * $bodysection_search = $stub["body"];
         * $footersection_search = $stub["footer"];
         * }
         * else
         * {
         * $preheadsection_search = array(
         * '<head>'
         * );
         * $headsection_search = array(
         * '</head>'
         * );
         * $bodysection_search = array(
         * '<body>'
         * );
         * $footersection_search = array(
         * '</body>'
         * );
         * }
         */
        // start preg code
        $preheadsection_search = '~<head(
            (?(?=(\s[^>]*))\s[^>]*)
            )>~ixU';
        $headsection_search = '~</head(
            (?(?=(\s[^>]*))\s[^>]*)
            )>~ixU';
        $bodysection_search = '~<body(
            (?(?=(\s[^>]*))\s[^>]*)
            )>~ixU';
        $footersection_search = '~</body(
            (?(?=(\s[^>]*))\s[^>]*)
            )>~ixU';
        // replacers
        $prehead = '<head\1>';
        $head = '</head\1>';
        $body_tag = '<body\1>';
        $foot_tag = '</body\1>';
        // end preg code

        // begin
        $search_array = array(); // initialise search array to null array
        $replace_array = array();
        $comment_end = "Loaded by " . $multicache_name . " Copyright OnlineMarketingConsultants.in-->";
        // moderate self::$_js_orphaned
        if (! empty(self::$_js_orphaned_buffer) && ! empty(self::$_js_orphaned) && empty($loadsections[self::$_js_orphaned]))
        {
            if (is_array($loadsections))
            {
                $temp_js = array_filter($loadsections);
                $all_keys = array_keys($temp_js);
            }
            // IMPORTANT COMMENTED UNDER TESTING
            // self::$_js_orphaned = isset($all_keys) && is_array($all_keys) ? max($all_keys) : self::$_js_orphaned;
        }
        //setup orphaned inlined defer
        if(!empty(self::$_js_inlineorphaned_defer) && 1 === self::$_js_inlineorphaned_defer['switch'])
        {
        	$inline_orphaned_buffer_head = 'window.MULTICACHEDEFERINLINELOAD=[];var loadMulticacheinlineddefer=function(){for(var E=window.MULTICACHEDEFERINLINELOAD.length,I=0;E>I;I++)window.MULTICACHEDEFERINLINELOAD[I](),window.MULTICACHEDEFERINLINELOAD[I]=function(){}};';
        	if(!empty(self::$_js_inlineorphaned_defer['mau']))
        	{
        		$inline_orphaned_buffer_mau_trigger = 'if("undefined"!=typeof multicache_MAU&&"undefined"!=typeof loadMulticacheinlineddefer){var check={checkType:function(){return typeof '.self::$_js_inlineorphaned_defer['jQuery'].'},name:"multicache_defered_inline"};multicache_MAU(loadMulticacheinlineddefer,function(){console.log("failed to load inline defer orphaned")},check,'.self::$_js_inlineorphaned_defer['time'].',void 0,' .self::$_js_inlineorphaned_defer['count']. ',check.name)}';
        	}
        }
        if (! empty($loadsections[1]))
        {
            /*
             * foreach ($preheadsection_search as $prehead)
             * {
             */
            $search_array[] = $preheadsection_search;
            $load_js[1]["search"][] = $preheadsection_search;
            $comment = "<!--pre headsection ";
            $r_code = $prehead . trim(unserialize($loadsections[1]));
            $load_js1_item_temp = trim(unserialize($loadsections[1]));
            if(isset($inline_orphaned_buffer_head) && false === self::$_js_inlineorphaned_defer['head_loaded'])
            {
            	$r_code = self::scriptInlineCodeWrap($inline_orphaned_buffer_head) . $r_code;
            	$load_js1_item_temp = self::scriptInlineCodeWrap($inline_orphaned_buffer_head) .$load_js1_item_temp;
            	self::$_js_inlineorphaned_defer['head_loaded'] = true;
            }
            if (! empty(self::$_js_orphaned_buffer) && isset(self::$_js_orphaned) && self::$_js_orphaned == 1)
            {

                $r_code = self::$_jscomments ? $r_code . '<!-- orphaned scripts -->' . self::$_js_orphaned_buffer : $r_code . self::$_js_orphaned_buffer;
                $load_js1_item_temp = self::$_jscomments ? $load_js1_item_temp . '<!-- orphaned scripts -->' . self::$_js_orphaned_buffer : $load_js1_item_temp . self::$_js_orphaned_buffer;
            }
            $replace_array[] = self::$_jscomments ? $r_code . $comment . $comment_end : $r_code;
            $load_js[1]["item"][] = self::$_jscomments ? $load_js1_item_temp . $comment . $comment_end : $load_js1_item_temp;
            $load_js[1]["tag"][] = $prehead;
            /*
             * }
             */
        }

        if (! empty($loadsections[2]))
        {
            /*
             * foreach ($headsection_search as $head)
             * {
             */
            $search_array[] = $headsection_search;
            $load_js[2]["search"][] = $headsection_search;
            $comment = "<!-- headsection ";
            $r_code = trim(unserialize($loadsections[2]));
            $load_js2_item_temp = trim(unserialize($loadsections[2]));
            if(isset($inline_orphaned_buffer_head) && false === self::$_js_inlineorphaned_defer['head_loaded'])
            {
            	$r_code = self::scriptInlineCodeWrap($inline_orphaned_buffer_head) . $r_code;
            	$load_js2_item_temp = self::scriptInlineCodeWrap($inline_orphaned_buffer_head) .$load_js2_item_temp;
            	self::$_js_inlineorphaned_defer['head_loaded'] = true;
            }
            if (! empty(self::$_js_orphaned_buffer) && isset(self::$_js_orphaned) && self::$_js_orphaned == 2)
            {
                $r_code = self::$_jscomments ? $r_code . '<!-- orphaned scripts -->' . self::$_js_orphaned_buffer : $r_code . self::$_js_orphaned_buffer;
                $load_js2_item_temp = self::$_jscomments ? $load_js2_item_temp . '<!-- orphaned scripts -->' . self::$_js_orphaned_buffer : $load_js2_item_temp . self::$_js_orphaned_buffer;
            }
            $r_code .= $head;
            $replace_array[] = self::$_jscomments ? $comment . $comment_end . $r_code : $r_code;
            $load_js[2]["item"][] = self::$_jscomments ? $comment . $comment_end . $load_js2_item_temp : $load_js2_item_temp;
            $load_js[2]["tag"][] = $head;
            /*
             * }
             */
        }

        if (! empty($loadsections[3]))
        {
            /*
             * foreach ($bodysection_search as $body_tag)
             * {
             */
            $search_array[] = $bodysection_search;
            $load_js[3]["search"][] = $bodysection_search;
            $comment = "<!-- bodysection ";
            $r_code = $body_tag . trim(unserialize($loadsections[3]));
            $load_js3_item_temp = trim(unserialize($loadsections[3]));
            if(isset($inline_orphaned_buffer_head) && false === self::$_js_inlineorphaned_defer['head_loaded'])
            {
            	$r_code = self::scriptInlineCodeWrap($inline_orphaned_buffer_head) . $r_code;
            	$load_js3_item_temp = self::scriptInlineCodeWrap($inline_orphaned_buffer_head) .$load_js3_item_temp;
            	self::$_js_inlineorphaned_defer['head_loaded'] = true;
            }
            if(isset($inline_orphaned_buffer_head) && false === self::$_js_inlineorphaned_defer['head_loaded'])
            {
            	$r_code = self::scriptInlineCodeWrap($inline_orphaned_buffer_head) . $r_code;
            	$load_js3_item_temp = self::scriptInlineCodeWrap($inline_orphaned_buffer_head) .$r_code;
            	self::$_js_inlineorphaned_defer['head_loaded'] = true;
            }
            if (! empty(self::$_js_orphaned_buffer) && isset(self::$_js_orphaned) && self::$_js_orphaned == 3)
            {

                $r_code = self::$_jscomments ? $r_code . '<!-- orphaned scripts -->' . self::$_js_orphaned_buffer : $r_code . self::$_js_orphaned_buffer;
                $load_js3_item_temp = self::$_jscomments ? $load_js3_item_temp . '<!-- orphaned scripts -->' . self::$_js_orphaned_buffer : $load_js3_item_temp . self::$_js_orphaned_buffer;
            }
            $replace_array[] = self::$_jscomments ? $r_code . $comment . $comment_end : $r_code;
            $load_js[3]["item"][] = self::$_jscomments ? $load_js3_item_temp . $comment . $comment_end : $load_js3_item_temp;
            $load_js[3]["tag"][] = $body_tag;
            /*
             * }
             *
             */
        }

        if (! empty($loadsections[4]) || (isset(self::$_js_orphaned) && self::$_js_orphaned == 4))
        {
            /*
             * foreach ($footersection_search as $foot_tag)
             * {
             */
            $search_array[] = $footersection_search;
            $load_js[4]["search"][] = $footersection_search;
            $comment = "<!-- footsection ";
            $r_code = ! empty($loadsections[4]) ? trim(unserialize($loadsections[4])) : '';
            $load_js4_item_temp = ! empty($loadsections[4]) ? trim(unserialize($loadsections[4])) : '';
            if(isset($inline_orphaned_buffer_head) && false === self::$_js_inlineorphaned_defer['head_loaded'])
            {
            	$r_code = self::scriptInlineCodeWrap($inline_orphaned_buffer_head) . $r_code;
            	$load_js4_item_temp = self::scriptInlineCodeWrap($inline_orphaned_buffer_head) .$load_js4_item_temp;
            	self::$_js_inlineorphaned_defer['head_loaded'] = true;
            }
            if (! empty(self::$_js_orphaned_buffer) && isset(self::$_js_orphaned) && self::$_js_orphaned == 4)
            {

                $r_code = self::$_jscomments ? $r_code . '<!-- orphaned scripts -->' . self::$_js_orphaned_buffer : $r_code . self::$_js_orphaned_buffer;
                $load_js4_item_temp = self::$_jscomments ? $load_js4_item_temp . '<!-- orphaned scripts -->' . self::$_js_orphaned_buffer : $load_js4_item_temp . self::$_js_orphaned_buffer;
            }
            //start mau inline defer code
            if(!empty(self::$_js_inlineorphaned_defer) && 1=== self::$_js_inlineorphaned_defer['switch']
            		&& 1=== self::$_js_inlineorphaned_defer['mau'])
            {
            	$r_code .= self::scriptInlineCodeWrap( 'if("undefined"!==typeof multicache_MAU&&"undefined"!==typeof loadMulticacheinlineddefer){var check={checkType:function(){return typeof '.self::$_js_inlineorphaned_defer['jQuery'].'},name:"multicache_defered_inline"};multicache_MAU(loadMulticacheinlineddefer,function(){console.log("failed to load inline defer orphaned")},check,'.self::$_js_inlineorphaned_defer['time'].',void 0,'.self::$_js_inlineorphaned_defer['count'].',check.name)}' , true);
            	$load_js4_item_temp .= self::scriptInlineCodeWrap( 'if("undefined"!==typeof multicache_MAU&&"undefined"!==typeof loadMulticacheinlineddefer){var check={checkType:function(){return typeof '.self::$_js_inlineorphaned_defer['jQuery'].'},name:"multicache_defered_inline"};multicache_MAU(loadMulticacheinlineddefer,function(){console.log("failed to load inline defer orphaned")},check,'.self::$_js_inlineorphaned_defer['time'].',void 0,'.self::$_js_inlineorphaned_defer['count'].',check.name)}' , true);
            }
            //end mau inline defer code
            $r_code .= $foot_tag;
            $replace_array[] = self::$_jscomments ? $comment . $comment_end . $r_code : $r_code;
            $load_js[4]["item"][] = self::$_jscomments ? $comment . $comment_end . $load_js4_item_temp : $load_js4_item_temp;
            $load_js[4]["tag"][] = $foot_tag;
           
        }
        elseif(!empty(self::$_js_inlineorphaned_defer) && 1=== self::$_js_inlineorphaned_defer['switch']
        			&& 1=== self::$_js_inlineorphaned_defer['mau'])
        {
        	//critical footer scripts
        	$search_array[] = $footersection_search;
        	$load_js[4]["search"][] = $footersection_search;
        	$comment = "<!-- footsection Critical ";
        	    	//start mau inline defer code
        	
        		$r_code = self::scriptInlineCodeWrap( 'if("undefined"!==typeof multicache_MAU&&"undefined"!==typeof loadMulticacheinlineddefer){var check={checkType:function(){return typeof '.self::$_js_inlineorphaned_defer['jQuery'].'},name:"multicache_defered_inline"};multicache_MAU(loadMulticacheinlineddefer,function(){console.log("failed to load inline defer orphaned")},check,'.self::$_js_inlineorphaned_defer['time'].',void 0,'.self::$_js_inlineorphaned_defer['count'].',check.name)}' , true);
        		$load_js4_item_temp = self::scriptInlineCodeWrap( 'if("undefined"!==typeof multicache_MAU&&"undefined"!==typeof loadMulticacheinlineddefer){var check={checkType:function(){return typeof '.self::$_js_inlineorphaned_defer['jQuery'].'},name:"multicache_defered_inline"};multicache_MAU(loadMulticacheinlineddefer,function(){console.log("failed to load inline defer orphaned")},check,'.self::$_js_inlineorphaned_defer['time'].',void 0,'.self::$_js_inlineorphaned_defer['count'].',check.name)}' , true);
        	
        	//end mau inline defer code
        	$r_code .= $foot_tag;
        	$replace_array[] = self::$_jscomments ? $comment . $comment_end . $r_code : $r_code;
        	$load_js[4]["item"][] = self::$_jscomments ? $comment . $comment_end . $load_js4_item_temp : $load_js4_item_temp;
        	$load_js[4]["tag"][] = $foot_tag;
        	
        }

        if (! empty($replace_array))
        {
            if (self::$css_switch)
            {

                self::$_js_replace = $load_js;
                Return $tweaks; // at this point $tweaks is stripped of its script tags
            }
            // $tweaks = str_replace($search_array, $replace_array, $tweaks);
            $tweaks = preg_replace($search_array, $replace_array, $tweaks);
        }

        Return $tweaks;

    }
    
    protected static function checkPageSpeedStrategy( $obj)
    {
    	$pagespeed_obj = property_exists('JsStrategy' ,'pagespeed_strategy' )? JsStrategy::$pagespeed_strategy :false;
    	if(!$pagespeed_obj || strpos($obj , 'googletag')!== false)
    	{
    		Return $obj;
    	}
    	$search = '~(<script[^>]*?)>~';
    	if(!empty($pagespeed_obj['resultant_async']) && strpos($obj, ' async') === false)
    	{
    		//$obj = str_replace('>' , ' async >' , $obj);
    
    		$obj = preg_replace($search , '$1 async >' , $obj);
    	}
    	if(!empty($pagespeed_obj['resultant_defer']) && strpos($obj, ' defer') === false)
    	{
    		//$obj = str_replace('>' , ' defer >' , $obj);
    		$obj = preg_replace($search , '$1 defer >' , $obj);
    	}
    	Return $obj;
    
    	 
    }
    protected static function wrapInlineDeferStub($stub , $compress = false)
    {
    	if($compress)
    	{
    		$stub = MulticacheJSOptimize::process($stub);
    	}
    	/*$inline_code = '"undefined"!==typeof '.self::$_js_inlineorphaned_defer['jQuery'].'?('.$stub.'):window.MULTICACHEDEFERINLINELOAD.push(function(){'.$stub.'});';*/
    	$inline_code = 'if("undefined" !== typeof ' . self::$_js_inlineorphaned_defer['jQuery'].'){'.$stub.';}else{window.MULTICACHEDEFERINLINELOAD.push(function(){' .$stub. ' });}';
    	Return  self::scriptInlineCodeWrap($inline_code);
    }

    protected static function matchJsSignature($matches)
    {
        // hack begin
        /*
         * if (empty($matches[0]) || strpos($matches[0], 'widget.prnewswire') !== false || strpos($matches[0], 'googletagservices.com/tag/js/gpt.js') !== false || strpos($matches[0], 'googletag.cmd.push(function() {') !== false)
         * {
         * Return $matches[0];
         * }
         */
        // hack end
        if (empty($matches[0]))
        {
            Return $matches[0];
        }
        
        //wrap for code
        if(!empty(self::$_js_inlineorphaned_defer) 
        		&& 1 === self::$_js_inlineorphaned_defer['switch']
        		&& !empty($matches[2])
        		&& strpos($matches[2] , 'adsbygoogle' ) === false
        		&&(
        				0 === self::$_js_inlineorphaned_defer['ifjqdefined']
        				|| (1 === self::$_js_inlineorphaned_defer['ifjqdefined']
        						&& strpos( $matches[2] , self::$_js_inlineorphaned_defer['jQuery'] )!== false )
        			)
        	)
        {
        	
        	$matches[0] = self::wrapInlineDeferStub($matches[2]);
        }
        // positional excludes come here
        // urls begin
        if (isset(self::$dontmove_urls_js_p))
        {
            foreach (self::$dontmove_urls_js_p as $bit => $val)
            {
                if (stripos($matches[0], $bit) !== false)
                {
                    Return self::checkPageSpeedStrategy($matches[0]);
                }
            }
        }
        // urls end
        $sig = md5(serialize($matches[0]));
        // hash begin
        if (isset(self::$dontmove_js_p[$sig]))
        {
            Return self::checkPageSpeedStrategy($matches[0]);
        }
        // hash end
        if (isset(self::$_signatures[$sig]))
        {
            $blank = "";
            Return $blank;
        }
        else
        {
        	//test start intercept point for hash checks on orphaned
        	/*$obj = array('sig' => $sig , 'element' => serialize($matches[0]) , 'element1' => $matches[1] , 'element2' =>$matches[2]);
        	$obj = print_r($obj , true);//
        	$e_message = print_r($obj ,true);
        	error_log($e_message , 3, __DIR__ .'/e_obj.log');*/
        	//test end
            if (empty(self::$_js_orphaned))
            {
                Return self::checkPageSpeedStrategy($matches[0]);
            }
            $blank = '';
            
            //detox orphaned
            if(isset(self::$_detox_orphaned_js_src) && !empty($matches[1]))
            {
            	//src links
            	foreach(self::$_detox_orphaned_js_src As $key=> $src)
            	{
            		if(strpos($matches[1] , $src['name']) !== false)
            		{
            			Return $blank;
            		}
            	}
            
            }
            if(isset(self::$_detox_orphaned_js_code) && !empty($matches[2]))
            {
            
            	//src links
            	foreach(self::$_detox_orphaned_js_code As $key=> $code)
            	{
            
            		$check_1 = $check_2 = $check_3 = false;
            		if( strpos($matches[2] , $code['name']) !== false)
            		{
            			//Return $blank;
            			$check_1 = true;
            		}
            		if( strpos($matches[2] , $code['name_2']) !== false)
            		{
            			//Return $blank;
            			$check_2 = true;
            		}
            		if( strpos($matches[2] , $code['name_3']) !== false)
            		{
            			//Return $blank;
            			$check_3 = true;
            		}
            
            		if($check_1 && $check_2 && $check_3)
            		{
            			$test_ejection = false;
            			if($test_ejection)
            			{
            				error_log(var_export($matches[2] , true) , 3 , dirname(__FILE__).'/zzeject.log');
            			}
            			Return $blank;
            		}
            	}
            
            }
            //end detox orphaned

            if (! isset(self::$_js_orphan_sig[$sig]))
            {
                self::$_js_orphaned_buffer .= self::checkPageSpeedStrategy($matches[0]);
                $skip = false;
                if (isset(self::$allow_multiple_orphaned) && is_array(self::$allow_multiple_orphaned))
                {

                    foreach (self::$allow_multiple_orphaned as $key => $v)
                    {
                        if (strpos($matches[0], $key) !== false)
                        {
                            $skip = true;
                            break;
                        }
                    }
                }
                if (! (isset(self::$allow_multiple_orphaned) && self::$allow_multiple_orphaned === - 1) && ! $skip)
                {
                    self::$_js_orphan_sig[$sig] = 1; // loads orphans only once
                }
            }
            Return $blank;
        }

    }

    protected function accountForJavascript($tweaks)
    {

        /*
         * if (empty(self::$_js_replace))
         * {
         * Return $tweaks;
         * }
         * $search_array = array();
         * $replace_array = array();
         * $load_js = self::$_js_replace;
         * foreach ($load_js as $key => $item)
         * {
         * $search[] = $item["search"];
         * $replace[] = $item["item"];
         * }
         * $tweaks = str_replace($search_array, $replace_array, $tweaks);
         * Return $tweaks;
         */

        // transport from WP
        if (empty(self::$_js_replace))
        {
            Return $tweaks;
        }
        $search_array = array();
        $replace_array = array();
        $load_js = self::$_js_replace;
        foreach ($load_js as $key => $item)
        {
            if (empty($item))
            {
                continue;
            }
            foreach ($item as $type => $code)
            {
                if (empty($code))
                {
                    continue;
                }
                foreach ($code as $k => $string)
                {

                    if ($type == 'search')
                    {
                        $search_array[] = $string;
                    }
                    elseif ($type == 'item')
                    {
                        $replace_array[] = strpos($item['search'][$k], '</') !== false ? $string . $item['tag'][$k] : $item['tag'][$k] . $string;
                    }
                }
            }
        }

        // $tweaks = str_replace($search_array, $replace_array, $tweaks);
        $tweaks = preg_replace($search_array, $replace_array, $tweaks);
        Return $tweaks;
        // end transport from WP
    }

    protected function performCsstweaks($page)
    {

        if (! $this->canDoOp('CSS'))
        {
            $page = $this->accountForJavascript($page);
            Return $page;
        }

        //if (! class_exists('JsStrategy') || ! property_exists('JsStrategy', 'sig_css') || ! property_exists('JsStrategy', 'loadsec_css'))
        //ammended from wp seems fuller
        if (! class_exists('JsStrategy')   || ! property_exists('JsStrategy', 'sig_css') || (property_exists('JsStrategy', 'sig_css') && !isset(JsStrategy::$sig_css)) || ! property_exists('JsStrategy', 'loadsec_css') || (property_exists('JsStrategy', 'loadsec_css') && !isset(JsStrategy::$loadsec_css)) )
        {
            $page = $this->accountForJavascript($page);
            Return $page;
        }

        self::$_signatures_css = JsStrategy::$sig_css;
        $loadsections_css = JsStrategy::$loadsec_css;

        if (empty($loadsections_css))
        {
            // if any remnants from js tweaks dump them here
            $page = $this->accountForJavascript($page);

            return $page;
        }
        
        //detox orphaned css begin
        
        if(defined('MULTICACHEDETOXORPHANEDCSS'))
        {
        	self::$_detox_orphaned_css_link = property_exists('MulticacheDetoxOrphanedCss' ,'_css_link') ? MulticacheDetoxOrphanedCss::$_css_link : null;
        	self::$_detox_orphaned_css_code = property_exists('MulticacheDetoxOrphanedCss' ,'_css_style') ? MulticacheDetoxOrphanedCss::$_css_style : null;
        
        }
        //detox orphaned css end
        // transported from WP non matching in last paraphrase
        /* $search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<link(?= (?>[^\\s>]*+[\\s] (?!(?:itemprop|disabled|type=(?! ["\']?text/css)|rel=(?!["\']?stylesheet))))*+[^\\s>]*+>)(?>[^\\s>]*+\\s)+?(?>href)=["\']?((?<!["\'])[^\\s>]*+|(?<!\')[^"]*+| [^\']*+)[^>]*+>)|(?:<style(?:(?!(?:type=(?!["\']?text/css))|(?:scoped))[^>])*>((?><?[^<]+)*?)</style>)|\\K$)~six'; */
        $search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<link(?= (?>[^\\s>]*+[\\s] (?!(?:itemprop|disabled|type=(?!  ["\']?text/css)|rel=(?!["\']?stylesheet))))*+[^\\s>]*+>)(?>[^\\s>]*+\\s)+?(?>href)=["\']?((?<!["\'])[^\\s>]*+|(?<!\')[^"]*+| [^\']*+)[^>]*+>)|(?:<style(?:(?!(?:type=(?!["\']?text/css))|(?:scoped))[^>])*>(?:(?><?[^<]+)*?)</style>)|\\K$)~six';

        $tweaks = preg_replace_callback($search, 'self::matchMulticacheCssSignature', $page);
        $tweaks = isset($tweaks) ? $tweaks : $page;
        $multicache_name = 'MulticachePluginCsstweaks';
        /*
         * if (property_exists('JsStrategy', 'stubs'))
         * {
         * $stub = unserialize(JsStrategy::$stubs);
         * $preheadsection_search = $stub["head_open"];
         * $headsection_search = $stub["head"];
         * $bodysection_search = $stub["body"];
         * $footersection_search = $stub["footer"];
         * }
         * else
         * {
         * $preheadsection_search = array(
         * '<head>'
         * );
         * $headsection_search = array(
         * '</head>'
         * );
         * $bodysection_search = array(
         * '<body>'
         * );
         * $footersection_search = array(
         * '</body>'
         * );
         * }
         */
        // transport from WP
        // at min these should be set
        /*
         * if (empty($headsection_search))
         * {
         * $headsection_search = array(
         * '</head>'
         * );
         * }
         * if (empty($footersection_search))
         * {
         * $footersection_search = array(
         * '</body>'
         * );
         * }
         */
        // start preg code
        $preheadsection_search = '~<head(
            (?(?=(\s[^>]*))\s[^>]*)
            )>~ixU';
        $headsection_search = '~</head(
            (?(?=(\s[^>]*))\s[^>]*)
            )>~ixU';
        $bodysection_search = '~<body(
            (?(?=(\s[^>]*))\s[^>]*)
            )>~ixU';
        $footersection_search = '~</body(
            (?(?=(\s[^>]*))\s[^>]*)
            )>~ixU';
        // replacers
        $prehead = '<head\1>';
        $head = '</head\1>';
        $body_tag = '<body\1>';
        $foot_tag = '</body\1>';
        // end preg code
        // begin
        $search_array = array(); // initialise search array to null array
        $replace_array = array();
        $comment_end = "Loaded by " . $multicache_name . " Copyright OnlineMarketingConsultants.in-->";
        $load_css = array();
        //critical css includes
        if(isset($this->css_groupsasync) && ("2" === $this->css_groupsasync || "1" === $this->css_groupsasync))
        {
        	
        	//new
        	if($_REQUEST['f'] ==='m')
        	{
        		$path = preg_replace('~[^a-zA-Z0-9]~','' ,$this->uri->getPath());
        		
        		$crit_loc_exclusive = $_SERVER['DOCUMENT_ROOT'].'/media/com_multicache/assets/css/multicachemobilecritical-'.$path.'.css';
        		
        	if(0)
        		{
        			$crit_stub = 'media/com_multicache/assets/css/multicachemobilecritical-'.$path.'.css';
        			//drop this name only if you neeed to get the path to which critical file must be setup
        			error_log($crit_stub , 3 , dirname(__FILE__).'/zzzCriticalfilename.log');
        		}
        		if(is_file($crit_loc_exclusive))
        		{
        			$crit_stub =   'media/com_multicache/assets/css/multicachemobilecritical-'.$path.'.css';
        			$crit_loc = $_SERVER['DOCUMENT_ROOT'].'/'. $crit_stub;
        			$href =  JURI::root() . $crit_stub;
        			$critical_css_link = '<link href="' . $href . '" rel="stylesheet" type="text/css" />';
        			$critical_added = false;
        		}
        		else{
        			$crit_loc = $_SERVER['DOCUMENT_ROOT'].'/media/com_multicache/assets/css/multicachemobilecriticalcss.css';
        			
        			if(is_file($crit_loc))
        			{
        				
        				//$path = Multicac
        				//$href = $this->uri->toString(array('scheme','host')).'/media/com_multicache/assets/css/multicachecriticalcss.css';
        				$href =  JURI::root() . 'media/com_multicache/assets/css/multicachemobilecriticalcss.css';
        				$critical_css_link = '<link href="' . $href . '" rel="stylesheet" type="text/css"/>';
        				$critical_added = false;
        			}
        			
        		}
        	}
        	else{
        		//start
        		$path = preg_replace('~[^a-zA-Z0-9]~','' ,$this->uri->getPath());
        		$crit_loc_exclusive = $_SERVER['DOCUMENT_ROOT'].'/media/com_multicache/assets/css/multicachecritical-'.$path.'.css';
        		if(0)
        		{
        			$crit_stub = 'media/com_multicache/assets/css/multicachecritical-'.$path.'.css';
        			//drop this name only if you neeed to get the path to which critical file must be setup
        			error_log($crit_stub , 3 , dirname(__FILE__).'/zzzCriticalfilenameDesktop.log');
        		}
        		if(is_file($crit_loc_exclusive))
        		{
        			$crit_stub = 'media/com_multicache/assets/css/multicachecritical-'.$path.'.css';
        			$crit_loc = $_SERVER['DOCUMENT_ROOT'] .'/'.$crit_stub;
        			//$href = plugins_url($crit_stub , dirname(__FILE__));
        			$href =  JURI::root() . $crit_stub ;//JURI::getInstance($crit_stub )->toString();
        			$critical_css_link = '<link href="' . $href . '" rel="stylesheet" type="text/css" />';
        			$critical_added = false;
        		}
        		else{
        			//stop
        			$crit_loc = $_SERVER['DOCUMENT_ROOT'] .'/media/com_multicache/assets/css/multicachecriticalcss.css';
        			
        			if(is_file($crit_loc))
        			{
        				//$href = $this->uri->toString(array('scheme','host')).'/media/com_multicache/assets/css/multicachecriticalcss.css';
        				$href = JURI::root() . 'media/com_multicache/assets/css/multicachecriticalcss.css';
        				
        				$critical_css_link = '<link href="' . $href . '" rel="stylesheet" type="text/css" />';
        				
        				$critical_added = false;
        			}
        			//end here
        		}
        	}
        	 
        	//end new
        	//old
        	/*
        	$crit_loc = $_SERVER['DOCUMENT_ROOT'].'/media/com_multicache/assets/css/multicachecriticalcss.css';
        	if(is_file($crit_loc))
        	{
        		$href = $this->uri->toString(array('scheme','host')).'/media/com_multicache/assets/css/multicachecriticalcss.css';
        		$critical_css_link = '<link href="' . $href . '" rel="stylesheet" type="text/css"/>';
        		$critical_added = false;
        	}
        	*/
        	//end old
        }

        // moderate self::$_js_orphaned
        if (! empty(self::$_css_orphaned_buffer) && ! empty(self::$_css_orphaned) && empty($loadsections_css[self::$_css_orphaned]))
        {
            if (is_array($loadsections_css))
            {
                $temp_css = array_filter($loadsections_css);
                $all_keys = array_keys($temp_css);
            }
            // loadsection is optimizing itself in the spl case that no other script exists at that section
            self::$_css_orphaned = isset($all_keys) && is_array($all_keys) ? max($all_keys) : self::$_css_orphaned;
        }
        if (! empty($loadsections_css[1]))
        {
            /*
             * foreach ($preheadsection_search as $prehead)
             * {
             */
            $search_array[] = $preheadsection_search;
            $load_css[1]["search"][] = $preheadsection_search;
            $comment = "<!--pre headsection css ";
            $r_code = $prehead . trim(unserialize($loadsections_css[1]));
            $load_css1_item_temp = trim(unserialize($loadsections_css[1]));
            if(false === $critical_added)
            {
            	$r_code .= $critical_css_link;
            	$load_css1_item_temp .=$critical_css_link; 
            	$critical_added = true;
            }
            if (! empty(self::$_css_orphaned_buffer) && isset(self::$_css_orphaned) && self::$_css_orphaned == 1)
            {

                $r_code = self::$_css_comments ? $r_code . '<!-- orphaned css -->' . self::$_css_orphaned_buffer : $r_code . self::$_css_orphaned_buffer;
                $load_css1_item_temp = self::$_css_comments ? $load_css1_item_temp . '<!-- orphaned css -->' . self::$_css_orphaned_buffer : $load_css1_item_temp . self::$_css_orphaned_buffer;
            }
            $replace_array[] = self::$_css_comments ? $r_code . $comment . $comment_end : $r_code;
            $load_css[1]["item"][] = self::$_css_comments ? $load_css1_item_temp . $comment . $comment_end : $load_css1_item_temp;
            $load_css[1]["tag"][] = $prehead;
            /*
             * }
             *
             */
        }

        if (! empty($loadsections_css[2]))
        {
            /*
             * foreach ($headsection_search as $head)
             * {
             */
            $search_array[] = $headsection_search;
            $load_css[2]["search"][] = $headsection_search;
            $comment = "<!-- headsection ";
            $r_code = trim(unserialize($loadsections_css[2]));
            $load_css2_item_temp = trim(unserialize($loadsections_css[2]));
            if(false === $critical_added)
            {
            	$r_code .= $critical_css_link;
            	$load_css2_item_temp .=$critical_css_link;
            	$critical_added = true;
            }
            if (! empty(self::$_css_orphaned_buffer) && isset(self::$_css_orphaned) && self::$_css_orphaned == 2)
            {

                $r_code = self::$_css_comments ? $r_code . '<!-- orphaned css -->' . self::$_css_orphaned_buffer : $r_code . self::$_css_orphaned_buffer;
                $load_css2_item_temp = self::$_css_comments ? $load_css2_item_temp . '<!-- orphaned css -->' . self::$_css_orphaned_buffer : $load_css2_item_temp . self::$_css_orphaned_buffer;
            }
            $r_code .= $head;
            $replace_array[] = self::$_css_comments ? $comment . $comment_end . $r_code : $r_code;
            $load_css[2]["item"][] = self::$_css_comments ? $comment . $comment_end . $load_css2_item_temp : $load_css2_item_temp;
            $load_css[2]["tag"][] = $head;
            /*
             * }
             */
        }

        if (! empty($loadsections_css[3]))
        {
            /*
             * foreach ($bodysection_search as $body_tag)
             * {
             */
            $search_array[] = $bodysection_search;
            $load_css[3]["search"][] = $bodysection_search;
            $comment = "<!-- bodysection ";
            $r_code = $body_tag . trim(unserialize($loadsections_css[3]));
            $load_css3_item_temp = trim(unserialize($loadsections_css[3]));
            if(false === $critical_added)
            {
            	$r_code .= $critical_css_link;
            	$load_css3_item_temp .=$critical_css_link;
            	$critical_added = true;
            }
            if (! empty(self::$_css_orphaned_buffer) && isset(self::$_css_orphaned) && self::$_css_orphaned == 3)
            {

                $r_code = self::$_css_comments ? $r_code . '<!-- orphaned css -->' . self::$_css_orphaned_buffer : $r_code . self::$_css_orphaned_buffer;
                $load_css3_item_temp = self::$_css_comments ? $load_css3_item_temp . '<!-- orphaned css -->' . self::$_css_orphaned_buffer : $load_css3_item_temp . self::$_css_orphaned_buffer;
            }
            $replace_array[] = self::$_css_comments ? $r_code . $comment . $comment_end : $r_code;
            $load_css[3]["item"][] = self::$_css_comments ? $load_css3_item_temp . $comment . $comment_end : $load_css3_item_temp;
            $load_css[3]["tag"][] = $body_tag;
            /* } */
        }

        if (! empty($loadsections_css[4]))
        {
            /*
             * foreach ($footersection_search as $foot_tag)
             * {
             */
            $search_array[] = $footersection_search;
            $load_css[4]["search"][] = $footersection_search;
            $comment = "<!-- footsection ";
            $r_code = trim(unserialize($loadsections_css[4]));
            $load_css4_item_temp = trim(unserialize($loadsections_css[4]));
            if(false === $critical_added)
            {
            	$r_code .= $critical_css_link;
            	$load_css4_item_temp .=$critical_css_link;
            	$critical_added = true;
            }
            if (! empty(self::$_css_orphaned_buffer) && isset(self::$_css_orphaned) && self::$_css_orphaned == 4)
            {

                $r_code = self::$_css_comments ? $r_code . '<!-- orphaned css -->' . self::$_css_orphaned_buffer : $r_code . self::$_css_orphaned_buffer;
                $load_css4_item_temp = self::$_css_comments ? $load_css4_item_temp . '<!-- orphaned css -->' . self::$_css_orphaned_buffer : $load_css4_item_temp . self::$_css_orphaned_buffer;
            }
            $r_code .= $foot_tag;
            $replace_array[] = self::$_css_comments ? $comment . $comment_end . $r_code : $r_code;
            $load_css[4]["item"][] = self::$_css_comments ? $comment . $comment_end . $load_css4_item_temp : $load_css4_item_temp;
            $load_css[4]["tag"][] = $foot_tag;
            /* } */
        }

        if (! empty($replace_array) && empty(self::$js_switch))
        {

            // $tweaks = str_replace($search_array, $replace_array, $tweaks);
            $tweaks = preg_replace($search_array, $replace_array, $tweaks);
            Return $tweaks;
        }

        $search_array = array();
        $replace_array = array();
        $load_js = self::$_js_replace;
        

        /*
         * Segmentwise stitching
         */
        // prehead segment

        if (isset($load_css))
        {
            foreach ($load_css as $key_lsec => $seg)
            {

                /*
                 * $replace_temp = null;
                 * foreach ($seg["search"] as $key => $val)
                 * {
                 * $search_array[] = $val;
                 * $replace_temp = $key_lsec % 2 != 0 ? $val : '';
                 * $replace_temp .= (isset($load_css[$key_lsec]["item"][$key])) ? $load_css[$key_lsec]["item"][$key] : '';
                 * $replace_temp .= (isset($load_js[$key_lsec]["item"][$key]) && $load_js[$key_lsec]["search"][$key] == $val) ? $load_js[$key_lsec]["item"][$key] : '';
                 * $replace_temp .= $key_lsec % 2 == 0 ? $val : '';
                 * }
                 * if (isset($replace_temp))
                 * {
                 * $replace_array[] = $replace_temp;
                 * }
                 */
                // Correction transport from WP
                foreach ($seg["search"] as $key => $val)
                {
                    $replace_temp = null;
                    $search_array[] = $val;
                    $replace_temp = $key_lsec % 2 != 0 ? $load_css[$key_lsec]["tag"][$key] : '';
                    $replace_temp .= (isset($load_css[$key_lsec]["item"][$key])) ? $load_css[$key_lsec]["item"][$key] : '';
                    $replace_temp .= (isset($load_js[$key_lsec]["item"][$key]) && $load_js[$key_lsec]["search"][$key] == $val) ? $load_js[$key_lsec]["item"][$key] : '';
                    $replace_temp .= $key_lsec % 2 == 0 ? $load_css[$key_lsec]["tag"][$key] : '';
                    if (isset($replace_temp))
                    {
                        $replace_array[] = $replace_temp;
                    }
                }
            }
        }
//adding critical css

        if (isset($load_js))
        {
            foreach ($load_js as $key_lsec_js => $seg_js)
            {
            	
                /*
                 *
                 * $replace_temp = null;
                 * foreach ($seg_js["search"] as $key_js => $val_js)
                 * {
                 * if (in_array($val_js, $search_array))
                 * {
                 * continue;
                 * }
                 * $search_array[] = $val_js;
                 * $replace_temp = $key_lsec_js % 2 != 0 ? $val : '';
                 * $replace_temp .= (isset($load_css[$key_lsec_js]["item"][$key_js]) && $load_css[$key_lsec_js]["search"][$key_js] == $val_js) ? $load_css[$key_lsec_js]["item"][$key_js] : '';
                 * $replace_temp .= (isset($load_js[$key_lsec_js]["item"][$key_js])) ? $load_js[$key_lsec_js]["item"][$key_js] : '';
                 * $replace_temp .= $key_lsec_js % 2 == 0 ? $val_js : '';
                 * }
                 * if (isset($replace_temp))
                 * {
                 * $replace_array[] = $replace_temp;
                 * }
                 */
                // Correction transport from WP
                foreach ($seg_js["search"] as $key_js => $val_js)
                {
                    $replace_temp = null;
                    if (in_array($val_js, $search_array))
                    {
                        continue;
                    }
                    $search_array[] = $val_js;
                    $replace_temp = $key_lsec_js % 2 != 0 ? $load_js[$key_lsec_js]["tag"][$key_js] : '';
                    $replace_temp .= (isset($load_css[$key_lsec_js]["item"][$key_js]) && $load_css[$key_lsec_js]["search"][$key_js] == $val_js) ? $load_css[$key_lsec_js]["item"][$key_js] : '';
                    if(false === $critical_added && $key_lsec_js == 2)
                    {
                    	$replace_temp .= $critical_css_link;
                    	$critical_added = true;
                    }
                    $replace_temp .= (isset($load_js[$key_lsec_js]["item"][$key_js])) ? $load_js[$key_lsec_js]["item"][$key_js] : '';
                    $replace_temp .= $key_lsec_js % 2 == 0 ? $load_js[$key_lsec_js]["tag"][$key_js] : '';
                    if (isset($replace_temp))
                    {
                        $replace_array[] = $replace_temp;
                    }
                }
            }
        }

        if (! empty($replace_array))
        {

            // $tweaks = str_replace($search_array, $replace_array, $tweaks);
            $tweaks = preg_replace($search_array, $replace_array, $tweaks);
            Return $tweaks;
        }

        Return $page;

    }

    protected static function imgAttrRep($matches)
    {

        if (empty($matches))
        {
            Return $matches[0];
        }
        $attributes = array();
        foreach ($matches as $key => $match)
        {
            if (strpos($key, 'MULTICACHEGROUP') !== false)
            {
                continue;
            }
            if ($key % 2 === 1)
            {
                $type = ! empty($match) ? trim($match) : null;
            }
            else
            {
                if (isset($type))
                {
                    switch ($type)
                    {
                        case 'id':
                            $attributes["id"] = ! empty($match) ? trim($match) : '';
                            break;
                        case 'class':
                            $attributes["classes"] = ! empty($match) ? trim($match) : '';
                            break;
                        case 'src':
                            $attributes["src"] = ! empty($match) ? trim($match) : '';
                            break;
                        case 'alt':
                            $attributes["alt"] = ! empty($match) ? trim($match) : '';
                            break;
                        case 'style':
                            $attributes["style"] = ! empty($match) ? trim($match) : '';

                            break;
                        case 'type':
                            $attributes["type"] = ! empty($match) ? trim($match) : '';
                            break;
                        case 'data_original':
                            $attributes["data_original"] = ! empty($match) ? trim($match) : '';
                            break;
                        case 'srcset':
                            $attributes["srcset"] = ! empty($match) ? trim($match) : '';
                            break;
                        case 'height':
                            $attributes["height"] = ! empty($match) ? trim($match) : '';
                            break;
                        case 'width':
                            $attributes["width"] = ! empty($match) ? trim($match) : '';
                            break;
                        case 'title':
                            $attributes["title"] = ! empty($match) ? trim($match) : '';
                            break;
                    }
                }
            }
        }

        $attributes["closure_type"] = strpos(trim($matches[0]), '/>') !== false ? 'html' : 'xml';
        // end transport from WP

        if (! empty($attributes["classes"]))
        {
            $attributes["class"] = array_map('trim', explode(' ', $attributes["classes"]));
        }

        // start sending them home

        if (empty($attributes["src"]) || (isset($attributes["src"]) && strpos(trim($attributes["src"]), 'data:') === 0))
        {
            Return $matches[0];
        }
        
        if(isset(self::$_multicache_extraordinary_nolazy))
        {
        	foreach(self::$_multicache_extraordinary_nolazy As $key =>$src)
        	{
        		if(stripos($attributes["src"], $src['name']) !== false )
        		{
        			Return $matches[0];
        		}
        	}
        }
        // plugin exclusions not required for Joomla
        // $container_rules = null;abandoned due to memory leaks in phpquery lib
        $img_selector_rules = null;
        $img_deselector_rules = null;
        if (! isset($params))
        {
            $params = ! empty(self::$_img_tweak_params) ? self::$_img_tweak_params : null;
        }
        if (! isset($params_urlstrings))
        {
            $params_urlstrings = ! empty(self::$_img_urlstringexclude) ? self::$_img_urlstringexclude : null;
        }
        if (! empty($params_urlstrings))
        {
            foreach ($params_urlstrings as $url_bit)
            {
                if (strpos($attributes["src"], $url_bit) !== false)
                {
                    Return $matches[0];
                }
            }
        }
        // moderate params to unserialize serialized content

        if (! empty($params["img_selectors_switch"]) && isset($params["img_selector_rules"]))
        {
            $img_selector_rules = unserialize($params["img_selector_rules"]);
        }
        if (! empty($params["image_deselector_switch"]) && isset($params["image_deselector_rules"]))
        {
            $img_deselector_rules = unserialize($params["image_deselector_rules"]);
        }

        $continue_flag = false;
        $curid = ! empty($attributes["id"]) ? $attributes["id"] : null;
        // deselctors
        if (! empty($img_deselector_rules))
        {
            $img_deselector_rules = array_map('trim', $img_deselector_rules);
            foreach ($img_deselector_rules as $deselct)
            {
                if (strpos($deselct, '.') === 0 && ! empty($attributes["class"]))
                {
                    $classname = substr($deselct, 1);

                    $cf = in_array($classname, $attributes["class"]);

                    if ($cf)
                    {
                        Return $matches[0];
                    }
                }
                elseif ((strpos($deselct, '#') === 0) && isset($curid))
                {
                    $deselct = substr($deselct, 1);

                    if ($curid === $deselct)
                    {
                        Return $matches[0];
                    }
                }
            }
        }

        // end deselectors
        // selectors

        if (! empty($img_selector_rules))
        {
            $img_selector_rules = array_map('trim', $img_selector_rules);
            $continue_flag = true;
            foreach ($img_selector_rules as $select)
            {
                if (strpos($select, '.') === 0 && ! empty($attributes["class"]))
                {
                    $classname = substr($select, 1);
                    $cf = in_array($classname, $attributes["class"]);

                    if ($cf)
                    {
                        $continue_flag = false;
                        break;
                    }
                }
                elseif ((strpos($select, '#') === 0) && isset($curid))
                {
                    $select = substr($select, 1);

                    if ($curid === $select)
                    {

                        $continue_flag = false;
                        break;
                    }
                }
            }

            if ($continue_flag)
            {
                Return $matches[0];
            }
        }

        // end selectors

        $lazyimage = self::makeLazeImage($attributes);
        Return $lazyimage . '<noscript>' . $matches[0] . '</noscript>';

    }

    protected function performIMGtweaks($page)
    {

        /*
         * // initiating vars
         * if (empty(self::$_img_tweak_params) && property_exists('JsStrategy', 'img_tweak_params'))
         * {
         * self::$_img_tweak_params = JsStrategy::$img_tweak_params;
         * }
         * if (empty(self::$_img_tweak_params))
         * {
         * Return $page;
         * }
         *
         * $search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<img([^>]+)>))~';
         *
         * $page = preg_replace_callback($search, 'self::imgAttrRep', $page);
         *
         * Return $page;
         */
        // transport from WP
        // initiating vars
        if (empty(self::$_img_tweak_params) && property_exists('JsStrategy', 'img_tweak_params'))
        {
            self::$_img_tweak_params = JsStrategy::$img_tweak_params;
        }
        if (empty(self::$_img_urlstringexclude) && property_exists('JsStrategy', 'IMGurl_strings'))
        {
            self::$_img_urlstringexclude = JsStrategy::$IMGurl_strings;
        }
        if(defined('MULTICACHEIMAGENOLAZY') && isset($_REQUEST['f']) && $_REQUEST['f'] == 'm')
        {
        
        	self::$_multicache_extraordinary_nolazy = property_exists('MulticacheMobileNoLazy' , '_no_lazy')? MulticacheMobileNoLazy::$_no_lazy : null;
        
        }
        if (empty(self::$_img_tweak_params))
        {
            Return $page;
        }
        //issues with IAL register
        //$page = html_entity_decode($page);
        /* $search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<img([^>]+)>))~'; */
        /* $search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<\s*img\s{1}.*(width|height|src|class|id|alt|title|data_original|style|type|srcset)=[\'"]{1}([^\'"]*)[\'"]{1}.*(width|height|src|class|id|alt|title|data_original|style|type|srcset)=[\'"]{1}([^\'"]*)[\'"]{1}.*(width|height|src|class|id|alt|title|data_original|style|type|srcset)=[\'"]{1}([^\'"]*)[\'"]{1}.*>))~ixU'; 
//version without unicode support
        $search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<\s*img\s{1}[^>]*

(?(?=[^>]*(?<MULTICACHEGROUP1>[\w-]+)=)(?:\k<MULTICACHEGROUP1>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP2>[\w-]+)=)(?:\k<MULTICACHEGROUP2>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP3>[\w-]+)=)(?:\k<MULTICACHEGROUP3>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP4>[\w-]+)=)(?:\k<MULTICACHEGROUP4>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP5>[\w-]+)=)(?:\k<MULTICACHEGROUP5>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP6>[\w-]+)=)(?:\k<MULTICACHEGROUP6>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP7>[\w-]+)=)(?:\k<MULTICACHEGROUP7>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP8>[\w-]+)=)(?:\k<MULTICACHEGROUP8>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
		>))~ixU';
		*/
        //transport from WP unicode support
        $search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<\s*img\s{1}[^>]*
        
(?(?=[^>]*(?<MULTICACHEGROUP1>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP1>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP2>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP2>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP3>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP3>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP4>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP4>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP5>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP5>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP6>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP6>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP7>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP7>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP8>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP8>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
        		>))~ixUu';
        $pagenew = preg_replace_callback($search, 'self::imgAttrRep', $page);
        $page = isset($pagenew) ? $pagenew : $page;

        Return $page;
        // end transport from WP
    }

    /*
     * protected static function matchIMGcase($name, $string, $type = 1)
     * {
     *
     * if ($type == 1)
     * {
     * $pattern = '~' . $name . '=["\']([^"\']+)["\']~';
     * }
     * elseif ($type == 2)
     * {
     * $pattern = '~' . $name . '=["\']?([\S]+)["\']?~';
     * }
     *
     * // $s = '(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?';
     * preg_match($pattern, $string, $matches);
     * Return $matches[1];
     *
     * }
     */
    protected static function makeLazeImage($img_attr)
    {

        $image_string_start = '<img';
        // $image_string_end = $img_attr["type"] == 'html' ? ' />' : ' >';
        // correction from WP due to regex change
        $image_string_end = $img_attr["closure_type"] == 'html' ? ' />' : ' >';
        $image_attributes = '';
        if (empty($img_attr["class"]))
        {
            $classes_string = " multicache_lazy";
        }
        else
        {
            $classes_string = implode(' ', $img_attr["class"]);
            $classes_string .= ' multicache_lazy';
        }
        $image_attributes = ' class="' . $classes_string . '" ';
        foreach ($img_attr as $key => $attr)
        {
            if (empty($attr))
            {
                continue;
            }
            // correction from WP due to changes in regex
            if ($key != 'classes' /*&& $key != 'type'*/ && $key != 'closure_type' && $key != 'class')
            {
                $key = $key != 'src' ? $key : 'data-original';
                $image_attributes .= ' ' . $key . '=' . '"' . $attr . '" ';
            }
        }

        $image = $image_string_start . $image_attributes . $image_string_end;
        Return $image;

    }

    protected static function matchMulticacheCssSignature($matches)
    {

        $sig = md5(serialize($matches[0]));

        if (isset(self::$_signatures_css[$sig]))
        {
            $blank = "";
            Return $blank;
        }
        else
        {

            if (empty(self::$_css_orphaned))
            {
                Return $matches[0];
            }
            $blank = '';
            //detox begin
            
                      
            
            if(isset(self::$_detox_orphaned_css_link) && !empty($matches[1]))
            {
            	//src links
            	foreach(self::$_detox_orphaned_css_link As $key=> $src)
            	{
            		if(strpos($matches[1] , $src['name']) !== false)
            		{
            			Return $blank;
            		}
            	}
            
            }
            elseif(isset(self::$_detox_orphaned_css_code) && !isset($matches[1]) && !empty($matches[0]))
            {
            
            	foreach(self::$_detox_orphaned_css_code As $key=> $code)
            	{
            		$check_1 = $check_2 = $check_3 = false;
            		if(empty($check_1) && strpos($matches[0] , $code['name']) !== false)
            		{
            			//Return $blank;
            			$check_1 = true;
            		}
            		if(empty($check_2) && strpos($matches[0] , $code['name_2']) !== false)
            		{
            			//Return $blank;
            			$check_2 = true;
            		}
            		if(empty($check_3) && strpos($matches[0] , $code['name_3']) !== false)
            		{
            			//Return $blank;
            			$check_3 = true;
            		}
            		if($check_1 && $check_2 && $check_3)
            		{
            			$test_ejection = false;
            			if($test_ejection)
            			{
            				error_log(var_export($matches[2] , true) , 3 , dirname(__FILE__).'/zzcsseject.log');
            			}
            			Return $blank;
            		}
            	}
            }
            //detox end
            if (! isset(self::$_css_orphan_sig[$sig]))
            {
                self::$_css_orphaned_buffer .= $matches[0];
                self::$_css_orphan_sig[$sig] = 1; // loads orphans only once
            }
            Return $blank;
        }

    }

    public function onAfterDispatch()
    {

        $app = JFactory::getApplication();
        if ($app->isAdmin())
        {
            Return;
        }
        $this->initPageCacheClear();
        // return if switched off
        if (! ($this->conduit_switch || $this->_img_tweaks))
        {
            return;
        }
        /*
         * The following are only necessary to hard code conduit. However leaving it as a normal script provides more options in terms of grouping and delays at the javascript tweakr
         * //not applicable if jstweaks is on: Operation:Normal(nontesting)->JSTEAWKS
         * if (class_exists('JsStrategy') && self::$js_switch)
         * {
         * return;
         * }
         *
         * //not applicable to Advanced simulation : Operation:Testing->SIMULATION->ADVANCED
         * if (class_exists('JsStrategySimControl') && $this->js_simulation && $this->js_advanced)
         * {
         * return;
         * }
         *
         *
         */
        // normal filters 1) Not applicable for admin 2) not applicable for loggedin users and 3) not applicable for post requests

        if ($app->isAdmin() || ! JFactory::getUser()->get('guest') || $app->input->getMethod() != 'GET')
        {

            return;
        }
        /*
         * //least in hierarchy on expected request rate
         * if (null !== $app->input->get('multicachetask'))
         * {
         * return;
         * }
         */
        $document = JFactory::getDocument();
        if ($this->conduit_switch == 1)
        {
            $document->addScript(JURI::Root() . 'media/com_multicache/assets/js/conduit.js');
        }
        elseif ($this->conduit_switch == 2)
        {
            $document->addScript(JURI::Root() . 'media/com_multicache/assets/js/conduit_jquery.js');
        }

        // loading libraries for image tweaks
        if (! empty($this->_img_tweaks))
        {

            if (property_exists('JsStrategy', 'img_tweak_params') && $this->canDoOP('IMG'))
            {
                if (empty(self::$_img_tweak_params))
                {
                    self::$_img_tweak_params = JsStrategy::$img_tweak_params;
                }

                if (! empty(self::$_img_tweak_params["ll_script"]) && ! empty(self::$_img_tweak_params["ll_style"]))
                {
                    $script = unserialize(self::$_img_tweak_params["ll_script"]);
                    $style = unserialize(self::$_img_tweak_params["ll_style"]);
                    // ensure the jQuery library is loaded

                    JHtml::_('jquery.framework');
                    JHtml::_('jquery.ui');
                    $document->addScript(JURI::Root() . 'media/com_multicache/assets/js/jquery.lazyload.js');
                    $document->addScriptDeclaration($script);
                    $document->addStyleDeclaration($style);
                }
            }
        }

    }
    protected function doMobileOptimizations()
    {
    	$app = JFactory::getApplication();
    	$body = $app->getBody();
    	$search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<script(?= (?> [^\\s>]*+[\\s] (?(?=type= )type=["\']?(?:text|application)/javascript ) )*+ [^\\s>]*+> )(?:(?> [^\\s>]*+\\s )+? (?>src)=["\']?( (?<!["\']) [^\\s>]*+| (?<!\') [^"]*+ | [^\']*+ ))?[^>]*+>(?: (?> <?[^<]*+ )*? )</script>)|\\K$)~six';
    
    	$tweaks = preg_replace_callback($search, 'self::matchJsMobileSignature', $body);
    	//removeParts
    	if(method_exists('MulticacheMobileStrategy' , 'removeParts'))
    	{
    		$body = MulticacheMobileStrategy::removeParts($body);
    	}
    	$search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<link(?= (?>[^\\s>]*+[\\s] (?!(?:itemprop|disabled|type=(?!  ["\']?text/css)|rel=(?!["\']?stylesheet))))*+[^\\s>]*+>)(?>[^\\s>]*+\\s)+?(?>href)=["\']?((?<!["\'])[^\\s>]*+|(?<!\')[^"]*+| [^\']*+)[^>]*+>)|(?:<style(?:(?!(?:type=(?!["\']?text/css))|(?:scoped))[^>])*>(?:(?><?[^<]+)*?)</style>)|\\K$)~six';
    	//preg_match_all($search , $tweaks , $e_message);
    	/*
    	$e_message = print_r($e_message ,true);
    	error_log($e_message , 3, __DIR__ .'/e_message.log');
    	*/
    	if(0 && class_exists('MulticacheMobileStrategy'))
    	{
    		$tweaks = preg_replace_callback($search, 'self::matchCssMobileSignature', $tweaks);
    		if(self::$mobile_strategy_replace_inlinestyle)
    		{
    			$tweaks = str_replace('</body' , self::$master_inlinecss_buffer_mobile . '</body' , $tweaks);
    		}
    	}
    	$options = array();
    	$options['minify_level'] = 3;
    	$options['jsMinifier'] = array(
    			'MulticacheJSOptimize',
    			'process'
    	);
    	$options['cssMinifier'] = array(
    			'MulticacheCSSOptimize',
    			'optimize'
    	);
    	$options['js_comments'] = self::$_jscomments;
    	$options['css_comments'] = self::$_css_comments;
    	$options['jsCleanComments'] = true;
    	$tweaks = MulticacheHtmlMinify::process($tweaks, $options);
    	$app->setBody($tweaks);
    }
    protected static function matchJsMobileSignature( $matches)
    {
    	if (empty($matches[0]))
    	{
    		Return $matches[0];
    	}
    	$sub = $matches[0];
    	if(stripos($sub , ' async ') ===false)
    	{
    		$sub = str_replace('>' , ' async >', $sub);
    	}
    	if(stripos($sub , ' defer ') ===false)
    	{
    		$sub = str_replace('>' , ' defer >', $sub);
    	}
    	$sub = MulticacheJSOptimize::process($sub);
    	Return $sub;
    }
    
    protected function doExtraOrdinaryHacks($setbodyflag , $body_sub = false)
    {
    	if(!$setbodyflag)
    	{
    		$app = JFactory::getApplication();
    		$body_sub = $app->getBody();
    	}
    	/*$search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<link(?= (?>[^\\s>]*+[\\s] (?!(?:itemprop|disabled|type=(?!  ["\']?text/css)|rel=(?!["\']?stylesheet))))*+[^\\s>]*+>)(?>[^\\s>]*+\\s)+?(?>href)=["\']?((?<!["\'])[^\\s>]*+|(?<!\')[^"]*+| [^\']*+)[^>]*+>)|(?:<style(?:(?!(?:type=(?!["\']?text/css))|(?:scoped))[^>])*>(?:(?><?[^<]+)*?)</style>)|\\K$)~six';*/
    	$search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<link(?= (?>[^\\s>]*+[\\s] (?!(?:itemprop|disabled|type=(?!  ["\']?text/css)|rel=(?!["\']?stylesheet))))*+[^\\s>]*+>)(?>[^\\s>]*+\\s)+?(?>href)=["\']?((?<!["\'])[^\\s>]*+|(?<!\')[^"]*+| [^\']*+)[^>]*+>)|\\K$)~six';
    	//preg_match_all($search ,$body_sub , $e_message );
    	//var_dump($matches);exit;
    	
    	 //$e_message = print_r($e_message ,true);
    	 //error_log($e_message , 3, __DIR__ . '/e_message.log' );
    	 
    	$tweaks = preg_replace_callback($search, 'self::matchMulticacheExtraordinary', $body_sub);
    	/*
    	 if(isset(self::$master_inlinecss_buffer))
    	 {
    		//$tweaks = str_replace('</body' ,'<!--MulticacheInliningCSs-->'.self::$master_inlinecss_buffer .'</body' , $tweaks  );
    		}
    		*/
    	Return $tweaks;
    }
    //lets build static property master_inlinecss_buffer
    protected static function matchMulticacheExtraordinary($matches)
    {
    	if(empty($matches[0]))
    	{
    		Return $matches[0];
    	}
    	$inline_css = property_exists('MulticacheExtraOrdinary' , '_css_property')? MulticacheExtraOrdinary::$_css_property : false;
    	if(!$inline_css)
    	{
    		Return $matches[0];
    	}
    	foreach($inline_css As $css_obj)
    	{
    		if(strpos($matches[1] , $css_obj['name']) !== false)
    		{
    			//self::$master_inlinecss_buffer .= $css_obj['css'];
    			Return $css_obj['css'];
    		}
    	}
    	Return $matches[0];
    }
    protected static function matchCssMobileSignature($matches)
    {
    	if (empty($matches[0]))
    	{
    		Return $matches[0];
    	}
    	if(!isset($inline_css))
    	{
    	    $inline_css = property_exists('MulticacheMobileStrategy' , '_mobile_property')? MulticacheMobileStrategy::$_mobile_property : false;
    	}
    	if(!$inline_css)
    	{
    		Return $matches[0];
    	}
    	
    	if(!$inline_css)
    	{
    		Return $matches[0];
    	}
    	$sub = $matches[0];
    	foreach($inline_css As $css_obj)
    	{
    	   if(stripos($sub , $css_obj['url']) !==false)
    	    {
    	    self::$master_inlinecss_buffer_mobile .= $css_obj['style'];
    	    //IMP FLAG change to false to append style at location
    		self::$mobile_strategy_replace_inlinestyle = true;
    		  if(self::$mobile_strategy_replace_inlinestyle)
    		  {
    		   Return '';
    		  }
    		
    		Return $css_obj['style'];
    	    }
    	}
    	Return $matches[0];
    }
    
    protected function doExtraOrdinaryImageHacks($setbodyflag , $body_sub = false)
    {
    	if(!$setbodyflag)
    	{
    		$app = JFactory::getApplication();
    		$body_sub = $app->getBody();
    	}
    	//add blank 1 px image to body
    	/*
    	if(0)
    	{
    		$cnt = 1;
    	$img = '<img src="/media/com_multicache/assets/images/FF4D00-0.png" style="display:none">';
    	$tag = '';
    	   for($i = 0 ; $i<=$cnt; $i++ )
    	   {
    		$tag .= $img;
    	    }
    	    $tag .= '</body>';
    	    $body_sub = str_replace('</body>' ,$tag , $body_sub );
    	}
    	*/
    	/*
    	$search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<\s*img\s{1}[^>]*

(?(?=[^>]*(?<MULTICACHEGROUP1>[\w-]+)=)(?:\k<MULTICACHEGROUP1>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP2>[\w-]+)=)(?:\k<MULTICACHEGROUP2>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP3>[\w-]+)=)(?:\k<MULTICACHEGROUP3>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP4>[\w-]+)=)(?:\k<MULTICACHEGROUP4>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP5>[\w-]+)=)(?:\k<MULTICACHEGROUP5>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP6>[\w-]+)=)(?:\k<MULTICACHEGROUP6>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP7>[\w-]+)=)(?:\k<MULTICACHEGROUP7>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
(?(?=[^>]*(?<MULTICACHEGROUP8>[\w-]+)=)(?:\k<MULTICACHEGROUP8>=[\'"]{1}([^\'"]*)[\'"]{1}.*) )
		>))~ixU';
		NO unicode support
    	$search = '~(?><noscript).*?(?></noscript>)(*SKIP)(*F)|(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<\s*img\s{1}[^>]*
    	
(?(?=[^>]*(?<MULTICACHEGROUP1>[\w-]+)=)(?:\k<MULTICACHEGROUP1>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP2>[\w-]+)=)(?:\k<MULTICACHEGROUP2>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP3>[\w-]+)=)(?:\k<MULTICACHEGROUP3>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP4>[\w-]+)=)(?:\k<MULTICACHEGROUP4>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP5>[\w-]+)=)(?:\k<MULTICACHEGROUP5>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP6>[\w-]+)=)(?:\k<MULTICACHEGROUP6>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP7>[\w-]+)=)(?:\k<MULTICACHEGROUP7>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP8>[\w-]+)=)(?:\k<MULTICACHEGROUP8>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
		>))~ixU';
		*/
    	//transport from multicacheWP
    	$search = '~(?><noscript).*?(?></noscript>)(*SKIP)(*F)|(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<\s*img\s{1}[^>]*
   
(?(?=[^>]*(?<MULTICACHEGROUP1>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP1>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP2>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP2>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP3>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP3>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP4>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP4>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP5>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP5>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP6>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP6>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP7>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP7>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
(?(?=[^>]*(?<MULTICACHEGROUP8>[\pL\pN\w-]+)=)(?:\k<MULTICACHEGROUP8>=(?:[\'"]{1}|(?>&quot;))([^\'"]*)(?:[\'"]{1}|(?>&quot;)).*) )
    			>))~ixUu';
    	
    	$tweaks = preg_replace_callback($search ,'self::matchMulticacheExtraordinaryImage',$body_sub );
    	$tweaks = isset($tweaks) ? $tweaks : $body_sub;
    
    	Return $tweaks;
    }
    
protected static function matchMulticacheExtraordinaryImage($sub)
{
if (empty($sub))
        {
            Return $sub[0];
        }
        
        $attributes = array();
        foreach ($sub as $key => $match)
        {
        
        	if (strpos($key, 'MULTICACHEGROUP') !== false)
        	{
        		continue;
        	}
        	if ($key % 2 === 1)
        	{
        		$type = ! empty($match) ? trim($match) : null;
        
        	}
        	else
        	{
        		if (isset($type))
        		{
        
        			switch ($type)
        			{
        				case 'id':
        					$attributes["id"] = ! empty($match) ? trim($match) : '';
        					break;
        				case 'class':
        					$attributes["class"] = ! empty($match) ? trim($match) : '';
        					break;
        				case 'src':
        					$attributes["src"] = ! empty($match) ? trim($match) : '';
        					break;
        				case 'alt':
        					$attributes["alt"] = ! empty($match) ? trim($match) : '';
        					break;
        				case 'style':
        					$attributes["style"] = ! empty($match) ? trim($match) : '';
        
        					break;
        				case 'type':
        					$attributes["type"] = ! empty($match) ? trim($match) : '';
        					break;
        				case 'data-original':
        					$attributes["data-original"] = ! empty($match) ? trim($match) : '';
        					break;
        				case 'srcset':
        					$attributes["srcset"] = ! empty($match) ? trim($match) : '';
        					break;
        				case 'height':
        					$attributes["height"] = ! empty($match) ? trim($match) : '';
        					break;
        				case 'width':
        					$attributes["width"] = ! empty($match) ? trim($match) : '';
        					break;
        				case 'title':
        					$attributes["title"] = ! empty($match) ? trim($match) : '';
        					break;
        				case 'data-lazyload':
        					$attributes["data-lazyload"] = ! empty($match) ? trim($match) : '';
        					break;
        			}
        		}
        	}
        }
        if(empty($attributes["data-lazyload"]) && empty($attributes["data-original"]) && empty($attributes["src"]))
        {
        	Return $sub[0];
        }
        $src_string = !empty($attributes["data-lazyload"])?$attributes["data-lazyload"]:( !empty($attributes["data-original"]) ? $attributes["data-original"] :$attributes["src"]);
        //start
        static $inline_imageencode;
        if(!isset($inline_imageencode))
        {
        $inline_imageencode = property_exists('MulticacheExtraOrdinaryImage' , '_inlinebaseimage_property')? MulticacheExtraOrdinaryImage::$_inlinebaseimage_property : false;
         }
         
        if(!$inline_imageencode)
        {
        	Return $sub[0];
        }
        foreach($inline_imageencode As $image_obj)
        {
        	if(strpos($src_string , $image_obj['name']) !== false)
        	{
        		//self::$master_inlinecss_buffer .= $css_obj['css'];
        		Return self::makeBaseImage($attributes , $src_string , $sub , $image_obj);
        	}
        }
        Return $sub[0];
        //end
        
        
        
	
}

protected static function baseImageresolvePath($path , $obj)
{
	static $site;
	if(!isset($site))
	{
		$site = JURI::getInstance()->toString(array('scheme' , 'host'));
	}
	
	if((strpos($path , 'http://') !==false || strpos($path , 'https://') !== false) 
			&&!(strpos($path , $site) === 0))
	{
		
		Return $path;
	}
	
	static $root;
	if(!isset($root))
	{
		$root = $_SERVER["DOCUMENT_ROOT"];
	}
	
	//correction for dynamic images
	if(isset($obj['type']) && $obj['type'] === 'php')
	{
		Return $path;
	}
	
	if(strpos($path , $site) === 0)
	{
		$path = str_replace($site , $root , $path);
		
		Return $path;
	}
	
	if(strpos($path ,'/') === 0)
	{
		Return $root. $path;
	}
	
	If(preg_match('~^[a-zA-Z]~' , $path))
	{
		Return $root . '/' . $path;
	}
	Return $path;
}
protected static function makeBaseImage($attr ,$src_string , $sub , $image_obj)
{
	$m = preg_match('~^[^\s]+\.(?:jpe?g|png|gif)~' , $src_string , $sub_src_string);
	if(!$m)
	{
		/*$e_message = var_export(array($src_string ) , true);
		 error_log($e_message, 3 , dirname(dirname(__FILE__)).'/logs/FAILEDTYPES.log');*/
		Return $sub[0];
	}
	$type = pathinfo($sub_src_string[0], PATHINFO_EXTENSION);
	 
	/*
	if(strpos($src_string , 'http://') === false
			&& strpos($src_string , 'https://') === false
			)
	{
		if(strpos($src_string , '/') !== 0)
		{
			$src_string = '/' . $src_string;
		}
		$a = JURI::getInstance();
		$a->setPath($src_string);
		$src_string = $a->toString(array('scheme' ,'host' , 'path'));
	}
	*/
	$src_string = self::baseImageresolvePath($src_string , $image_obj);
	$src_string = html_entity_decode($src_string);
	$data = @file_get_contents($src_string);
	
	if($data === false){
		//echo "<h2>failed $src_string</h2>";exit;
		Return $sub[0];
	}
	$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
	$img_tag_open = "<img";
	$attr["class"] = str_replace('multicache_lazy' , ' ' ,$attr["class"]);
	if(isset($attr["data-lazyload"]))
	{
		unset($attr["data-lazyload"]);
	}
	$img_tag = "";
	/*
	if($attr["src"] == "/images/speed.jpg")
	{
		//FILE_USE_INCLUDE_PATH
		
		var_dump($src_string , $type ,$attr ,$data );
		var_dump($a);
		
		exit;
	}
	*/
	foreach($attr As $key => $a)
	{
		if($key == 'src' || $key == 'data-original')
		{
			continue;
		}
		$img_tag .= ' '. $key . '=' . '"'. $a.'"';
	}
	$img = $img_tag_open .' src="'. $base64 .'" '. $img_tag . '/>';
	
	Return $img;
}
    public function onAfterRender()
    {
        // hack
        $u_check = $this->uri->toString();
        $format = $this->uri->getVar('format');
        $option = $this->uri->getVar('option');
        if (strpos($u_check, '-solar-installers') !== false || strpos($u_check, '/feed/') !== false ||
        // create-an-account
        strpos($u_check, '/create-an-account/') !== false || strpos($u_check, '/rss/') !== false || strpos($u_check, '/atom/') !== false || $format == 'ajax' || strpos($u_check, '=geomaps_map') !== false || ($option == 'com_jrevews' && $format == 'ajax'))
        {
            Return;
        }
        // end hack

        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        if ($app->isAdmin() || ! JFactory::getUser()->get('guest') || $app->input->getMethod() != 'GET')
        {

            return;
        }

        if (count($app->getMessageQueue()))
        {

            return;
        }
        if (isset($this->_debug_mode))
        {

            $debug_url = unserialize($this->_debug_mode);

            if (strtolower($debug_url) !== strtolower($this->current))
            {
                return;
            }
        }
        // do we need to work on this page
        $body_sub = $app->getBody();
        $match2 = preg_match('#^(?><?[^<]*+)+?<html(?><?[^<]*+)+?<head(?><?[^<]*+)+?</head(?>' . '<?[^<]*+)+?<body(?><?[^<]*+)+?</body(?><?[^<]*+)+?</html.*+$#i', $body_sub);
        if (! $match2)
        {
        	Return;
        }
        //hack for mobile pages
        if($format == 'mobile' && defined('MULTICACHEEXTRAORDINARYMOBILE'))
        {
        	$this->doMobileOptimizations();
        
        	Return;
        }
        $setbodyflag = false;
        // were not checking for class simcontrol as the work flow assumes that simcontrol can be prepared only if initial strategy exists

        if ($this->_img_tweaks && $this->canDoOP('IMG'))
        {
            $body_sub = $app->getBody();

            $body_sub = $this->performIMGtweaks($body_sub);

            $setbodyflag = true;
        }
        $scraping = (null === $app->input->get('multicachecsstask', null)) && (null === $app->input->get('multicachetask', null));
        if (class_exists('JsStrategy') && $scraping && (self::$js_switch || ($this->js_simulation && $this->js_advanced)))
        {
            if (! $setbodyflag)
            {
                $body_sub = $app->getBody();
            }

            $body_sub = $this->performJstweaks($body_sub);
            $setbodyflag = true;
        }
        if (class_exists('JsStrategy') && $scraping && self::$css_switch)
        {
            if (! $setbodyflag)
            {

                $body_sub = $app->getBody();
            }

            $body_sub = $this->performCsstweaks($body_sub);
            $setbodyflag = true;
        }
        //extraordinary hacks
        if(defined('MULTICACHEEXTRAORDINARY') && $scraping)
        {
        	$body_sub = $this->doExtraOrdinaryHacks($setbodyflag , $body_sub);
        	$setbodyflag = true;
        }
        //extraordinary images
        if(defined('MULTICACHEEXTRAORDINARYIMAGE') && $scraping)
        {
        	$body_sub = $this->doExtraOrdinaryImageHacks($setbodyflag , $body_sub);
        	$setbodyflag = true;
        }
        //end extraordinary images
        if ($this->_minify_html && $scraping && class_exists("MulticacheHtmlMinify"))
        {

            if (! $setbodyflag)
            {
                $body_sub = $app->getBody();
            }
            $options = array();
            $options['minify_level'] = 3;
            $options['jsMinifier'] = array(
                'MulticacheJSOptimize',
                'process'
            );
            $options['cssMinifier'] = array(
                'MulticacheCSSOptimize',
                'optimize'
            );
            $options['js_comments'] = self::$_jscomments;
            $options['css_comments'] = self::$_css_comments;
            $options['jsCleanComments'] = true;
            $body_sub = MulticacheHtmlMinify::process($body_sub, $options);
            $setbodyflag = true;
        }
        if ($setbodyflag)
        {
            // $body_sub = Minify_HTML::minify($body_sub,$options);

            $app->setBody($body_sub);
        }

    }

    /*
     * protected function canDoLazy()
     * {
     * // lets just return true or false
     * $clude_settings = null;
     * if (property_exists('JsStrategy', 'IMGsetting'))
     * {
     * $clude_settings = JsStrategy::$IMGsetting;
     * }
     * if (property_exists('JsStrategy', 'IMGCludeUrl') && isset($clude_settings))
     * {
     * $IMGCludeUrl = JsStrategy::$IMGCludeUrl;
     * if (($clude_settings['url_switch'] == 1 && ! isset($IMGCludeUrl[JURI::Current()])) || ($clude_settings['url_switch'] == 2 && isset($IMGCludeUrl[JURI::Current()])))
     * {
     * // exclude these pages
     * Return false;
     * }
     * }
     *
     * if (property_exists('JsStrategy', 'IMGCludeQuery') && isset($clude_settings))
     * {
     * $query_params = JURI::getInstance()->getQuery(true);
     * $IMGcludequeries = JsStrategy::$IMGCludeQuery;
     * if ($clude_settings['query_switch'] == 1)
     * {
     * // include these pages
     * $include_page = false;
     * foreach ($query_params as $key => $value)
     * {
     * if (isset($IMGcludequeries[$key][$value]) || (isset($IMGcludequeries[$key]) && $IMGcludequeries[$key][true] == 1))
     * {
     * $include_page = true;
     * break;
     * }
     * }
     * if (! $include_page)
     * {
     * Return false;
     * }
     * }
     * if ($clude_settings['query_switch'] == 2)
     * {
     * // exclude these pages
     * foreach ($query_params as $key => $value)
     * {
     * if (isset($IMGcludequeries[$key][$value]) || (isset($IMGcludequeries[$key]) && $IMGcludequeries[$key][true] == 1))
     * {
     * Return false;
     * }
     * }
     * }
     * }
     *
     * if (property_exists('JsStrategy', 'IMGexcluded_components'))
     * {
     * // $excluded_comp = JsStrategy::$JSTexcluded_components;
     * $option = JFactory::getApplication()->input->get('option', null);
     * if (isset(JsStrategy::$IMGexcluded_components[$option]))
     * {
     * Return false;
     * }
     * }
     * if (property_exists('JsStrategy', 'IMGurl_strings'))
     * {
     * $urlstrings = JsStrategy::$IMGurl_strings;
     * $current = JURI::getInstance()->toString();
     * foreach ($urlstrings as $string)
     * {
     * if (stristr($current, $string))
     * {
     * Return false;
     * }
     * }
     * }
     * Return true;
     *
     * }
     */
    protected function canDoOp($pre = 'JST', $classname = 'JsStrategy')
    {

        $st_var = $pre . '_' . $classname;
        if (isset(self::$_cando[$st_var]))
        {
            Return self::$_cando[$st_var];
        }

        if (! class_exists($classname))
        {
            self::$_cando[$st_var] = true;
            Return true;
        }
        // initiating static
        self::$_cando[$st_var] = false;

        // lets just return true or false
        $clude_settings = null;
        $property_name_setting = $pre . 'setting';
        if (property_exists($classname, $property_name_setting))
        {
            $clude_settings = $classname::$$property_name_setting;
        }
        $property_name_cludeurl = $pre . 'CludeUrl';
        if (property_exists($classname, $property_name_cludeurl) && isset($clude_settings))
        {
            $P_CludeUrl = $classname::$$property_name_cludeurl;
            if (($clude_settings['url_switch'] == 1 && ! isset($P_CludeUrl[$this->current])) || ($clude_settings['url_switch'] == 2 && isset($P_CludeUrl[$this->current])))
            {
                // exclude these pages
                Return false;
            }
        }
        $property_name_cludequery = $pre . 'CludeQuery';
        if (property_exists($classname, $property_name_cludequery) && isset($clude_settings))
        {
            $query_params = $this->uri->getQuery(true);
            $P_cludequeries = $classname::$$property_name_cludequery;
            if ($clude_settings['query_switch'] == 1)
            {
                // include these pages
                $include_page = false;
                foreach ($query_params as $key => $value)
                {
                    if (isset($P_cludequeries[$key][$value]) || (isset($P_cludequeries[$key]) && $P_cludequeries[$key][true] == 1))
                    {
                        $include_page = true;
                        break;
                    }
                }
                if (! $include_page)
                {
                    Return false;
                }
            }
            if ($clude_settings['query_switch'] == 2)
            {
                // exclude these pages
                foreach ($query_params as $key => $value)
                {
                    if (isset($P_cludequeries[$key][$value]) || (isset($P_cludequeries[$key]) && $P_cludequeries[$key][true] == 1))
                    {
                        Return false;
                    }
                }
            }
        }
        $property_name_excluded_components = $pre . 'excluded_components';

        if (property_exists($classname, $property_name_excluded_components))
        {
            // $excluded_comp = JsStrategy::$JSTexcluded_components;
            $option = JFactory::getApplication()->input->get('option', null);
            $O_ptionsxclude = $classname::$$property_name_excluded_components;
            if (isset($O_ptionsxclude[$option]))
            {
                Return false;
            }
        }
        $property_name_url_strings = $pre . 'url_strings';
        if (property_exists($classname, $property_name_url_strings))
        {
            $urlstrings = $classname::$$property_name_url_strings;
            $current = $this->uri->toString();
            foreach ($urlstrings as $string)
            {
                // the next part changed in WP version but i doubt the accuracy of strcasecompare when dealing with
                // forward slash segments . Will need to look into this again
                if (stristr($current, $string))
                {
                    Return false;
                }
            }
        }
        self::$_cando[$st_var] = true;
        Return true;

    }

    protected function initPageCacheClear()
    {

        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $app->input->getMethod();
        $session = JFactory::getSession();
        if ($app->input->getMethod() != 'GET')
        {
            Return;
        }
        $hide_panel = $session->get('multicache_cclr_panelhide');
        if (! empty($hide_panel))
        {
            Return;
        }
        $doc = JFactory::getDocument();
        if ($user->get('guest'))
        {
            Return;
        }
        $canDo = new JObject();
        $assetName = 'com_multicache';
        $actions = array(
            'core.admin',
            'core.manage',
            'core.create',
            'core.edit',
            'core.edit.own',
            'core.edit.state',
            'core.delete'
        );

        foreach ($actions as $action)
        {
            $canDo->set($action, $user->authorise($action, $assetName));
        }
        if (! $canDo->get('core.admin'))
        {
            Return;
        }

        $script_content = 'jQuery(function(){
var tcclr="<div id=\'cclr_admin_multicache_container\' style=\'position: absolute; top: 0px;right:0px;\'><button id=\'cclr_admin_multicache\' p_sec=\"' . JSession::getFormToken() . '\" p_cur=\"' . $this->current . '\" p_instance=\"' . $this->uri->toString() . '\"  style=\' z-index: 99; height: 30px; width: 120px; font-size: 1.2em; border-radius: 4px; cursor: pointer; background: none repeat scroll 0% 0% rgb(68, 121, 186); color: rgb(255, 255, 255); border: 1px solid rgb(32, 83, 141); text-shadow: 0px -1px 0px rgba(0, 0, 0, 0.4); box-shadow: 0px 1px 0px rgba(255, 255, 255, 0.4) inset, 0px 1px 1px rgba(0, 0, 0, 0.2);\'  >Clear Page</button><button id=\'cclr_admin_multicache_hide\' style=\' z-index: 99; height: 30px; width: 80px; font-size: 1.2em; border-radius: 4px; cursor: pointer; background: none repeat scroll 0% 0% rgb(68, 121, 186); color: rgb(255, 255, 255); border: 1px solid rgb(32, 83, 141); text-shadow: 0px -1px 0px rgba(0, 0, 0, 0.4); box-shadow: 0px 1px 0px rgba(255, 255, 255, 0.4) inset, 0px 1px 1px rgba(0, 0, 0, 0.2);\'  >hide</button><div id=\'cclr_admin_multicache_message\'  ></div>";

jQuery("body").append(tcclr);
    jQuery("#cclr_admin_multicache").on("click",function(e){
    e.preventDefault();
    var p_url = jQuery("button#cclr_admin_multicache").attr("p_cur");
    var p_urli = jQuery("button#cclr_admin_multicache").attr("p_instance");
    var p_sec = jQuery("button#cclr_admin_multicache").attr("p_sec");
    jQuery("#cclr_admin_multicache_message").text("Clearing Cache");
      jQuery.ajax({
  type: "POST",
  url: "' . JURI::root() . 'administrator/components/com_multicache/lib/multicache_cachecleaner.php",
  data:{ p_url: p_url,
      p_urli: p_urli,
      p_sec: p_sec,
        },
  success:function(t,status){
       jQuery("#cclr_admin_multicache_message").text(t);
    },
  dataType: "html"
});

    });

      jQuery("#cclr_admin_multicache_hide").on("click",function(e){
      e.preventDefault();
      var p_sec = jQuery("button#cclr_admin_multicache").attr("p_sec");
      jQuery.ajax({
      type: "POST",
      url: "' . JURI::root() . 'administrator/components/com_multicache/lib/multicache_cachecleaner.php",
          data:{ task: "hidecclr",
            p_sec: p_sec,
        },
  success:function(t,status){
       jQuery("#cclr_admin_multicache_container").fadeOut(1200);
    },
  dataType: "html"
      });

    });
});    ';

        $doc->addScriptDeclaration($script_content, 'text/javascript');

    }

    protected function loaderrorlogger($emessage = null, $type = null, $extra_message = '')
    {

        if (! defined('MULTICACHE_SYSTEMPLUGINLOGGER_READY'))
        {
            jimport('joomla.log.log');
            JLog::addLogger(array(
                'text_file' => 'fastcache.multicache-library.errors.php'
            ), JLog::ALL, array(
                'fastcache_multicache'
            ));
            define('MULTICACHE_SYSTEMPLUGINLOGGER_READY', TRUE);
        }
        if (! empty($emessage))
        {
            if (isset($type) && $type == 'message')
            {
                JLog::add(JText::_($emessage) . $extra_message, JLog::INFO);
            }
            elseif (isset($type) && $type == 'error')
            {
                JLog::add(JText::_($emessage) . $extra_message, JLog::ERROR);
            }
            elseif (isset($type) && $type == 'notice')
            {
                JLog::add(JText::_($emessage) . $extra_message, JLog::NOTICE);
            }
            else
            {
                JLog::add(JText::_($emessage) . $extra_message, JLog::WARNING);
            }
        }

    }

}