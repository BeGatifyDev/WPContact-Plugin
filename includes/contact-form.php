<?php


add_shortcode('contact', 'show_contact_form');
add_action('rest_api_init', 'create_rest_endpoint');
add_action('init', 'create_submissions_page');
add_action('add_meta_boxes', 'create_meta_box');
add_filter('manage_submission_posts_columns', 'custom_submission_columns');
add_action('manage_submission_posts_custom_column', 'fill_submission_columns', 10, 2);
add_action('admin_init', 'setup_search');


function setup_search()
{

      // Only apply filter to submissions page

      global $typenow;

      if ($typenow === 'submission') {

            add_filter('posts_search', 'submission_search_override', 10, 2);
      }
}

function submission_search_override($search, $query)
{
      

      global $wpdb;

      if ($query->is_main_query() && !empty($query->query['s'])) {
            $sql    = "
              or exists (
                  select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
                  and meta_key in ('name','email','phone')
                  and meta_value like %s
              )
          ";
            $like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
            $search = preg_replace(
                  "#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
                  $wpdb->prepare($sql, $like),
                  $search
            );
      }

      return $search;
}


function fill_submission_columns($column, $post_id)
{
    switch ($column) {

        case 'name':
            echo get_post_meta($post_id, 'name', true);
            break;

            case 'email':
            echo get_post_meta($post_id, 'email', true);
            break;

            case 'phone':
            echo get_post_meta($post_id, 'phone', true);
            break;

            case 'message':
            echo get_post_meta($post_id, 'message', true);
            break;
    }
}




function custom_submission_columns($columns)
{
    $columns = array(
        'cb'     => $columns['cb'],
        'name'   => __('Name', 'contact-plugin'),
        'email'  => __('Email', 'contact-plugin'),
        'phone'  => __('Phone', 'contact-plugin'),
        'message'=> __('Message', 'contact-plugin'),
    );

    return $columns;
}


function create_meta_box()
{

    add_meta_box('custom_contact_form', 'Submission', 'display_submission', 'submission');

}


function display_submission()
{
    echo '<ul>';

    echo '<li><strong>Name:</strong><br />' . get_post_meta(get_the_ID(), 'name', true) . '</li>';
    echo '<li><strong>Email:</strong><br />' . get_post_meta(get_the_ID(), 'email', true) . '</li>';
    echo '<li><strong>Phone:</strong><br />' . get_post_meta(get_the_ID(), 'phone', true) . '</li>';
    echo '<li><strong>Message:</strong><br />' . get_post_meta(get_the_ID(), 'message', true) . '</li>';

    // Display the attachment if available
    $file_url = get_post_meta(get_the_ID(), 'attachment', true);
    if ($file_url) {
        $file_type = wp_check_filetype($file_url);
        echo '<li><strong>Attachment:</strong><br />';
        if (strpos($file_type['type'], 'image') !== false) {
            // Show as image preview
            echo '<img src="' . esc_url($file_url) . '" alt="Attachment" style="max-width:200px;height:auto;" />';
        } else {
            // Show as download link
            echo '<a href="' . esc_url($file_url) . '" target="_blank">Download file</a>';
        }
        echo '</li>';
    }

    echo '</ul>';
}


function create_submissions_page()
{
    $args = [
        'public'      => true,
        'has_archive' => true,
        'publicly_queryable' => false,
        'labels'      => [
            'name'          => 'Submissions',
            'singular_name' => 'Submissions',
            'edit_item' => 'View_Submission'
        ],
        'supports' => false,
        'capability_type' => 'post',
        'capabilities' => array(

            'create_posts' => false,
        ),
        'map_meta_cap' => true,
    ];
   
    register_post_type('submission', $args);
}

function show_contact_form()
{
    include MY_PLUGIN_PATH . '/includes/templates/contact-form.php';
}

function create_rest_endpoint()
{
    register_rest_route('v1/contact-form', 'submit', [
        'methods'  => 'POST',
        'callback' => 'handle_enquiry',
    ]);
}
 
function handle_enquiry($data)
{
    $params = $data->get_params();

    if (!wp_verify_nonce($params['_wpnonce'], 'wp_rest')) {
        return new WP_REST_Response('Message not sent', 422);
    }

    unset($params['_wpnonce'], $params['_wp_http_referer']);

    // Handle File Upload
    if (!empty($_FILES['attachment']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $file_id = media_handle_upload('attachment', 0);

        if (is_wp_error($file_id)) {
            return new WP_REST_Response('File upload failed.', 500);
        } else {
            $file_url = wp_get_attachment_url($file_id);
            $params['attachment'] = $file_url;
        }
    }

    // Prepare Email
    $headers     = [];
    $admin_email = get_bloginfo('admin_email'); 
    $admin_name  = get_bloginfo('name');

    $headers[] = "From: {$admin_name} <{$admin_email}>";
    $headers[] = "Reply-To: {$params['name']} <{$params['email']}>";
    $headers[] = "Content-Type: text/html; charset=UTF-8";

    $subject = "New enquiry from {$params['name']}";

    $message = "<h1>Message from {$params['name']}</h1>";

    // Prepare post data
    $postarr = [
        'post_title' => $params['name'],
        'post_type'  => 'submission',
        'post_status'=> 'publish'
    ];

    // Insert post
    $post_id = wp_insert_post($postarr);

    foreach ($params as $label => $value) {
        $message .= '<strong>' . ucfirst($label) . '</strong>: ' . $value . '<br />';
        add_post_meta($post_id, $label, $value);
    }

    wp_mail($admin_email, $subject, $message, $headers);

    return new WP_REST_Response('✅ Your message has been sent successfully!', 200);
}
