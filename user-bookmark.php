<?php
/**
 * Plugin Name: User Bookmarks
 * Plugin URI: https://mrunknown0001.github.io
 * Description: Allow users to bookmark posts and articles for easy access later. Includes shortcode [user_bookmarks] to display bookmark list.
 * Version: 1.0.0
 * Author: MrUnkown0001
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class UserBookmarks {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_toggle_bookmark', array($this, 'ajax_toggle_bookmark'));
        add_action('wp_ajax_nopriv_toggle_bookmark', array($this, 'ajax_toggle_bookmark_guest'));
        add_action('wp_ajax_remove_bookmark', array($this, 'ajax_remove_bookmark'));
        add_shortcode('user_bookmarks', array($this, 'bookmarks_shortcode'));
        add_shortcode('bookmark_button', array($this, 'bookmark_button_shortcode'));
        add_filter('the_content', array($this, 'add_bookmark_button_to_posts'));
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        
        // Inline CSS
        wp_add_inline_style('wp-block-library', $this->get_bookmark_css());
        
        // Inline JavaScript
        wp_add_inline_script('jquery', $this->get_bookmark_js());
        
        // Localize script for AJAX
        wp_localize_script('jquery', 'bookmark_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bookmark_nonce'),
            'logged_in' => is_user_logged_in()
        ));
    }
    
    /**
     * Get CSS styles for bookmarks
     */
    private function get_bookmark_css() {
        return '
        .bookmark-button {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            margin: 10px 0;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #495057;
            font-size: 14px;
        }
        .bookmark-button:hover {
            background: #e9ecef;
            border-color: #dee2e6;
            color: #495057;
            text-decoration: none;
        }
        .bookmark-button.bookmarked {
            background: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        .bookmark-button.bookmarked:hover {
            background: #ffeaa7;
        }
        .bookmark-icon {
            margin-right: 6px;
            font-size: 16px;
        }
        .bookmarks-container {
            max-width: 800px;
            margin: 20px 0;
        }
        .bookmarks-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .bookmarks-count {
            color: #6c757d;
            font-size: 14px;
        }
        .bookmark-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .bookmark-content {
            flex: 1;
        }
        .bookmark-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .bookmark-title a {
            color: #495057;
            text-decoration: none;
        }
        .bookmark-title a:hover {
            color: #007cba;
        }
        .bookmark-meta {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .bookmark-excerpt {
            color: #495057;
            font-size: 14px;
            line-height: 1.5;
        }
        .bookmark-actions {
            margin-left: 15px;
        }
        .remove-bookmark {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.3s ease;
        }
        .remove-bookmark:hover {
            background: #c82333;
        }
        .no-bookmarks {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .no-bookmarks-icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        .login-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        ';
    }
    
    /**
     * Get JavaScript for bookmarks
     */
    private function get_bookmark_js() {
        return "
        jQuery(document).ready(function($) {
            // Toggle bookmark
            $(document).on('click', '.bookmark-button', function(e) {
                e.preventDefault();
                
                if (!bookmark_ajax.logged_in) {
                    alert('Please log in to bookmark posts.');
                    return;
                }
                
                var button = $(this);
                var postId = button.data('post-id');
                
                button.prop('disabled', true);
                
                $.ajax({
                    url: bookmark_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'toggle_bookmark',
                        post_id: postId,
                        nonce: bookmark_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.data.bookmarked) {
                                button.addClass('bookmarked');
                                button.find('.bookmark-text').text('Bookmarked');
                                button.find('.bookmark-icon').text('★');
                            } else {
                                button.removeClass('bookmarked');
                                button.find('.bookmark-text').text('Bookmark');
                                button.find('.bookmark-icon').text('☆');
                            }
                            
                            // Update bookmarks count if present
                            $('.bookmarks-count').text(response.data.count + ' bookmark' + (response.data.count !== 1 ? 's' : ''));
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Network error occurred.');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            });
            
            // Remove bookmark from list
            $(document).on('click', '.remove-bookmark', function(e) {
                e.preventDefault();
                
                if (!confirm('Remove this bookmark?')) {
                    return;
                }
                
                var button = $(this);
                var postId = button.data('post-id');
                var bookmarkItem = button.closest('.bookmark-item');
                
                button.prop('disabled', true);
                
                $.ajax({
                    url: bookmark_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'remove_bookmark',
                        post_id: postId,
                        nonce: bookmark_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            bookmarkItem.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Update count
                                $('.bookmarks-count').text(response.data.count + ' bookmark' + (response.data.count !== 1 ? 's' : ''));
                                
                                // Show no bookmarks message if empty
                                if (response.data.count === 0) {
                                    $('.bookmarks-list').html('<div class=\"no-bookmarks\"><div class=\"no-bookmarks-icon\">📚</div><p>No bookmarks yet!</p><p>Start bookmarking posts to see them here.</p></div>');
                                }
                            });
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Network error occurred.');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            });
        });
        ";
    }
    
    /**
     * Add/remove bookmark
     */
    public function add_bookmark($user_id, $post_id) {
        $bookmarks = get_user_meta($user_id, 'user_bookmarks', true);
        if (!is_array($bookmarks)) {
            $bookmarks = array();
        }
        
        if (!in_array($post_id, $bookmarks)) {
            $bookmarks[] = $post_id;
            update_user_meta($user_id, 'user_bookmarks', $bookmarks);
            return true;
        }
        
        return false;
    }
    
    public function remove_bookmark($user_id, $post_id) {
        $bookmarks = get_user_meta($user_id, 'user_bookmarks', true);
        if (!is_array($bookmarks)) {
            return false;
        }
        
        $key = array_search($post_id, $bookmarks);
        if ($key !== false) {
            unset($bookmarks[$key]);
            $bookmarks = array_values($bookmarks); // Reindex array
            update_user_meta($user_id, 'user_bookmarks', $bookmarks);
            return true;
        }
        
        return false;
    }
    
    public function is_bookmarked($user_id, $post_id) {
        $bookmarks = get_user_meta($user_id, 'user_bookmarks', true);
        if (!is_array($bookmarks)) {
            return false;
        }
        
        return in_array($post_id, $bookmarks);
    }
    
    /**
     * AJAX handler for toggle bookmark
     */
    public function ajax_toggle_bookmark() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bookmark_nonce')) {
            wp_send_json_error('Security check failed.');
            return;
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('Please log in to bookmark posts.');
            return;
        }
        
        $user_id = get_current_user_id();
        $post_id = intval($_POST['post_id']);
        
        if (!$post_id || !get_post($post_id)) {
            wp_send_json_error('Invalid post.');
            return;
        }
        
        $is_bookmarked = $this->is_bookmarked($user_id, $post_id);
        
        if ($is_bookmarked) {
            $this->remove_bookmark($user_id, $post_id);
            $bookmarked = false;
        } else {
            $this->add_bookmark($user_id, $post_id);
            $bookmarked = true;
        }
        
        // Get updated count
        $bookmarks = get_user_meta($user_id, 'user_bookmarks', true);
        $count = is_array($bookmarks) ? count($bookmarks) : 0;
        
        wp_send_json_success(array(
            'bookmarked' => $bookmarked,
            'count' => $count
        ));
    }
    
    /**
     * AJAX handler for guests
     */
    public function ajax_toggle_bookmark_guest() {
        wp_send_json_error('Please log in to bookmark posts.');
    }
    
    /**
     * AJAX handler for remove bookmark
     */
    public function ajax_remove_bookmark() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bookmark_nonce')) {
            wp_send_json_error('Security check failed.');
            return;
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('Please log in.');
            return;
        }
        
        $user_id = get_current_user_id();
        $post_id = intval($_POST['post_id']);
        
        if (!$post_id) {
            wp_send_json_error('Invalid post.');
            return;
        }
        
        $this->remove_bookmark($user_id, $post_id);
        
        // Get updated count
        $bookmarks = get_user_meta($user_id, 'user_bookmarks', true);
        $count = is_array($bookmarks) ? count($bookmarks) : 0;
        
        wp_send_json_success(array(
            'count' => $count
        ));
    }
    
    /**
     * Bookmark button shortcode
     */
    public function bookmark_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'text' => 'Bookmark',
            'bookmarked_text' => 'Bookmarked'
        ), $atts);
        
        $post_id = intval($atts['post_id']);
        if (!$post_id) {
            return '';
        }
        
        $is_bookmarked = false;
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $is_bookmarked = $this->is_bookmarked($user_id, $post_id);
        }
        
        $class = $is_bookmarked ? 'bookmark-button bookmarked' : 'bookmark-button';
        $icon = $is_bookmarked ? '★' : '☆';
        $text = $is_bookmarked ? $atts['bookmarked_text'] : $atts['text'];
        
        return sprintf(
            '<button class="%s" data-post-id="%d"><span class="bookmark-icon">%s</span><span class="bookmark-text">%s</span></button>',
            esc_attr($class),
            $post_id,
            $icon,
            esc_html($text)
        );
    }
    
    /**
     * Bookmarks list shortcode
     */
    public function bookmarks_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 10,
            'show_excerpt' => 'true',
            'excerpt_length' => 150
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<div class="login-message">Please <a href="' . wp_login_url() . '">log in</a> to view your bookmarks.</div>';
        }
        
        $user_id = get_current_user_id();
        $bookmarks = get_user_meta($user_id, 'user_bookmarks', true);
        
        if (!is_array($bookmarks) || empty($bookmarks)) {
            return '<div class="bookmarks-container"><div class="no-bookmarks"><div class="no-bookmarks-icon">📚</div><p>No bookmarks yet!</p><p>Start bookmarking posts to see them here.</p></div></div>';
        }
        
        // Reverse array to show newest bookmarks first
        $bookmarks = array_reverse($bookmarks);
        
        // Paginate if needed
        $posts_per_page = intval($atts['posts_per_page']);
        if ($posts_per_page > 0) {
            $bookmarks = array_slice($bookmarks, 0, $posts_per_page);
        }
        
        // Get posts
        $posts = get_posts(array(
            'post__in' => $bookmarks,
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'post__in'
        ));
        
        if (empty($posts)) {
            return '<div class="bookmarks-container"><div class="no-bookmarks"><div class="no-bookmarks-icon">📚</div><p>No valid bookmarks found.</p></div></div>';
        }
        
        $output = '<div class="bookmarks-container">';
        $output .= '<div class="bookmarks-header">';
        $output .= '<h3>My Bookmarks</h3>';
        $output .= '<div class="bookmarks-count">' . count($bookmarks) . ' bookmark' . (count($bookmarks) !== 1 ? 's' : '') . '</div>';
        $output .= '</div>';
        
        $output .= '<div class="bookmarks-list">';
        
        foreach ($posts as $post) {
            $output .= '<div class="bookmark-item">';
            $output .= '<div class="bookmark-content">';
            $output .= '<div class="bookmark-title"><a href="' . get_permalink($post->ID) . '">' . get_the_title($post->ID) . '</a></div>';
            $output .= '<div class="bookmark-meta">';
            $output .= 'Published on ' . get_the_date('F j, Y', $post->ID);
            $categories = get_the_category($post->ID);
            if (!empty($categories)) {
                $output .= ' in ' . $categories[0]->name;
            }
            $output .= '</div>';
            
            if ($atts['show_excerpt'] === 'true') {
                $excerpt = get_the_excerpt($post->ID);
                if (empty($excerpt)) {
                    $excerpt = wp_trim_words(get_the_content('', false, $post->ID), 20);
                }
                $excerpt_length = intval($atts['excerpt_length']);
                if ($excerpt_length > 0 && strlen($excerpt) > $excerpt_length) {
                    $excerpt = substr($excerpt, 0, $excerpt_length) . '...';
                }
                $output .= '<div class="bookmark-excerpt">' . wp_kses_post($excerpt) . '</div>';
            }
            
            $output .= '</div>';
            $output .= '<div class="bookmark-actions">';
            $output .= '<button class="remove-bookmark" data-post-id="' . $post->ID . '">Remove</button>';
            $output .= '</div>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Automatically add bookmark button to posts
     */
    public function add_bookmark_button_to_posts($content) {
        if (is_single() && get_post_type() === 'post') {
            $bookmark_button = $this->bookmark_button_shortcode(array());
            $content .= '<div style="margin-top: 20px;">' . $bookmark_button . '</div>';
        }
        
        return $content;
    }
}

// Initialize the plugin
new UserBookmarks();

/**
 * Helper functions (optional)
 */

// Function to get user's bookmarked posts
function get_user_bookmarks($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return array();
    }
    
    $bookmarks = get_user_meta($user_id, 'user_bookmarks', true);
    return is_array($bookmarks) ? $bookmarks : array();
}

// Function to check if a post is bookmarked
function is_post_bookmarked($post_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $bookmarks = get_user_bookmarks($user_id);
    return in_array($post_id, $bookmarks);
}

// Function to get bookmark count for a user
function get_user_bookmark_count($user_id = null) {
    $bookmarks = get_user_bookmarks($user_id);
    return count($bookmarks);
}
?>