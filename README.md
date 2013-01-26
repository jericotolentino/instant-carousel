Instant Carousel is a small PHP script that generates the appropriate HTML and image previews for a set of images in a directory instead of just showing an image listing. It's great for generating a quick page to show your photos without having to write HTML.

![Sulfur deposits en route to Mt. Pinatubo](https://raw.github.com/jericotolentino/instant-carousel/master/sulfur.png "Sulfur deposits en route to Mt. Pinatubo")

It works by scanning the directory for images (currently supports only JPG and PNG), then creates previews for each image. By default, it resizes images to 512px, but this can be changed in the file. Other configurable options include:

- NO_GALLERY_IMAGES_TITLE : Title of page if no images were found (or not readable) in the directory
- NO_GALLERY_IMAGES : Message in page if no images were found (or not readable) in the directory
- PREVIEW_DIR_NOT_WRITABLE_TITLE : Title of page if the directory where previews are written to is not writable
- PREVIEW_DIR_NOT_WRITABLE : Message in page if the directory where previews are written to is not writable
- DEFAULT_GALLERY_TITLE : Default gallery title
- CONVERTED_IMAGE_WIDTH : Controls width of generated preview
- PREVIEW_PREFIX : Sets a prefix for preview filenames
- PREVIEW_FOLDER : Path of preview folder relative to the current directory

Installation

1. Upload your photos to your web site (CPanel, FTP, SSH, etc). For example, you can have a beach-vacation folder
under an images folder in your site which is accessed through http://www.mysite.com/images/beach-vacation.
2. If desired, edit the configurable constants of Instant Carousel in the index.php file
3. Upload the index.php file to the same directory as your photos
4. Create a directory that is writable to the web server for the previews. The name of the directory must be the same
as the value of the PREVIEW_FOLDER constant in index.php
5. Visit the folder you just created from your browser, access http://www.mysite.com/images/beach-vacation. It should
show a carousel-like interface with the photos changing every two seconds.

Dependencies

Instant Carousel is dependent on jQuery and Twitter Bootstrap. Both are loaded by a CDN.