<?php
/*
Template Name: Clubs
*/


global $THEMEREX_GLOBALS;
$THEMEREX_GLOBALS['blog_streampage'] = true;

get_header(); 
$clubs = new WP_Query(
    array(
        'post_type' => 'clubs',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'status',
                'value' => 'active',
                'compare' => '='
            )
        )
    )
);

?>
<div class="itemscope post_item post_item_single post_featured_default post_format_standard club_list" data-columns="3">
    <?php if($clubs->have_posts()) : ?>
    <?php while($clubs->have_posts()) : $clubs->the_post(); ?>
    <div class="club_item">
        <article class="post_item post_item_masonry">

            <div class="post_featured">
                <div class="post_thumb">
                    <a class="hover_icon hover_icon_link" href="javascript:void(0);">
                    <?php the_post_thumbnail('thumbnail');?>
                    </a>
                </div>
            </div>

            <div class="post_content">

                <h4 class="post_title">
                    <?php the_title()?></h4>             

            </div> <!-- /.post_content -->
        </article> <!-- /.post_item -->
    </div>
    <?php endwhile; ?>
    <?php endif; wp_reset_query(); ?>
</div>
<?php
get_footer();
?>