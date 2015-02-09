# PicasaWeb
Interfacing with PicasasWeb made easy.

## Usage
```php
require_once 'PicasaWebUser.php';
require_once 'PicasaWebAlbum.php';
require_once 'PicasaWebPhoto.php';

$user = new PicasaWebUser("userId");

$user->fetchAlbums();
foreach ($user->albums as $album) {
  // code
}
```

## Future
- Add support for authorized requests to private albums and photos

## Questions
Email me at nathan@npetersen.net
