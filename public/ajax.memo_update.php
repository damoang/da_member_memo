<?php

declare(strict_types=1);

include_once ('../../../common.php');

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