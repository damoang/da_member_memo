<?php

include_once ('../../../common.php');

if (!$member['mb_id']) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');

    echo json_encode((object) [
        'error' => true,
        'message' => '회원만 이용할 수 있습니다'
    ], JSON_PRETTY_PRINT);

    exit;
}

if($_GET['token_only']) {
    $memo = [];
} else {
    $memberId = $_GET['member_id'];
    $memo = \DamoangMemberMemo::getMemo($memberId);
}

$memo['_token'] = \DamoangMemberMemo::csrfTokenCreate();

header('Content-Type: application/json');
echo json_encode($memo ?? (object) [], JSON_PRETTY_PRINT);
