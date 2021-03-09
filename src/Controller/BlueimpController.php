<?php
namespace App\Controller;

use App\Form\AlbumFormType;
use App\Entity\Meta;
use App\Form\MetaType;
use App\Entity\Pictures;
use App\Form\UserType;
use App\Repository\PicturesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\PictureHelper;

/**
 * Extra js options can be found in src/Klik/MetaBundle/Resources/public/js/blueimp/jquery.fileupload-ui.js
 *
 * @author bart
 *
 */

/**
 * Controller used for photo album.
 *
 * @Route("/album")
 * @IsGranted("ROLE_USER")
 *
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
class BlueimpController extends AbstractController
{
    protected $options;
    // PHP File Upload error message codes:
    // http://php.net/manual/en/features.file-upload.errors.php
    protected $error_messages = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'De foto is te groot qua Kb',
        'min_file_size' => 'De foto is te klein qua Kb',
        'accept_file_types' => 'Ongeldig bestandstype, alleen jpg, gif en png is toegestaan',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'max_width' => 'De foto is te breed',
        'min_width' => 'De foto is te smal. De foto heeft een minimale breedte van 400 pixels nodig',
        'max_height' => 'De foto is te hoog',
        'min_height' => 'De foto is niet hoog genoeg. De foto heeft een minimale hoogte van 300 pixels nodig'
    );

    //public $image_folder;
    public $upload_dir;

    public $userId;
    public $file_status = array
    (
        '1' => 'Inladen gelukt, wacht op keuring',
        '0' => 'Goedgekeurd',
        '2' => 'Afgekeurd'
    );

    public $em;
    public $seo_file_name = "beste_dating";

    public $setMain;

    /*public function __construct(EntityManagerInterface $entityManager)
    {
        //$this->em = $this->getDoctrine()->getManager();
        $PictureHelper = new PictureHelper();
        //$user = $this->getUser();
        //$this->userId = $user->getId();
        $PictureHelper->setUploadPathFromUser(4);
        $this->upload_path = $PictureHelper->upload_path;
    }*/
    public function getUploadPath()
    {
        $this->em = $this->getDoctrine()->getManager();

        $PictureHelper = new PictureHelper($this->em, $this->getUser()->getId());
        //$PictureHelper->setEntityManager($this->em);
        //$PictureHelper->setUploadPathFromUser($this->getUser()->getId());

        return $PictureHelper->upload_path;
    }

    public function getUploadFullDir()
    {
       return $_SERVER['DOCUMENT_ROOT'] . $this->getUploadPath();
    }

    public function setOptions()
    {
        $options = null;
        $initialize = true;
        $this->em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $this->userId = $user->getId();

        $this->upload_path = $this->getUploadPath();

        $this->upload_dir = $this->getUploadFullDir();

        $subfolders = ['thumbnail', 'mediumnail', 'bignail'];

        if(!is_dir($this->upload_dir))
        {
            mkdir($this->upload_dir);
        }

        foreach($subfolders as $subfolder)
        {
            if(!is_dir($this->upload_dir . $subfolder. '/' ))
            {
                mkdir($this->upload_dir . $subfolder. '/');
            }
        }


        $this->response = array();
        $fu = $this->get_full_url().'/';
        $this->options = array(
            'script_url' => '/nl/album/',
            'upload_dir' => $this->upload_dir,
            //'upload_url' => $this->get_full_url().'/files/',
            'upload_path' => $this->upload_path,
            'user_dirs' => false,
            'mkdir_mode' => 0755,
            'param_name' => 'files',
            //'param_name' => 'fotos',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'delete_type' => 'DELETE',
            'access_control_allow_origin' => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods' => array(
                'OPTIONS',
                'HEAD',
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE',
                'CHANGE_THE_MAIN_IMAGE'
            ),
            'access_control_allow_headers' => array(
                'Content-Type',
                'Content-Range',
                'Content-Disposition'
            ),
            // Enable to provide file downloads via GET requests to the PHP script:
            'download_via_php' => false,
            // Defines which files can be displayed inline when downloaded:
            'inline_file_types' => '/\.(gif|jpe?g|png)$/i',
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types' => '/.+$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 200,
            'min_height' => 200,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to true to rotate images based on EXIF meta data, if available:
            'orient_image' => false,
            //'autoUpload' => true,
            'image_versions' => array(
                // Uncomment the following version to restrict the size of
                // uploaded images:

                '' => array(
                    'max_width' => 600,
                    'max_height' => 600,
                    'jpeg_quality' => 95
                ),
                'bignail' => array(
                    'max_width' => 300,
                    'max_height' => 300,
                    'jpeg_quality' => 95,
                    'crop' => true
                ),

                'mediumnail' => array(
                    'max_width' => 150,
                    'max_height' => 150,
                    'jpeg_quality' => 95,
                    'crop' => true
                ),
                'thumbnail' => array(
                    'max_width' => 75,
                    'max_height' => 75,
                    'jpeg_quality' => 95,
                    'crop' => true
                )
            )
        );
    }



    /**
     * @Route("/upload", methods={"GET", "POST"}, name="album_upload")
     */
    function uploadAction(Request $request): Response
    {
        $this->setOptions();

        return new JsonResponse($this->initializeAction());
    }

    //protected function initialize() {
    protected function initializeAction()
    {

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
            case 'HEAD':
                $this->head();
                break;
            case 'GET':
                return $this->getImages();
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                //$this->post();
                return $this->post();
                break;
            case 'DELETE':
                $this->delete();
                break;
            case 'CHANGE_THE_MAIN_IMAGE':
                $this->change_the_main_image();
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
    }

    protected function get_full_url() {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        return
            ($https ? 'https://' : 'http://').
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
                ($https && $_SERVER['SERVER_PORT'] === 443 ||
                $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function get_user_id() {
        @session_start();
        return session_id();
    }

    protected function get_user_path() {
        if ($this->options['user_dirs']) {
            return $this->get_user_id().'/';
        }
        return '';
    }

    protected function get_upload_path($file_name = null, $version = null)
    {
        $file_name = $file_name ? $file_name : '';
        $version_path = empty($version) ? '' : $version.'/';

        $this->options['upload_dir'] = $this->getUploadFullDir();

        return $this->options['upload_dir'].$this->get_user_path().$version_path.$file_name;
    }

    protected function get_query_separator($url) {
        return strpos($url, '?') === false ? '?' : '&';
    }

    protected function get_download_url($file_name, $version = null) {
        if ($this->options['download_via_php']) {
            $url = $this->options['script_url']
                .$this->get_query_separator($this->options['script_url'])
                .'file='.rawurlencode($file_name);
            if ($version) {
                $url .= '&version='.rawurlencode($version);
            }
            return $url.'&download=1';
        }
        $version_path = empty($version) ? '' : rawurlencode($version).'/';
        return $this->options['upload_path'].$this->get_user_path()
        .$version_path.rawurlencode($file_name);
    }

    /**
     * properties of newly uploaded file (before page refresh)
     * we added "delete" url parameter so it inetgrates nicely whit symfony
     *
     * @param unknown $file
     */
    protected function set_file_delete_properties($file)
    {
        $file->deleteUrl = $this->options['script_url']
            .'delete'
            .$this->get_query_separator($this->options['script_url'])
            .'file='.rawurlencode($file->name);

        $file->deleteType = $this->options['delete_type'];

        if ($file->deleteType !== 'DELETE')
        {
            $file->deleteUrl .= '&_method=DELETE';
        }
        if ($this->options['access_control_allow_credentials'])
        {
            $file->delete_with_credentials = true;
        }
    }

    /**
     * New function for setting metapic link
     * @param unknown $file
     */
    protected function set_file_metapic_properties($file)
    {
        $file->mainimageUrl = $this->options['script_url']
            .'mainimage'
            .$this->get_query_separator($this->options['script_url'])
            .'file='.rawurlencode($file->name);

        // Set mainfoto if none exists
        //$file->mainfoto = 1;
        if($this->main_exists())
        {
            //$file->mainfoto = 0;
        }


    }

    // Fix for overflowing signed 32 bit integers,
    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
    protected function fix_integer_overflow($size) {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }

    protected function get_file_size($file_path, $clear_stat_cache = false) {
        if ($clear_stat_cache) {
            clearstatcache(true, $file_path);
        }
        return $this->fix_integer_overflow(filesize($file_path));

    }

    protected function is_valid_file_object($file_name) {
        $file_path = $this->get_upload_path($file_name);
        if (is_file($file_path) && $file_name[0] !== '.') {
            return true;
        }
        return false;
    }

    protected function get_file_object($file_name)
    {
        if ($this->is_valid_file_object($file_name))
        {
            $file = new \stdClass();
            $file->name = $file_name;
            $file->size = $this->get_file_size(
                $this->get_upload_path($file_name)
            );
            $file->url = $this->get_download_url($file->name);
            foreach($this->options['image_versions'] as $version => $options) {
                if (!empty($version)) {
                    if (is_file($this->get_upload_path($file_name, $version))) {
                        $file->{$version.'_url'} = $this->get_download_url(
                            $file->name,
                            $version
                        );
                    }
                }
            }
            $this->set_file_delete_properties($file);
            // Newly added, do we realy need it here, see line 718 ?
            //$this->set_file_metapic_properties($file);

            return $file;
        }
        return null;
    }

    protected function get_file_objects($iteration_method = 'get_file_object')
    {
        $upload_dir = $this->get_upload_path();

        if (!is_dir($upload_dir))
        {
            return array();
        }

        // Instead of dir scan read from db
        $user = $this->getUser();
        $PictureHelper = new PictureHelper($this->em, $user->getId());
        //$PictureHelper->setEntityManager($this->em);
        //$PictureHelper->setUploadPathFromUser($user->getId());

        $pictures = $PictureHelper->getPicturesAll();

        return $pictures;
        //return new JsonResponse($uploads);
        /* return array_values(array_filter(array_map(
         array($this, $iteration_method),
         scandir($upload_dir)
        ))); */
    }

    protected function count_file_objects()
    {
        return count($this->get_file_objects('is_valid_file_object'));
    }

    protected function create_scaled_image($file_name, $version, $options) {
        $file_path = $this->get_upload_path($file_name);
        if (!empty($version)) {
            $version_dir = $this->get_upload_path(null, $version);
            if (!is_dir($version_dir)) {
                mkdir($version_dir, $this->options['mkdir_mode'], true);
            }
            $new_file_path = $version_dir.'/'.$file_name;
        } else {
            $new_file_path = $file_path;
        }
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }
        $max_width = $options['max_width'];
        $max_height = $options['max_height'];
        $scale = min(
            $options['max_width'] / $img_width,
            $options['max_height'] / $img_height,
            $max_width / $img_width,
            $max_height / $img_height
        );
        if ($scale >= 1) {
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }
        /* $new_width = $img_width * $scale;
         $new_height = $img_height * $scale;
         $new_img = @imagecreatetruecolor($new_width, $new_height); */
        if (empty($options['crop'])) {
            $new_width = $img_width * $scale;
            $new_height = $img_height * $scale;
            $dst_x = 0;
            $dst_y = 0;
            $new_img = @imagecreatetruecolor($new_width, $new_height);
        } else {
            if (($img_width / $img_height) >= ($max_width / $max_height)) {
                $new_width = $img_width / ($img_height / $max_height);
                $new_height = $max_height;
            } else {
                $new_width = $max_width;
                $new_height = $img_height / ($img_width / $max_width);
            }
            $dst_x = 0 - ($new_width - $max_width) / 2;
            $dst_y = 0 - ($new_height - $max_height) / 2;
            $new_img = @imagecreatetruecolor($max_width, $max_height);
        }

        switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ?
                    $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ?
                    $options['png_quality'] : 9;
                break;
            default:
                $src_img = null;
        }
        $success = $src_img && @imagecopyresampled(
                $new_img,
                $src_img,
                // 0, 0, 0, 0,
                $dst_x,
                $dst_y,
                0,
                0,
                $new_width,
                $new_height,
                $img_width,
                $img_height
            ) && $write_image($new_img, $new_file_path, $image_quality);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }

    protected function get_error_message($error) {
        return array_key_exists($error, $this->error_messages) ?
            $this->error_messages[$error] : $error;
    }

    function get_config_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $this->fix_integer_overflow($val);
    }

    protected function validate($uploaded_file, $file, $error, $index) {
        if ($error) {
            $file->error = $this->get_error_message($error);
            return false;
        }
        $content_length = $this->fix_integer_overflow(intval($_SERVER['CONTENT_LENGTH']));

        //$post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
        // ugly hack
        $post_max_size = 99000000;

        if ($post_max_size && ($content_length > $post_max_size)) {
            $file->error = $this->get_error_message('post_max_size');
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->get_error_message('accept_file_types');
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = $this->get_file_size($uploaded_file);
        } else {
            $file_size = $content_length;
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
        ) {
            $file->error = $this->get_error_message('max_file_size');
            return false;
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            $file->error = $this->get_error_message('min_file_size');
            return false;
        }
        if (is_int($this->options['max_number_of_files']) && (
                $this->count_file_objects() >= $this->options['max_number_of_files'])
        ) {
            $file->error = $this->get_error_message('max_number_of_files');
            return false;
        }
        list($img_width, $img_height) = @getimagesize($uploaded_file);
        if (is_int($img_width)) {
            if ($this->options['max_width'] && $img_width > $this->options['max_width']) {
                $file->error = $this->get_error_message('max_width');
                return false;
            }
            if ($this->options['max_height'] && $img_height > $this->options['max_height']) {
                $file->error = $this->get_error_message('max_height');
                return false;
            }
            if ($this->options['min_width'] && $img_width < $this->options['min_width']) {
                $file->error = $this->get_error_message('min_width');
                return false;
            }
            if ($this->options['min_height'] && $img_height < $this->options['min_height']) {
                $file->error = $this->get_error_message('min_height');
                return false;
            }
        }
        return true;
    }

    protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function get_unique_filename($name, $type, $index, $content_range) {
        while(is_dir($this->get_upload_path($name))) {
            $name = $this->upcount_name($name);
        }

        // Keep an existing filename if this is part of a chunked upload:
        // Gave an error, we are not using chunked upload anyway
       /* $uploaded_bytes = $this->fix_integer_overflow(intval($content_range[1]));
        while(is_file($this->get_upload_path($name))) {
            if ($uploaded_bytes === $this->get_file_size(
                    $this->get_upload_path($name))) {
                break;
            }
            $name = $this->upcount_name($name);
        }*/
        return $name;
    }

    protected function trim_file_name($name, $type, $index, $content_range) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Use a timestamp for empty filenames:
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        // Add missing file extension for known image types:
        if (strpos($name, '.') === false &&
            preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $name .= '.'.$matches[1];
        }
        return $name;
    }

    protected function get_file_name($name, $type, $index, $content_range) {
        return $this->get_unique_filename(
            $this->trim_file_name($name, $type, $index, $content_range),
            $type,
            $index,
            $content_range
        );
    }

    protected function handle_form_data($file, $index) {
        // Handle form data, e.g. $_REQUEST['description'][$index]
    }

    protected function orient_image($file_path) {
        if (!function_exists('exif_read_data')) {
            return false;
        }
        $exif = @exif_read_data($file_path);
        if ($exif === false) {
            return false;
        }
        $orientation = intval(@$exif['Orientation']);
        if (!in_array($orientation, array(3, 6, 8))) {
            return false;
        }
        $image = @imagecreatefromjpeg($file_path);
        switch ($orientation) {
            case 3:
                $image = @imagerotate($image, 180, 0);
                break;
            case 6:
                $image = @imagerotate($image, 270, 0);
                break;
            case 8:
                $image = @imagerotate($image, 90, 0);
                break;
            default:
                return false;
        }
        $success = imagejpeg($image, $file_path);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($image);
        return $success;
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null) {

        $file = new \stdClass();

        $rand = rand(10000, 99999);
        $randstring = [];
        $randstring[4] = substr(md5($rand), 17, 4);
        $filenameBase = $randstring[4].'_'.$this->seo_file_name;

        //$file->name = $this->get_file_name(substr(md5(uniqid()), 0, 8), $type, $index, $content_range);
        $file->name = $this->get_file_name($filenameBase, $type, $index, $content_range);
        if(strpos($file->name, '.') === false)
        {
            // Made some fixes for IE explorer to work
            $name_raw = strtok($name, '.');
            $extension = strtok('.');
            $file->name = $file->name . $extension;
        }

        // Debugging purposes
        //list($img_width, $img_height) = @getimagesize($uploaded_file);

        $file->size = $this->fix_integer_overflow(intval($size));
        $file->type = $type;
        $file->status = $this->file_status[1];

        if ($this->validate($uploaded_file, $file, $error, $index)) {
            $this->handle_form_data($file, $index);
            $upload_dir = $this->get_upload_path();
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, $this->options['mkdir_mode'], true);
            }
            $file_path = $this->get_upload_path($file->name);
            $append_file = $content_range && is_file($file_path) && $file->size > $this->get_file_size($file_path);
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );
                } else
                {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = $this->get_file_size($file_path, $append_file);

            $dimensions = getimagesize($file_path);

            if ($file_size === $file->size) {
                if ($this->options['orient_image']) {
                    $this->orient_image($file_path);
                }
                $file->url = $this->get_download_url($file->name);
                foreach($this->options['image_versions'] as $version => $options) {
                    if ($this->create_scaled_image($file->name, $version, $options)) {
                        if (!empty($version)) {
                            $file->{$version.'_url'} = $this->get_download_url(
                                $file->name,
                                $version
                            );
                        } else {
                            $file_size = $this->get_file_size($file_path, true);
                        }
                    }
                }
            } else if (!$content_range && $this->options['discard_aborted_uploads']) {
                unlink($file_path);
                $file->error = 'abort';
            }
            $file->size = $file_size;
            $file->width = $dimensions[0];
            $file->height = $dimensions[1];

            $file->thumbnailUrl = $this->options['upload_path'].'/thumbnail/'. $file->name;
            $file->mediumnailUrl = $this->options['upload_path'].'/mediumnail/'. $file->name;

            $file->upload_to_db = $this->add_img($file);
            $file->mainfoto = $this->setMain;
            $this->set_file_delete_properties($file);

            //newly added
            $this->set_file_metapic_properties($file);
        }
        //var_dump($file); exit;

        return $file;
    }

    function add_img($file) {

        $user = $this->getUser();

        $pictures = new Pictures();
        $pictures->setUser($user);

        $extension = strtolower(substr(strrchr($file->name, '.'), 1));
        //$fotos->setNaam($randstring[3].'_'.$this->seo_file_name.'.'.$extension);
        $pictures->setNaam($file->name);
        //$main_exists = $this->em->getRepository(Pictures::class)->findoneBy(array('user' => $this->userId, 'mainfoto'=>1));
        $this->setMain = $this->main_exists()? 0 : 1;

        $pictures->setMainFoto($this->setMain );


        // Save the object
        $this->em->persist($pictures);
        $this->em->flush();

        return true;

    }

    protected function readfile($file_path) {
        return readfile($file_path);
    }

    protected function body($str) {
        echo $str;
    }

    protected function header($str) {
        header($str);
    }

    protected function generate_response($content, $print_response = true) {
        if ($print_response) {
            $json = json_encode($content);
            $redirect = isset($_REQUEST['redirect']) ?
                stripslashes($_REQUEST['redirect']) : null;
            if ($redirect) {
                // Comment this part to get IE working for resultset (show thumb after uploading)
                //$this->header('Location: '.sprintf($redirect, rawurlencode($json)) );
                //return;
            }
            $this->head();
            if (isset($_SERVER['HTTP_CONTENT_RANGE'])) {
                $files = isset($content[$this->options['param_name']]) ?
                    $content[$this->options['param_name']] : null;
                if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
                    $this->header('Range: 0-'.($this->fix_integer_overflow(intval($files[0]->size)) - 1));
                }
            }
            $this->body($json);
        }

        return $content;
        //return new JosnResponse($content);
    }

    protected function get_version_param() {
        return isset($_GET['version']) ? basename(stripslashes($_GET['version'])) : null;
    }

    protected function get_file_name_param() {
        return isset($_GET['file']) ? basename(stripslashes($_GET['file'])) : null;
    }

    protected function get_file_type($file_path) {
        switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return '';
        }
    }

    protected function download() {
        if (!$this->options['download_via_php']) {
            $this->header('HTTP/1.1 403 Forbidden');
            return;
        }
        $file_name = $this->get_file_name_param();
        if ($this->is_valid_file_object($file_name)) {
            $file_path = $this->get_upload_path($file_name, $this->get_version_param());
            if (is_file($file_path)) {
                if (!preg_match($this->options['inline_file_types'], $file_name)) {
                    $this->header('Content-Description: File Transfer');
                    $this->header('Content-Type: application/octet-stream');
                    $this->header('Content-Disposition: attachment; filename="'.$file_name.'"');
                    $this->header('Content-Transfer-Encoding: binary');
                } else {
                    // Prevent Internet Explorer from MIME-sniffing the content-type:
                    $this->header('X-Content-Type-Options: nosniff');
                    $this->header('Content-Type: '.$this->get_file_type($file_path));
                    $this->header('Content-Disposition: inline; filename="'.$file_name.'"');
                }
                $this->header('Content-Length: '.$this->get_file_size($file_path));
                $this->header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime($file_path)));
                $this->readfile($file_path);
            }
        }
    }

    protected function send_content_type_header() {
        $this->header('Vary: Accept');
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    protected function send_access_control_headers() {
        $this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: '
            .($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: '
            .implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: '
            .implode(', ', $this->options['access_control_allow_headers']));
    }

    public function head() {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        // Prevent Internet Explorer from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->send_access_control_headers();
        }
        $this->send_content_type_header();
    }

    public function getImages($print_response = true) {
        if ($print_response && isset($_GET['download']))
        {
            return $this->download();
        }
        $file_name = $this->get_file_name_param();

        if ($file_name)
        {
            $response = array(
                substr($this->options['param_name'], 0, -1) => $this->get_file_object($file_name)
            );
        }
        else
        {
            $response = array(
                $this->options['param_name'] => $this->get_file_objects()
            );
        }
        //return $this->generate_response($response, $print_response);
        return $response;
        //return new JsonResponse($this->generate_response($response, $print_response));

        // new
        //$uploads = $this->query_db();

        // head checks for right content-type
        //$this->head();

        // echo htmlentities(json_encode($uploads));
    }

    public function query_db(PictureHelper $pictureHelper)
    {
        $uploads_array = array();

        $user = $this->getUser();

        $metaObject = $this->getDoctrine()
            ->getRepository(Meta::class)
            ->findOneBy(['user' => $user->getId()]);

        $fotos = $pictureHelper->getPicturesAll();

        foreach($fotos as $foto)
        {
            $file = new \stdClass();

            $file->id = $foto->getId() ;
            $file->name = $foto->getNaam();
            $file->size = 100;
            $file->mainfoto = $foto->getMainFoto();
            //$file->mainfoto = 1;

            $file->status = 1;

            $file->url = $this->options['upload_path'].$foto->getNaam();
            $file->thumbnailUrl = $this->options['upload_path'].'/thumbnail/'.$foto->getNaam();
            $file->mediumnailUrl = $this->options['upload_path'].'/mediumnail/'.$foto->getNaam();
            $file->deleteUrl = "/nl/album/delete?file=".$foto->getNaam();
            $file->mainimageUrl = "/nl/album/mainimage?file=".$foto->getNaam();
            $file->deleteType = "DELETE";
            array_push($uploads_array, $file);
        }

        return array('files' => $uploads_array);
    }

    /**
     * methods={"GET", "POST"}
     *
     * @param bool $print_response
     * @return array
     */
    public function post($print_response = true)
    {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete($print_response);
        }

        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : null;
        // Parse the Content-Disposition header, if available:
        $file_name = isset($_SERVER['HTTP_CONTENT_DISPOSITION']) ?
            rawurldecode(preg_replace(
                '/(^[^"]+")|("$)/',
                '',
                $_SERVER['HTTP_CONTENT_DISPOSITION']
            )) : null;
        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $content_range = isset($_SERVER['HTTP_CONTENT_RANGE']) ?
            preg_split('/[^0-9]+/', $_SERVER['HTTP_CONTENT_RANGE']) : null;
        $size =  $content_range ? $content_range[3] : null;
        $files = array();
        if ($upload && is_array($upload['tmp_name'])) {
            // param_name is an array identifier like "files[]",
            // $_FILES is a multi-dimensional array:
            foreach ($upload['tmp_name'] as $index => $value) {
                $files[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    $file_name ? $file_name : $upload['name'][$index],
                    $size ? $size : $upload['size'][$index],
                    $upload['type'][$index],
                    $upload['error'][$index],
                    $index,
                    $content_range
                );
            }
        } else {
            // param_name is a single object identifier like "file",
            // $_FILES is a one-dimensional array:

            $files[] = $this->handle_file_upload(
                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                $file_name ? $file_name : (isset($upload['name']) ?
                    $upload['name'] : null),
                $size ? $size : (isset($upload['size']) ?
                    $upload['size'] : $_SERVER['CONTENT_LENGTH']),
                isset($upload['type']) ?
                    $upload['type'] : $_SERVER['CONTENT_TYPE'],
                isset($upload['error']) ? $upload['error'] : null,
                null,
                $content_range
            );
        }

        return array($this->options['param_name'] => $files);
    }

    /**
     * @Route("/delete")
     * @IsGranted("ROLE_USER")
     *
     * @return JsonResponse
     */
    public function deleteAction()
    {
        $this->em = $this->getDoctrine()->getManager();

        $this->setOptions();

        $file_name = $this->get_file_name_param();
        $file_path = $this->get_upload_path($file_name);

        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);

        if ($success)
        {
            foreach($this->options['image_versions'] as $version => $options) {
                if (!empty($version)) {
                    $file = $this->get_upload_path($file_name, $version);
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
        $success = $this->delete_img($file_name);
        //$this->delete_img($file->id);

        return new JsonResponse(array('success' => $success));
        //return $this->get($print_response);
    }

    function main_exists()
    {
       $main_exists =  $this->em->getRepository(Pictures::class)->findoneBy(array('user' => $this->userId, 'mainfoto'=>1));

       return $main_exists;
    }

    // Delete file from database
    function delete_img($file_name)
    {

        $foto = $this->em->getRepository(Pictures::class)->findOneBy(array('naam' => $file_name));

        if (!$foto)
        {
            throw $this->createNotFoundException('No foto found for naam '.$file_name);
        }

        $this->em->remove($foto);
        $this->em->flush();

        if($foto->getMainFoto() == 1) {
            $this->make_main_image_if_none();
        }

        return true;
    }

    /**
     *
     * @Route("/mainimage")
     * @IsGranted("ROLE_USER")
     *
     * @param bool $print_response
     * @return JsonResponse
     */
    public function change_the_main_image($print_response = true)
    {

        $this->em = $this->getDoctrine()->getManager();
        $file_name = $this->get_file_name_param();

        $user = $this->getUser();
        $userId = $user->getId();

        $fotos = $this->em->getRepository(Pictures::class)->findBy(array('user' => $userId));

        foreach ($fotos as $foto)
        {
            $fname = $foto->getNaam();

            if($fname == $file_name)
            {
                $foto->setMainFoto(1);
            }
            else
            {
                $foto->setMainFoto(0);
            }
            $this->em->persist($foto);
            $this->em->flush();
        }

        return new JsonResponse(array('success' => true));
    }

    public function make_main_image_if_none()
    {
        $this->em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $userId = $user->getId();

        $fotos = $this->em->getRepository(Pictures::class)->findBy(
            array('user' => $userId),
            array('id' => 'ASC')
        );
        if(!empty($fotos))
        {
            $fotos[0]->setMainFoto(1);
            $this->em->persist($fotos[0]);
            $this->em->flush();
        }

    }



}

?>
