<?php

class PicasaWebPhoto {
  public $userId;
  public $albumId;
  public $photoId;

  public $exif = null;
  public $dimensions = null;
  public $url = null;
  public $fileSize = null;
  public $fileName = null;
  public $caption = null;

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
    $this->fetchFileName();
    $this->fetchCaption();
  }

  /*
  =========================== PRIVATE METHODS ===========================
  */

  private function fetchExif() {
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

  private function fetchUrl() {
    $this->url = (string)$this->feed->content->attributes()->src;
  }

  private function fetchFileSize() {
    $gphoto = $this->feed->children('http://schemas.google.com/photos/2007');
    $this->fileSize = (int)$gphoto->size;
  }

  private function fetchDimensions() {
    $gphoto = $this->feed->children('http://schemas.google.com/photos/2007');
    $width = (int)$gphoto->width;
    $height = (int)$gphoto->height;
    $this->dimensions = array('width' => $width, 'height' => $height);
  }

  private function fetchFileName() {
    $this->fileName = (string)$this->feed->children('http://search.yahoo.com/mrss/')->group->title;
  }

  private function fetchCaption() {
    $this->caption = (string)$this->feed->children('http://search.yahoo.com/mrss/')->group->description;
  }

}

?>