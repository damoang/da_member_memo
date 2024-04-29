<?php
include_once '../../../common.php';
include_once DA_PLUGIN_MEMO_PATH . '/templates/_head.php';

// 메모 목록

$list = \DamoangMemberMemo::getMemoList();

// HTML 템플릿
include_once DA_PLUGIN_MEMO_PATH . '/templates/memo_list.php';

include_once DA_PLUGIN_MEMO_PATH . '/templates/_tail.php';
