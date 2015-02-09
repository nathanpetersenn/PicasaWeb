<?php

if (!defined('PICASA_URL_BASE')) define('PICASA_URL_BASE', 'http://picasaweb.google.com/data/feed/api/user/');
require_once '_PicasaWebPhoto.php';

class PicasaWebAlbum {
  public $userId;
  public $albumId;

  public $title;
  public $timestamp = null;
  public $photos = null;
  public $photosCount = null;

  private $feed;
  private $parent = null;

  function __construct($parent, $userId, $albumId) {
    $this->userId = (string)$userId;
    $this->albumId = (string)$albumId;

    $this->parent = $parent;

    // TODO implement Exception on fail to load XML file
    // throw new Exception('Unable to load file: ' . $this->identifier)
    if ($albumId == "recent") {
      $this->feed = simplexml_load_file('http://picasaweb.google.com/data/feed/base/user/' . $userId . '?kind=photo&access=public&max-results=15');
    } else {
      $this->feed = simplexml_load_file(PICASA_URL_BASE . $userId . '/albumid/' . $albumId);
    }

    $this->fetchTitle();
    $this->fetchTimestamp();
    $this->fetchPhotosCount();
  }

  /*
  =========================== PRIVATE METHODS ===========================
  */

  private function fetchTitle() {
    if ($this->albumId == 'recent') {
      $this->title = 'Recent';
    } else {
      $this->title = (string)$this->feed->title;
    }
  }

  private function fetchTimestamp() {
    $this->timestamp= substr((string)$this->feed->children('http://schemas.google.com/photos/2007')->timestamp, 0, -3);
  }

  private function fetchPhotosCount() {
    $this->photosCount = count($this->feed->entry);
  }

  /*
  =========================== PUBLIC METHODS ===========================
  */

  public function fetchPhotos() {
    if (is_null($this->photos)) {
      $this->photos = array();

      foreach ($this->feed->entry as $photo) {
        $subject = (string)$photo->id;
        $pattern = "/\/user\/(\d+)\/albumid\/(\d+)/";
        $matches = array();
        preg_match($pattern, $subject, $matches);

        $userId = (string)$matches[1];
        $albumId = (string)$matches[2];
        $photoId = (string)$photo->children('http://schemas.google.com/photos/2007')->id;
      
        $photo = new PicasaWebPhoto($this, $userId, $albumId, $photoId);
        array_push($this->photos, $photo);
      }
    }
  }

  public function getNext() {
    if (is_null($this->parent)) {
      return;
    }

    $this->parent->fetchAlbums();

    $i = 0;
    foreach ($this->parent->albums as $album) {
      if ($album->albumId == $this->albumId) {
        if (($i+1) >= count($this->parent->albums)) {
          return null;
        } else {
          return $this->parent->albums[$i+1];
        }
      }

      $i++;
    }
  }

  public function getPrev() {
    if (is_null($this->parent)) {
      return;
    }

    $this->parent->fetchAlbums();

    $i = 0;
    foreach ($this->parent->albums as $album) {
      if ($album->albumId == $this->albumId) {
        if ($i == 0) { // first album
          return null;
        } else {
          return $this->parent->albums[$i-1];
        }
      }

      $i++;
    }

  }

  public function getCover($size) {
    if ($this->albumId == "recent") {
      // TODO crop this image to square and sized to $size
      return (string)$this->feed->entry->content->attributes()->src;
    } else {
      $url = (string)$this->feed->icon;
      return str_replace("s160-c", "s{$size}-c", $url);
    }
  }

  /*
  =========================== STATIC PUBLIC METHODS ===========================
  */

  public static function isAlbum($userId, $albumId) {
    if ($albumId == 'recent' && $this->parent->opts['recent'] == true) { return true; }

    $file = @file_get_contents(PICASA_URL_BASE . $userId . '/albumid/' . $albumId);

    if (!$file) {
      return false;
    }

    $file = explode(' ', $file);
    if ($file[0] == 'Invalid') {
      return false;
    } else {
      return true;
    }
  }

  public static function makeLink($link) {
    $link = strtolower($link);
    $replace = array('.', ',', ' ', '_', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '+', '=', '[', ']', ':', ';', '<', '>', '|', '\'', '"');
    $link = str_replace($replace, '-', $link);
    $link=  preg_replace('~-{2,}~', '-', $link);
    if ($link[0] == '-') {$link = substr($link, 1);}
    if ($link[strlen($link)-1] == '-') {$link = substr_replace($link ,'',-1);}
    return $link;
  }

}

?>