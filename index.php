<?php

class Instant_Carousel
{
    protected $_validExtensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp');

    public function __construct()
    {
        $this->root = dirname(__FILE__);
        $fileList = $this->_getFileList();
        $previewList = $this->_checkPreviews($fileList);
        $this->_createPreview($previewList);
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

            if (!empty($files)) {
                return $files;
            }
        }
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

        preg_match('/ic_preview_(.+)/', $file, $matches);
        if (!empty($matches)) {
            return false;
        }

        return true;
    }

    private function _checkPreviews($fileList)
    {
        $forCreation = array();
        
        foreach ($fileList as $fl) {
            $preview = 'ic_preview_' . $fl;
            if (!file_exists($preview)) {
                $forCreation[] = $fl;
            }
        }
        return $forCreation;
    }

    private function _createPreview($previewList)
    {
        foreach ($previewList as $pl) {
            echo '<pre>' . $pl . '</pre>';
        }
    }

}

$ic = new Instant_Carousel();