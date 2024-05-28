<?
//========= Core Sections =========

include "_sections.php";

//========= Panel Categories =========

$panel_categories = array(
	"إدارة نظام الطيران" => array("إعدادات نظام الطيران","إدارة قواعد البيانات","إدارة الحجوزات","الإدارة المالية"),
	"إدارة الموقع الإلكتروني" => array("المحتوي المدمج","المحتوي المخصص"),
	"المستخدمين و جهات التواصل" => array("إدارة المستخدمين","إدارة جهات التواصل"),
	"الإعدادات و الصلاحيات" => array("الإعدادات و الصلاحيات"),
);

//========= Panel Pages =========

$panel_section["إعدادات نظام الطيران"] = array(
	"flights_settings" => "إعدادات النظام الأساسية",
	"flights_pricing" => "إدارة سياسات التسعير",
	"flights_warnings" => "إدارة تنبيهات الوجهات",
	"flights_custom" => "إدارة الرحلات الشارتر",
	"flights_ratings" => "إدارة تقييمات الرحلات",
	"flights_errors" => "سجل العمليات الخاطئة"
);

$panel_section["إدارة قواعد البيانات"] = array(
	"database_countries" => "قاعدة بيانات الدول",
	"database_regions" => "قاعدة بيانات المدن",
	"database_airports" => "قاعدة بيانات المطارات",
	"database_airlines" => "قاعدة بيانات خطوط الطيران",
	"database_planes" => "قاعدة بيانات الطائرات",
);

$panel_section["إدارة الحجوزات"] = array(
	"reservations_pending_payment" => "الحجوزات بانتظار الدفع",
	"reservations_pending" => "الحجوزات المعلقة",
	"reservations_confirmed" => "الحجوزات المؤكدة",
	"reservations_executed" => "الحجوزات المنفذة",
	"reservations_pending_update" => "حجوزات قيد التعديل",
	"reservations_pending_cancel" => "حجوزات قيد الالغاء",
	"reservations_cancelled" => "الحجوزات الملغاه",
	"reservations_database" => "قاعدة بيانات الحجوزات",
);

$panel_section["الإدارة المالية"] = array(
	"finance_balance_manage" => "إدارة ارصدة الحسابات",
	"finance_balance_database" => "سجلات ارصدة الحسابات",
	"finance_payment_records" => "سجلات الدفع الإلكتروني",
	"finance_summary" => "حسابات الموقع",
);

$panel_section["المحتوي المدمج"] = array(
	"website_contact" => "إدارة بيانات التواصل",
	"website_pages" => "إدارة الصفحات المدمجة",
	"website_destinations" => "إدارة صفحات الوجهات",
	"website_seo" => "إدارة صفحات محركات البحث",
	"website_seo_links" => "روابط صفحات محركات البحث",
	"website_menu" => "إدارة القائمة الرئيسية",
	"module_slider" => "إدارة محتويات السلايدر",
	"website_links" => "إدارة الروابط السريعة",
	"website_popup" => "إدارة النوافذ المنبثقة",
	"website_sitemap_custom" => "إدارة خارطة الموقع",
);

$panel_section["المحتوي المخصص"] = array(
	"website_custom_pages" => "إدارة الصفحات المخصصة",
	"website_custom_contents" => "إدارة صفحات المحتوي",
	"website_custom_displays" => "إدارة المعروضات",
	"website_forms" => "إدارة نماذج الإدخال",
	"website_custom_modules" => "إدارة القوالب",
	"website_classes" => "إدارة الأنماط",
	"website_theme" => "إدارة الثيم الرئيسي",
);

$panel_section["إدارة المستخدمين"] = array(
	"users_management" => "إدارة حسابات المستخدمين",
	"users_database" => "قاعدة بيانات المستخدمين",
	"users_passengers" => "قاعدة بيانات المسافرين",
	"users_agents" => "إدارة حسابات الوكلاء",
	"users_login" => "تسجيل الدخول كمستخدم",
);

$panel_section["إدارة جهات التواصل"] = array(
	"channel_templates" => "إدارة نماذج المراسلات",
	"channel_sms" => "إرسال رسالة نصية",
	"channel_email" => "إرسال بريد إلكتروني",
	"channel_push" => "إرسال تنبيه لحظي",
	"channel_records_sms" => "سجل المراسلات النصية",
	"channel_records_email" => "سجل المراسلات البريدية",
	"channel_records_push" => "سجل التنبيهات اللحظية",
	"channel_push_subscribers" => "مشتركين التنبيهات اللحظية",
	"channel_forms_records" => "تسجيلات نماذج الإدخال",
	"channel_newsletter" => "مشتركين النشرة البريدية",
	"channel_requests_custom" => "طلبات التواصل من الموقع",
);

$panel_section["الإعدادات و الصلاحيات"] = array(
	"system_information" => "معلومات النظام",
	"system_settings" => "الإعدادات التقنية",
	"system_administrators" => "ادارة مديرين النظام",
	"system_permissions" => "إدارة صلاحيات المديرين",
	"system_language" => "ملف اللغة",
);
?>