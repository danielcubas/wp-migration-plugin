<?php

function wpmig_add_admin_menu() {
    add_menu_page(
        'WP Migration Plugin',
        'WP Migration',
        'manage_options',
        'wpmig',
        'wpmig_admin_page',
        'dashicons-migrate'
    );
}

add_action( 'admin_menu', 'wpmig_add_admin_menu' );

function wpmig_get_custom_post_types() {
    $args = array(
        'public'   => false, // Inclui CPTs não públicos
        '_builtin' => false,
        'show_ui'  => true, // Inclui CPTs que são visíveis no admi
    );
    $output = 'names'; // 'names' or 'objects'
    $operator = 'and'; // 'and' or 'or'
    $post_types = get_post_types( $args, $output, $operator );

    return $post_types;
}

function wpmig_admin_page() {
    $custom_post_types = wpmig_get_custom_post_types();

    ?>
    <div class="wrap">
        <h1>WP Migration Plugin</h1>
        <?php if ( isset( $_SESSION['wpmig_message'] ) ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html( $_SESSION['wpmig_message'] ); ?></p>
            </div>
            <?php unset( $_SESSION['wpmig_message'] ); ?>
        <?php endif; ?>
        <?php if ( isset( $_SESSION['wpmig_error'] ) ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html( $_SESSION['wpmig_error'] ); ?></p>
            </div>
            <?php unset( $_SESSION['wpmig_error'] ); ?>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <input type="hidden" name="action" value="wpmig_export_content">
            <h2>Export Content</h2>
            <label>
                <input type="checkbox" name="content_type[]" value="post"> Posts
            </label>
            <label>
                <input type="checkbox" name="content_type[]" value="page"> Pages
            </label>
            <?php foreach ( $custom_post_types as $post_type ) : ?>
                <label>
                    <input type="checkbox" name="content_type[]" value="<?php echo esc_attr( $post_type ); ?>"> <?php echo esc_html( ucfirst( $post_type ) ); ?>
                </label>
            <?php endforeach; ?>
            <br><br>
            <label>
                Start Date: <input type="date" name="start_date">
            </label>
            <label>
                End Date: <input type="date" name="end_date">
            </label>
            <br><br>
            <input type="submit" name="export_content" value="Export" class="button button-primary">
        </form>
        <br><br>
        <h2>Import Content</h2>
        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="wpmig_import_content">
            <input type="file" name="import_file" accept=".json">
            <br><br>
            <input type="submit" name="import_content" value="Import" class="button button-primary">
        </form>
    </div>
    <?php
}
?>
