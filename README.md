# WordPress User Bookmarks Plugin

A simple and elegant WordPress plugin that allows registered users to bookmark posts and articles for easy access later. Features AJAX functionality, responsive design, and easy-to-use shortcodes.

## 📋 Features

- ✅ **One-click bookmarking** with AJAX (no page reload)
- ✅ **Automatic bookmark buttons** on single posts
- ✅ **Bookmark management page** with remove functionality
- ✅ **Responsive design** that works on all devices
- ✅ **User authentication** (only logged-in users can bookmark)
- ✅ **Security** with WordPress nonce verification
- ✅ **Clean, modern UI** with smooth animations
- ✅ **Shortcode support** for flexible placement

## 🚀 Installation

### Method 1: Manual Installation

1. **Download the plugin file**
   - Save the provided PHP code as `user-bookmarks.php`

2. **Upload to WordPress**
   - Upload the file to `/wp-content/plugins/user-bookmarks/` directory
   - Or upload via WordPress admin: **Plugins → Add New → Upload Plugin**

3. **Activate the plugin**
   - Go to **WordPress Admin → Plugins**
   - Find "User Bookmarks" and click **Activate**

### Method 2: FTP Installation

1. Create a folder named `user-bookmarks` in `/wp-content/plugins/`
2. Upload `user-bookmarks.php` to this folder
3. Activate the plugin in WordPress admin

## 🎯 Quick Setup

### Step 1: Automatic Setup
Once activated, the plugin automatically:
- Adds bookmark buttons to all single posts
- Loads necessary CSS and JavaScript
- Sets up AJAX endpoints

### Step 2: Create a Bookmarks Page
1. Go to **Pages → Add New**
2. Title: "My Bookmarks" (or any title you prefer)
3. In the content area, add: `[user_bookmarks]`
4. Publish the page

### Step 3: Test the Functionality
1. Visit any single post on your site
2. You should see a bookmark button at the bottom
3. Click to bookmark/unbookmark (must be logged in)
4. Visit your bookmarks page to see saved posts

## 📖 Shortcode Documentation

### `[user_bookmarks]` - Display Bookmarks List

Display a complete list of user's bookmarked posts with management options.

**Basic Usage:**
```php
[user_bookmarks]
```

**Advanced Usage:**
```php
[user_bookmarks posts_per_page="20" show_excerpt="true" excerpt_length="200"]
```

**Parameters:**
- `posts_per_page` (default: 10) - Number of bookmarks to display
- `show_excerpt` (default: true) - Show post excerpts
- `excerpt_length` (default: 150) - Maximum excerpt length in characters

### `[bookmark_button]` - Add Bookmark Button

Add a bookmark button anywhere on your site.

**Basic Usage:**
```php
[bookmark_button]
```

**Advanced Usage:**
```php
[bookmark_button post_id="123" text="Save Article" bookmarked_text="Saved!"]
```

**Parameters:**
- `post_id` (default: current post) - ID of post to bookmark
- `text` (default: "Bookmark") - Text for unbookmarked state
- `bookmarked_text` (default: "Bookmarked") - Text for bookmarked state

## 🎨 Customization

### Custom CSS Styling

Add custom CSS to your theme's `style.css` or in **Appearance → Customize → Additional CSS**:

```css
/* Customize bookmark button */
.bookmark-button {
    background: #your-color !important;
    border-color: #your-border-color !important;
}

/* Customize bookmarked state */
.bookmark-button.bookmarked {
    background: #your-active-color !important;
}

/* Customize bookmark items */
.bookmark-item {
    border-left: 4px solid #your-accent-color;
}
```

### Disable Automatic Buttons

If you prefer to place bookmark buttons manually using shortcodes:

1. Edit the plugin file
2. Find this line: `add_filter('the_content', array($this, 'add_bookmark_button_to_posts'));`
3. Comment it out: `// add_filter('the_content', array($this, 'add_bookmark_button_to_posts'));`

### Custom Placement in Theme

Add bookmark buttons in your theme templates:

```php
// In single.php or other template files
if (function_exists('is_post_bookmarked')) {
    echo do_shortcode('[bookmark_button]');
}
```

## 🔧 Advanced Usage

### PHP Helper Functions

The plugin provides several helper functions for developers:

```php
// Check if a post is bookmarked
if (is_post_bookmarked(get_the_ID())) {
    echo "This post is bookmarked!";
}

// Get user's bookmark count
$count = get_user_bookmark_count();
echo "You have {$count} bookmarks";

// Get all bookmarked post IDs for current user
$bookmarks = get_user_bookmarks();
foreach ($bookmarks as $post_id) {
    echo get_the_title($post_id) . "<br>";
}

// Get bookmarks for specific user
$user_bookmarks = get_user_bookmarks($user_id);
```

### Custom Page Templates

Create a custom page template for bookmarks:

```php
<?php
/*
Template Name: Bookmarks Page
*/

get_header(); ?>

<div class="container">
    <h1>My Saved Articles</h1>
    <p>Manage your bookmarked posts and articles below:</p>
    
    <?php echo do_shortcode('[user_bookmarks posts_per_page="15" show_excerpt="true"]'); ?>
</div>

<?php get_footer(); ?>
```

## 🔐 User Authentication

### Login Requirements
- Only **logged-in users** can bookmark posts
- Guest users see login prompts when attempting to bookmark
- Bookmarks are tied to user accounts

### User Roles
The plugin works with all WordPress user roles:
- Subscribers
- Contributors  
- Authors
- Editors
- Administrators

## 📱 Responsive Design

The plugin includes responsive CSS that works on:
- **Desktop** - Full-featured interface
- **Tablet** - Optimized layout
- **Mobile** - Touch-friendly buttons

## 🎯 Page Examples

### Basic Bookmarks Page
```html
<h2>My Reading List</h2>
<p>Articles I want to read later:</p>
[user_bookmarks]
```

### Advanced Bookmarks Page
```html
<div class="my-bookmarks-section">
    <h2>📚 My Saved Articles</h2>
    <p>Keep track of interesting articles and posts.</p>
    
    [user_bookmarks posts_per_page="20" show_excerpt="true" excerpt_length="250"]
    
    <div class="bookmark-tips">
        <h3>💡 Tips:</h3>
        <ul>
            <li>Click the star (☆) on any post to bookmark it</li>
            <li>Visit this page anytime to see your saved articles</li>
            <li>Use the "Remove" button to clean up your list</li>
        </ul>
    </div>
</div>
```

### User Dashboard Integration
```html
<div class="user-dashboard">
    <h2>Welcome back, [user_display_name]!</h2>
    
    <div class="dashboard-stats">
        <p>You have <strong>[bookmark_count]</strong> saved articles</p>
    </div>
    
    <h3>Recent Bookmarks</h3>
    [user_bookmarks posts_per_page="5" show_excerpt="false"]
    
    <p><a href="/my-bookmarks/">View All Bookmarks →</a></p>
</div>
```

## 🐛 Troubleshooting

### Common Issues

**Bookmark button not appearing:**
- Check if you're viewing a single post
- Ensure the plugin is activated
- Try clearing cache if using caching plugins

**AJAX not working:**
- Check browser console for JavaScript errors
- Ensure jQuery is loaded
- Verify WordPress AJAX is functioning

**Bookmarks not saving:**
- Confirm user is logged in
- Check WordPress database permissions
- Verify nonce validation is passing

**Styling issues:**
- Check for theme CSS conflicts
- Add `!important` to custom CSS rules
- Clear browser cache

### Debug Mode

Add this to your `wp-config.php` for debugging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🔄 Updates & Maintenance

### Database
- Bookmarks are stored in WordPress `user_meta` table
- Field name: `user_bookmarks`
- Data format: Array of post IDs

### Cleanup
To remove all bookmark data:
```php
// Add this to functions.php temporarily
global $wpdb;
$wpdb->delete($wpdb->usermeta, array('meta_key' => 'user_bookmarks'));
```

## 📞 Support

### Common Questions

**Q: Can guests bookmark posts?**
A: No, users must be logged in to bookmark posts.

**Q: Are bookmarks shared between users?**
A: No, each user has their own private bookmark list.

**Q: Can I export bookmarks?**
A: Not built-in, but you can use the helper functions to create custom export functionality.

**Q: Does this work with custom post types?**
A: Currently only supports standard posts. Custom post type support can be added.

**Q: Will bookmarks survive plugin updates?**
A: Yes, bookmarks are stored in WordPress database, not plugin files.

## 🎯 Tips for Success

1. **Create a prominent bookmarks page** - Add it to your main menu
2. **Educate users** - Add instructions on how bookmarking works  
3. **Mobile optimization** - Test on mobile devices
4. **User onboarding** - Show new users how to use bookmarks
5. **Regular maintenance** - Monitor for broken bookmarks

## 🔧 Customization Examples

### Custom Bookmark Icon
```css
.bookmark-icon::before {
    content: "🔖"; /* Use any emoji or icon */
}
```

### Different Button Style
```css
.bookmark-button {
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 25px;
}
```

### Bookmark Counter in Menu
```php
// Add to functions.php
function add_bookmark_count_to_menu($items, $args) {
    if ($args->theme_location == 'primary') {
        if (is_user_logged_in()) {
            $count = get_user_bookmark_count();
            $items .= '<li><a href="/bookmarks/">Bookmarks ('.$count.')</a></li>';
        }
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'add_bookmark_count_to_menu', 10, 2);
```

---

## 🎉 You're All Set!

Your WordPress User Bookmarks plugin is now ready to use. Users can start bookmarking their favorite posts and manage them easily through your bookmarks page.

**Need help?** Check the troubleshooting section or review the code comments in the plugin file for more technical details.