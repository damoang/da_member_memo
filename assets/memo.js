if (!window.damoang) {
    window.damoang = {};
}

window.damoang.memo = function ($) {
    const plugin_url = window.g5_url + '/plugin/da_member_memo/';

    return {
        endpoints: {
            'memo': plugin_url + '/public/ajax.memo.php',
            'memo_update': plugin_url + '/public/ajax.memo_update.php',
        },
    };
}(window.jQuery);

document.addEventListener("DOMContentLoaded", function () {
    /** @var HTMLElement 메모 수정 모달 */
    const modalElement = document.getElementById('memberMemoEdit')
    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget
            const memberId = button.getAttribute('data-bs-member-id')
            const idAttr = modalElement.querySelector('[data-member-id]');
            if (idAttr) {
                idAttr.attributes['data-member-id'].value = memberId;
            }

            /** @var HTMLElement 모달 제목 */
            const modalTitle = modalElement.querySelector('.modal-title')
            /** @var HTMLElement 대상 회원ID */
            const fMemberId = modalElement.querySelector('[name=target_member_id]')
            /** @var HTMLElement 메모 */
            const fMemo = modalElement.querySelector('#da-modal__field-member-memo')
            /** @var HTMLElement 상세 내용 */
            const fMemoDetail = modalElement.querySelector('[name=memo_detail]')

            $.ajax(damoang.memo.endpoints.memo, {
                data: {
                    'member_id': memberId,
                }
            })
                .done(function (data) {
                    modalTitle.textContent = `회원 메모: ${data.target_member_id}`

                    // 가져온 데이터 채우기
                    fMemberId.value = data.target_member_id;
                    modalElement.querySelector('[name=color][value=' + data.color + ']').checked = true;
                    fMemo.value = data.memo;
                    fMemoDetail.textContent = data.memo_detail;
                });
        });

        // 모달에서 메모 저장
        modalElement.querySelector('form').addEventListener('submit', e => {
            e.preventDefault();

            $.post(
                damoang.memo.endpoints.memo_update,
                $(modalElement.querySelector('form')).serialize()
            )
                .then((result) => {
                    Swal.fire({
                        position: "bottom-end",
                        title: "저장했습니다.",
                        showConfirmButton: false,
                        timer: 3500
                    });
                });

            return false;
        });
    }
});