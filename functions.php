<?php
require_once ABSPATH . 'wp-admin/includes/post.php';

function support(){
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme','support');

function load_custom_style() {
    wp_enqueue_style('custom', get_template_directory_uri() . "/style.css", array(), '1.0', 'all');
}
add_action('wp_enqueue_scripts', 'load_custom_style');

function custom_post_type() {
    $labels = array(
        'name'                  => _x( 'Custom posts', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'radi', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'Custom Posts', 'text_domain' ),
        'name_admin_bar'        => __( 'Custom Post', 'text_domain' ),
        'archives'              => __( 'Item Archives', 'text_domain' ),
        'attributes'            => __( 'Item Attributes', 'text_domain' ),
        'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
        'all_items'             => __( 'All Items', 'text_domain' ),
        'add_new_item'          => __( 'Add New Item', 'text_domain' ),
        'add_new'               => __( 'Add New', 'text_domain' ),
        'new_item'              => __( 'New Item', 'text_domain' ),
        'edit_item'             => __( 'Edit Item', 'text_domain' ),
        'update_item'           => __( 'Update Item', 'text_domain' ),
        'view_item'             => __( 'View Item', 'text_domain' ),
        'view_items'            => __( 'View Items', 'text_domain' ),
        'search_items'          => __( 'Search Item', 'text_domain' ),
        'not_found'             => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
        'featured_image'        => __( 'Featured Image', 'text_domain' ),
        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
        'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
        'items_list'            => __( 'Items list', 'text_domain' ),
        'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
        'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
    );
    $args = array(
        'label'                 => __( 'Custom Post', 'text_domain' ),
        'description'           => __( 'Custom Post Type Description', 'text_domain' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
    );
    register_post_type( 'radi', $args );

}
add_action( 'init', 'custom_post_type', 0 );

// Funkcija za dohvaćanje podataka s GitHuba i stvaranje postova
add_action('init', 'get_github_repos');
function get_github_repos(){
    $githubUsername = 'laravel';
    $page = isset($_GET['page']) ? abs((int)$_GET['page']) : 1; // Broj stranice
    $per_page = 10; // Broj postova po stranici
    $reposURL = "https://api.github.com/users/{$githubUsername}/repos?page={$page}&per_page={$per_page}";

    $ch = curl_init($reposURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: PHP',
        "Authorization: Bearer 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",
        "Accept: application/vnd.github.v3+json"
    ]);
    $rawResponse = curl_exec($ch);
    $reposData = json_decode($rawResponse, true);
    $curlError = curl_error($ch);
    curl_close($ch);

    require_once 'C:\xampp\htdocs\wordpress\wp-content\themes\git\parsedown.php';
    $parsedown = new Parsedown();

    foreach ($reposData as $repo) {
        $token = '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';
        $readeMeUrl = "https://api.github.com/repos/{$githubUsername}/{$repo['name']}/contents/README.md";

        $headers = [
            'Authorization' => 'token ' . $token,
            'User-Agent' => 'YourApp',
        ];

        $response = wp_remote_get($readeMeUrl, array(
            'headers' => $headers,
        ));

        $redaMeData = json_decode(wp_remote_retrieve_body($response));

        $existing_post = get_page_by_title($repo['name'], OBJECT, 'radi');

        if (!$existing_post) {
            if (!empty($redaMeData->content)) {
                $readeMeMd = base64_decode($redaMeData->content);
                $readeMeHtml = $parsedown->text($readeMeMd);

                $post_data = array(
                    'post_title' => $repo['name'],
                    'post_content' => $readeMeHtml,
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type' => 'radi',
                );

                wp_insert_post($post_data);
            }
        } else {
            if (!empty($redaMeData->content)) {
                $readeMeMd = base64_decode($redaMeData->content);
                $readeMeHtml = $parsedown->text($readeMeMd);

                $updated_post_data = array(
                    'ID' => $existing_post->ID,
                    'post_content' => $readeMeHtml,
                );

                wp_update_post($updated_post_data);
            }
        }
    }

    $total_pages = ceil(count($reposData) / $per_page); // Ukupan broj stranica
    if ($total_pages > 1) {
        echo paginate_links(array(
            'base' => add_query_arg('page', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo; Previous'),
            'next_text' => __('Next &raquo;'),
            'total' => $total_pages,
            'current' => $page,
        ));
    }
}
?>
