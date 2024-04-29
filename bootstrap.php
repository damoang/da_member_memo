<?php

declare(strict_types=1);

if (!defined('_GNUBOARD_')) {
    exit;
}

define('DA_PLUGIN_MEMO_VERSION', 10000);
define('DA_PLUGIN_MEMO_PATH', __DIR__);

include_once DA_PLUGIN_MEMO_PATH . '/src/DamoangMemberMemo.php';

// DB 마이그레이션
add_replace('admin_dbupgrade', function ($is_check = false) {
    $tableName = \DamoangMemberMemo::tableName();

    // 테이블 생성
    sql_query("CREATE TABLE IF NOT EXISTS `{$tableName}` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `member_uid` int(11) DEFAULT NULL,
        `member_id` varchar(20) CHARACTER SET ascii NOT NULL,
        `target_member_uid` int(11) DEFAULT NULL,
        `target_member_id` varchar(20) CHARACTER SET ascii NOT NULL,
        `color` varchar(20) CHARACTER SET ascii DEFAULT NULL,
        `memo` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
        `memo_detail` text COLLATE utf8mb4_unicode_ci,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_keys` (`member_id`,`target_member_id`)
    );", true);

    g5_set_cache('da-installed-member-memo', \DA_PLUGIN_MEMO_VERSION);

    return $is_check;
});

if (!\DamoangMemberMemo::installed()) {
    return;
}

add_stylesheet('<link rel="stylesheet" href="' . G5_PLUGIN_URL . '/da_member_memo/assets/memo.css" />');
add_javascript('<script src="' . G5_PLUGIN_URL . '/da_member_memo/assets/memo.js" data-cfasync="false"></script>');

/**
 * 글 목록 배열에 `da_member_memo` 항목에 메모를 출력하는 HTML을 추가
 */
add_replace('da_board_list', function ($list = []) {
    foreach ($list as &$item) {
        if (empty ($item['mb_id'])) {
            continue;
        }

        // 목록용 템플릿
        $item['da_member_memo'] = \DamoangMemberMemo::printMemo($item['mb_id'], \DamoangMemberMemo::PRINT_PRESET_LIST);
    }

    return $list;
});

add_replace('da_board_view', function ($view = []) {
    // 비회원 글이면 패스
    if (empty ($view['mb_id'])) {
        return $view;
    }

    $view['da_member_memo'] = \DamoangMemberMemo::printMemo($view['mb_id'], \DamoangMemberMemo::PRINT_PRESET_VIEW);

    return $view;
});

add_replace('da_comment_list', function ($list = []) {
    foreach ($list as &$item) {
        if (empty ($item['mb_id'])) {
            continue;
        }

        // 목록용 템플릿
        $item['da_member_memo'] = \DamoangMemberMemo::printMemo($item['mb_id'], \DamoangMemberMemo::PRINT_PRESET_VIEW);
    }

    return $list;
});

// 회원 사이드뷰 메뉴
add_replace('member_sideview_items', function ($sideview, $data = []) {
    global $member;

    if (empty ($data['mb_id'] ?? '') || $data['mb_id'] === $member['mb_id']) {
        return $sideview;
    }

    // 메모
    $sideview['menus']['member_memo'] = '<a href="#dummy-memo" data-bs-toggle="modal" data-bs-target="#memberMemoEdit" data-bs-member-id="' . $data['mb_id'] . '">메모</a>';

    // 차단
    if (!in_array($data['mb_id'], explode(',', $member['as_chadan'] ?? ''))) {
        $sideview['menus']['member_chadan'] = '<a href="#dummy-chadan" onclick="na_chadan(\'' . $data['mb_id'] . '\');">차단하기</a>';
    } else {
        $sideview['menus']['member_chadan'] = '<a href="#dummy-chadan" onclick="na_chadan(\'' . $data['mb_id'] . '\');">차단 해제</a>';
    }

    return $sideview;
}, G5_HOOK_DEFAULT_PRIORITY, 2);


add_replace('html_process_buffer', function ($html = '') {
    $modal = file_get_contents(DA_PLUGIN_MEMO_PATH . '/templates/memo_edit.html');

    $html = \DamoangMemberMemo::replaceLast('</body>', $modal . '</body>', $html);

    return $html;
});
