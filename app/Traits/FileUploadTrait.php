<?php
namespace App\Traits;
trait FileUploadTrait {

  public function uploadImage($image) {

    if ($image) {

      $image_name = $image->storeAs('public/',$image->getClientOriginalName());
      $name = explode("/", $image_name);
      $img_name = $name[count($name) - 1];
      return $img_name;
    }
    return null;
  }
}