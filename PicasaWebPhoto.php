<?php

class PicasaWebPhoto {
  public $userId;
  public $albumId;
  public $photoId;

  public $exif = null;
  public $dimensions = null;
  public $url = null;
  public $fileSize = null;
  public $fileName; // TODO populate this
  public $caption; // TODO populate this

  private $feed;
  private $parent = null;

  function __construct($parent, $userId, $albumId, $photoId) {
    $this->userId = (string)$userId;
    $this->albumId = (string)$albumId;
    $this->photoId = (string)$photoId;

    $this->parent = $parent;

    $this->feed = simplexml_load_file('http://picasaweb.google.com/data/entry/api/user/' . $userId . '/albumid/' . $albumId . '/photoid/' . $photoId);
    $this->fetchExif();
    $this->fetchUrl();
    $this->fetchFileSize();
    $this->fetchDimensions();
  }

  /*
  =========================== PRIVATE METHODS ===========================
  */

  private function fetchExif() {
    if (is_null($this->exif)) {
      $e = $this->feed->children('http://schemas.google.com/photos/exif/2007')->tags;

      $exp = (float)$e->exposure;
      if ($exp && $exp < 1) { $this->exif['exposure'] = '1/' . round(1 / $exp); }
      if ($exp && $exp >= 1) { $this->exif['exposure'] = round($exp) . ' sec'; }
      
      $this->exif['fstop'] = (float)$e->fstop;
      $this->exif['iso'] = (float)$e->iso;
      $this->exif['make']= (string)$e->make;
      $this->exif['model'] = (string)$e->model;
      $this->exif['focallength'] = (float)$e->focallength;
      $this->exif['time'] = ((int)$e->time);
    }
  }

  private function fetchUrl() {
    if (is_null($this->url)) {
      $this->url = (string)$this->feed->content->attributes()->src;
    }
  }

  private function fetchFileSize() {
    if (is_null($this->fileSize)) {
      $gphoto = $this->feed->children('http://schemas.google.com/photos/2007');
      $this->fileSize = (int)$gphoto->size;
    }
  }

  private function fetchDimensions() {
    if (is_null($this->dimensions)) {
      $gphoto = $this->feed->children('http://schemas.google.com/photos/2007');
      $width = (int)$gphoto->width;
      $height = (int)$gphoto->height;
      $this->dimensions = array('width' => $width, 'height' => $height);
    }
  }

}

?>