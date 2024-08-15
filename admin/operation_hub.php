<? include "system/_handler.php";

$mysqltable = "user_requests";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);


if ($post["token"] && $edit) {
    // $exists = mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE id=$edit"));
    // if ($exists) {
    //     $query = "UPDATE $mysqltable SET
    // 		status='{$post['status']}'
    // 	WHERE id=$edit";
    //     mysqlQuery($query);
    // }
    $result = mysqlQuery("SELECT * FROM user_requests WHERE id=" . $edit);
    $entry = mysqlFetch($result);

    if (isset($entry)) {
        if (isset($post["replies"]) && $post["replies"] != "") {
            $conversation = $entry["replies"];
            $conversationArray = $conversation ? json_decode($conversation, true) : [];
            $conversationArray[] = [
                'sender' => 'FLYZAT',
                'message' => $post["replies"],
                'timestamp' => date('Y-m-d H:i:s')
            ];
            $newConversationJson = json_encode($conversationArray);
        } else {
            $newConversationJson = $entry["replies"];
        }

        $query = "UPDATE user_requests SET replies = '$newConversationJson', status='{$post['status']}' WHERE id = " . $edit;
        mysqlQuery($query);
        $success = readLanguage(records, updated);
    }
}

//Read and Set Operation
if ($edit) {
    $entry = getID($edit, $mysqltable);
    if (!$entry) {
        $error = readLanguage(records, unavailable);
        $edit = null;
    }
}
if ($edit) {
    $button = readLanguage(records, update);
    $action = "$base_name.php" . rebuildQueryParameters(array("delete", "token"));
}
if ($success) {
    $message = "<div class='alert alert-success'>$success</div>";
}
if ($error) {
    $message = "<div class='alert alert-danger'>$error</div>";
}


include "_header.php"; ?>


<script src="../plugins/fixed-data.js?v=<?= $system_settings["system_version"] ?>"></script>

<div class=title><?= getPageTitle($base_name) ?></div>
<?= $message ?>

<?
$query = mysqlQuery("SELECT status, COUNT(id) AS status_count FROM user_requests GROUP BY status");
$stat = [];
while ($rows = mysqlFetch($query)) {

    $stat[$rows["status"]] = $rows["status_count"];
}

?>
<div class="container">
    <div class="row">
        <div style="width: 33.33%; padding: 10px">
            <div class="panel panel-primary text-center">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-plus-circle" aria-hidden="true"></i> طلبات جديدة
                    </h3>
                </div>
                <div class="panel-body">
                    <p class="lead" style="font-size: 2em;"><?= $stat[0] > 0 ? $stat[0] : 0  ?></p>
                </div>
            </div>
        </div>

        <div style="width: 33.33%; padding: 10px">
            <div class="panel panel-warning text-center">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-spinner" aria-hidden="true"></i> طلبات قيد التنفيذ
                    </h3>
                </div>
                <div class="panel-body">
                    <p class="lead" style="font-size: 2em;"><?= $stat[1] > 0 ? $stat[1] : 0  ?></p>
                </div>
            </div>
        </div>

        <div style="width: 33.33%; padding: 10px">
            <div class="panel panel-success text-center">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-check-circle" aria-hidden="true"></i> تم التنفيذ
                    </h3>
                </div>
                <div class="panel-body">
                    <p class="lead" style="font-size: 2em;"><?= $stat[2] > 0 ? $stat[2] : 0  ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FontAwesome Link (if not already included) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<script>
    function showCurrency(currency = null) {
        $("ul.inline_input li").hide();

        if (!currency) {
            $("ul.inline_input").hide();
            $(".empty").show();

        } else {
            $("ul.inline_input").show();
            $(".empty").hide();
            $("ul.inline_input li[data-fixed-currency='" + currency + "']").show();
        }
    }

    <? if ($edit) { ?>showCurrency("<?= getID($entry["user_id"], "users_database", "user_currency") ?>");
    <? } ?>
</script>
<? if ($edit) { ?>
    <hr>
    <?
    $conversation = $entry["replies"];
    $conversationArray = $conversation ? json_decode($entry["replies"], true) : [];

    echo "<h6><strong>{$entry['content']}</strong></h6><br>";
    foreach ($conversationArray as $entry) {
        if ($entry['sender'] == 'user') {
            echo "<p class='user-message'><strong>USER: </strong> {$entry['message']} <small><em>({$entry['timestamp']})</em></small></p>";
        } else {
            echo "<p class='admin-message'> <strong>FLYZAT: </strong> {$entry['message']} <small><em>({$entry['timestamp']})</em></small></p>";
        }
    }
    ?>
    <form method=post enctype="multipart/form-data" action="<?= $action ?>">
        <input type=hidden name=token value="<?= $token ?>">
        <table class=data_table>
            <tr>
                <td class=title>حالة الطلب: <i class=requ></i></td>
                <td>
                    <select name="status" required>
                        <option value="0" <?= $entry["status"] == 0 ? 'selected' : '' ?>>طلب جديد</option>
                        <option value="1" <?= $entry["status"] == 1 ? 'selected' : '' ?>>قيد التنفيذ</option>
                        <option value="2" <?= $entry["status"] == 2 ? 'selected' : '' ?>>تم التنفيذ</option>
                    </select>
                </td>

                <td class=title>رد: </td>
                <td><input type=text name=replies></td>
            </tr>
        </table>
        <div class=submit_container><input type=button class=submit value="<?= $button ?>"></div>
    </form>
    <br>
    <hr>
<? } ?>
<div class=crud_separator></div>

<?
$crud_data["buttons"] = array(false, true, true, true, false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
    array("user_id", "ملف المستخدم", "160px", "center", "getCustomData('name','users_database','id','%s','_view_user')", false, true),
    array("status", "حالة الطلب", "120px", "center", "returnRequestStatusLabel('%s')", true, false),
    array("channel", "مصدر الحجز", "120px", "center", null, false, false),
    array("content", "تفاصيل الطلب", "250px", "center", "getFirstFourWords('%s')", false, false),
    array("code", "رقم الحجز", "120px", "center", null, false, false),
    array("created_at", "تاريخ الطلب", "250px", "center", "dateLanguage('l, d M Y h:i A',strtotime('%s'))", false, false),
    array("type", "نوع الطلب", "100px", "center", "returnTypeLabel('%s')", false, false),
    array("id", "رقم الطلب", "100px", "center", null, false, false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>