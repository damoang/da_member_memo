if (!window.damoang) {
  window.damoang = {};
}

window.damoang.memo = function ($) {
  const plugin_url = window.g5_url + '/plugin/da_member_memo';

  return {
    endpoints: {
      'memo': plugin_url + '/public/ajax.memo.php',
      'memo_update': plugin_url + '/public/ajax.memo_update.php',
      'memo_delete': plugin_url + '/public/ajax.memo_delete.php',
    },
  };
}(window.jQuery);

document.addEventListener("DOMContentLoaded", function () {
  /** @var HTMLElement 메모 수정 모달 */
  const modalElement = document.getElementById('memberMemoEdit')
  if (modalElement) {
    /** @var string? CSRF 토큰 */
    let token = null;

    modalElement.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget
      const memberId = button.getAttribute('data-bs-member-id')

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
          modalTitle.textContent = `회원 메모: ${data.target_member_nickname}`
          token = data._token;

          // 가져온 데이터 채우기
          modalElement.querySelector('[name=_token]').value = token;
          fMemberId.value = data.target_member_id;
          modalElement.querySelector('[name=color][value=' + data.color + ']').checked = true;
          fMemo.value = data.memo;
          fMemoDetail.value = data.memo_detail;
        })
        .fail(() => {
          alert('메모 정보를 가져올 수 없음');
        });
    });

    // 모달에서 메모 저장
    modalElement.querySelector('form').addEventListener('submit', e => {
      e.preventDefault();

      $.post(
        damoang.memo.endpoints.memo_update,
        $(modalElement.querySelector('form')).serialize(),
        (result) => {
          window.location.reload();
        },
        'text'
      )
        .fail((error) => {
          Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "저장에 실패했습니다.",
            showConfirmButton: false,
            timer: 3500
          });
        });
      return false;
    });

    // 모달에서 메모 삭제
    modalElement.querySelector('.js-memo-delete').addEventListener('click', e => {
      const target_member_id = modalElement.querySelector('[name=target_member_id]').value ?? '';

      $.ajax({
        type: "POST",
        url: damoang.memo.endpoints.memo_delete,
        data: {
          _token: token,
          target_member_id,
        }
      })
        .done(() => {
          window.location.reload();
        })
        .fail((jqXHR) => {
          Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: jqXHR.responseJSON.message,
            showConfirmButton: false,
            timer: 3500
          });
        });
    });
  }

  document.querySelectorAll('.js-memo-delete-from-list').forEach((item) => {
    item.addEventListener('click', e => {
      const target_member_id = e.target.value;

      $.ajax(damoang.memo.endpoints.memo, {
        data: {
          'token_only': true,
        }
      })
        .done(function (data) {

          $.ajax({
            type: "POST",
            url: damoang.memo.endpoints.memo_delete,
            data: {
              _token: data._token,
              target_member_id,
            }
          })
            .done(() => {
              window.location.reload();
            })
            .fail((jqXHR) => {
              console.log(jqXHR);
              alert('메모 삭제 오류입니다.');
            });
        })
        .fail(() => {
          alert('토큰 오류 입니다.');
        });
    });
  });
});
