<?php

declare(strict_types=1);
return;
include_once ('../../../common.php');

$target_member_id = $_POST['target_member_id'];

\DamoangMemberMemo::deleteMemo($targetMember['mb_id']);

header('Content-type: application/json');
echo json_encode(['success' => true], JSON_PRETTY_PRINT);