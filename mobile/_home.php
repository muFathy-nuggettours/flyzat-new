<style>body::-webkit-scrollbar { display:none; }</style>

<script>
//Disables Pull-Refresh & Controls Header Icons Visibility
indexPage = true; 
</script>

<? $index_page = getData($suffix . "website_pages", "page", "mobile");
$index_modules = explode(",", $index_page["page_layout"]);
foreach ($index_modules AS $module){
	echo customModuleRender($module);
} ?>