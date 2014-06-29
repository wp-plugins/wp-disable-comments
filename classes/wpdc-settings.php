<?php

if (!class_exists('WPDC_Settings')) {

    /**
     * Handles plugin settings and user profile meta fields
     */
    class WPDC_Settings extends WPDC_Module
    {
        protected $settings;
        private $network_activated;
        protected static $default_settings;
        protected static $readable_properties = array('settings');
        protected static $writeable_properties = array('settings');

        const REQUIRED_CAPABILITY = 'manage_options';
        const REQUIRED_CAPABILITY_MU = 'manage_network_plugins';


        /*
         * General methods
         */

        /**
         * Constructor
         *
         * @mvc Controller
         */
        protected function __construct()
        {
            $this->network_activated = (is_multisite() && array_key_exists(plugin_basename(dirname(__DIR__)) . '/bootstrap.php', get_site_option('active_sitewide_plugins')));
            $this->register_hook_callbacks();
        }

        /**
         * Public setter for protected variables
         *
         * Updates settings outside of the Settings API or other subsystems
         *
         * @mvc Controller
         *
         * @param string $variable
         * @param array $value This will be merged with WPDC_Settings->settings, so it should mimic the structure of the WPDC_Settings::$default_settings. It only needs the contain the values that will change, though. See WordPress_Disable_Comments->upgrade() for an example.
         */
        public function __set($variable, $value)
        {
            // Note: WPDC_Module::__set() is automatically called before this

            if ($variable != 'settings') {
                return;
            }

            $this->settings = self::validate_settings($value);
            update_option('wpdc_settings', $this->settings);
        }

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks()
        {
            if ($this->network_activated) {
                add_action('network_admin_menu', array($this, 'register_settings_pages'));
                add_action('network_admin_edit_wpdc_settings', array($this, 'update_network_setting'));
            } else {
                add_action('admin_menu', array($this, 'register_settings_pages'));
            }
            add_action('init', array($this, 'init'));
            add_action('admin_init', array($this, 'register_settings'));

            if ($this->network_activated) {
                add_filter(
                    'network_admin_plugin_action_links_' . plugin_basename(dirname(__DIR__)) . '/bootstrap.php',
                    array($this, 'add_plugin_action_links')
                );
            } else {
                add_filter(
                    'plugin_action_links_' . plugin_basename(dirname(__DIR__)) . '/bootstrap.php',
                    array($this, 'add_plugin_action_links')
                );
            }
        }

        function update_network_setting()
        {
            update_site_option('wpdc_settings', $_POST['wpdc_settings']);
            wp_redirect(add_query_arg(array('page' => wpdc_settings, 'updated' => 'true'), network_admin_url('settings.php')));
            exit;
        }

        /**
         * Prepares site to use the plugin during activation
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        public
        function activate($network_wide)
        {
        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public
        function deactivate()
        {
        }

        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public
        function init()
        {
            self::$default_settings = self::get_default_settings();
            $this->settings = self::get_settings();
        }

        /**
         * Executes the logic of upgrading from specific older versions of the plugin to the current version
         *
         * @mvc Model
         *
         * @param string $db_version
         */
        public
        function upgrade($db_version = 0)
        {
            /*
            if( version_compare( $db_version, 'x.y.z', '<' ) )
            {
                // Do stuff
            }
            */
        }

        /**
         * Checks that the object is in a correct state
         *
         * @mvc Model
         *
         * @param string $property An individual property to check, or 'all' to check all of them
         * @return bool
         */
        protected
        function is_valid($property = 'all')
        {
            // Note: __set() calls validate_settings(), so settings are never invalid

            return true;
        }


        /*
         * Plugin Settings
         */

        /**
         * Establishes initial values for all settings
         *
         * @mvc Model
         *
         * @return array
         */
        protected
        static function get_default_settings()
        {
            $disable_what = array(
                "disable-pingbacks" => false,
                "disable-comments" => false,
                "disable-trackbacks" => false,
                "disable-xmlrpc" => false,
                "disable-rsdlink" => false,
                "disable-rcwidget" => false,
                "disable-urlfield" => false,
                "disable-authorlink" => false,
                "prevent-ownership" => false,
            );

            $disable_where = array(
                "disable-on-logged-in" => false,
                "disable-post-id" => "",
                "disable-category" => array(),
                "disable-tag" => array(),
                "disable-user" => array(),
                "disable-format" => array(),
                "disable-post-type" => array(),
                "disable-language" => array(),
                "disable-url" => "",
                "disable-referrer" => "",
                "disable-ipaddress" => "",
                "disable-checkboxes" => array(),
            );

            return array(
                'db-version' => '0',
                'disablewhere' => $disable_where,
                'disablewhat' => $disable_what
            );
        }

        /**
         * Retrieves all of the settings from the database
         *
         * @mvc Model
         *
         * @return array
         */
        protected function get_settings()
        {
            $settings = shortcode_atts(
                self::$default_settings,
                $this->network_activated ? get_site_option('wpdc_settings', array()) : get_option('wpdc_settings', array())
            );

            return $settings;
        }

        /**
         * Adds links to the plugin's action link section on the Plugins page
         *
         * @mvc Model
         *
         * @param array $links The links currently mapped to the plugin
         * @return array
         */
        public function add_plugin_action_links($links)
        {
            array_unshift($links, '<a href="http://wordpress.org/extend/plugins/wp-disable-comments/faq/">Help</a>');
            if ($this->network_activated) {
                array_unshift($links, '<a href="settings.php?page=wpdc_settings">Settings</a>');
            } else {
                array_unshift($links, '<a href="options-general.php?page=wpdc_settings">Settings</a>');
            }
            return $links;
        }

        /**
         * Adds pages to the Admin Panel menu
         *
         * @mvc Controller
         */
        public function register_settings_pages()
        {
            if ($this->network_activated) {
                add_submenu_page(
                    'settings.php',
                    WPDC_NAME . ' Settings',
                    WPDC_NAME,
                    self::REQUIRED_CAPABILITY,
                    'wpdc_settings',
                    array($this, 'markup_settings_page')
                );
            } else {
                add_submenu_page(
                    'options-general.php',
                    WPDC_NAME . ' Settings',
                    WPDC_NAME,
                    self::REQUIRED_CAPABILITY,
                    'wpdc_settings',
                    array($this, 'markup_settings_page')
                );
            }
        }

        /**
         * Creates the markup for the Settings page
         *
         * @mvc Controller
         */
        public function markup_settings_page()
        {
            if (($this->network_activated && current_user_can(self::REQUIRED_CAPABILITY_MU)) ||
                (!$this->network_activated && current_user_can(self::REQUIRED_CAPABILITY))
            ) {
                echo self::render_template('wpdc-settings/page-settings.php', array('network_activated' => $this->network_activated), 'always');
            } else {
                wp_die('Access denied.');
            }
        }

        private function add_settings_field($id, $title, $section)
        {
            add_settings_field(
                $id,
                $title,
                array($this, 'markup_fields'),
                'wpdc_settings',
                $section,
                array('label_for' => $id)
            );
        }

        private function add_settings_field_disablewhat($id, $title)
        {
            $this->add_settings_field($id, $title, 'wpdc_section-disablewhat');
        }

        private function add_settings_field_disablewhere($id, $title)
        {
            $this->add_settings_field($id, $title, 'wpdc_section-disablewhere');
        }

        private function add_settings_section($id, $title)
        {
            add_settings_section(
                $id,
                $title,
                array($this, 'markup_section_headers'),
                'wpdc_settings'
            );
        }

        /**
         * Registers settings sections, fields and settings
         *
         * @mvc Controller
         */
        public function register_settings()
        {
            /*
             * What Section
             */
            $this->add_settings_section('wpdc_section-disablewhat', 'What to disable');
            $this->add_settings_field_disablewhat('wpdc_disable-comments', 'Comments');
            $this->add_settings_field_disablewhat('wpdc_disable-pingbacks', 'Pingbacks');
            $this->add_settings_field_disablewhat('wpdc_disable-trackbacks', 'Trackbacks');
            $this->add_settings_field_disablewhat('wpdc_disable-xmlrpc', 'XML-RPC');
            $this->add_settings_field_disablewhat('wpdc_disable-rsdlink', 'RSD links');
            $this->add_settings_field_disablewhat('wpdc_disable-rcwidget', 'Recent Comments Widget in Dashboard');
            $this->add_settings_field_disablewhat('wpdc_disable-urlfield', 'URL Field in comment form');
            $this->add_settings_field_disablewhat('wpdc_disable-authorlink', 'Comment author link');
            $this->add_settings_field_disablewhat('wpdc_prevent-ownership', 'Comment URL with Google authorship link');

            /*
             * Where Section
             */
            $this->add_settings_section('wpdc_section-disablewhere', 'Where/When to disable');

            $this->add_settings_field_disablewhere('wpdc_disable-on-logged-in', 'for logged in users');
            $this->add_settings_field_disablewhere('wpdc_disable-post-id', 'for specific post/page IDs');
            $this->add_settings_field_disablewhere('wpdc_disable-category', 'for specific categories');
            $this->add_settings_field_disablewhere('wpdc_disable-tag', 'for specific tags');
            $this->add_settings_field_disablewhere('wpdc_disable-user', 'for specific authors');
            if (current_theme_supports('post-formats')) {
                $this->add_settings_field_disablewhere('wpdc_disable-format', 'for specific post formats');
            }
            $this->add_settings_field_disablewhere('wpdc_disable-post-type', 'for specific post types');
            $this->add_settings_field_disablewhere('wpdc_disable-language', 'for specific languages');
            $this->add_settings_field_disablewhere('wpdc_disable-url', 'for specific URL paths');
            $this->add_settings_field_disablewhere('wpdc_disable-referrer', 'for specific referrers');
            $this->add_settings_field_disablewhere('wpdc_disable-ipaddress', 'for specific IP addresses');
            $this->add_settings_field_disablewhere('wpdc_disable-checkboxes', 'Uncheck discussion comment checkboxes for');

            // The settings container
            register_setting('wpdc_settings', 'wpdc_settings', array($this, 'validate_settings'));
        }

        /**
         * Adds the section introduction text to the Settings page
         *
         * @mvc Controller
         *
         * @param array $section
         */
        public function markup_section_headers($section)
        {
            echo self::render_template('wpdc-settings/page-settings-section-headers.php', array('section' => $section), 'always');
        }


        /**
         * Delivers the markup for settings fields
         *
         * @mvc Controller
         *
         * @param array $field
         */
        public function markup_fields($field)
        {
            global $q_config;
            echo self::render_template('wpdc-settings/page-settings-fields.php', array('settings' => $this->settings, 'field' => $field, 'q_config' => $q_config), 'always');
        }

        private function setting_default_if_not_set($new_settings, $section, $id, $value)
        {
            if (!isset($new_settings[$section][$id])) {
                $new_settings[$section][$id] = $value;
            }
        }

        private function setting_empty_string_if_not_set($new_settings, $section, $id)
        {
            $this->setting_default_if_not_set($new_settings, $section, $id, '');
        }

        private function setting_empty_array_if_not_set($new_settings, $section, $id)
        {
            $this->setting_default_if_not_set($new_settings, $section, $id, array());
        }

        private function setting_zero_if_not_set($new_settings, $section, $id)
        {
            $this->setting_default_if_not_set($new_settings, $section, $id, '0');
        }

        /**
         * Validates submitted setting values before they get saved to the database. Invalid data will be overwritten with defaults.
         *
         * @mvc Model
         *
         * @param array $new_settings
         * @return array
         */
        public function validate_settings($new_settings)
        {
            $new_settings = shortcode_atts($this->settings, $new_settings);

            if (!is_string($new_settings['db-version'])) {
                $new_settings['db-version'] = WordPress_Disable_Comments::VERSION;
            }

            /*
             * What Settings
             */

            if (!isset($new_settings['disablewhat'])) {
                $new_settings['disablewhat'] = array();
            }

            $this->setting_zero_if_not_set($new_settings, 'disablewhat', 'disable-comments');
            $this->setting_zero_if_not_set($new_settings, 'disablewhat', 'disable-pingbacks');
            $this->setting_zero_if_not_set($new_settings, 'disablewhat', 'disable-trackbacks');
            $this->setting_zero_if_not_set($new_settings, 'disablewhat', 'disable-xmlrpc');
            $this->setting_zero_if_not_set($new_settings, 'disablewhat', 'disable-rsdlink');
            $this->setting_zero_if_not_set($new_settings, 'disablewhat', 'disable-rcwidget');
            $this->setting_zero_if_not_set($new_settings, 'disablewhat', 'disable-urlfield');
            $this->setting_zero_if_not_set($new_settings, 'disablewhat', 'disable-authorlink');
            $this->setting_zero_if_not_set($new_settings, 'disablewhat', 'prevent-ownership');

            /*
             * Where Settings
             */

            if (!isset($new_settings['disablewhere'])) {
                $new_settings['disablewhere'] = array();
            }

            $this->setting_zero_if_not_set($new_settings, 'disablewhere', 'disable-on-logged-in');
            $this->setting_empty_string_if_not_set($new_settings, 'disablewhere', 'disable-post-id');
            $this->setting_empty_array_if_not_set($new_settings, 'disablewhere', 'disable-category');
            $this->setting_empty_array_if_not_set($new_settings, 'disablewhere', 'disable-tag');
            $this->setting_empty_array_if_not_set($new_settings, 'disablewhere', 'disable-user');
            $this->setting_empty_array_if_not_set($new_settings, 'disablewhere', 'disable-format');
            $this->setting_empty_array_if_not_set($new_settings, 'disablewhere', 'disable-post-type');
            $this->setting_empty_array_if_not_set($new_settings, 'disablewhere', 'disable-language');
            $this->setting_empty_string_if_not_set($new_settings, 'disablewhere', 'disable-url');
            $this->setting_empty_string_if_not_set($new_settings, 'disablewhere', 'disable-referrer');
            $this->setting_empty_string_if_not_set($new_settings, 'disablewhere', 'disable-ipaddress');
            $this->setting_empty_array_if_not_set($new_settings, 'disablewhere', 'disable-checkboxes');

            return $new_settings;
        }
    } // end WPDC_Settings
}
