<div id=page-loading><div class=lds-ellipsis><div></div><div></div><div></div><div></div></div></div>

<!--Custom JavaScript-->
<? if ($system_settings["custom_javascript"]){ ?>
<script><?=html_entity_decode($system_settings["custom_javascript"])?></script>
<? } ?>

<!--Custom CSS-->
<? if ($system_settings["custom_css"]){ ?>
<style><?=$system_settings["custom_css"]?></style>
<? } ?>

<script src="plugins/mixitup.min.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="plugins/selecty.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="plugins/selecty.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<script src="plugins/moment.min.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="plugins/bootstrap-notify.min.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="plugins/rellax.min.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="plugins/parallax-complex.min.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="plugins/tween-max.min.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="plugins/tilt-animation.js?v=<?=$system_settings["system_version"]?>"></script>