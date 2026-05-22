<?php
!isset($_SESSION) ? session_start() : $_SESSION = null;

require_once dirname(__DIR__, 1) .'/settings/controller/CTRLGetListingCategoriesForOthers.php';

//$m_dir =  dirname(__DIR__);
?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <base href="http://localhost:122/laborow/" />
    <meta charset="utf-8" />
    <title>Laborow! - <?php echo $page_title; ?></title>
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="description" content="Laborow! is the biggest rental platform in Africa." />
    <meta name="keywords" content="rent wheelbarrow, rent forklift, rent farm tools, rent digital camera, rent spare room" />
    <meta name="keywords" content="rent packing space, rent hand drill, rent lawn mowers, rent sewing machines, rent oven, ladder, rent a billboard" />
    <meta name="keywords" content="rent a motor bike, rent a scooter, rent a van, rent a truck, rent a TV, rent sounds system, rent event chairs" />
    <meta name="keywords" content=" rent canopies, rent an auditorium, rent farm spraying machine, rent a farm tractor, rent a watering can" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:title" content="Laborow!" />
    <meta property="og:type" content="" />
    <meta property="og:url" content="laborow.com" />
    <meta property="og:image" content="" />
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="apps/template/statics/assets/imgs/theme/logo.png" />
    <!-- Template CSS -->
    <link rel="stylesheet" href="apps/template/statics/assets/css/plugins/animate.min.css" />
    <link rel="stylesheet" href="apps/template/statics/assets/css/main17e6.css?v=5.2" />

    <link rel="stylesheet" href="apps/template/statics/toast/css/jquery.toast.css" />

    <link type="text/css" rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="apps/template/statics/assets/upload_preview/css/image-uploader.min.css" />


    <link href="apps/template/statics/assets/loaders/custom-loader.css" rel="stylesheet" type="text/css" />
</head>