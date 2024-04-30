<?php

declare(strict_types=1);

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

if (!\DamoangMemberMemo::csrfTokenCheck(trim($_POST['_token'] ?? ''))) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'error' => true,
        'message' => '잘못된 접근입니다',
    ], JSON_PRETTY_PRINT);
    exit;
}

$target_member_id = $_POST['target_member_id'];

$memo = $_POST['memo'];
$memo_detail = $_POST['memo_detail'];
$color = $_POST['color'];

$targetMember = get_member($target_member_id);
if (empty($targetMember)) {
    return;
}

\DamoangMemberMemo::updateMemo(
    $targetMember['mb_id'],
    compact('memo', 'memo_detail', 'color')
);

header('Content-type: application/json');
echo json_encode([], JSON_PRETTY_PRINT);
