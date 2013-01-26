<?php
/**
 * Configurable constants
 */

// Title to show if there are no images in the gallery
define('NO_GALLERY_IMAGES_TITLE', 'No photos available');

// Message to show if there are no images in the gallery
define('NO_GALLERY_IMAGES', 'The gallery you are trying to view has no images. Upload some images to this 
    folder first.');

// Title to show if the preview directory (PREVIEW_FOLDER is not writable)
define('PREVIEW_DIR_NOT_WRITABLE_TITLE', 'Unable to generate previews');

// Message to show if the preview directory (PREVIEW_FOLDER is not writable)
define('PREVIEW_DIR_NOT_WRITABLE', 'The preview directory is not writable');

// Default gallery title
define('DEFAULT_GALLERY_TITLE', 'My Gallery');

// Pixel width of images resized for carousel
define('CONVERTED_IMAGE_WIDTH', 512);

// Prefix to prepend to generated previews
define('PREVIEW_PREFIX', 'ic_preview_');

// Folder where generated previews are shown
define('PREVIEW_FOLDER', 'previews');

/* ============================ STOP EDITING ============================ */

/**
 * Instant carousel class
 */
class Instant_Carousel
{
    /**
     * Valid extensions
     * @todo Add BMP support
     */
    protected $_validExtensions = array('jpg', 'jpeg', 'png');

    /**
     * Filenames of generated previews
     */
    private $_generatedPreviews = array();

    /**
     * Class constructor
     */
    public function __construct()
    {
        // Location of the current image directory
        $this->root = dirname(__FILE__);

        /**
         * If we have a file named ic_gallery_title.txt in the same directory as this file,
         * use the contents of that file as the name of the gallery
         */
        $this->_checkGalleryTitleFile();

        // Grab the list of images that previews can be generated for
        $fileList = $this->_getFileList();

        try {
            // Check which files already have previews, 
            // we only need to generate previews for new files
            $previewList = $this->_checkPreviews($fileList);
            $this->_createPreview($previewList);
        } catch (Exception $e) {
            $this->_galleryHtml = $e->getMessage();
        }

    }

    /**
     * Used later when gallery HTML is embedded into the body of the page
     */
    public function getGalleryHtml()
    {
        return $this->_galleryHtml;
    }

    /**
     * Used later when gallery title is embedded into the title tag of the page
     */
    public function getGalleryTitle()
    {
        return $this->_galleryTitle;
    }

    /**
     * Checks if a gallery title file is available (ic_gallery_title.txt)
     *
     * @return string Gallery title file
     */
    private function _checkGalleryTitleFile()
    {
        $gallery_title_file = $this->root . '/ic_gallery_title.txt';
        if (file_exists($gallery_title_file)) {
            $this->_galleryTitle = strip_tags(file_get_contents($gallery_title_file));
        } else {
            $this->_galleryTitle = DEFAULT_GALLERY_TITLE;
        }
    }

    /**
     * Gets list of images in current folder
     *
     * @return array List of files that a preview can be generated for
     */
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

    /**
     * Checks if a file is included in the valid extension
     *
     * @param string $file Filename to check
     * @return bool True if a preview can be generated for the file, false otherwise
     */
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

        preg_match('/'.PREVIEW_PREFIX.'(.+)/i', $file, $matches);
        if (!empty($matches)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if previews have already been generated for the file
     *
     * @param array $fileList List of images available in current folder
     * @return array Files that still need a preview generated
     */
    private function _checkPreviews($fileList)
    {
        if (empty($fileList)) {
            throw new Exception($this->_emptyGallery());
        }

        $forCreation = array();
        
        foreach ($fileList as $fl) {
            $preview = $this->root . DIRECTORY_SEPARATOR . PREVIEW_FOLDER . DIRECTORY_SEPARATOR . PREVIEW_PREFIX . $fl;
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

    /**
     * Creates previews for images in the PREVIEW_FOLDER directory
     *
     * @param array $previewList List of files that need a preview
     */
    private function _createPreview($previewList)
    {
        if (!is_writable($this->root . DIRECTORY_SEPARATOR . PREVIEW_FOLDER)) {
            throw new Exception($this->_previewDirNotWriteable());
        }
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

                    $converted_img_height = (CONVERTED_IMAGE_WIDTH * $imgy) / $imgx;
                    $converted_img = imagecreatetruecolor(CONVERTED_IMAGE_WIDTH, $converted_img_height);
                    imagecopyresampled($converted_img, $src, 0, 0, 0, 0, CONVERTED_IMAGE_WIDTH, 
                        $converted_img_height, $imgx, $imgy);
                    imagejpeg($converted_img, $this->root . DIRECTORY_SEPARATOR . PREVIEW_FOLDER . 
                        DIRECTORY_SEPARATOR . $preview);
                    break;
                case 'png':
                    $src = imagecreatefrompng($path);
                    $imgx = imagesx($src);
                    $imgy = imagesy($src);

                    $converted_img_height = (CONVERTED_IMAGE_WIDTH * $imgy) / $imgx;
                    $converted_img = imagecreatetruecolor(CONVERTED_IMAGE_WIDTH, $converted_img_height);
                    imagecopyresampled($converted_img, $src, 0, 0, 0, 0, CONVERTED_IMAGE_WIDTH, 
                        $converted_img_height, $imgx, $imgy);
                    imagepng($converted_img, $this->root . DIRECTORY_SEPARATOR . PREVIEW_FOLDER . 
                        DIRECTORY_SEPARATOR . $preview);
                    break;
            }
        }
        $this->_generatedPreviews[] = $this->_getPreviewsList();
        $this->_buildGallery();
    }

    /**
     * Get a list of files that have previews
     *
     * @return array List of files in the PREVIEW_FOLDER directory
     */
    private function _getPreviewsList()
    {
        $previews = array();

        if ($handle = opendir($this->root . DIRECTORY_SEPARATOR . PREVIEW_FOLDER)) {
            while (false !== ($entry = readdir($handle))) {
                if (substr($entry, 0, 11) == PREVIEW_PREFIX) {
                    $previews[] = substr($entry, 11);
                }
            }
            closedir($handle);
        }
        $this->_generatedPreviews = $previews;
    }

    /**
     * Generates HTML required to show the carousel in a way understood by Twitter Bootstrap
     */
    private function _buildGallery()
    {
        $gallery = '<h3>' . $this->_galleryTitle . '</h3>';
        $gallery .= '<div id="ic_gallery" class="carousel slide"><div class="carousel-inner">';
        $count = 0;
        foreach ($this->_generatedPreviews as $gp) {
            if (file_exists(PREVIEW_FOLDER . '/' . PREVIEW_PREFIX . $gp)) {
                $gallery .= ($count < 1) ? '<div class="active item"><img src="' . 
                    PREVIEW_FOLDER . '/' . PREVIEW_PREFIX . $gp . '"></div>' : '<div class="item"><img src="'. 
                    PREVIEW_FOLDER . '/' . PREVIEW_PREFIX . $gp . '"></div>';
                $count++;
            }
        }
        $gallery .= '</div></div>';
        $gallery .= '<a class="carousel-control left" href="#ic_gallery" data-slide="prev">&lsaquo;</a>
  <a class="carousel-control right" href="#ic_gallery" data-slide="next">&rsaquo;</a>';
        $this->_galleryHtml = $gallery;
    }

    /**
     * Generates an HTML string that shows an empty gallery message
     */
    private function _emptyGallery()
    {
        return '<h3>' . NO_GALLERY_IMAGES_TITLE . '</h3><p>' . NO_GALLERY_IMAGES . '</p>';
    }

    /**
     * Generates an HTML string that shows a preview directory not writable message
     */
    private function _previewDirNotWriteable()
    {
        $message = '<h3>' . PREVIEW_DIR_NOT_WRITABLE_TITLE . '</h3><p>' . PREVIEW_DIR_NOT_WRITABLE . '</p>';
        $message .= '<p>Please make sure the path set for PREVIEW_FOLDER (' . $this->root . DIRECTORY_SEPARATOR . 
            PREVIEW_FOLDER . ') is correct and that the correct permissions are set for the said folder.</p>';
        return $message;
    }

}

// Instantiate the class, generate previews (if necessary) and get/build the necessary HTML
$ic = new Instant_Carousel();
$gallery = $ic->getGalleryHtml();
$title = $ic->getGalleryTitle();
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title><?=$title?></title>
    <script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.1.1/css/bootstrap-combined.min.css" rel="stylesheet">
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.1.1/js/bootstrap.min.js"></script>
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