<?php

define('NO_GALLERY_IMAGES_TITLE', 'No photos available');
define('NO_GALLERY_IMAGES', 'The gallery you are trying to view has no images. Upload some images to this folder first.');
define('DEFAULT_GALLERY_TITLE', 'My Gallery');
define('CONVERTED_IMAGE_WIDTH', 512);
define('CONVERTED_IMAGE_HEIGHT', 384);
define('PREVIEW_PREFIX', 'ic_preview_');

class Instant_Carousel
{
    protected $_validExtensions = array('jpg', 'jpeg', 'png');
    private $_generatedPreviews = array();

    public function __construct()
    {
        $this->root = dirname(__FILE__);
        $this->_checkGalleryTitleFile();
        $fileList = $this->_getFileList();

        try {
            $previewList = $this->_checkPreviews($fileList);
            $this->_createPreview($previewList);
        } catch (Exception $e) {
            $this->_galleryHtml = $e->getMessage();
        }

    }

    public function getGalleryHtml()
    {
        return $this->_galleryHtml;
    }

    public function getGalleryTitle()
    {
        return $this->_galleryTitle;
    }

    private function _checkGalleryTitleFile()
    {
        $gallery_title_file = $this->root . '/ic_gallery_title.txt';
        if (file_exists($gallery_title_file)) {
            $this->_galleryTitle = file_get_contents($gallery_title_file);
        } else {
            $this->_galleryTitle = DEFAULT_GALLERY_TITLE;
        }
    }

    private function _getFileList()
    {
        $files = array();

        if ($handle = opendir($this->root)) {
            while (false !== ($entry = readdir($handle))) {
                if ($this->_checkFile($entry)) {
                    $files[] = $entry;
                }
            }
            closedir($handle);
        }
        return $files;
    }

    private function _checkFile($file)
    {
        $path = $this->root . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            return false;
        }

        if (in_array($file, array('.', '..'))) {
            return false;
        }

        if (!in_array(pathinfo($path, PATHINFO_EXTENSION), $this->_validExtensions)) {
            return false;
        }

        preg_match('/'.PREVIEW_PREFIX.'(.+)/', $file, $matches);
        if (!empty($matches)) {
            return false;
        }

        return true;
    }

    private function _checkPreviews($fileList)
    {
        if (empty($fileList)) {
            throw new Exception($this->_emptyGallery());
        }

        $forCreation = array();
        
        foreach ($fileList as $fl) {
            $preview = PREVIEW_PREFIX . $fl;
            if (!file_exists($preview)) {
                $forCreation[] = $fl;
            }
        }

        if (empty($forCreation)) {
            $this->_getPreviewsList();
            $this->_buildGallery();
        }

        return $forCreation;
    }

    private function _createPreview($previewList)
    {
        foreach ($previewList as $pl) {
            $path = $this->root . DIRECTORY_SEPARATOR . $pl;
            $preview = PREVIEW_PREFIX . $pl;
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    $src = imagecreatefromjpeg($path);
                    $imgx = imagesx($src);
                    $imgy = imagesy($src);

                    $converted_img = imagecreatetruecolor(CONVERTED_IMAGE_WIDTH, CONVERTED_IMAGE_HEIGHT);
                    imagecopyresampled($converted_img, $src, 0, 0, 0, 0, CONVERTED_IMAGE_WIDTH, CONVERTED_IMAGE_HEIGHT, $imgx, $imgy);
                    imagejpeg($converted_img, $this->root . DIRECTORY_SEPARATOR . $preview);
                    break;
                case 'png':
                    break;
            }
            $this->_generatedPreviews[] = $pl;
            $this->_buildGallery();
            /*echo '<pre>' . $pl . 'is ' . pathinfo($path, PATHINFO_EXTENSION) . '</pre>';*/
        }
    }

    private function _getPreviewsList()
    {
        $previews = array();

        if ($handle = opendir($this->root)) {
            while (false !== ($entry = readdir($handle))) {
                if (substr($entry, 0, 11) == PREVIEW_PREFIX) {
                    $previews[] = substr($entry, 11);
                }
            }
            closedir($handle);
        }
        $this->_generatedPreviews = $previews;
    }

    private function _buildGallery()
    {
        $gallery = '<h3>' . $this->_galleryTitle . '</h3>';
        $gallery .= '<div id="ic_gallery" class="carousel slide"><div class="carousel-inner">';
        $count = 0;
        foreach ($this->_generatedPreviews as $gp) {
            $gallery .= ($count < 1) ? '<div class="active item"><img src="' . PREVIEW_PREFIX . $gp . '"></div>' : '<div class="item"><img src="'. PREVIEW_PREFIX . $gp . '"></div>';
            $count++;
        }
        $gallery .= '</div></div>';
        $gallery .= '<a class="carousel-control left" href="#ic_gallery" data-slide="prev">&lsaquo;</a>
  <a class="carousel-control right" href="#ic_gallery" data-slide="next">&rsaquo;</a>';
        $this->_galleryHtml = $gallery;
    }

    private function _emptyGallery()
    {
        return '<h3>' . NO_GALLERY_IMAGES_TITLE . '</h3><p>' . NO_GALLERY_IMAGES . '</p>';
    }

}

$ic = new Instant_Carousel();
$gallery = $ic->getGalleryHtml();
$title = $ic->getGalleryTitle();
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title><?=$title?></title>
    <script type="text/javascript" src="http://static.local.net/jquery/jquery-1.8.2.min.js"></script>
    <link href="http://static.local.net/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="http://static.local.net/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <?=$gallery?>
    </div>
    <script type="text/javascript">
        $('.carousel').carousel({
            interval: 4000
        })
    </script>
</body>
</html>