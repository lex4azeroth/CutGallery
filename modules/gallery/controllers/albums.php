<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class Albums_Controller extends Items_Controller {
  public function index() {
    $this->show(ORM::factory("item", 1));
  }

  public function show($album) {
    if (!is_object($album)) {
      // show() must be public because we route to it in url::parse_url(), so make
      // sure that we're actually receiving an object
      throw new Kohana_404_Exception();
    }

    access::required("view", $album);
    // CutGallery - Modified - pagesize for different level of album.
    if ($album->level < 2){
        $page_size = 10; // pagesize
    }
    else {
        $page_size = module::get_var("gallery", "page_size", 9);
    }
    $input = Input::instance();
    $show = $input->get("show");

    if ($show) {
      $child = ORM::factory("item", $show);
      $index = item::get_position($child);
      if ($index) {
        $page = ceil($index / $page_size);
        if ($page == 1) {
          url::redirect($album->abs_url());
        } else {
          url::redirect($album->abs_url("page=$page"));
        }
      }
    }

    $page = $input->get("page", "1");
    $children_count = $album->viewable()->children_count();
    $offset = ($page - 1) * $page_size;
    $max_pages = max(ceil($children_count / $page_size), 1);

    // Make sure that the page references a valid offset
    if ($page < 1) {
      url::redirect($album->abs_url());
    } else if ($page > $max_pages) {
      url::redirect($album->abs_url("page=$max_pages"));
    }

    $template = new Theme_View("page.html", "collection", "album");
    $template->set_global(
      array("page" => $page,
            "page_title" => null,
            "max_pages" => $max_pages,
            "page_size" => $page_size,
            "item" => $album,
            "children" => $album->viewable()->children($page_size, $offset),
            "parents" => $album->parents()->as_array(), // view calls empty() on this
            "children_count" => $children_count));
    $template->content = new View("album.html");

    $album->increment_view_count();

    print $template;
  }
  // CutGallery - ADDED 
  public function form_showcover($album)
  {
      
  }
  public function create($parent_id) {
    access::verify_csrf();
    $album = ORM::factory("item", $parent_id);
    access::required("view", $album);
    access::required("add", $album);

    $form = album::get_add_form($album);
    try {
      $valid = $form->validate();
      $album = ORM::factory("item");
      $album->type = "album";
      $album->parent_id = $parent_id;
/** CutGallery - Use Ymd_Hms_millisecond as directory name, for example, 20120209_032342_322
      $album->name = $form->add_album->inputs["name"]->value;
 * ==> 
 */
      $date_time = date("Ymd_Hms", time());
      $splitted_microtime = split( '   ',   microtime());
      $millisecond = $splitted_microtime[0] * 1000;
      $album->name = $date_time."_".substr($millisecond, 0, 3);
      $album->title = $form->add_album->title->value ?
        $form->add_album->title->value : $form->add_album->inputs["name"]->value;
      $album->description = $form->add_album->description->value;
/** CutGallery - Disable 'slug'.
 *    $album->slug = $form->add_album->slug->value;
 * ==>
 */

      // ==> CugGallery - Assign owner to this album
      $user_name = "admin";
      if ($form->add_album->owner->value != 0) {// A VIP user has been choosen.
          $vip_users = vip::lookup_vip_users();
          array_unshift($vip_users, $user_name);
          $user_name = $vip_users[$form->add_album->owner->value];
      }
      else {
          // Nothing to do...the owner is admin.
      }
      
      $owner = user::lookup_by_name($user_name);
      $album->owner_id = $owner->id;
      // <==
      
      $album->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->add_album->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $album->save();
      module::event("album_add_form_completed", $album, $form);
      log::success("content", "Created an album",
                   html::anchor("albums/$album->id", "view album"));
      message::success(t("Created album %album_title",
                         array("album_title" => html::purify($album->title))));
      //json::reply(array("result" => "success", "location" => $album->url())); // CutGallery - After added one albums, should leave at the level 1 album
      json::reply(array("result" => "success", "reload" => 1));
    } else {
      json::reply(array("result" => "error", "html" => (string)$form));
      // CutGallery - disable this line
      // print $form;
    }
  }

  public function update($album_id) {
    access::verify_csrf();
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);
    $old_owner_id = "";

    $form = album::get_edit_form($album);
    try {
      $valid = $form->validate();
      $album->title = $form->edit_item->title->value;
      $album->description = $form->edit_item->description->value;
/** CutGallery - Disable 'sort'     
      $album->sort_column = $form->edit_item->sort_order->column->value;
      $album->sort_order = $form->edit_item->sort_order->direction->value;
 * 
 */
      if (array_key_exists("name", $form->edit_item->inputs)) {
        $album->name = $form->edit_item->inputs["name"]->value;
      }
/** CutGallery - Disable slug
      $album->slug = $form->edit_item->slug->value;
 * 
 */
      // ==> CutGallery - Assign owner to this album
      $old_owner_id = $album->owner_id; // Keeps the old owner id for update.
      $user_name = "admin";
      if ($form->edit_item->owner->value != 0) { // A VIP user has been choosen.
          $vip_users = vip::lookup_vip_users();
          array_unshift($vip_users, $user_name);
          $user_name = $vip_users[$form->edit_item->owner->value];
      }
      else {
          // Nothing to do...the owner is admin.
      }
      
      $owner = user::lookup_by_name($user_name);
      $album->owner_id = $owner->id;
      // ==<
      
      $album->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->edit_item->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $album->save();
      item::update_owner_id($old_owner_id, $album->owner_id, $album->id); // CutGallery - Update photoes of this album with new owner id.
      module::event("item_edit_form_completed", $album, $form);

      log::success("content", "Updated album", "<a href=\"albums/$album->id\">view</a>");
      message::success(t("Saved album %album_title",
                         array("album_title" => html::purify($album->title))));

      if ($form->from_id->value == $album->id) {
        // Use the new url; it might have changed.
        // json::reply(array("result" => "success", "location" => $album->url())); // CutGallery - After added one albums, should leave at the level 1 album
        json::reply(array("result" => "success", "reload" => 1));
      } else {
        // Stay on the same page
        json::reply(array("result" => "success"));
      }
    } else {
      json::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  public function form_add($album_id) {
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("add", $album);

    print album::get_add_form($album);
  }

  public function form_edit($album_id) {
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    print album::get_edit_form($album);
  }
}
