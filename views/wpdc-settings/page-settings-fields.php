<?php
/*
 * What Section
 */
?>

<?php if ('wpdc_disable-comments' == $field['label_for']) : ?>
    <input type="checkbox" name="wpdc_settings[disablewhat][disable-comments]"
           id="wpdc_settings[disablewhat][disable-comments]"
           value="1" <?php checked(1, isset($settings['disablewhat']['disable-comments']) ? $settings['disablewhat']['disable-comments'] : 0) ?>>
<?php
elseif ('wpdc_disable-pingbacks' == $field['label_for']) : ?>
    <input type="checkbox" name="wpdc_settings[disablewhat][disable-pingbacks]"
           id="wpdc_settings[disablewhat][disable-pingbacks]"
           value="1" <?php checked(1, isset($settings['disablewhat']['disable-pingbacks']) ? $settings['disablewhat']['disable-pingbacks'] : 0) ?>>
<?php
elseif ('wpdc_disable-trackbacks' == $field['label_for']) : ?>
    <input type="checkbox" name="wpdc_settings[disablewhat][disable-trackbacks]"
           id="wpdc_settings[disablewhat][disable-trackbacks]"
           value="1" <?php checked(1, isset($settings['disablewhat']['disable-trackbacks']) ? $settings['disablewhat']['disable-trackbacks'] : 0) ?>>
<?php
elseif ('wpdc_disable-xmlrpc' == $field['label_for']) : ?>
    <input type="checkbox" name="wpdc_settings[disablewhat][disable-xmlrpc]"
           id="wpdc_settings[disablewhat][disable-xmlrpc]"
           value="1" <?php checked(1, isset($settings['disablewhat']['disable-xmlrpc']) ? $settings['disablewhat']['disable-xmlrpc'] : 0) ?>>
    <p class="description" style="display: inline;">This setting is to be used in combination with the settings above.</p>
<?php
elseif ('wpdc_disable-rsdlink' == $field['label_for']) : ?>
    <input type="checkbox" name="wpdc_settings[disablewhat][disable-rsdlink]"
           id="wpdc_settings[disablewhat][disable-rsdlink]"
           value="1" <?php checked(1, isset($settings['disablewhat']['disable-rsdlink']) ? $settings['disablewhat']['disable-rsdlink'] : 0) ?>>
<?php
elseif ('wpdc_disable-rcwidget' == $field['label_for']) : ?>
    <input type="checkbox" name="wpdc_settings[disablewhat][disable-rcwidget]"
           id="wpdc_settings[disablewhat][disable-rcwidget]"
           value="1" <?php checked(1, isset($settings['disablewhat']['disable-rcwidget']) ? $settings['disablewhat']['disable-rcwidget'] : 0) ?>>
<?php
elseif ('wpdc_disable-authorlink' == $field['label_for']) : ?>
    <input type="checkbox" name="wpdc_settings[disablewhat][disable-authorlink]"
           id="wpdc_settings[disablewhat][disable-authorlink]"
           value="1" <?php checked(1, isset($settings['disablewhat']['disable-authorlink']) ? $settings['disablewhat']['disable-authorlink'] : 0) ?>>
<?php
elseif ('wpdc_disable-urlfield' == $field['label_for']) : ?>
    <input type="checkbox" name="wpdc_settings[disablewhat][disable-urlfield]"
           id="wpdc_settings[disablewhat][disable-urlfield]"
           value="1" <?php checked(1, isset($settings['disablewhat']['disable-urlfield']) ? $settings['disablewhat']['disable-urlfield'] : 0) ?>>
<?php
elseif ('wpdc_prevent-ownership' == $field['label_for']) : ?>
    <input type="checkbox" name="wpdc_settings[disablewhat][prevent-ownership]"
           id="wpdc_settings[disablewhat][prevent-ownership]"
           value="1" <?php checked(1, isset($settings['disablewhat']['prevent-ownership']) ? $settings['disablewhat']['prevent-ownership'] : 0) ?>>
    <p class="description" style="display: inline;">This setting applies to all comments no matter which criteria are selected below.</p>
<?php
elseif ('wpdc_disable-on-logged-in' == $field['label_for']) : ?>
    <input type="checkbox" name="wpdc_settings[disablewhere][disable-on-logged-in]"
           id="wpdc_settings[disablewhere][disable-on-logged-in]"
           value="1" <?php checked(1, isset($settings['disablewhat']['disable-on-logged-in']) ? $settings['disablewhere']['disable-on-logged-in'] : 0) ?>>
<?php
elseif ('wpdc_disable-post-id' == $field['label_for']) : ?>
    <input type="text" name="wpdc_settings[disablewhere][disable-post-id]"
           id="wpdc_settings[disablewhere][disable-post-id]"
           value="<?php echo $settings['disablewhere']['disable-post-id']; ?>" placeholder="e.g. 32,9-19,33">
<?php
elseif ('wpdc_disable-category' == $field['label_for']) : ?>
    <?php $categories = get_terms('category'); ?>
    <select style="min-width: 190px;" id="wpdc_settings[disablewhere][disable-category]"
            name="wpdc_settings[disablewhere][disable-category][]" size="4"
            multiple="multiple">
        <?php foreach ($categories as $category) { ?>
            <option
                value="<?php echo esc_attr($category->term_id); ?>" <?php echo(isset($settings['disablewhere']['disable-category']) && in_array($category->term_id, (array)$settings['disablewhere']['disable-category']) ? 'selected="selected"' : ''); ?>><?php echo esc_html($category->name); ?></option>
        <?php } ?>
    </select>
    <button id="clear-category" class="button-secondary"
            onclick="javascript:jQuery('#wpdc_settings\\[disablewhere\\]\\[disable-category\\]')[0].selectedIndex = -1;return false;">
        Clear
    </button>
<?php
elseif ('wpdc_disable-tag' == $field['label_for']) : ?>
    <?php $tags = get_terms('post_tag'); ?>
    <select style="min-width: 190px;" id="wpdc_settings[disablewhere][disable-tag]"
            name="wpdc_settings[disablewhere][disable-tag][]" size="4"
            multiple="multiple">
        <?php foreach ($tags as $tag) { ?>
            <option
                value="<?php echo esc_attr($tag->term_id); ?>" <?php echo(isset($settings['disablewhere']['disable-tag']) && in_array($tag->term_id, (array)$settings['disablewhere']['disable-tag']) ? 'selected="selected"' : ''); ?>><?php echo esc_html($tag->name); ?></option>
        <?php } ?>
    </select>
    <button id="clear-tag" class="button-secondary"
            onclick="javascript:jQuery('#wpdc_settings\\[disablewhere\\]\\[disable-tag\\]')[0].selectedIndex = -1;return false;">
        Clear
    </button>
<?php
elseif ('wpdc_disable-user' == $field['label_for']) : ?>
    <?php
    $allUsers = get_users('orderby=post_count&order=DESC');
    $users = array();
    // Remove subscribers from the list as they won't write any articles
    foreach ($allUsers as $currentUser) {
        if (!in_array('subscriber', $currentUser->roles)) {
            $users[] = $currentUser;
        }
    }
    ?>
    <select style="min-width: 190px;" id="wpdc_settings[disablewhere][disable-user]"
            name="wpdc_settings[disablewhere][disable-user][]" size="4"
            multiple="multiple">
        <?php foreach ($users as $user) { ?>
            <option
                value="<?php echo esc_attr($user->ID); ?>" <?php echo(isset($settings['disablewhere']['disable-user']) && in_array($user->ID, (array)$settings['disablewhere']['disable-user']) ? 'selected="selected"' : ''); ?>><?php echo esc_html($user->display_name); ?></option>
        <?php } ?>
    </select>
    <button id="clear-user" class="button-secondary"
            onclick="javascript:jQuery('#wpdc_settings\\[disablewhere\\]\\[disable-user\\]')[0].selectedIndex = -1;return false;">
        Clear
    </button>
<?php
elseif ('wpdc_disable-format' == $field['label_for']) : ?>
    <?php $formats = get_theme_support('post-formats'); ?>
    <select style="min-width: 190px;" id="wpdc_settings[disablewhere][disable-format]"
            name="wpdc_settings[disablewhere][disable-format][]" size="4"
            multiple="multiple">
        <?php
        if (is_array($formats) && count($formats) > 0) {
            ?>
            <option
                value="0" <?php echo(isset($settings['disablewhere']['disable-format']) && in_array('0', (array)$settings['disablewhere']['disable-format']) ? 'selected="selected"' : ''); ?>><?php echo get_post_format_string('standard'); ?></option>
            <?php
            foreach ($formats[0] as $format_name) {
                ?>
                <option
                    value="<?php echo esc_attr($format_name); ?>" <?php echo(isset($settings['disablewhere']['disable-format']) && in_array($format_name, (array)$settings['disablewhere']['disable-format']) ? 'selected="selected"' : ''); ?>><?php echo esc_html(get_post_format_string($format_name)); ?></option>
            <?php
            }
        }
        ?>
    </select>
    <button id="clear-format" class="button-secondary"
            onclick="javascript:jQuery('#wpdc_settings\\[disablewhere\\]\\[disable-format\\]')[0].selectedIndex = -1;return false;">
        Clear
    </button>
<?php
elseif ('wpdc_disable-post-type' == $field['label_for']) : ?>
    <?php $post_types = get_post_types(); ?>
    <select style="min-width: 190px;" id="wpdc_settings[disablewhere][disable-post-type]"
            name="wpdc_settings[disablewhere][disable-post-type][]" size="4"
            multiple="multiple">
        <?php
        foreach ($post_types as $post_type_name) {
            ?>
            <option
                value="<?php echo esc_attr($post_type_name); ?>" <?php echo(isset($settings['disablewhere']['disable-post-type']) && in_array($post_type_name, (array)$settings['disablewhere']['disable-post-type']) ? 'selected="selected"' : ''); ?>><?php echo esc_html(get_post_type_object($post_type_name)->labels->name); ?></option>
        <?php
        }
        ?>
    </select>
    <button id="clear-post-type" class="button-secondary"
            onclick="javascript:jQuery('#wpdc_settings\\[disablewhere\\]\\[disable-post-type\\]')[0].selectedIndex = -1;return false;">
        Clear
    </button>
<?php
elseif ('wpdc_disable-language' == $field['label_for'] && function_exists('qtrans_getSortedLanguages')) : ?>
    <?php $languages = qtrans_getSortedLanguages(); ?>
    <select style="min-width: 190px;" id="wpdc_settings[disablewhere][disable-language]"
            name="wpdc_settings[disablewhere][disable-language][]" size="4"
            multiple="multiple">
        <?php
        foreach ($languages as $language_name) {
            ?>
            <option
                value="<?php echo esc_attr($language_name); ?>" <?php echo(isset($settings['disablewhere']['disable-language']) && in_array($language_name, (array)$settings['disablewhere']['disable-language']) ? 'selected="selected"' : ''); ?>><?php echo $q_config['language_name'][$language_name]; ?></option>
        <?php
        }
        ?>
    </select>
    <button id="clear-language" class="button-secondary"
            onclick="javascript:jQuery('#wpdc_settings\\[disablewhere\\]\\[disable-language\\]')[0].selectedIndex = -1;return false;">
        Clear
    </button>
<?php
elseif ('wpdc_disable-language' == $field['label_for']) : ?>
    <p>This option is only available with the plugin <a href="https://wordpress.org/plugins/qtranslate/">qTranslate</a>
        or <a href="https://wordpress.org/plugins/mqtranslate/">mqTranslate</a>.</p>
<?php
elseif ('wpdc_disable-url' == $field['label_for']) : ?>
    <input type="text" name="wpdc_settings[disablewhere][disable-url]"
           id="wpdc_settings[disablewhere][disable-url]"
           value="<?php echo $settings['disablewhere']['disable-url']; ?>">
<?php
elseif ('wpdc_disable-referrer' == $field['label_for']) : ?>
    <input type="text" name="wpdc_settings[disablewhere][disable-referrer]"
           id="wpdc_settings[disablewhere][disable-referrer]"
           value="<?php echo $settings['disablewhere']['disable-referrer']; ?>">
<?php
elseif ('wpdc_disable-ipaddress' == $field['label_for']) : ?>
    <input type="text" name="wpdc_settings[disablewhere][disable-ipaddress]"
           id="wpdc_settings[disablewhere][disable-ipaddress]"
           value="<?php echo $settings['disablewhere']['disable-ipaddress']; ?>">
<?php
elseif ('wpdc_disable-checkboxes' == $field['label_for']) : ?>
    <?php $post_types = get_post_types(); ?>
    <select style="min-width: 190px;" id="wpdc_settings[disablewhere][disable-checkboxes]"
            name="wpdc_settings[disablewhere][disable-checkboxes][]" size="4"
            multiple="multiple">
        <?php
        foreach ($post_types as $post_type_name) {
            ?>
            <option
                value="<?php echo esc_attr($post_type_name); ?>" <?php echo(isset($settings['disablewhere']['disable-checkboxes']) && in_array($post_type_name, (array)$settings['disablewhere']['disable-checkboxes']) ? 'selected="selected"' : ''); ?>><?php echo esc_html(get_post_type_object($post_type_name)->labels->name); ?></option>
        <?php
        }
        ?>
    </select>
    <button id="clear-checkboxes" class="button-secondary"
            onclick="javascript:jQuery('#wpdc_settings\\[disablewhere\\]\\[disable-checkboxes\\]')[0].selectedIndex = -1;return false;">
        Clear
    </button>
    <p class="description">This can be used to disable the checkboxes by default but without completely disabling commenting.</p>
<?php endif; ?>