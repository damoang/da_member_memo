<?php

declare(strict_types=1);

include_once ('../../../common.php');

header('Content-type: application/json');

if (!$member['mb_id']) {
    header('HTTP/1.1 403 Forbidden');

    echo json_encode((object) [
        'error' => true,
        'message' => '회원만 이용할 수 있습니다'
    ], JSON_PRETTY_PRINT);

    exit;
}

if (!\DamoangMemberMemo::csrfTokenCheck(trim($_POST['_token'] ?? ''))) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'error' => true,
        'message' => '잘못된 접근입니다',
    ], JSON_PRETTY_PRINT);
    exit;
}

$target_member_id = $_POST['target_member_id'];

$result = \DamoangMemberMemo::deleteMemo($target_member_id);

echo json_encode($result ?? [], JSON_PRETTY_PRINT);
