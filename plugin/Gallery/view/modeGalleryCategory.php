<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/category.php';
$obj = AVideoPlugin::getObjectData("Gallery");
$liveobj = AVideoPlugin::getObjectDataIfEnabled("Live");
$_REQUEST['rowCount'] = 2;
$_REQUEST['current'] = getCurrentPage();
if(empty($_GET['tagsid']) && !empty($_REQUEST['tags_id'])){
    $_GET['tagsid'] = $_REQUEST['tags_id'];
}
$onlySuggested = $obj->CategoriesShowOnlySuggested;
if(!empty(getSearchVar())){
    $onlySuggested = false;
}
$sort = @$_POST['sort'];
unset($_POST['sort']);
$categories = Category::getAllCategories(false, true, $onlySuggested);
$total = Category::getTotalCategories(false, true, $onlySuggested);
$totalPages = ceil($total / getRowCount());
$page = getCurrentPage();
if ($totalPages < $page) {
    $page = $totalPages;
}
$link = addSearchOptions("{$global['webSiteRootURL']}plugin/Gallery/view/modeGalleryCategory.php") . "&current={page}";

if (empty($categories)) {
    return false;
}
$_REQUEST['current'] = 1;
$_REQUEST['rowCount'] = $obj->CategoriesRowCount;
?>
<!-- modeGalleryCategory start -->
<div class="categoriesContainerItem">
    <?php
    $timeLogName = TimeLogStart('modeGalleryCategory');
    foreach ($categories as $_cat) {
        $setCacheName = "include{$_cat['clean_name']}";
        $contents = getIncludeFileContent("{$global['systemRootPath']}plugin/Gallery/view/modeGalleryCategoryInclude.php", 
        ['_cat'=>$_cat, 'obj'=>$obj], $setCacheName);
        echo $contents;
    }
    TimeLogEnd($timeLogName, __LINE__, 1);
    ?>
</div>
<!-- modeGalleryCategory -->
<div class="col-sm-12" style="z-index: 1;">
    <?php
    //getPagination($total, $page = 0, $link = "", $maxVisible = 10, $infinityScrollGetFromSelector="", $infinityScrollAppendIntoSelector="")
    echo getPagination($totalPages, $page, $link, 10, ".categoriesContainerItem", ".categoriesContainerItem");
    ?>
</div>
<!-- modeGalleryCategory end -->
<?php
$_POST['sort'] = $sort;
?>