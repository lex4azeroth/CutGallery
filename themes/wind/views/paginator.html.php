<?php defined("SYSPATH") or die("No direct script access.") ?>
<?
// This is a generic paginator for album, photo and movie pages.  Depending on the page type,
// there are different sets of variables available.  With this data, you can make a paginator
// that lets you say "You're viewing photo 5 of 35", or "You're viewing photos 10 - 18 of 37"
// for album views.
//
// Available variables for all page types:
//   $page_type               - "collection", "item", or "other"
//   $page_subtype            - "album", "movie", "photo", "tag", etc.
//   $previous_page_url       - the url to the previous page, if there is one
//   $next_page_url           - the url to the next page, if there is one
//   $total                   - the total number of photos in this album
//
// Available for the "collection" page types:
//   $page                    - what page number we're on
//   $max_pages               - the maximum page number
//   $page_size               - the page size
//   $first_page_url          - the url to the first page, or null if we're on the first page
//   $last_page_url           - the url to the last page, or null if we're on the last page
//   $first_visible_position  - the position number of the first visible photo on this page
//   $last_visible_position   - the position number of the last visible photo on this page
//
// Available for "item" page types:
//   $position                - the position number of this photo
//
?>

<ul class="g-paginator ui-helper-clearfix">
  <li class="g-back">
    <? if (isset($item) && $item->level > 1): ?>
      <a href ="<?= $back_page_url ?>" class="g-button ui-corner-all">Back</a> <!-- CutGallery - ADDED -->
    <? endif ?>
  </li>
  
  <li class="g-first">
  <? if ($page_type == "collection"): ?>
    <? if (isset($first_page_url)): ?>
      <a href="<?= $first_page_url ?>" class="g-button ui-icon-left ui-state-default ui-corner-all">
        <span class="ui-icon ui-icon-seek-first"></span><?= t("First") ?></a>
    <? else: ?>
      <a class="g-button ui-icon-left ui-state-disabled ui-corner-all">
        <span class="ui-icon ui-icon-seek-first"></span><?= t("First") ?></a>
    <? endif ?>
  <? endif ?>
  <? if (isset($previous_page_url)): ?>
    <a href="<?= $previous_page_url ?>" class="g-button ui-icon-left ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-seek-prev"></span></a> <!-- CutGallery - REMOVE text -->
  <? else: ?>
    <a class="g-button ui-icon-left ui-state-disabled ui-corner-all">
      <span class="ui-icon ui-icon-seek-prev"></span></a> <!-- CutGallery - REMOVE text -->
  <? endif ?>
  </li>

  <li class="g-info">
    <? if ($total): ?>
      <? if ($page_type == "collection"): ?>
        <?= /* @todo This message isn't easily localizable */
            t2("Item %from_number of %count",
               "Items %from_number - %to_number of %count",
               $total,
               array("from_number" => $first_visible_position,
                     "to_number" => $last_visible_position,
                     "count" => $total)) ?>
      <? else: ?>
        <?= t("%position of %total", array("position" => $position, "total" => $total)) ?>
      <? endif ?>
    <? else: ?>
      <?= t("No items") ?>
    <? endif ?>
  </li>

  <li class="g-text-right">
  <? if (isset($next_page_url)): ?>
    <a href="<?= $next_page_url ?>" class="g-button ui-icon-right ui-state-default ui-corner-all">  
      <span class="ui-icon ui-icon-seek-next"></span></a> <!--CutGallery - REMOVE-->
  <? else: ?>
    <a class="g-button ui-state-disabled ui-icon-right ui-corner-all">
      <span class="ui-icon ui-icon-seek-next"></span></a> <!--CutGallery - REMOVE-->
  <? endif ?>

  <? if ($page_type == "collection"): ?>
    <? if (isset($last_page_url)): ?>
      <a href="<?= $last_page_url ?>" class="g-button ui-icon-right ui-state-default ui-corner-all">
        <span class="ui-icon ui-icon-seek-end"></span><?= t("Last") ?></a>
    <? else: ?>
      <a class="g-button ui-state-disabled ui-icon-right ui-corner-all">
        <span class="ui-icon ui-icon-seek-end"></span><?= t("Last") ?></a>
    <? endif ?>
  <? endif ?>
  </li>
  <? if ($page_subtype == "photo"): ?>
  <li class="g-share">
      <a href ="<?= $share_url ?>" class="g-button ui-corner-all">Share</a>
  </li>
  <li class="g-download-full">
      <a href ="<?= $download_full_url ?>" class="g-button ui-corner-all">Download</a>
  </li>
  <? endif ?>
</ul>
