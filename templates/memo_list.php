<?php

declare(strict_types=1);

if (!defined('_GNUBOARD_')) {
    exit;
}

include_once DA_PLUGIN_MEMO_PATH . '/src/DamoangMemberMemoPagination.php';

$list_cnt = \DamoangMemberMemo::getMemoCount();
?>

<style>
    #bo_list .wr-no,
    #bo_list .wr-date,
    #bo_list .wr-num,
    #bo_list .wr-name {
        font-size: 13px;
    }
</style>
<div id="bo_list_wrap">
    <form name="fboardlist" id="fboardlist" method="post">
        <section id="bo_list" class="line-top mb-3">
            <ul class="list-group list-group-flush border-bottom">
                <li class="list-group-item d-none d-md-block hd-wrap">
                    <div class="d-flex flex-md-row align-items-md-center gap-1 fw-bold">
                        <div class="col-md-2 text-center">
                            아이디
                        </div>
                        <div class="flex-grow-1">
                            메모 내용
                        </div>
                    </div>
                </li>
                <?php while ($memo = sql_fetch_array($list)): ?>
                    <li class="list-group-item">

                        <div class="d-flex align-items-center gap-1">
                            <div class="flex-grow-1">
                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-1">
                                    <div class="col-md-2 text-md-center wr-name">
                                        <?php
                                        // 회원 정보 구하기
                                        $target_member = get_member($memo['target_member_id']);

                                        echo get_sideview($target_member['mb_id'], $target_member['mb_nick']);
                                        ?>
                                    </div>
                                    <div class="flex-fill align-self-md-center">
                                        <span data-bs-toggle="popover" data-bs-content="<?php echo nl2br(na_htmlspecialchars($memo['memo_detail'], true)) ?>" data-bs-trigger="focus hover" data-bs-html="true"><?= $memo['memo'] ?></span>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#memberMemoEdit" data-bs-member-id="<?=$memo['target_member_id']?>">
                                            수정
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger js-memo-delete-from-list" value="<?=$memo['target_member_id']?>">
                                            삭제
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </li>
                <?php endwhile; ?>
                <?php if ($list_cnt === 0) { ?>
                    <li class="list-group-item text-center py-5">
                        메모가 없습니다.
                    </li>
                <?php } ?>
            </ul>
        </section>
    </form>

    <?php
    $pg = new DamoangMemberMemoPagination();
    $pg->list_count = $limitCnt;
    $pg->page = (int) $page + 1;
    $pg->count = $list_cnt;
    $pg->one_section = G5_IS_MOBILE ? (int) $config['cf_mobile_pages'] : (int) $config['cf_write_pages'];

    $pagination = $pg->getPagination();
    $page_buttons = $pg->getPaginationButton($pagination);
    ?>
    <ul class="pagination pagination-sm justify-content-center">
        <?= $page_buttons ?>
    </ul>
</div>
