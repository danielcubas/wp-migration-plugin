<?php

function wpmig_export_content() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $content_types = isset( $_POST['content_type'] ) ? $_POST['content_type'] : [];
    $start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
    $end_date = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';

    $args = [
        'post_type' => $content_types,
        'post_status' => ['publish', 'draft'],
        'date_query' => [
            [
                'after' => $start_date,
                'before' => $end_date,
                'inclusive' => true,
            ],
        ],
        'posts_per_page' => -1,
    ];

    $query = new WP_Query( $args );
    $posts = $query->posts;
    $data = [];

    foreach ( $posts as $post ) {
        $post_data = [
            'post' => $post,
            'meta' => get_post_meta( $post->ID ),
            'terms' => wp_get_post_terms( $post->ID, get_object_taxonomies( $post->post_type ) ),
        ];

        $thumbnail_id = get_post_thumbnail_id( $post->ID );
        if ( $thumbnail_id ) {
            $post_data['thumbnail'] = [
                'id' => $thumbnail_id,
                'url' => wp_get_attachment_url( $thumbnail_id ),
            ];
        }

        $data[] = $post_data;
    }

    $json_data = json_encode( $data );
    header( 'Content-disposition: attachment; filename=wp-migration-export.json' );
    header( 'Content-Type: application/json' );
    echo $json_data;
    exit;
}

?>
