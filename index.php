<?php

/**
 * Plugin Name: Featured image widget
 * Plugin URI: http://wordpress.org/extend/plugins/featured-image-widget/
 * Description: This widget shows the featured image for posts and pages. If a featured image hasn't been set, several fallback mechanisms can be used.
 * Version: 0.4
 * Author: Walter Vos
 * Author URI: http://www.waltervos.nl/
 */
 
class FeaturedImageWidget extends WP_Widget {

    function FeaturedImageWidget() {
        parent::WP_Widget(false, $name = 'Featured Image Widget', array('description' => __('Shows the featured image for posts and pages. If a featured image hasn\'t been set, several fallback mechanisms can be used.')));
    }

    function form($instance) {
        $title = esc_attr($instance['title']);
        $instance['image-size'] = (!$instance['image-size'] || $instance['image-size'] == '') ? 'post-thumbnail' : $instance['image-size'];
        ?>
        <fieldset>
            <p>
                <label for="<?php echo $this->get_field_id('image-size'); ?>">Image size to display:</label>
                <select class="widefat" id="<?php echo $this->get_field_id('image-size'); ?>" name="<?php echo $this->get_field_name('image-size'); ?>">
                    <?php foreach (get_image_sizes() as $name => $properties) : ?>
                        <option value="<?php echo $name; ?>"<?php selected($instance['image-size'], $name); ?>><?php echo $name . " (" . $properties['width'] . "x" . $properties['height'] . ")"; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
        </fieldset>
        <fieldset>
            <legend>Before image, display:</legend>
            <p>
                <input id="<?php echo $this->get_field_id('before_widget'); ?>" name="<?php echo $this->get_field_name('before_widget'); ?>" type="radio" value="standard" />
                <input class="regular-text" id="<?php echo $this->get_field_id('before_widget_title'); ?>" name="<?php echo $this->get_field_name('before_widget_title'); ?>" type="text" value="<?php echo $title; ?>" />
            </p>
            <p>
                <label>
                    <input id="<?php echo $this->get_field_id('before_widget'); ?>" name="<?php echo $this->get_field_name('before_widget'); ?>" type="radio" value="image_title" />
                    Image title
                </label>
            </p>
            <p>
                <label>
                    <input id="<?php echo $this->get_field_id('before_widget'); ?>" name="<?php echo $this->get_field_name('before_widget'); ?>" type="radio" value="post_title" />
                    Post/page title
                </label>
            </p>
            <p>
                <label>
                    <input id="<?php echo $this->get_field_id('before_widget'); ?>" name="<?php echo $this->get_field_name('before_widget'); ?>" type="radio" value="image_caption" />
                    Image caption
                </label>
            </p>
        </fieldset>
        <h5>Enable fallback mechanisms:</h5>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($instance['attached_img_fallback']); ?> id="<?php echo $this->get_field_id('attached_img_fallback'); ?>" name="<?php echo $this->get_field_name('attached_img_fallback'); ?>" />
            <label for="<?php echo $this->get_field_id('attached_img_fallback'); ?>"><?php _e('Attached image'); ?></label><br />
            <input class="checkbox" type="checkbox" <?php checked($instance['random_img_fallback']); ?> id="<?php echo $this->get_field_id('random_img_fallback'); ?>" name="<?php echo $this->get_field_name('random_img_fallback'); ?>" />
            <label for="<?php echo $this->get_field_id('random_img_fallback'); ?>"><?php _e('Random image'); ?></label><br />
            <small><?php _e('When a featured image hasn\'t been set, this plugin can use one or both of the two fallback mechanisms mentioned above.'); ?></small>
        </p>
        <?php
    }

    function update($new_instance, $old_instance) {
        $new_instance['title'] = strip_tags($new_instance['title']);
        return $new_instance;
    }

    function widget($args, $instance) {
        extract($args);
        $size = $instance['image-size'];
        global $post;

        if ($this->has_post_thumbnail($post->ID)) {
            $title = apply_filters('widget_title', $instance['title']);
            echo $before_widget;
            if ($title)
                echo $before_title . $title . $after_title;
            echo $this->get_the_post_thumbnail($post->ID, $size);
            echo $after_widget;
        } elseif ($post->post_parent && has_post_thumbnail($post->post_parent)) {
            $title = apply_filters('widget_title', $instance['title']);
            echo $before_widget;
            if ($title) {
                echo $before_title . $title . $after_title;
            }
            echo get_the_post_thumbnail($post->post_parent, $size);
            echo $after_widget;
        } elseif ($post->post_parent && has_post_thumbnail($post->post_parent)) {
            $title = apply_filters('widget_title', $instance['title']);
            echo $before_widget;
            if ($title) {
                echo $before_title . $title . $after_title;
            }
            echo get_the_post_thumbnail($post->post_parent, $size);
            echo $after_widget;
        } else {
            // the current post lacks a thumbnail, we do nothing?
        }
    }

    function has_post_thumbnail($post_id) {
        return has_post_thumbnail($post_id);
    }

    function get_the_post_thumbnail($post_id, $size) {
        return get_the_post_thumbnail($post_id, $size);
    }

    function get_attached_image($post_id) { // unused ATM
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'numberposts' => 1,
			'post_status' => null,
            'post_parent' => $post_id
        );
        $attachments = get_posts($args);
        if (empty($attachments))
            return false;
        else {
            return $attachments;
        }
    }

}

// End class FeaturedImageWidget

/* From the WordPress Codex */
function get_image_sizes() {

    global $_wp_additional_image_sizes;

    $sizes = array();
    $get_intermediate_image_sizes = get_intermediate_image_sizes();

    // Create the full array with sizes and crop info
    foreach ($get_intermediate_image_sizes as $_size) {

        if (in_array($_size, array('thumbnail', 'medium', 'large'))) {

            $sizes[$_size]['width'] = get_option($_size . '_size_w');
            $sizes[$_size]['height'] = get_option($_size . '_size_h');
            $sizes[$_size]['crop'] = (bool) get_option($_size . '_crop');
        } elseif (isset($_wp_additional_image_sizes[$_size])) {

            $sizes[$_size] = array(
                'width' => $_wp_additional_image_sizes[$_size]['width'],
                'height' => $_wp_additional_image_sizes[$_size]['height'],
                'crop' => $_wp_additional_image_sizes[$_size]['crop']
            );
        }
    }

    return $sizes;
}

function fiw_add_theme_support() {
    if (function_exists('add_theme_support')) {
        if (!current_theme_supports('post-thumbnails'))
            add_theme_support('post-thumbnails');
        add_action('widgets_init', create_function('', 'return register_widget("FeaturedImageWidget");'));
    }
}

add_action('after_setup_theme', 'fiw_add_theme_support');
?>
