<?php

class DamoangMemberMemo
{
    /**
     * 메모 수정 아이콘/버튼
     * 
     * @var int
     */
    const PRINT_ICON = 1;
    /**
     * 메모 내용
     * 
     * @var int
     */
    const PRINT_MEMO = 2;

    /**
     * 목록에서 사용하는 프리셋
     * 
     * @var int
     */
    const PRINT_PRESET_LIST = 2;
    /**
     * 보기 페이지에서 사용하는 프리셋
     * 
     * @var int
     */
    const PRINT_PRESET_VIEW = 3;

    /**
     * 회원 메모 테이블의 `prefix`를 포함한 이름을 반환
     */
    public static function tableName(): string
    {
        return \G5_TABLE_PREFIX . 'member_memo';
    }

    /**
     * 그누보드의 mysql connection
     * 
     * @return \mysqli
     */
    public static function db()
    {
        return $GLOBALS['g5']['connect_db'];
    }

    /**
     * 회원의 메모를 출력
     * 
     * @param string $targetMemberId 메모를 가져 올 회원의 ID
     * @param int $printType 편집 버튼, 메모 내용 등 출력할 UI 요소
     */
    public static function printMemo($targetMemberId, $printType = self::PRINT_MEMO): string
    {
        global $member;

        if (
            !empty($member['mb_id'])
            && $member['mb_id'] === $targetMemberId
        ) {
            return '';
        }

        $memo = static::getMemo($targetMemberId);

        ob_start();
        include DA_PLUGIN_MEMO_PATH . '/templates/sideview.php';
        $addtionalHtml = ob_get_clean();

        return $addtionalHtml;
    }

    /**
     * 메모 내용을 insert, update
     * 
     * @param string $targetMemberId 대상 회원ID
     * @param array $data
     */
    public static function updateMemo(string $targetmemberId, array $data = [])
    {
        global $member;

        $targetMember = get_member($targetmemberId);

        $data['member_uid'] = $member['mb_no'];
        $data['member_id'] = $member['mb_id'];
        $data['target_member_uid'] = $targetMember['mb_no'];
        $data['target_member_id'] = $targetMember['mb_id'];

        $tableName = self::tableName();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $stmt = self::db()->prepare("INSERT INTO `{$tableName}`
            (member_uid, member_id, target_member_uid, target_member_id, memo, memo_detail, color)
            VALUES
            (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                `memo` = VALUES(memo),
                `memo_detail` = VALUES(memo_detail),
                `color` = VALUES(color),
                `updated_at` = CURRENT_TIMESTAMP()
        ");

        $stmt->bind_param(
            'isissss',
            $data['member_uid'],
            $data['member_id'],
            $data['target_member_uid'],
            $data['target_member_id'],
            $data['memo'],
            $data['memo_detail'],
            $data['color'],
        );
        $stmt->execute();

        // 뭘 리턴해줄까?
    }

    /**
     * 대상 회원에게 메모한 메모 정보 반환
     * 
     * @param array|string $targetMemberId
     * @return ?array{
     *      'id': int,
     *      'member_id': string,
     *      'target_member_id': string,
     *      'color': string,
     *      'background_color': string,
     *      'foreground_color': string,
     * }[]
     */
    public static function getMemo($targetMemberId)
    {
        global $member;

        static $cache = [];

        // 로그인 상태가 아니면 반환할 데이터 없음
        if (!$member['mb_id']) {
            return;
        }

        if (empty($targetMemberId)) {
            return;
        }

        $cacheKey = "{$member['mb_id']}-{$targetMemberId}";
        if ($cache[$cacheKey]) {
            return $cache[$cacheKey];
        }

        $memo = sql_fetch("SELECT * FROM `g5_member_memo`
            WHERE
                `member_id` = '{$member['mb_id']}'
                AND `target_member_id` = '{$targetMemberId}'
        ");

        $cache[$cacheKey] = self::arrangeMemo($targetMemberId, $memo ?? []);

        return $cache[$cacheKey];
    }

    protected static function arrangeMemo($targetMemberId, $memo): array
    {
        $memo['target_member_id'] = $targetMemberId;
        $memo['memo'] = $memo['memo'] ?? '';
        $memo['memo_detail'] = $memo['memo_detail'] ?? '';
        $memo['color'] = $memo['color'] ?? 'yellow';

        return $memo;
    }

    public static function getMemoList()
    {
        global $member;

        if (empty($member['mb_id'])) {
            return [];
        }

        $tableName = self::tableName();
        $memberId = $member['mb_id'];

        $result = sql_query("SELECT * FROM `{$tableName}`
            WHERE
                `member_id` = '{$memberId}'
        ");

        return $result;
    }

    public static function deleteMemo($targetMemberId)
    {
        global $member;

        $memberId = $member['mb_id'];

        $tableName = self::tableName();

        $result = sql_query("DELETE FROM `{$tableName}`
            WHERE
                `member_id` = '{$memberId}'
                AND `target_member_id` = '{$targetMemberId}'
        ;");
    }

    public static function attr(string $name, ?string $value = ''): string
    {
        return (!(string) $value) ? '' : $name . '="' . htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
    }

    public static function attrs(array $attrs = []): string
    {
        foreach ($attrs as $name => $value) {
            if ($name === 'class' && is_array($value)) {
                $value = implode(' ', $value);
            }

            $output[] = static::attr($name, $value);
        }
        $output[] = '';

        return implode(' ', $output);
    }

    public static function installed(): bool
    {
        $cacheKey = 'da-installed-member-memo';

        return g5_get_cache($cacheKey) >= \DA_PLUGIN_MEMO_VERSION;
    }

    public static function replaceLast(string $search, string $replace, string $subject): string
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    public static function autolinkMemoDetail($text): string
    {
        return url_auto_link($text);
    }
}