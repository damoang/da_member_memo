<?php
declare(strict_types=1);
?>
<span class="da-member-memo">
    <?php if ($printType & \DamoangMemberMemo::PRINT_ICON) { ?>
        <button class="btn btn-sm btn-link" style="--bs-btn-padding-x: .25rem;" data-bs-toggle="modal" data-bs-target="#memberMemoEdit" data-bs-member-id="<?= $targetMemberId ?>">
            <i class="bi bi-journal-text"></i>
            <span class="visually-hidden">메모</span>
        </button>
    <?php } // end icon ?>

    <?php
    if (($printType & \DamoangMemberMemo::PRINT_MEMO) && $memo['memo'] !== '') {
        $attrsPopover = [
            'data-bs-toggle' => 'popover',
            'data-bs-content' => $memo['memo'],
            'data-bs-trigger' => 'hover',
            'data-bs-html' => 'true',
            'data-bs-custom-class' => 'da-member-memo-popover',
        ];

        if ($memo['memo_detail'] !== '') {
            $attrsPopover['data-bs-title'] = $memo['memo'];
            $attrsPopover['data-bs-content'] = nl2br(url_auto_link($memo['memo_detail'] ?? null));
            $attrsPopover['data-bs-trigger'] = 'focus hover';
        }
        ?>
        <em class="badge rounded-pill text-truncate align-middle da-member-memo__memo da-memo-color--<?= $memo['color'] ?>" tabindex="0" role="button" <?= \DamoangMemberMemo::attrs($attrsPopover) ?>>
            <?= $memo['memo'] ?>
        </em>
    <?php } // end memo ?>
</span>
