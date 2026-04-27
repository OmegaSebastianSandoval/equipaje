<?php

class Core_Model_Upload_Image
{
  private $_exts = array("image/jpg", "image/jpeg", "image/png", "image/gif"); // Tipos MIME soportados
  private $_allowedExtensions = array("jpg", "jpeg", "png", "gif"); // Extensiones permitidas
  private $_width = 19200; // Ancho máximo
  private $_height = 19200; // Alto máximo
  private $_size = 2000097152; // Peso máximo (bytes)

  public function changeConfig($exts, $size, $width, $height)
  {
    if ($exts != null) {
      $this->_exts = $exts;
    }
    if ($width != null) {
      $this->_width = $width;
    }
    if ($height != null) {
      $this->_height = $height;
    }
    if ($size != null) {
      $this->_size = $size;
    }
  }

  public function upload($image)
  {
    $this->_width = 8000;
    $this->_height = 8000;

    if ($_FILES[$image]["error"] > 0) {
      print_r($_FILES[$image]["error"]);
      return false;
    }

    $fileType = $_FILES[$image]['type'];
    $extension = strtolower(pathinfo($_FILES[$image]['name'], PATHINFO_EXTENSION));

    // Validar MIME y extensión
    if (!in_array($fileType, $this->_exts) || !in_array($extension, $this->_allowedExtensions)) {
      return false;
    }

    if ($_FILES[$image]['size'] > $this->_size) {
      return false;
    }

    $filename = $this->clearName(pathinfo($_FILES[$image]['name'], PATHINFO_FILENAME));
    $name = $filename . '.' . $extension;
    list($ancho_orig, $alto_orig) = getimagesize($_FILES[$image]['tmp_name']);
    $ruta = IMAGE_PATH . $name;

    // Evitar sobrescribir archivos
    $increment = 0;
    while (file_exists($ruta)) {
      $increment++;
      $name = $filename . $increment . '.' . $extension;
      $ruta = IMAGE_PATH . $name;
    }

    $origen = $_FILES[$image]['tmp_name'];
    $ancho_max = $this->_width;
    $alto_max = $this->_height;

    if ($ancho_orig > $ancho_max || $alto_orig > $alto_max) {
      $ratio_orig = $ancho_orig / $alto_orig;
      if ($ancho_max / $alto_max > $ratio_orig) {
        $ancho_max = $alto_max * $ratio_orig;
      } else {
        $alto_max = $ancho_max / $ratio_orig;
      }

      // Redimensionar
      $canvas = imagecreatetruecolor($ancho_max, $alto_max);
      switch ($fileType) {
        case "image/jpg":
        case "image/jpeg":
          $imageRes = imagecreatefromjpeg($origen);
          imagecopyresampled($canvas, $imageRes, 0, 0, 0, 0, $ancho_max, $alto_max, $ancho_orig, $alto_orig);
          imagejpeg($canvas, $ruta, 100);
          break;
        case "image/gif":
          $imageRes = imagecreatefromgif($origen);
          imagecopyresampled($canvas, $imageRes, 0, 0, 0, 0, $ancho_max, $alto_max, $ancho_orig, $alto_orig);
          imagegif($canvas, $ruta);
          break;
        case "image/png":
          $imageRes = imagecreatefrompng($origen);
          imagecopyresampled($canvas, $imageRes, 0, 0, 0, 0, $ancho_max, $alto_max, $ancho_orig, $alto_orig);
          imagepng($canvas, $ruta, 0);
          break;
      }
    } else {
      move_uploaded_file($origen, $ruta);
    }

    return $name;
  }

  public function delete($image)
  {
    if (file_exists(IMAGE_PATH . $image)) {
      unlink(IMAGE_PATH . $image);
      return true;
    }
    return false;
  }

  private function clearName($str)
  {
    $tildes = array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ');
    $vocales = array('a', 'e', 'i', 'o', 'u', 'n', 'A', 'E', 'I', 'O', 'U', 'N');
    $str = str_replace($tildes, $vocales, $str);
    $simbolos = array("=", "¿", "?", "¡", "!", "'", "%", "$", "€", "(", ")", "[", "]", "{", "}", "*", "+", "·", ".", "<", ">");
    $str = str_replace($simbolos, "", $str);
    $str = str_replace(" ", "_", $str);
    return strtolower($str);
  }

  public function uploadmultiple($image)
  {
    $permitidos = $this->_exts;
    $extPermitidas = $this->_allowedExtensions;
    $limite_kb = 2000;
    $images = array();

    foreach ($_FILES[$image]["tmp_name"] as $key => $tmp_name) {
      $fileType = $_FILES[$image]['type'][$key];
      $extension = strtolower(pathinfo($_FILES[$image]['name'][$key], PATHINFO_EXTENSION));

      if (in_array($fileType, $permitidos) && in_array($extension, $extPermitidas) &&
          $_FILES[$image]['size'][$key] <= $limite_kb * 1024) {

        $filename = $this->clearName(pathinfo($_FILES[$image]['name'][$key], PATHINFO_FILENAME));
        $name = $filename . '.' . $extension;
        $ruta = IMAGE_PATH . $name;

        $increment = 0;
        while (file_exists($ruta)) {
          $increment++;
          $name = $filename . $increment . '.' . $extension;
          $ruta = IMAGE_PATH . $name;
        }

        if (move_uploaded_file($_FILES[$image]['tmp_name'][$key], $ruta)) {
          $images[$key] = "images/" . $name;
        }
      }
    }

    return (count($images) > 0) ? $images : false;
  }

  public function uploadThumbs($image)
  {
    $this->_width = 500;
    $this->_height = 500;

    if ($_FILES[$image]["error"] > 0) {
      print_r($_FILES[$image]["error"]);
      return false;
    }

    $fileType = $_FILES[$image]['type'];
    $extension = strtolower(pathinfo($_FILES[$image]['name'], PATHINFO_EXTENSION));

    if (!in_array($fileType, $this->_exts) || !in_array($extension, $this->_allowedExtensions)) {
      return false;
    }

    if ($_FILES[$image]['size'] > $this->_size) {
      return false;
    }

    $filename = $this->clearName(pathinfo($_FILES[$image]['name'], PATHINFO_FILENAME));
    $name = $filename . '.' . $extension;
    list($ancho_orig, $alto_orig) = getimagesize($_FILES[$image]['tmp_name']);
    $ruta = IMAGE_PATH . 'thumbs/' . $name;

    $increment = 0;
    while (file_exists($ruta)) {
      $increment++;
      $name = $filename . $increment . '.' . $extension;
      $ruta = IMAGE_PATH . 'thumbs/' . $name;
    }

    $origen = $_FILES[$image]['tmp_name'];
    $ancho_max = $this->_width;
    $alto_max = $this->_height;

    if ($ancho_orig > $ancho_max || $alto_orig > $alto_max) {
      $ratio_orig = $ancho_orig / $alto_orig;
      if ($ancho_max / $alto_max > $ratio_orig) {
        $ancho_max = $alto_max * $ratio_orig;
      } else {
        $alto_max = $ancho_max / $ratio_orig;
      }

      $canvas = imagecreatetruecolor($ancho_max, $alto_max);
      switch ($fileType) {
        case "image/jpg":
        case "image/jpeg":
          $imageRes = imagecreatefromjpeg($origen);
          imagecopyresampled($canvas, $imageRes, 0, 0, 0, 0, $ancho_max, $alto_max, $ancho_orig, $alto_orig);
          imagejpeg($canvas, $ruta, 100);
          break;
        case "image/gif":
          $imageRes = imagecreatefromgif($origen);
          imagecopyresampled($canvas, $imageRes, 0, 0, 0, 0, $ancho_max, $alto_max, $ancho_orig, $alto_orig);
          imagegif($canvas, $ruta);
          break;
        case "image/png":
          $imageRes = imagecreatefrompng($origen);
          imagecopyresampled($canvas, $imageRes, 0, 0, 0, 0, $ancho_max, $alto_max, $ancho_orig, $alto_orig);
          imagepng($canvas, $ruta, 0);
          break;
      }
    } else {
      move_uploaded_file($origen, $ruta);
    }

    return $name;
  }
}
