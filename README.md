# 다모앙 회원 메모 플러그인

이 기능은 다모앙(damoang.net)에 최적화 되었음.  
일반 그누보드에서는 동작하지 않음.

## 설치
1. `plugin` 폴더에 `da_member_memo` 폴더 복사
2. `plugin/da_member_memo/_extend/member_memo.extend.php` 파일을 `extend` 폴더에 복사(또는 심볼릭 링크 권장)
   ```ln -s plugin/da_member_memo/_extend/member_memo.extend.php extend/member_memo.extend.php```
4. 관리페이지 `환경설정 -> DB업그레이드` 메뉴에 접근하여 DB 테이블 생성
  - `{prefix}member_memo` 테이블이 생성되어야 함

## 템플릿 파일
- 'templates/memo_edit.html'
    - 메모 수정 모달 템플릿
- 'templates/sideview.php'
    - 메모 출력 템플릿
