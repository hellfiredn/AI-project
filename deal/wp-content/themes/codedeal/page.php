<?php
/**
 * Generic page — Bootstrap.
 */
get_header();
while (have_posts()): the_post(); ?>
<section class="cd-section">
    <div class="container">
        <div class="mx-auto" style="max-width:760px">
            <h1 class="mb-4"><?php the_title(); ?></h1>
            <div class="cd-prose"><?php the_content(); ?></div>
        </div>
    </div>
</section>
<?php endwhile; get_footer(); ?>
