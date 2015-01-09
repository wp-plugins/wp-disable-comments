<?php

if (!class_exists('WordPress_Disable_Comments')) {

    /**
     * Main / front controller class
     */
    class WordPress_Disable_Comments extends WPDC_Module
    {
        protected static $readable_properties = array(); // These should really be constants, but PHP doesn't allow class constants to be arrays
        protected static $writeable_properties = array();
        protected $modules;
        protected $modified_types = array();

        const VERSION = '0.3.3';
        const PREFIX = 'wpdc_';
        const DEBUG_MODE = false;


        /*
         * Magic methods
         */

        /**
         * Constructor
         *
         * @mvc Controller
         */
        protected function __construct()
        {
            $this->register_hook_callbacks();

            $this->modules = array(
                'WPDC_Settings' => WPDC_Settings::get_instance()
            );
        }


        /*
         * Static methods
         */

        /**
         * Enqueues CSS, JavaScript, etc
         *
         * @mvc Controller
         */
        public static function load_resources()
        {
            wp_register_script(
                self::PREFIX . 'wp-disable-comments',
                plugins_url('javascript/wp-disable-comments.js', dirname(__FILE__)),
                array('jquery'),
                self::VERSION,
                true
            );

            wp_register_script(
                self::PREFIX . 'wp-disable-comments-admin',
                plugins_url('javascript/wp-disable-comments-admin.js', dirname(__FILE__)),
                array('jquery'),
                self::VERSION,
                true
            );

            wp_register_style(
                self::PREFIX . 'admin',
                plugins_url('css/admin.css', dirname(__FILE__)),
                array(),
                self::VERSION,
                'all'
            );

            if (is_admin()) {
                wp_enqueue_style(self::PREFIX . 'admin');
                wp_enqueue_script(self::PREFIX . 'wp-disable-comments-admin');
            } else {
                wp_enqueue_script(self::PREFIX . 'wp-disable-comments');
            }
        }

        /**
         * Clears caches of content generated by caching plugins like WP Super Cache
         *
         * @mvc Model
         */
        protected static function clear_caching_plugins()
        {
            // WP Super Cache
            if (function_exists('wp_cache_clear_cache')) {
                wp_cache_clear_cache();
            }

            // W3 Total Cache
            if (class_exists('W3_Plugin_TotalCacheAdmin')) {
                $w3_total_cache = w3_instance('W3_Plugin_TotalCacheAdmin');

                if (method_exists($w3_total_cache, 'flush_all')) {
                    $w3_total_cache->flush_all();
                }
            }
        }


        /*
         * Instance methods
         */

        /**
         * Prepares sites to use the plugin during single or network-wide activation
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        public function activate($network_wide)
        {
            global $wpdb;

            if (function_exists('is_multisite') && is_multisite()) {
                if ($network_wide) {
                    $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                    foreach ($blogs as $blog) {
                        switch_to_blog($blog);
                        $this->single_activate($network_wide);
                    }

                    restore_current_blog();
                } else {
                    $this->single_activate($network_wide);
                }
            } else {
                $this->single_activate($network_wide);
            }
        }

        /**
         * Runs activation code on a new WPMS site when it's created
         *
         * @mvc Controller
         *
         * @param int $blog_id
         */
        public function activate_new_site($blog_id)
        {
            switch_to_blog($blog_id);
            $this->single_activate(true);
            restore_current_blog();
        }

        /**
         * Prepares a single blog to use the plugin
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        protected function single_activate($network_wide)
        {
            foreach ($this->modules as $module) {
                $module->activate($network_wide);
            }
        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public function deactivate()
        {
            foreach ($this->modules as $module) {
                $module->deactivate();
            }
        }

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks()
        {
            add_action('wpmu_new_blog', __CLASS__ . '::activate_new_site');
            add_action('wp_enqueue_scripts', __CLASS__ . '::load_resources');
            add_action('admin_enqueue_scripts', __CLASS__ . '::load_resources');

            add_action('init', array($this, 'init'));
            add_action('init', array($this, 'upgrade'), 11);

            add_filter('wp_headers', array($this, 'filter_x_pingback'));
            add_filter('rewrite_rules_array', array($this, 'filter_trackback_rewrites'));
            add_filter('bloginfo_url', array($this, 'filter_pingback_url'), 10, 2);
            add_filter('xmlrpc_methods', array($this, 'filter_xmlrpc_methods'));
            add_filter('preprocess_comment', array($this, 'filter_preprocess_comment'));
            add_filter('comment_form_default_fields', array($this, 'filter_comment_form_default_fields'));
            add_filter('get_comment_author_link', array($this, 'filter_get_comment_author_link'));

            add_action('widgets_init', array($this, 'unregister_rc_widget'));

            add_filter('pre_option_default_comment_status', array(&$this, 'disable_default_comment_status'));
            add_filter('pre_option_default_ping_status', array(&$this, 'disable_default_comment_status'));

            add_action('wp_loaded', array($this, 'remove_post_type_support'));
            add_action('wp_loaded', array($this, 'add_filter_on_wp_loaded'));
        }

        public function filter_get_comment_author_link( $author_link ){
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            if ($disable_what['disable-authorlink'] == 1) {
                $disable_where = $this->modules['WPDC_Settings']->settings['disablewhere'];
                if ($this->is_disable_specific($disable_where)) {
                    return strip_tags( $author_link );
                }
            }
            return $author_link;
        }

        public function filter_comment_form_default_fields($fields)
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            if ($disable_what['disable-urlfield'] == 1) {
                $disable_where = $this->modules['WPDC_Settings']->settings['disablewhere'];
                if ($this->is_disable_specific($disable_where)) {
                    unset($fields['url']);
                }
            }
            return $fields;
        }

        public function filter_preprocess_comment($commentdata)
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            if ($disable_what['prevent-ownership'] == 1) {
                if (preg_match('/http(s)?:\/\/plus\.google\.com(.*)$/', $commentdata['comment_author_url'])) {
                    unset($commentdata['comment_author_url']);
                }
            }
            return $commentdata;
        }

        public function disable_default_comment_status($default)
        {
            $post_type = $this->get_current_post_type();
            $disable_where = $this->modules['WPDC_Settings']->settings['disablewhere'];
            $disable_checkboxes = $disable_where['disable-checkboxes'];
            error_log("post_type=".$post_type);
            error_log("disable_checkboxes=".print_r($disable_checkboxes, true));
            if (isset($post_type) && count($disable_checkboxes) > 0 && in_array($post_type, $disable_checkboxes)) {
                return "closed";
            }
            return $default;
        }

        function get_current_post_type()
        {
            global $post, $typenow, $current_screen;

            //we have a post so we can just get the post type from that
            if ($post && $post->post_type)
                return $post->post_type;

            //check the global $typenow - set in admin.php
            elseif ($typenow)
                return $typenow;

            //check the global $current_screen object - set in sceen.php
            elseif ($current_screen && $current_screen->post_type)
                return $current_screen->post_type;

            //lastly check the post_type querystring
            elseif (isset($_REQUEST['post_type']))
                return sanitize_key($_REQUEST['post_type']);

            elseif ($current_screen && $current_screen->id && $current_screen->id=='async-upload') {
                return "attachment";
            }

            //we do not know the post type!
            return null;
        }

        public function get_disable_post_id($disable_where)
        {
            $disable_post_id = array();

            foreach (explode(',', $disable_where['disable-post-id']) as $id) {
                $id2 = explode('-', $id);
                if (count($id2) == 1) {
                    array_push($disable_post_id, $id2[0]);
                } else {
                    for ($i = $id2[0]; $i <= $id2[1]; $i++) {
                        array_push($disable_post_id, $i);
                    }
                }
            }
            return $disable_post_id;
        }

        public function get_disable_ipaddress($disable_where)
        {
            $disable_ipaddress = array();

            foreach (explode(',', $disable_where['disable-ipaddress']) as $id) {
                array_push($disable_ipaddress, $id);
            }
            return $disable_ipaddress;
        }

        public function get_disable_url($disable_where)
        {
            $disable_url = array();

            foreach (explode(',', $disable_where['disable-url']) as $id) {
                array_push($disable_url, $id);
            }
            return $disable_url;
        }

        public function get_disable_referrer($disable_where)
        {
            $disable_referrer = array();

            foreach (explode(',', $disable_where['disable-referrer']) as $id) {
                array_push($disable_referrer, $id);
            }
            return $disable_referrer;
        }

        private function to_int_array($array_in)
        {
            $array_out = array();

            if (isset($array_in) && is_array($array_in)) {
                foreach ($array_in as $id) {
                    array_push($array_out, intval($id));
                }
            }
            return $array_out;
        }

        public function get_disable_category($disable_where)
        {
            return isset($disable_where['disable-category']) ? $this->to_int_array($disable_where['disable-category']) : array();
        }

        public function get_disable_tag($disable_where)
        {
            return isset($disable_where['disable-tag']) ? $this->to_int_array($disable_where['disable-tag']) : array();
        }

        public function get_disable_user($disable_where)
        {
            return isset($disable_where['disable-user']) ? $this->to_int_array($disable_where['disable-user']) : array();
        }

        public function get_disable_format($disable_where)
        {
            return isset($disable_where['disable-format']) ? $disable_where['disable-format'] : array();
        }

        public function get_disable_post_type($disable_where)
        {
            return isset($disable_where['disable-post-type']) ? $disable_where['disable-post-type'] : array();
        }

        public function get_disable_language($disable_where)
        {
            return isset($disable_where['disable-language']) ? $disable_where['disable-language'] : array();
        }

        public function in_array_substr($needle, $haystack)
        {
            foreach ($haystack as $hay_item) {
                if ($hay_item !== "" && strpos($needle, $hay_item)) {
                    return true;
                }
            }
            return false;
        }

        public function is_disable_specific($disable_where)
        {
            $disable_post_id = $this->get_disable_post_id($disable_where);
            $disable_category = $this->get_disable_category($disable_where);
            $disable_tag = $this->get_disable_tag($disable_where);
            $disable_user = $this->get_disable_user($disable_where);
            $disable_format = $this->get_disable_format($disable_where);
            $disable_post_type = $this->get_disable_post_type($disable_where);
            $disable_language = $this->get_disable_language($disable_where);
            $disable_url = $this->get_disable_url($disable_where);
            $disable_referrer = $this->get_disable_referrer($disable_where);
            $disable_ipaddress = $this->get_disable_ipaddress($disable_where);

            return ((count($disable_format) > 0 && in_array(get_post_format(), $disable_format))
                || (count($disable_user) > 0 && in_array(get_the_author_meta('ID'), $disable_user))
                || (count($disable_tag) > 0 && has_tag($disable_tag))
                || (count($disable_category) > 0 && has_category($disable_category))
                || (count($disable_post_type) > 0 && in_array(get_post_type(get_the_ID()), $disable_post_type))
                || (count($disable_language) > 0 && function_exists('qtrans_getLanguage') && in_array(qtrans_getLanguage(), $disable_language))
                || (count($disable_url) > 0 && $this->in_array_substr(get_the_permalink(), $disable_url))
                || (count($disable_referrer) > 0 && $this->in_array_substr($_SERVER['HTTP_REFERER'], $disable_referrer))
                || (count($disable_ipaddress) > 0 && $this->in_array_substr($_SERVER['REMOTE_ADDR'], $disable_ipaddress))
                || (!is_feed() && in_array(get_the_ID(), $disable_post_id))
                || (is_user_logged_in() && $disable_where['disable-on-logged-in'] == 1)
            );
        }

        function filter_xmlrpc_methods($methods)
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            if ($disable_what['disable-xmlrpc'] == 1) {
                if ($disable_what['disable-pingbacks'] == 1) {
                    unset($methods['pingback.ping']);
                    unset($methods['pingback.extensions.getPingbacks']);
                }
                if ($disable_what['disable-comments'] == 1) {
                    unset($methods['wp.getCommentCount']);
                    unset($methods['wp.getComment']);
                    unset($methods['wp.getComments']);
                    unset($methods['wp.deleteComment']);
                    unset($methods['wp.editComment']);
                    unset($methods['wp.newComment']);
                    unset($methods['wp.getCommentStatusList']);
                }
                if ($disable_what['disable-trackbacks'] == 1) {
                    unset($methods['mt.getTrackbackPings']);
                }
            }
            return $methods;
        }

        function filter_x_pingback($headers)
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            if (($disable_what['disable-pingbacks'] == 1) && (isset($headers['X-Pingback']))) {
                unset($headers['X-Pingback']);
            }
            return $headers;
        }

        function filter_trackback_rewrites($rewrite_rules)
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            if ($disable_what['disable-trackbacks'] == 1) {
                foreach ($rewrite_rules as $rule => $rewrite) {
                    if (preg_match('/trackback\/\?\$$/i', $rule)) {
                        unset($rewrite_rules[$rule]);
                    }
                }
            }
            return $rewrite_rules;
        }

        function filter_pingback_url($output, $show)
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            if (($disable_what['disable-pingbacks'] == 1) && ($show == 'pingback_url')) {
                $output = '';
            }
            return $output;
        }

        function unregister_rc_widget()
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            if ($disable_what['disable-rcwidget'] == 1) {
                unregister_widget('WP_Widget_Recent_Comments');
            }
        }

        function remove_post_type_support()
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            $disable_where = $this->modules['WPDC_Settings']->settings['disablewhere'];
            $disable_post_type = $this->get_disable_post_type($disable_where);
            if (!empty($disable_post_type)) {
                foreach ($disable_post_type as $post_type) {
                    if ($disable_what['disable-comments'] == 1 && post_type_supports($post_type, 'comments')) {
                        $this->modified_types[] = $post_type;
                        remove_post_type_support($post_type, 'comments');
                    }
                    if ($disable_what['disable-trackbacks'] == 1 && post_type_supports($post_type, 'trackbacks')) {
                        $this->modified_types[] = $post_type;
                        remove_post_type_support($post_type, 'trackbacks');
                    }
                }
            }
        }

        function add_filter_on_wp_loaded()
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];

            add_filter('comments_open', array($this, 'filter_comments_open'));
            add_action('template_redirect', array($this, 'override_comment_template'));
            if (isset($disable_what['disable-pingbacks']) && $disable_what['disable-pingbacks'] == 1) {
                add_filter('pre_update_default_ping_status', '__return_false');
                add_filter('pre_option_default_ping_status', '__return_zero');
                add_filter('pre_update_default_pingback_flag', '__return_false');
                add_filter('pre_option_default_pingback_flag', '__return_zero');
            }
            if (isset($disable_what['disable-rsdlink']) && $disable_what['disable-rsdlink'] == 1) {
                remove_action('wp_head', 'rsd_link');
            }
            add_action('admin_head', array($this, 'remove_meta_boxes'));
            if (isset($disable_what['disable-rcwidget']) && $disable_what['disable-rcwidget'] == 1) {
                unregister_widget('WP_Widget_Recent_Comments');
            }
        }

        function remove_meta_boxes()
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            $disable_where = $this->modules['WPDC_Settings']->settings['disablewhere'];

            if (isset($disable_what['disable-comments']) && $disable_what['disable-comments'] == 1) {
                $disable_post_type = $this->get_disable_post_type($disable_where);
                if (!empty($disable_post_type)) {
                    foreach ($disable_post_type as $post_type) {
                        remove_meta_box('commentstatusdiv', $post_type, 'normal');
                        remove_meta_box('commentsdiv', $post_type, 'normal');
                    }
                }
            }
            if (isset($disable_what['disable-trackbacks']) && $disable_what['disable-trackbacks'] == 1) {
                $disable_post_type = $this->get_disable_post_type($disable_where);
                if (!empty($disable_post_type)) {
                    foreach ($disable_post_type as $post_type) {
                        remove_meta_box('trackbacksdiv', $post_type, 'normal');
                    }
                }
            }
        }

        function filter_comments_open($open)
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            if (isset($disable_what['disable-comments']) && $disable_what['disable-comments'] == 1) {
                $disable_where = $this->modules['WPDC_Settings']->settings['disablewhere'];
                if ($this->is_disable_specific($disable_where)) {
                    return false;
                }
            }
            return $open;
        }

        function override_comment_template()
        {
            $disable_what = $this->modules['WPDC_Settings']->settings['disablewhat'];
            if (isset($disable_what['disable-comments']) && $disable_what['disable-comments'] == 1) {
                $disable_where = $this->modules['WPDC_Settings']->settings['disablewhere'];
                if (is_singular() && $this->is_disable_specific($disable_where)) {
                    add_filter('comments_template', array($this, 'empty_comments_template'));
                    wp_deregister_script('comment-reply');
                }
            }
        }

        function empty_comments_template()
        {
            return dirname(__FILE__) . '/../empty-comments-template.php';
        }

        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public function init()
        {
            try {
                $instance_example = new WPDC_Instance_Class('Instance example', '42');
                //add_notice( $instance_example->foo .' '. $instance_example->bar );
            } catch (Exception $exception) {
                add_notice(__METHOD__ . ' error: ' . $exception->getMessage(), 'error');
            }
        }

        /**
         * Checks if the plugin was recently updated and upgrades if necessary
         *
         * @mvc Controller
         *
         * @param string $db_version
         */
        public function upgrade($db_version = 0)
        {
            if (version_compare($this->modules['WPDC_Settings']->settings['db-version'], self::VERSION, '==')) {
                return;
            }

            foreach ($this->modules as $module) {
                $module->upgrade($this->modules['WPDC_Settings']->settings['db-version']);
            }

            $this->modules['WPDC_Settings']->settings = array('db-version' => self::VERSION);
            self::clear_caching_plugins();
        }

        /**
         * Checks that the object is in a correct state
         *
         * @mvc Model
         *
         * @param string $property An individual property to check, or 'all' to check all of them
         * @return bool
         */
        protected function is_valid($property = 'all')
        {
            return true;
        }
    }

    ; // end WordPress_Disable_Comments
}
