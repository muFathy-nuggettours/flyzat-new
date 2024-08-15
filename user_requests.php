<div class="col-md-14 grid-item">
    <div class=module_contact_form>
        <?
        if ($_POST["token"] && $_POST["action"] == "request-form") {
            $result = mysqlQuery("SELECT * FROM user_requests WHERE user_id=" . $logged_user["id"] . " AND id=" . $get["method"] . " ORDER BY id DESC");
            $entry = mysqlFetch($result);
            if (isset($entry)) {
                $conversation = $entry["replies"];
                $conversationArray = $conversation ? json_decode($conversation, true) : [];
                $conversationArray[] = [
                    'sender' => 'user',
                    'message' => $_POST["message"],
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                $newConversationJson = json_encode($conversationArray);

                $query = "UPDATE user_requests SET replies = '$newConversationJson' WHERE id = " . $get["method"];
                mysqlQuery($query);
                $success = readLanguage(contact, success);
            }
        }
        ?>

    </div>
</div>

<?
if (isset($get["method"]) && $get["method"] != null) {
    $result = mysqlQuery("SELECT * FROM user_requests WHERE user_id=" . $logged_user["id"] . " AND id=" . $get["method"] . " ORDER BY id DESC");
    if (!mysqlNum($result)) { ?>
        <div class=page_container>
            <div class=message>
                <i class="fas fa-plane"></i>
                <b><?= readLanguage(reservation, no_reserves_added) ?></b>
                <small><?= readLanguage(reservation, no_reserves_small) ?>!</small>
            </div>
        </div>
    <? } else {
        while ($entry = mysqlFetch($result)) {
            $conversation = $entry["replies"];
            $conversationArray = $conversation ? json_decode($entry["replies"], true) : [];

            echo "<h3><strong>{$entry['content']}</strong></h3><br><br>";
            echo "<div class='conversation'>";
            foreach ($conversationArray as $entry) {
                if ($entry['sender'] == 'user') {
                    echo "<p class='user-message'><small><em>({$entry['timestamp']})</em></small> {$entry['message']}</p>";
                } else {
                    echo "<p class='admin-message'> <strong>FLYZAT:</strong> {$entry['message']} <small><em>({$entry['timestamp']})</em></small></p>";
                }
            }
            echo "</div>";
        }
    ?>
        <form method=post>
            <input type=hidden name=token value="<?= $token ?>">
            <input type=hidden name=action value="request-form">
            <input type=hidden name=id value="<?= $get["method"] ?>">
            <div class=form-item>
                <div class=input>
                    <textarea name="message" class="form-control" placeholder="Type Here Your Message..." required></textarea>
                </div>
            </div>
            <div class=submit_container><button type=button class=submit><?= readLanguage(contact, send) ?></button></div>
        </form>
    <?
    }
} else {
    $result = mysqlQuery("SELECT * FROM user_requests WHERE user_id=" . $logged_user["id"] . " ORDER BY id DESC");
    if (!mysqlNum($result)) { ?>
        <div class=page_container>
            <div class=message>
                <i class="fas fa-plane"></i>
                <b><?= readLanguage(reservation, no_reserves_added) ?></b>
                <small><?= readLanguage(reservation, no_reserves_small) ?>!</small>
            </div>
        </div>

    <? } else { ?>
        <table class="fancy square">
            <thead>
                <th>#</th>
                <th><?= readLanguage(request, type) ?></th>
                <th><?= readLanguage(request, date) ?></th>
                <th><?= readLanguage(request, code) ?></th>
                <th><?= readLanguage(request, channel) ?></th>
                <th width=100><?= readLanguage(request, Details) ?></th>
                <th><?= readLanguage(request, status) ?></th>
                <th><?= readLanguage(request, more) ?></th>
            </thead>
            <? while ($entry = mysqlFetch($result)) {
                $serial++;
            ?>
                <tr>
                    <td class=center-large><?= $serial ?></td>
                    <td class=center>
                        <?
                        switch ($entry["type"]) {
                            case "1":
                                echo  readLanguage(request, inquiry);
                                break;
                            case "2":
                                echo  readLanguage(request, edit_reservation);
                                break;
                            case "3":
                                echo  readLanguage(request, cancel_reservation);
                                break;
                            case "4":
                                echo  readLanguage(request, refund_reservation);
                                break;
                        }
                        ?>
                    </td>
                    <td class=center-large><?= dateLanguage("l, d M Y", strtotime($entry["created_at"])) ?></td>
                    <td class=center-large><?= $entry["code"] ?></td>
                    <td class=center-large><?= $entry["channel"] ?></td>
                    <td class=center-large><?= getFirstFourWords($entry["content"]) ?></td>
                    <td class=center-large>
                        <?
                        switch ($entry["status"]) {
                            case "1":
                                echo "<span class=\"label label-default label-block\">" . readLanguage(request, new_req) . "</span>";
                                break;
                            case "2":
                                echo "<span class=\"label label-primary label-block\">" . readLanguage(request, progress_req) . "</span>";
                                break;
                            case "3":
                                echo "<span class=\"label label-success label-block\">" . readLanguage(request, resolved_req) . "</span>";
                                break;
                        }
                        ?>
                    </td>
                    <td class=center-large>
                        <a class="btn btn-default btn-sm btn-block" href="user/user-requests/<?= $entry["id"] ?>"><span class="fa fa-eye"></span></a>
                    </td>
                </tr>
            <? } ?>
        </table>
<? }
} ?>