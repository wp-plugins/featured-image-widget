<?php

/**
 * Plugin Name: Featured image widget
 * Plugin URI: http://wordpress.org/extend/plugins/featured-image-widget/
 * Description: This widget shows the featured image for posts and pages. If a featured image hasn't been set, several fallback mechanisms can be used.
 * Version: 0.5
 * Author: Walter Vos
 * Author URI: http://www.waltervos.nl/
 */
class FeaturedImageWidget extends WP_Widget {

    private $image_size;
    private $before_image;
    private $fallbacks = array();

    function __construct() {
        parent::__construct(
                'fiw', 'Featured Image Widget', array('description' => __('Shows the featured image for posts and pages. If a featured image hasn\'t been set, several fallback mechanisms can be used.'))
        );
    }

    public function form($instance) {
        if (isset($instance['title'])) {
            $fixed_title_text = esc_attr($instance['title']);
            unset ($instance['title']);
        } else {
            $fixed_title_text = esc_attr($instance['fixed_title_text']);
        }
        $instance['image-size'] = (!$instance['image-size'] || $instance['image-size'] == '') ? 'post-thumbnail' : $instance['image-size'];
        ?>
        <fieldset>
            <p><legend><?php _e('Image size to display:'); ?></p></legend>
            <p>
                <select class="widefat" id="<?php echo $this->get_field_id('image_size'); ?>" name="<?php echo $this->get_field_name('image_size'); ?>">
                    <?php foreach (get_image_sizes() as $name => $properties) : ?>
                        <option value="<?php echo $name; ?>"<?php selected($instance['image_size'], $name); ?>><?php echo $name . " (" . $properties['width'] . "x" . $properties['height'] . ")"; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
        </fieldset>
        <fieldset>
            <p><legend>Before image, display:</p></legend>
            <p>
                <input type="radio" value="fixed_title" <?php checked($instance['before_image'], 'fixed_title'); ?> id="<?php echo $this->get_field_id('before_image'); ?>-fixed_title" name="<?php echo $this->get_field_name('before_image'); ?>" />
                <input type="text" value="<?php echo $fixed_title_text; ?>" id="<?php echo $this->get_field_id('fixed_title_text'); ?>" name="<?php echo $this->get_field_name('fixed_title_text'); ?>" class="regular-text" />
            </p>
            <p>
                <label>
                    <input type="radio" value="image_title" <?php checked($instance['before_image'], 'image_title'); ?> id="<?php echo $this->get_field_id('before_image'); ?>-image_title" name="<?php echo $this->get_field_name('before_image'); ?>" />
                    <?php _e('Image title'); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" value="post_title" <?php checked($instance['before_image'], 'post_title'); ?> id="<?php echo $this->get_field_id('before_image'); ?>-post_title" name="<?php echo $this->get_field_name('before_image'); ?>" />
                    <?php _e('Post/page title'); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" value="image_caption" <?php checked($instance['before_image'], 'image_caption'); ?> id="<?php echo $this->get_field_id('before_image'); ?>-image_caption" name="<?php echo $this->get_field_name('before_image'); ?>" />
                    <?php _e('Image caption'); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" value="nothing" <?php checked($instance['before_image'], 'nothing'); ?> id="<?php echo $this->get_field_id('before_image'); ?>-nothing" name="<?php echo $this->get_field_name('before_image'); ?>" />
                    <?php _e('Nothing'); ?>
                </label>
            </p>
        </fieldset>
        <fieldset>
            <p><legend><?php _e('Enable fallback mechanisms:'); ?></p></legend>
            <p>
                <label>
                    <input type="checkbox" value="true" <?php checked($instance['attached_img_fallback'], 'true'); ?> id="<?php echo $this->get_field_id('attached_img_fallback'); ?>" name="<?php echo $this->get_field_name('attached_img_fallback'); ?>" class="checkbox" />
                    <?php _e('Attached image'); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="checkbox" value="true" <?php checked($instance['random_img_fallback'], 'true'); ?> id="<?php echo $this->get_field_id('random_img_fallback'); ?>" name="<?php echo $this->get_field_name('random_img_fallback'); ?>" class="checkbox" />
                    <?php _e('Random image'); ?>
                </label>
            </p>
            <p><small><?php _e('When a featured image hasn\'t been set, this plugin can use one or both of the two fallback mechanisms mentioned above.'); ?></small></p>
        </fieldset>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $new_instance['fixed_title_text'] = strip_tags($new_instance['fixed_title_text']);
        return $new_instance;
    }

    public function widget($args, $instance) {
        if (is_singular() && !is_attachment()) {
            global $post;

            $fiw_instance = new FIW_Instance($instance, $args, $post);

            if ($fiw_instance->getFIWImage()) {
                echo $args['before_widget'];
                echo $fiw_instance->getFIWTitle();
                echo $fiw_instance->getFIWImage();
                echo $args['after_widget'];
            }
        }
    }

    private function has_post_thumbnail($post_id) {
        return has_post_thumbnail($post_id);
    }

    private function get_the_post_thumbnail($post_id, $size) {
        return get_the_post_thumbnail($post_id, $size);
    }

}

// End class FeaturedImageWidget

class FIW_Instance {

    private $fiw_image_id = false;
    private $title = false;
    private $properties;
    private $args;
    private $post;

    public function __construct($instance, $args, $post) {
        $this->properties = $instance;
        $this->post = $post;
        $this->args = $args;

        $this->setFIWImage();
        $this->setFIWTitle();
    }

    private function setFIWTitle() {
        switch ($this->properties['before_image']) {
            case 'fixed_title' :
                $this->title = $this->properties['fixed_title_text'];
                break;
            case 'image_title' :
                $image = get_post($this->fiw_image_id);
                $this->title = $image->post_title;
                break;
            case 'post_title' :
                $this->title = $this->post->post_title;
                break;
            case 'image_caption' :
                $image = get_post($this->fiw_image_id);
                $this->title = $image->post_excerpt;
                break;
            case 'nothing' :
            default:
                break;
        }
    }

    public function getFIWTitle() {
        if ($this->properties['before_image'] != 'nothing') {
            return $this->args['before_title'] . apply_filters('widget_title', $this->title) . $this->args['after_title'];
        }
    }

    private function setFIWImage() {
        $this->fiw_image_id = $this->getPostThumbnailId($this->post->ID);

        if (!$this->fiw_image_id && $this->properties['attached_img_fallback']) {
            $this->fiw_image_id = $this->getFIWImageId();
        }
        if (!$this->fiw_image_id && $this->properties['random_img_fallback']) {
            $this->fiw_image_id = $this->getFIWImageId(true);
        }
    }

    public function getFIWImage() {
        return wp_get_attachment_image($this->fiw_image_id, $this->properties['image_size']);
    }

    private function getPostThumbnailId($post_id) {
        $post_thumbnail_id = get_post_thumbnail_id($post_id);
        if ($post_thumbnail_id == '') {
            return false;
        } else {
            return $post_thumbnail_id;
        }
    }

    private function getFIWImageId($rand = false) {
        $post_parent = $rand ? null : $this->post->ID;

        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'order' => 'ASC',
            'post_status' => 'any',
            'post_parent' => $post_parent
        ));

        if (!empty($attachments)) {
            if ($rand) {
                shuffle($attachments);
            }
            foreach ($attachments as $attachment) {
                $image_html = wp_get_attachment_image($attachment->ID, $this->properties['image_size']);
                if ($image_html != '') {
                    return $attachment->ID;
                }
            }
        }
        return false;
    }

}

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
        if (!current_theme_supports('post-thumbnails')) {
            add_theme_support('post-thumbnails');
        }
    }
}

add_action('after_setup_theme', 'fiw_add_theme_support');
add_action('widgets_init', create_function('', 'return register_widget("FeaturedImageWidget");'));
?>
