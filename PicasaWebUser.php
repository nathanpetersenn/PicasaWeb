<?php

if (!defined('PICASA_URL_BASE')) define('PICASA_URL_BASE', 'http://picasaweb.google.com/data/feed/api/user/');
require_once '_PicasaWebAlbum.php';

class PicasaWebUser {
  public $userId = '';

  public $name = null;
  public $aboutBlurb = null;
  public $profilePictureUrl = null;
  public $albums = null;
  public $albumsCount = null;

  private $feed;
  private $opts = array(
      "excluded_albums" => array('Profile Photos', 'Profile Data', 'Scrapbook Photos'),
      "recent" => true,
      "user_data_album_name" => "Profile Data",
      "profile_photos_album_name" => "Profile Photos"
    );

  function __construct($userId, $user_settings = array()) {
    $this->userId = $userId;
    $this->feed = simplexml_load_file(PICASA_URL_BASE . $userId);

    $this->opts = array_merge($this->opts, $user_settings);

    $this->fetchName();
    $this->fetchAboutBlurb();
    $this->fetchProfilePicture();
    $this->fetchAlbumsCount();
  }

  /*
  =========================== PRIVATE METHODS ===========================
  */

  private function fetchName() {
    $this->name = explode(' ', (string)$this->feed->author->name);
  }

  private function fetchAboutBlurb() {
    foreach ($this->feed->entry as $album) {
      if ($album->title == $this->opts['user_data_album_name']) {
        $this->aboutBlurb = (string)$album->summary;
        break;
      }
    }
  }

  private function fetchProfilePicture() {
    $this->profilePictureUrl = $this->_getAlbumCover($this->opts['user_data_album_name']);

    if (is_null($this->profilePictureUrl)) {
      $this->profilePictureUrl = $this->_getAlbumCover($this->opts['profile_photos_album_name']);
    }
  }

  private function fetchAlbumsCount() {
    $albumsCount = 0;

    if ($this->opts['recent']) {
      $albumsCount++;
    }

    foreach ($this->feed->entry as $entry) {
      if (in_array($entry->title, $this->opts['excluded_albums'])) {
        continue;
      }
      $albumsCount++;
    }

    $this->albumsCount = $albumsCount;
  }

  private function _getAlbumCover($searchTitle) {
    foreach ($this->feed->entry as $album) {
      if ($album->title == $searchTitle) {
        return (string)$album->children('http://search.yahoo.com/mrss/')->group->content->attributes()->url;
      }
    }
    return null;
  }

  /*
  =========================== PUBLIC METHODS ===========================
  */

  public function fetchAlbums() {
    if (is_null($this->albums)) {
      $this->albums = array();

      if ($this->opts['recent']) {
        $recent = new PicasaWebAlbum($this, $this->userId, "recent");
        array_push($this->albums, $recent);
      }

      foreach ($this->feed->entry as $entry) {
        if (in_array($entry->title, $this->opts['excluded_albums'])) {
          continue;
        }

        $albumId = (string)$entry->children('http://schemas.google.com/photos/2007')->id;

        $album = new PicasaWebAlbum($this, $this->userId, $albumId);
        array_push($this->albums, $album);
      }
    }
  }

}

?>