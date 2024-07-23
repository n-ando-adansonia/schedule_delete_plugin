<?php
/**
 * 
 */

 // ユーザーが自身のメディアのみを閲覧できるようにする
function restrict_media_library($query) {
    if (!is_admin()) {
        return;
    }
  
    $user = wp_get_current_user();
  
    if (!current_user_can('manage_options')) { // 管理者は制限を受けない
        if (isset($query->query['post_type']) && $query->query['post_type'] === 'attachment') {
            $query->set('author', $user->ID);
        }
    }
}
add_action('pre_get_posts', 'restrict_media_library');