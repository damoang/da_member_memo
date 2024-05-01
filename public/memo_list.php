<?php
include_once '../../../common.php';

$g5['title'] = '메모 목록';

include_once DA_PLUGIN_MEMO_PATH . '/templates/_head.php';

// 메모 목록
if ((int) $page > 0) {
    $page = (int) $page - 1;
}
$limitCnt = 20;
$limitOffset = $limitCnt * (int) $page;
$list = \DamoangMemberMemo::getMemoList($limitOffset, $limitCnt);

// 메모 목록 템플릿
include_once DA_PLUGIN_MEMO_PATH . '/templates/memo_list.php';

include_once DA_PLUGIN_MEMO_PATH . '/templates/_tail.php';
