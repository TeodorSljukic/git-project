<?php get_header(); ?>

<main class="main">
    <div class="container">
        <div class="post-1">
            <?php
            if (have_posts()) {
                while (have_posts()) {
                    the_post();
                    ?>
                   <div class="text-1">
                            <h1><?php the_title(); ?></h1>
                            <!-- Dodajemo kratki sadržaj posta -->
                            <p><?php the_excerpt(); ?></p>
                        </div>
                        <div class="photo-1">
                            <?php 
                            if (has_post_thumbnail()) {
                                the_post_thumbnail('thumbnail');
                            } else {
                                $thumbnail_url = wp_get_attachment_url(get_post_thumbnail_id());
                                if ($thumbnail_url) {
                                    echo '<img src="' . esc_url($thumbnail_url) . '" alt="Slika posta">';
                                } else {
                                    echo '<img src="' . get_template_directory_uri() . '/path/to/default/image.jpg" alt="Default slika">';
                                }
                            }
                            ?>
                        </div>
                    <?php
                }
            } else {
                echo 'No posts found';
            }
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
