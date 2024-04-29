<h2>메모 목록</h2>

<table>
    <thead>
        <tr>
            <th>아이디</th>
            <th>메모 내용</th>
        </tr>
    </thead>

    <tbody>
        <!-- 메모 목록 -->
        <?php while ($memo = sql_fetch_array($list)): ?>
            <tr>
                <td><?= $memo['target_member_id'] ?></td>
                <td><?= $memo['memo'] ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
