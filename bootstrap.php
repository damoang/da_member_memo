<?php

declare(strict_types=1);

if (!defined('_GNUBOARD_')) {
    exit;
}

define('DA_PLUGIN_MEMO_VERSION', 10000);
define('DA_PLUGIN_MEMO_PATH', __DIR__);
define('DA_PLUGIN_MEMO_DIR', basename(DA_PLUGIN_MEMO_PATH));
define('DA_PLUGIN_MEMO_URL', G5_PLUGIN_URL . '/' . DA_PLUGIN_MEMO_DIR);

include_once DA_PLUGIN_MEMO_PATH . '/src/DamoangMemberMemo.php';

if (
    /* 비로그인 시 모든 동작 제한 */
    !$member['mb_id']
    /* 레벨1은 사용 제한 */
    || intval($member['mb_level']) < 2
) {
    return;
}

// DB 마이그레이션
add_replace('admin_dbupgrade', function ($is_check = false) {
    $tableName = \DamoangMemberMemo::tableName();

    // 테이블 생성
    sql_query("CREATE TABLE IF NOT EXISTS `{$tableName}` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        -- member_uid: 메모를 한 회원의 `mb_no`
        -- 사용되지 않으나 마이그레이션을 대비하여 데이터 축적 중
        `member_uid` int(11) DEFAULT NULL,
        `member_id` varchar(20) CHARACTER SET ascii NOT NULL,
        -- target_member_uid: 메모 대상 회원의 `mb_no`
        -- 사용되지 않으나 마이그레이션을 대비하여 데이터 축적 중
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

    // DB 마이그레이션 결과를 캐시에 저장
    // 캐시는 삭제될 수 있으므로, 마이그레이션 코드가 반복 실행될 수 있으므로 주의해야 함
    g5_set_cache('da-installed-member-memo', \DA_PLUGIN_MEMO_VERSION);

    return $is_check;
}, \G5_HOOK_DEFAULT_PRIORITY, 1);


// 설치, 마이그레이션이 완료되지 았았다면 동작을 멈춤
if (!\DamoangMemberMemo::installed()) {
    return;
}

// 관리페이지에서는 동작 제한
if (defined('G5_IS_ADMIN') && \G5_IS_ADMIN) {
    return;
}

// assets
add_stylesheet('<link rel="stylesheet" href="' . \DamoangMemberMemo::asset(DA_PLUGIN_MEMO_PATH . '/assets/memo.css') . '" />');
add_javascript('<script src="' . \DamoangMemberMemo::asset(DA_PLUGIN_MEMO_PATH . '/assets/memo.js') . '"></script>');

/**
 * 글 목록 배열에 `da_member_memo` 메모를 출력하는 HTML을 추가
 */
add_replace('da_board_list', function ($list = []) {
    foreach ($list as &$item) {
        if (empty ($item['mb_id'])) {
            continue;
        }

        $item['da_member_memo'] = \DamoangMemberMemo::printMemo(
            $item['mb_id'],
            /* 목록 용 템플릿 */
            \DamoangMemberMemo::PRINT_PRESET_LIST
        );
    }

    return $list;
}, \G5_HOOK_DEFAULT_PRIORITY, 1);


/**
 * 글 보기에 메모를 출력하는 HTML을 추가
 */
add_replace('da_board_view', function ($view = []) {
    // 비회원 글이면 패스
    if (empty ($view['mb_id'])) {
        return $view;
    }

    $view['da_member_memo'] = \DamoangMemberMemo::printMemo(
        $view['mb_id'],
        /* 글 보기용 템플릿 */
        \DamoangMemberMemo::PRINT_PRESET_VIEW
    );

    return $view;
}, \G5_HOOK_DEFAULT_PRIORITY, 1);

/**
 * 댓글 목록에 메모를 출력하는 HTML을 추가
 */
add_replace('da_comment_list', function ($list = []) {
    foreach ($list as &$item) {
        if (empty ($item['mb_id'])) {
            continue;
        }

        $item['da_member_memo'] = \DamoangMemberMemo::printMemo(
            $item['mb_id'],
            /* 글 보기용 템플릿 */
            \DamoangMemberMemo::PRINT_PRESET_VIEW
        );
    }

    return $list;
}, \G5_HOOK_DEFAULT_PRIORITY, 1);


/**
 * 회원 사이드뷰 메뉴
 *
 * 메모, 차단하기 메뉴를 출력
 */
add_replace('member_sideview_items', function ($sideview, $data = []) {
    global $member;

    if (empty ($data['mb_id'] ?? '')) {
        return $sideview;
    }

    // 메모
    if ($data['mb_id'] === $member['mb_id']) {
        // 본인 계정의 메뉴일 때는 '메모 목록'페이지로 링크
        $sideview['menus']['member_memo'] = '<a href="' . DA_PLUGIN_MEMO_URL . '/public/memo_list.php" >메모 관리</a>';
    } else {
        $sideview['menus']['member_memo'] = '<a href="#dummy-memo" data-bs-toggle="modal" data-bs-target="#memberMemoEdit" data-bs-member-id="' . $data['mb_id'] . '">메모</a>';
    }

    // 차단
    // 이건 나리야 빌더에서 제공하는 기능.
    if ($data['mb_id'] !== $member['mb_id']) {
        if (!in_array($data['mb_id'], explode(',', $member['as_chadan'] ?? ''))) {
            $sideview['menus']['member_chadan'] = '<a href="#dummy-chadan" onclick="na_chadan(\'' . $data['mb_id'] . '\');">차단하기</a>';
        } else {
            // TODO: 차단해제 기능이 없어!
            // $sideview['menus']['member_chadan'] = '<a href="#dummy-chadan" onclick="na_chadan(\'' . $data['mb_id'] . '\');">차단 해제</a>';
        }
    }

    return $sideview;
}, \G5_HOOK_DEFAULT_PRIORITY, 2);


/**
 * 메모 편집 등 UI 출력
 */
add_event('tail_sub', function () {
    echo file_get_contents(DA_PLUGIN_MEMO_PATH . '/templates/memo_edit.html');
});
