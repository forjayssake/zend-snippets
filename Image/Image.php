<?php
namespace Application\Image;

use Zebra_Image;

/**
 * Wrapper for manipulating Zebra Image: https://github.com/stefangabos/Zebra_Image
 *
 * Class Image
 * @package Application\Image
 */
class Image
{
	/**
	 * An instance of Zebra_Image to handle image manipulation 
	 * @var Zebra _Image
	 */
	protected $image;
	
	/**
	 * @var int
	 */
	protected $thumbnailWidth = 200;
	
	/**
	 * @var int
	 */
	protected $thumbnailHeight = 200;
	
	/**
	 * default location for saving thumbnail images
	 * @var string
	 */
	protected $thumbnailLocation = './public/images/thumbnails/';
	
	/**
	 * default location for saving parent images
	 * @var string
	 */
	protected $imageLocation = './public/images/';
	
	/**
	 * actual image file name
	 * @var string
	 */
	protected $actualName;
	
	/**
	 * hashed file name stored to server
	 * @var string
	 */
	protected $storedName;
	
	
	/**
	 * @param string $source
	 * @param string $target
	 */
	public function __construct($source = null, $target = null)
	{
		require_once 'vendor/stefangabos/zebra_image/Zebra_Image.php';
		
		$this->image = new Zebra_Image();
		
		if (!is_null($source)) {
			$this->setSourcePath($source);
		}
		
		if (!is_null($target)) {
			$this->setTargetPath($target);
		}
		
		$this->setPreserveAspectRatio();
		$this->setPreserveTime();
	}
	
	/**
	 * return the stored name value for this image
	 * @return string
	 */
	public function getStoredName()
	{
		return $this->storedName;
	}
	
	/**
	 * return the actual name value for this image
	 * @param bool $excludeExtension
	 * @return string
	 */
	public function getActualName($excludeExtension = false)
	{
		if (!$excludeExtension)
		{
			return $this->actualName;
		} else {
			$extension = SplFileInfo::getExtension($this->actualName);
			return str_replace('.' . $extension, '', $this->actualName);
		}
	}
	
	/**
	 * set the parent image storage location
	 * @param string $location
	 * @return Image
	 */
	public function setImageLocation($location)
	{
		$this->imageLocation = $location;
		return $this;
	}
	
	public function getImageLocation()
	{
		return $this->imageLocation;
	}
	
	/**
	 * set the thumbnail image storage location
	 * @param unknown $location
	 * @return Image
	 */
	public function setImageThumbnailLocation($location)
	{
		$this->thumbnailLocation = $location;
		return $this;
	}
	
	public function getImageThumbnailLocation()
	{
		return $this->thumbnailLocation;
	}
	
	/**
	 * set the source and target variables from a $_FILE array 
	 * @param string A prefix identifier to add to the stored file name
	 * @param array $imageFileArray
	 * @param string $isTHumbnail
	 * @return Image
	 */
	public function setSourceAndTaregtFromFileInfo($prefix, array $imageFileArray = [], $isTHumbnail = true)
	{
		$fileInfo = pathinfo($imageFileArray['name']);
		$this->actualName = $fileInfo['basename'];
		$this->storedName = $prefix . '_' . md5(time() . $fileInfo['filename']) . '.' .$fileInfo['extension'];
		
		$this->setSourcePath($imageFileArray['tmp_name']);
		$this->setTargetPath(($isTHumbnail ? $this->thumbnailLocation : $this->imageLocation) . $this->storedName);
		
		return $this;
	}
	
	/**
	 * copy a file to the image location
	 * @param string $tmpName
	 */
	public function copy($tmpName)
	{
		return copy($tmpName , $this->imageLocation . $this->storedName);
	}
	
	/**
	 * determine whether a file exists with the 
	 * 	$this->stored name for both parent and thumbnail images
	 */
	public function verify()
	{
		$parent = file_exists($this->imageLocation . $this->storedName);
		$thumb = file_exists($this->thumbnailLocation . $this->storedName);
		
		return $parent && $thumb ? true : false;
	}
	
	/**
	 * set the width to resize thumbnail images to 
	 * @param int $width
	 * @return Image
	 */
	public function setThumbnailWidth($width)
	{
		$this->thumbnailWidth = (int)$width;
		return $this;
	}
	
	/**
	 * set the height to resize thumbnail images to
	 * @param int $height
	 * @return \Image
	 */
	public function setThumbnailHeigh($height)
	{
		$this->thumbnailHeight = (int)$height;
		return $this;
	}
	
	/**
	 * set the source path for an image
	 * @param string $path
	 * @return Image
	 */
	public function setSourcePath($path)
	{
		$this->image->source_path = $path;
		return $this;
	}
	
	/**
	 * set the target path for an image
	 * @param string $path
	 * @return Image
	 */
	public function setTargetPath($path)
	{
		$this->image->target_path = $path;
		return $this;
	}
	
	/**
	 * preserve aspect ratio on resize operations
	 * @param bool $preserve
	 * @return Image
	 */
	public function setPreserveAspectRatio($preserve = true)
	{
		$this->image->preserve_aspect_ratio = $preserve;
		return $this;
	}
	
	/**
	 * preserve time
	 * @param bool $preserve
	 * @return Image
	 */
	public function setPreserveTime($preserve = true)
	{
		$this->image->preserve_time = $preserve;
		return $this;
	}
	
	/**
	 * resize an image
	 * @param int $width
	 * @param int $height
	 * @param int $method @see \Zebra_Image.php
	 * 
	 * @return bool|string - true on success or error message on failure 
	 */
	public function resize($width, $height, $method = null)
	{
		$message = null;
		
		if (is_null($method))
			$method = ZEBRA_IMAGE_CROP_CENTER;
			
		$resized = $this->image->resize($width, $height);
		if (!$resized)
		{
			switch ($this->image->error) {
	            case 1:
	                $message = 'Source file could not be found!';
	                break;
	            case 2:
	                $message = 'Source file is not readable!';
	                break;
	            case 3:
	                $message = 'Could not write target file!';
	                break;
	            case 4:
	                $message = 'Unsupported source file format!';
	                break;
	            case 5:
	                $message = 'Unsupported target file format!';
	                break;
	            case 6:
	                $message = 'GD library version does not support target file format!';
	                break;
	            case 7:
	                $message = 'GD library is not installed!';
	                break;
	            default:
	            	$message = 'Failed to resize image - an unknown error occurred';
	            	break;
			}
			
			return $message;
			
		} else {
			return true;
		}
	}
	
	/**
	 * create a thumbnail of this image
	 * @return bool|string - true on success or error message on failure
	 */
	public function createThumbnail()
	{
		return $this->resize($this->thumbnailWidth, $this->thumbnailHeight);
	}
	
}