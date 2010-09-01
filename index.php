<?php
/**
 * Plugin Name: Featured image widget
 * Plugin URI: http://wordpress.org/extend/plugins/featured-image-widget/
 * Description: This widget shows the featured image for posts and pages. If a featured image hasn't been set, several fallback mechanisms can be used.
 * Version: 0.1
 * Author: Walter Vos
 * Author URI: http://www.waltervos.nl/
 */

class FeaturedImageWidget extends WP_Widget {
    function FeaturedImageWidget() {
        parent::WP_Widget(false, $name = 'Featured Image Widget');
    }

    function form($instance) {
        $title = esc_attr($instance['title']);
        ?>
<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    function widget($args, $instance) {
        extract($args);
        $size = 'post-thumbnail';
        global $post;

        if (has_post_thumbnail($post->ID)) {
            $title = apply_filters('widget_title', $instance['title']);
            echo $before_widget;
            if ( $title ) echo $before_title . $title . $after_title;
            echo get_the_post_thumbnail($post->ID, $size);
            echo $after_widget;
        } else {
            // the current post lacks a thumbnail, we do nothing?
        }
    }

    function get_attached_images($post_id) { // unused ATM
        $args = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'numberposts' => 1,
                'post_status' => null,
                'post_parent' => $post_id
        );
        $attachments = get_posts($args);
        if (empty($attachments)) return false;
        else {
            foreach ($attachments as $key => $attachment) {
                if ($attachments[$key]->post_content == 'no slideshow') unset($attachments[$key]);
            }
            return $attachments;
        }
    }
} // End class FeaturedImageWidget

add_action('widgets_init', create_function('', 'return register_widget("FeaturedImageWidget");'));
?>