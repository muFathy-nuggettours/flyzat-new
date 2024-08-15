<? include "system/_handler.php";

requireLogin(true);

include "system/header.php";
include "website/section_header.php"; ?>

<script src="plugins/croppie.min.js?v=<?= $system_settings["system_version"] ?>"></script>
<link href="plugins/croppie.min.css?v=<?= $system_settings["system_version"] ?>" rel="stylesheet">

<!-- Start Tags -->
<div class="row grid-container-15">

    <!-- Main Content -->
    <div class="col-md-14 grid-item">
        <div class=module_contact_form>
            <?
            if ($post["token"] && $post["action"] == "request-form") {
                //Validate reCAPTCHA
                $valid_attempt = false;
                if ($system_settings["recaptcha_secret_key"]) {
                    if (isset($post["g-recaptcha-response"]) && !empty($post["g-recaptcha-response"])) {
                        $recaptcha_response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $system_settings["recaptcha_secret_key"] . "&response=" . $post["g-recaptcha-response"]));
                        $valid_attempt = ($recaptcha_response->success ? true : false);
                    }
                } else {
                    $valid_attempt = true;
                }

                //Server Side Validation
                $rules["name"] = array("required", "max_length(100)");
                $rules["message"] = array("required", "max_length(1000)");
                $validation_result = SimpleValidator\Validator::validate($post, $rules);


                $query = "INSERT INTO user_requests (
                    content, 
                    code, 
                    status, 
                    type, 
                    user_id, 
                    flights_reservations_id,
                    channel
		) VALUES (
			'" . $post["details"] . "',
			'" . $post["reservation_code"] . "',
			'" . 0 . "',
			'" . $post["type"] . "',
			'" . $logged_user["id"] . "',
			'" . $post["reservation_id"] . "',
			'" . $post["channel"] . "'
		)";
                mysqlQuery($query);
                $success = readLanguage(contact, success);


                if ($success) {
                    $message_contact_form = "<div class='alert alert-success'>" . $success . "</div>";
                }
                if ($error) {
                    $message_contact_form = "<div class='alert alert-danger'>" . $error . "</div>";
                }
            }
            ?>

            <?= $message_contact_form ?>

            <? if (!$success) { ?>
                <? if ($system_settings["recaptcha_secret_key"]) { ?><script src="https://www.google.com/recaptcha/api.js" async defer></script><? } ?>
                <form method=post>
                    <input type=hidden name=token value="<?= $token ?>">
                    <input type=hidden name=action value="request-form">
                    <input type=hidden name=type value=<?= $get["type"] ?>>
                    <input type=hidden name=reservation_id value=<?= $get["id"] ?>>
                    <input type=hidden name=reservation_code value=<?= $get["code"] ?>>
                    <input type=hidden name=channel value="travel_port">
                    <div class=form-item>
                    <b><?= readLanguage(request, type) ?></b>
                    <div class=input>
                            <select name="type" class="form-control" required>
                                <option value="1">استفسار </option>
                                <option value="2">تعديل حجز</option>
                                <option value="3">الغاء حجز</option>
                                <option value="4">استرداد حجز</option>
                            </select>
                        </div>
                    </div>
                    <div class=form-item>
                        <b><?= readLanguage(request, Details) ?></b>
                        <div class=input>
                            <textarea name="details" class="form-control" rows="5" required></textarea>
                        </div>
                    </div>
                    <? if ($system_settings["recaptcha_secret_key"]) { ?>
                        <div class=recaptcha_box>
                            <small><?= readLanguage(general, recaptcha_required) ?></small>
                            <center>
                                <div class=g-recaptcha data-sitekey="<?= $system_settings["recaptcha_site_key"] ?>"></div>
                            </center>
                        </div>
                    <? } ?>
                    <div class=submit_container><button type=button class=submit><?= readLanguage(contact, send) ?></button></div>
                </form>
            <? } else { ?>
                <div class=message>
                    <div class=success_icon></div>
                    <b><?= readLanguage(contact, success_description) ?></b>
                </div>
            <? } ?>
        </div>
    </div>

    <!-- Side Content -->
    <div class="col-md-6 grid-item">
        <div class="page_container user_card margin-bottom">
            <img src="<?= ($logged_user["image"] ? "uploads/users/" . $logged_user["image"] : "images/user.png") ?>">
            <div class=single-line>
                <span class=single-line><a href="user/"><b><?= $logged_user["name"] ?></b></a></span>
                <span class=single-line><?= $logged_user["email"] ?></span>
                <input type=hidden name=profile_image_base64>
                <label><?= readLanguage(accounts, change_picture) ?><input type=file id=profile_image name=profile_image accept="image/*"></label>
            </div>
        </div>

        <div class=recursive_navigation>
            <?
            print "<ul>";
            foreach ($user_pages as $key => $value) {
                $class = ($key == $page_canonical ? "active" : "standard");
                print "<li class='$class'><a href='user/$key/'><i class='" . $value[2] . "'></i>&nbsp;&nbsp;" . $value[0] . "</a></li>";
            }
            print "</ul>";
            ?>
        </div>
    </div>

    <!-- End Tags -->
</div>

<script>
    $(document).ready(function() {
        bindCroppie("profile_image")
    });

    $("[name=profile_image_base64]").change(function() {
        var image = $(this).val();
        showLoadingCover();
        postForm({
            action: "update-picture",
            image: image
        });
    });
</script>

<? include "website/section_footer.php";
include "system/footer.php"; ?>