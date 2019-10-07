<?php
namespace jaci;

function related_posts_settings_api_init() {
//     add_settings_section(
//        'setting_section',
//        'Posts Relacionados',
//        'jaci\\related_posts_setting_section',
//        'reading'
//    );
   
    add_settings_field(
       'related_posts__use',
       __('Posts Relacionados', 'jaci'),
       'jaci\\related_posts_setting',
       'reading',
    //    'setting_section'
   );
    
    register_setting( 'reading', 'related_posts__use' );
    register_setting( 'reading', 'related_posts__tags_weight' );
    register_setting( 'reading', 'related_posts__categories_weight' );
}

add_action( 'admin_init', 'jaci\\related_posts_settings_api_init' );


function related_posts_setting_section() {
    // echo '<p>Intro text for our settings section</p>';
}

function related_posts_setting() {
    ?>
    <p><label><input name="related_posts__use" id="related_posts__use" type="checkbox" value="1" class="code" <?= checked( 1, get_option( 'related_posts__use' ), false ) ?> /> <?php _e('Usar posts relacionados') ?></label></p>
    <p><label><?php _e('Peso das tags') ?><br> <input name="related_posts__tags_weight" id="related_posts__tags_weight" type="number" step="0.1" value="<?= get_option('related_posts__tags_weight', 2) ?>" class="code" /></label></p>
    <p><label><?php _e('Usar posts relacionados') ?><br> <input name="related_posts__categories_weight" id="related_posts__categories_weight" type="number" step="0.1" value="<?= get_option('related_posts__categories_weight', 1.5) ?>" class="code" /></label></p>
    <?php
}


function get_post_taxonomy_terms($post_id, $taxonomy){
    $_terms = get_the_terms($post_id, $taxonomy) ?: [];

    $_terms = array_map(function($el) {
        return $el->term_taxonomy_id;
    }, $_terms);

    $result = implode(',', $_terms);
    return $result ?: -1;
}


function get_related_posts($post_id = null, $num = 6, $post_types = ['post']){
    global $wpdb;

    $post_types = implode("','", $post_types);

    if(is_null($post_id)){
        $post_id = get_the_ID();
    }

    $tags_multiplier = get_option('related_posts__tags_weight', 2);
    $categories_multiplier = get_option('related_posts__categories_weight', 1.5);


    $categories = get_post_taxonomy_terms($post_id, 'category');
    $tags = get_post_taxonomy_terms($post_id, 'post_tag');

    $tags_sql = "((SELECT COUNT(t1.term_taxonomy_id) * $tags_multiplier FROM $wpdb->term_relationships t1 WHERE t1.object_id = p.ID AND t1.term_taxonomy_id IN ($tags)))";
    $cats_sql = "((SELECT COUNT(t2.term_taxonomy_id) * $categories_multiplier FROM $wpdb->term_relationships t2 WHERE t2.object_id = p.ID AND t2.term_taxonomy_id IN ($categories)))";
    $date_sql = "
    (3/ABS(datediff(
        (SELECT post_date FROM $wpdb->posts WHERE ID = $post_id),
        p.post_date
    )))";

    $sql = "
    SELECT
        p.ID,
        (
            $tags_sql + 
            $cats_sql + 
            $date_sql
        ) AS num

    FROM $wpdb->posts p
    WHERE
        p.post_type IN ('$post_types') AND
        p.post_status = 'publish' AND
        p.ID <> $post_id
    GROUP BY p.ID
    ORDER BY num DESC, ID ASC
    LIMIT $num";

    $result = $wpdb->get_results($sql);
    echo '<pre>';
    echo $sql;
    print_r($result);
    echo '</pre>';
    $ids = array_map(function($el) { return $el->ID; }, $result);

    if(!$ids) {
        $ids = [-1];
    }

    $query = new \WP_Query([
        'post__in' => $ids,
        'post_type' => ['post'],
        'posts_per_page' => -1,
        'orderby' => 'post__in'
    ]);

    return $query;
}
