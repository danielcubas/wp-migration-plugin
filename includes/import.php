<?php

function wpmig_import_content() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    set_time_limit( 300 ); // Aumenta o tempo de execução para 300 segundos (5 minutos)

    if ( isset( $_FILES['import_file'] ) && $_FILES['import_file']['error'] == UPLOAD_ERR_OK ) {
        $file = $_FILES['import_file']['tmp_name'];
        $json_data = file_get_contents( $file );
        $data = json_decode( $json_data, true );

        $custom_post_types = wpmig_get_custom_post_types();
        $custom_post_types[] = 'post'; // Incluir posts
        $custom_post_types[] = 'page'; // Incluir páginas

        foreach ( $data as $item ) {
            if ( ! in_array( $item['post']['post_type'], $custom_post_types ) ) {
                $_SESSION['wpmig_error'] = 'Custom Post Type ' . esc_html( $item['post']['post_type'] ) . ' not found in this installation. Import failed.';
                wp_redirect( admin_url( 'admin.php?page=wpmig' ) );
                exit;
            }

            $post_data = [
                'post_type' => $item['post']['post_type'],
                'post_title' => $item['post']['post_title'],
                'post_content' => $item['post']['post_content'],
                'post_status' => $item['post']['post_status'],
                'post_date' => $item['post']['post_date'],
                'post_author' => $item['post']['post_author'],
            ];

            $post_id = wp_insert_post( $post_data );

            if ( ! is_wp_error( $post_id ) ) {
                foreach ( $item['meta'] as $meta_key => $meta_value ) {
                    update_post_meta( $post_id, $meta_key, maybe_unserialize( $meta_value[0] ) );
                }

                foreach ( $item['terms'] as $term ) {
                    wp_set_object_terms( $post_id, $term['name'], $term['taxonomy'] );
                }

                if ( isset( $item['thumbnail'] ) ) {
                    $thumbnail_id = wpmig_import_image( $item['thumbnail']['url'] );
                    if ( ! is_wp_error( $thumbnail_id ) ) {
                        set_post_thumbnail( $post_id, $thumbnail_id );
                    }
                }
            }
        }

        $_SESSION['wpmig_message'] = 'Content imported successfully.';
    } else {
        $_SESSION['wpmig_error'] = 'Failed to import content.';
    }

    wp_redirect( admin_url( 'admin.php?page=wpmig' ) );
    exit;
}

function wpmig_import_image( $url ) {
    $attachment_id = attachment_url_to_postid( $url );
    if ( $attachment_id ) {
        return $attachment_id;
    }

    $tmp = download_url( $url );

    if ( is_wp_error( $tmp ) ) {
        return $tmp;
    }

    $file = [
        'name' => basename( $url ),
        'type' => mime_content_type( $tmp ),
        'tmp_name' => $tmp,
        'error' => 0,
        'size' => filesize( $tmp ),
    ];

    $overrides = [ 'test_form' => false ];

    $results = wp_handle_sideload( $file, $overrides );

    if ( ! empty( $results['error'] ) ) {
        @unlink( $tmp );
        return new WP_Error( 'upload_error', $results['error'] );
    }

    $attachment = [
        'post_mime_type' => $results['type'],
        'post_title' => sanitize_file_name( $results['file'] ),
        'post_content' => '',
        'post_status' => 'inherit',
    ];

    $attachment_id = wp_insert_attachment( $attachment, $results['file'] );

    if ( ! is_wp_error( $attachment_id ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $results['file'] );
        wp_update_attachment_metadata( $attachment_id, $attachment_data );
    }

    return $attachment_id;
}

?>
