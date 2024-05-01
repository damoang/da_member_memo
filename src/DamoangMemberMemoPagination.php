<?php

declare(strict_types=1);

class DamoangMemberMemoPagination
{
    public $list_count = null;

    public $page = null;

    public $count = null;

    public $one_section = 10;

    public function getPaginationButton($pagination)
    {
        $output = '';

        foreach ($pagination as $p) {
            $btn = new stdClass();
            $btn->page = $p->page;
            switch ($p->type) {
                case 'onePage':
                    $output .= <<<EOD
                    <li class="page-first page-item">
                        <a class="page-link" href="/plugin/da_member_memo/public/memo_list.php?page=1" title="첫 페이지">
                            <i class="bi bi-chevron-double-left"></i><span class="visually-hidden">첫 페이지</span>
                        </a>
                    </li>
                    EOD;
                    break;
                case 'prevPage':
                    $output .= <<<EOD
                    <li class="page-prev page-item">
                        <a class="page-link" href="/plugin/da_member_memo/public/memo_list.php?page={$btn->page}" title="이전 페이지">
                            <i class="bi bi-chevron-left"></i><span class="visually-hidden">이전 페이지</span>
                        </a>
                    </li>
                    EOD;
                    break;
                case 'currentPage':
                    $output .= <<<EOD
                    <li class="page-item active">
                        <a class="page-link" href="/plugin/da_member_memo/public/memo_list.php?page={$btn->page}">
                            {$btn->page}<span class="visually-hidden">페이지 현재</span>
                        </a>
                    </li>
                    EOD;
                    break;
                case 'page':
                    $output .= <<<EOD
                    <li class="page-item">
                        <a class="page-link" href="/plugin/da_member_memo/public/memo_list.php?page={$btn->page}">
                            {$btn->page}<span class="visually-hidden">{$btn->page} 페이지</span>
                        </a>
                    </li>
                    EOD;
                    break;
                case 'nextPage':
                    $output .= <<<EOD
                    <li class="page-first page-item">
                        <a class="page-link" href="/plugin/da_member_memo/public/memo_list.php?page={$btn->page}" title="다음 페이지">
                            <i class="bi bi-chevron-right"></i><span class="visually-hidden">다음 페이지</span>
                        </a>
                    </li>
                    EOD;
                    break;
                case 'endPage':
                    $output .= <<<EOD
                    <li class="page-first page-item">
                        <a class="page-link" href="/plugin/da_member_memo/public/memo_list.php?page={$btn->page}" title="마지막 페이지">
                            <i class="bi bi-chevron-double-right"></i><span class="visually-hidden">마지막 페이지</span>
                        </a>
                    </li>
                    EOD;
                    break;
            }
        }

        return $output;
    }

    public function getPagination()
    {
        $list_count = $this->list_count;
        $page = $this->page;
        $count = $this->count;

        $total_page = ($count) ? ceil($count / $list_count) : 1;

        if ($page < 1 || ($total_page && $page > $total_page)) {
            return array();
        }

        $one_section = $this->one_section;
        $current_section = ceil($page / $one_section);
        $all_section = ceil($total_page / $one_section);

        $first_page = ($current_section * $one_section) - ($one_section - 1);

        $last_page = $current_section * $one_section;

        if ($current_section == $all_section)
            $last_page = $total_page;

        $prev_page = (($current_section - 1) * $one_section);
        $next_page = (($current_section + 1) * $one_section) - ($one_section - 1);

        $output = array();
        if ($page != 1) {
            $pagination = new stdClass();
            $pagination->type = 'onePage';
            $pagination->page = 1;

            array_push($output, $pagination);
        }

        if ($current_section != 1) {
            $pagination = new stdClass();
            $pagination->type = 'prevPage';
            $pagination->page = $prev_page;

            array_push($output, $pagination);
        }

        for ($i = $first_page; $i <= $last_page; $i++) {
            if ($i == $page) {
                $pagination = new stdClass();
                $pagination->type = 'currentPage';
                $pagination->page = $i;

                array_push($output, $pagination);
            } else {
                $pagination = new stdClass();
                $pagination->type = 'page';
                $pagination->page = $i;

                array_push($output, $pagination);
            }
        }

        if ($current_section != $all_section) {
            $pagination = new stdClass();
            $pagination->type = 'nextPage';
            $pagination->page = $next_page;

            array_push($output, $pagination);
        }

        if ($page != $total_page) {
            $pagination = new stdClass();
            $pagination->type = 'endPage';
            $pagination->page = $total_page;

            array_push($output, $pagination);
        }

        return $output;
    }
}
