<?php get_header(); ?>

<main class="main">
    <div class="container">
        <div class="posts">
            <?php
            $args = array(
                'post_type'      => 'radi',  
                'posts_per_page' => -1,       
            );

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                $is_odd = true;

                while ($query->have_posts()) {
                    $query->the_post();
                    ?>
                    
                   <a href="<?php the_permalink(); ?>" target="_blank" rel="noopener noreferrer" class="post <?php echo $is_odd ? 'post-right' : 'post-left'; ?>">
                       
                        <div class="text">
                            <h1><?php the_title(); ?></h1>
                            
                            <p><?php the_excerpt(); ?></p>
                        </div>
                        <div class="photo">
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
                    </a>
                    <?php
                    $is_odd = !$is_odd; 
                }
            } else {
                echo 'No posts found';
            }

            wp_reset_query(); 
            wp_reset_postdata(); 
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
