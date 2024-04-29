<?php

include_once ('../../../common.php');

$memberId = $_GET['member_id'];
$memo = \DamoangMemberMemo::getMemo($memberId);

header('Content-Type: application/json');
echo json_encode($memo ?? (object) [], JSON_PRETTY_PRINT);
