<?php
/*
Plugin Name: Scheduled Media Deletion
Description: Automatically delete media files on a specified date.
Version: 1.0
Author: Your Name
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// スケジュールイベントを設定
function smd_schedule_event() {
    if ( ! wp_next_scheduled( 'smd_daily_event' ) ) {
        wp_schedule_event( time(), 'daily', 'smd_daily_event' );
        error_log("Scheduled smd_daily_event");
    } else {
        error_log("smd_daily_event already scheduled");
    }
}
add_action( 'init', 'smd_schedule_event' );

// スケジュールイベントのクリア
function smd_clear_scheduled_event() {
    wp_clear_scheduled_hook( 'smd_daily_event' );
    error_log("Cleared smd_daily_event");
}
register_deactivation_hook( __FILE__, 'smd_clear_scheduled_event' );

// メディアの削除を実行
function smd_delete_scheduled_media() {
    $args = array(
        'post_type'      => 'attachment',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_smd_deletion_date',
                'value'   => current_time( 'Y-m-d' ),
                'compare' => '<=',
                'type'    => 'DATE'
            )
        )
    );

    $query = new WP_Query( $args );
    // debuglog にqueryを出力
    error_log( print_r( $query, true ) );
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            wp_delete_attachment( get_the_ID(), true );
            error_log( "Deleted attachment with ID " . get_the_ID() );
        }
        wp_reset_postdata();
    } else {
        error_log( "No attachments found for deletion on " . current_time( 'Y-m-d' ) );
    }
}
add_action( 'smd_daily_event', 'smd_delete_scheduled_media' );

// メタボックスの追加
function smd_add_meta_boxes() {
    add_meta_box( 'smd_meta_box', 'Scheduled Deletion Date', 'smd_meta_box_callback', 'attachment', 'side' );
}
add_action( 'add_meta_boxes', 'smd_add_meta_boxes' );

// メタボックスのコールバック
function smd_meta_box_callback( $post ) {
    wp_nonce_field( 'smd_save_meta_box_data', 'smd_meta_box_nonce' );
    $value = get_post_meta( $post->ID, '_smd_deletion_date', true );
    
    echo '<label for="smd_deletion_date">Deletion Date:</label>';
    echo '<input type="date" id="smd_deletion_date" name="smd_deletion_date" value="' . esc_attr( $value ) . '" />';
}

// メタデータの保存
function smd_save_meta_box_data( $post_id ) {
    error_log( "###### smd_save_meta_box_data #######" );
    error_log( "Attempting to save meta box data for post ID $post_id" );

    if ( ! isset( $_POST['smd_meta_box_nonce'] ) ) {
        error_log( "Nonce not set for post ID $post_id" );
        return;
    }

    if ( ! wp_verify_nonce( $_POST['smd_meta_box_nonce'], 'smd_save_meta_box_data' ) ) {
        error_log( "Nonce verification failed for post ID $post_id" );
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        error_log( "Autosave detected for post ID $post_id" );
        return;
    }

    if ( 'attachment' != get_post_type( $post_id ) ) {
        error_log( "Post type is not attachment for post ID $post_id" );
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        error_log( "Current user cannot edit post ID $post_id" );
        return;
    }

    if ( isset( $_POST['smd_deletion_date'] ) ) {
        $date = sanitize_text_field( $_POST['smd_deletion_date'] );
        update_post_meta( $post_id, '_smd_deletion_date', $date );
        error_log( "Saving deletion date for post ID $post_id: $date" );
    } else {
        error_log( "Deletion date not set for post ID $post_id" );
    }
}
add_action( 'save_post', 'smd_save_meta_box_data' );


// 手動トリガー用の関数
function smd_manual_trigger() {
    if ( isset( $_GET['smd_manual_trigger'] ) && current_user_can( 'manage_options' ) ) {
        do_action( 'smd_daily_event' );
        error_log("Manually triggered smd_daily_event");
        echo "Manually triggered smd_daily_event";
        exit;
    }
}
add_action( 'init', 'smd_manual_trigger' );

// Cronイベントをログに記録する関数
function smd_log_cron_events() {
    if ( current_user_can( 'manage_options' ) && isset( $_GET['smd_log_cron_events'] ) ) {
        $cron = _get_cron_array();
        error_log(print_r($cron, true));
        echo "Logged cron events to error log";
        exit;
    }
}
add_action( 'init', 'smd_log_cron_events' );


// $media_id = 8; // テストするメディアのID
// update_post_meta( $media_id, '_smd_deletion_date', '2024-07-19' ); // 過去の日付を設定

// $date = get_post_meta( $media_id, '_smd_deletion_date', true );
// error_log( "The saved deletion date for media ID {$media_id} is: {$date}" );

?>
