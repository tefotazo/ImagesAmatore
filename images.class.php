<?php

/**
 * 
 * @Author: Estefano Salazar
 * @Email: me@estefanosalazar.com
 * @Date: 03/12/2012
 * @Description: Class to get image information and change size.
 * @Version: 0.5b
 * 
 * 0.1 changelog: the class was created with the basic functionallity.
 * 0.2 changelog: the priavte vars that had the types, was deleted.
 * 0.3 changelog: added the crop image
 * 0.4 changelog: added download image and work with image downloaded
 * 0.5 changelog: added methods to crop automatically images
 * 
 */

class ImageAmatore
{
	private $_imageFileName;
	private $_image;
	private $_thumbImage;
	
	/**
	 * ['width'] ['height'] ['channels'] ['bits'] ['type']
	 */
	private $_imageInformation;
	
	public static $CROP_BOTTOM_LEFT = 1;
	public static $CROP_BOTTOM_RIGHT = 2;
	public static $CROP_TOP_LEFT = 3;
	public static $CROP_TOP_RIGHT = 4;
	
	public static $QUALITY_LOW = 1;
	public static $QUALITY_MEDIUM = 2;
	public static $QUALITY_HIGH = 3;
	
	/**
	 * @param $imageFileName: The name of the image to change (with the full address to the file)
	 */ 
	public function ImageAmatore( $imageFileName, $directoryToSave = "./" )
	{
		if( is_string( $imageFileName ) && preg_match( "/^[(http:\/\/)|(https:\/\/)|(ftp:\/\/)]/", $imageFileName ) && is_string( $directoryToSave ) && is_dir( $directoryToSave ) )
		{
			$imageFileName = $this->saveImageFromURL( $imageFileName, $directoryToSave );
		}
		else
		{
			if( !is_string( $imageFileName ) || !file_exists( $imageFileName ) )
				throw new Exception( "The filename is incorrect. " . $imageFileName );
		}
		
		$this->_imageFileName = $imageFileName;
		
		$this->saveImageInformation( $imageFileName );
		$this->createImage();
	}
	
	/**
	 * Method added in 0.4
	 * 
	 * @param $url: the url of the image file
	 * @param $directoryToSave: directory to save the image file
	 * 
	 * Download an online image and return the filename
	 */
	private function saveImageFromURL( $url, $directoryToSave )
	{
		if( !@$onlineFile = file_get_contents( $url ) )
			throw new Exception( "The URL have no data" );
		
		if( trim( $onlineFile ) == "" )
			throw new Exception( "The online file has no data" );
		
		$fileName = end( explode( "/", $url ) );
		@chmod( $directoryToSave, 777 );
		file_put_contents( $directoryToSave . "/" . $fileName, $onlineFile );
		return $directoryToSave . "/" . $fileName;
	}
	
	/**
	 * Create the image depending of the type
	 */
	private function createImage()
	{
		switch( $this->_imageInformation['type'] )
		{
			case IMAGETYPE_JPEG:
				$this->_image = imagecreatefromjpeg( $this->_imageFileName );
			break;
			
			case IMAGETYPE_GIF:
				$this->_image = imagecreatefromgif( $this->_imageFileName );
			break;
			
			case IMAGETYPE_PNG:
				$this->_image = imagecreatefrompng( $this->_imageFileName );
			break;
			
			default:
				throw new Exception( "The image is not a recognized type" );
			break;
		}
	}
	
	/**
	 * @param $width: the new width of the image
	 * @param $height: the new height of the image
	 * Parameters added in 0.3
	 * @param $sX: The x value for the image position in the new image size
	 * @param $sY: The x value for the image position in the new image size
	 * @param $imageWidth: the image width to crop. This size is to crop with "more image"
	 * @param $imageHeight: the image height to crop. This size is to crop with "more image"
	 * 
	 * Create a new image with the width and height passed from parameters
	 */
	private function resizeImage( $width, $height )
	{
		if( $width > $this->_imageInformation['width'] || $height > $this->_imageInformation['height'] )
			throw new Exception( "The new image size is greater than the actual image size" );
		
		$newImage = imagecreatetruecolor( $width, $height );
		
		if( $this->_imageInformation['type'] == IMAGETYPE_PNG || $this->_imageInformation['type'] == IMAGETYPE_GIF )
		{
			imagealphablending( $newImage, false );
			imagesavealpha( $newImage, true );
		}
		
		imagecopyresampled( $newImage, $this->_image, 0, 0, 0, 0, $width, $height, $this->_imageInformation['width'], $this->_imageInformation['height'] );
		
		$this->_thumbImage = $newImage;
	}
	
	/**
	 * @param $imageFileName: The name of the image to change (with the full address 
	 * to the file)
	 * 
	 * Save the information of the image and put into the array of information _imageInformation
	 */
	private function saveImageInformation( $imageFileName )
	{
		if( !$information = getimagesize( $this->_imageFileName ) )
			throw new Exception( "The file can not be readed" );
		
		$this->_imageInformation = array();
		
		$this->_imageInformation['type'] = $information[2];
		$this->_imageInformation['width'] = $information[0];
		$this->_imageInformation['height'] = $information[1];
		if( $this->_imageInformation['type'] == IMAGETYPE_JPEG )
			$this->_imageInformation['channels'] = $information['channels'];
		$this->_imageInformation['bits'] = $information['bits'];
	}
	
	/**
	 * Method added in 0.5
	 * 
	 * @param $valueQuality: The value of the quality with the constants $QUALITY_...
	 * 
	 * @return Quality value correspondit to the type of the image
	 */
	private function getQuality( $valueQuality )
	{
		if( $this->_imageInformation['type'] == IMAGETYPE_JPEG )
		{
			if( $valueQuality == self::$QUALITY_LOW )
			{
				return 25;
			}
			elseif( $valueQuality == self::$QUALITY_MEDIUM )
			{
				return 50;
			}
			else
			{
				return 75;
			}
		}
		elseif( $this->_imageInformation['type'] == IMAGETYPE_GIF || $this->_imageInformation['type'] == IMAGETYPE_PNG )
		{
			if( $valueQuality == self::$QUALITY_LOW )
			{
				return 9;
			}
			elseif( $valueQuality == self::$QUALITY_MEDIUM )
			{
				return 6;
			}
			else
			{
				return 3;
			}
		}
	}
	
	/**
	 * Method added in 0.3
	 * @param $thumbWidth: the width of the thumb to crop
	 * @param $thumbHeight: the height of the thumb to crop
	 * @param $sX: The x value for the image position in the new image size
	 * @param $sY: The x value for the image position in the new image size
	 * 
	 * Crop the image for create a thumb with not the total of the image
	 */
	private function crop( $width, $height, $sX = 0, $sY = 0 )
	{
		if( $width > $this->_imageInformation['width'] || $height > $this->_imageInformation['height'] )
			throw new Exception( "The measures for the crop are incorrects" );
		
		if( $width + $sX > $this->_imageInformation['width'] || $height + $sY > $this->_imageInformation['height'] )
			throw new Exception( "The measures to crop are greatest than the image size" );
		
		$newImage = imagecreatetruecolor( $width, $height );
		
		if( $this->_imageInformation['type'] == IMAGETYPE_PNG || $this->_imageInformation['type'] == IMAGETYPE_GIF )
		{
			imagealphablending( $newImage, false );
			imagesavealpha( $newImage, true );
		}
		
		imagecopyresampled( $newImage, $this->_image, 0, 0, $sX, $sY, $width, $height, $width, $height );
		
		$this->_thumbImage = $newImage;
	}
	
	/**
	 * @return the headers
	 * 
	 * Get the headers to show the image
	 */
	public function getHeaders()
	{
		switch( $this->_imageInformation['type'] )
		{
			case IMAGETYPE_JPEG:
				header( "Content-Type: image/jpeg;" );
			break;
			
			case IMAGETYPE_GIF:
				header( "Content-Type: image/gif;" );
			break;
			
			case IMAGETYPE_PNG:
				header( "Content-Type: image/png;" );
			break;
			
			default:
				throw new Exception( "The image is not a recognized type" );
			break;
		}
	}
	
	/**
	 * @return the variable _imageInformation with the image information
	 */
	public function getImageInformation()
	{
		return $this->_imageInformation;
	}
	
	/**
	 * @param $width: the new width of the image
	 * 
	 * Resize the image to the width ratio
	 */
	public function resizeToWidth( $width )
	{
		$ratio = $width / $this->_imageInformation['width'];
		$height = $this->_imageInformation['height'] * $ratio;
		$this->resizeImage( $width, $height );
	}
	
	/**
	 * @param $height: the new height of the image
	 * 
	 * Resize the image to the height ratio
	 */
	public function resizeToHeight( $height )
	{
		$ratio = $height / $this->_imageInformation['height'];
		$width = $this->_imageInformation['width'] * $ratio;
		$this->resizeImage( $width, $height );
	}
	
	/**
	 * @param $width: the new width of the image
	 * @param $height: the new height of the image
	 * 
	 * Resize the image with the fixed values
	 */
	public function resizeFixed( $width, $height )
	{
		$this->resizeImage( $width, $height );
	}
	
	/**
	 * @param $width: the new width of the image
	 * @param $height: the new height of the image
	 *  
	 * Resize the image without loossing the structure
	 */
	public function resizePerforming( $width, $height )
	{
		$ratio = $this->_imageInformation['width'] / $this->_imageInformation['height'];
		
		if( $height / $width < $ratio )
			$height = $width / $ratio;
		else
			$width = $height * $ratio;
		
		$this->resizeImage( $width, $height );
	}
	
	/**
	 * Get output image
	 */
	public function output( $valueQuality = 3 )
	{
		$quality = $this->getQuality( $valueQuality );
		
		switch( $this->_imageInformation['type'] )
		{
			case IMAGETYPE_JPEG:
				if( $this->_thumbImage == null )
					imagejpeg( $this->image, null, $quality );
				else
					imagejpeg( $this->_thumbImage, null, $quality );
			break;
			
			case IMAGETYPE_GIF:
				if( $this->_thumbImage == null )
					imagegif( $this->image, null, $quality );
				else
					imagegif( $this->_thumbImage, null, $quality );
			break;
			
			case IMAGETYPE_PNG:
				if( $this->_thumbImage == null )
					imagepng( $this->image, null, $quality );
				else
					imagepng( $this->_thumbImage, null, $quality );
			break;
			
			default:
				throw new Exception( "The image is not a recognized type" );
			break;
		}
	}
	
	/**
	 * @param $directory: the directory (without filename) for the file that was saved
	 * @param $fileName: the filename to the new image file
	 * 
	 * @return the filename with the directory
	 * 
	 * Save the image as a file
	 */
	public function save( $directory, $fileName, $valueQuality = 3 )
	{
		$quality = $this->getQuality( $valueQuality );
		
		if( $directory == null )
			$directory = "./";
		
		if( substr( strlen( $directory )-1, 1 ) != "/" )
			$directory .= "/";
		
		if( $fileName == null )
			$fileName = end( explode( "/", $this->_imageFileName ) );
		
		$imageFile = $directory . $fileName;
		
		if( file_exists( $imageFile ) )
			throw new Exception( "A file with the same name exists ({$imageFile})" );
		
		@chmod( $directory, 755 );
		
		switch( $this->_imageInformation['type'] )
		{
			case IMAGETYPE_JPEG:
				if( $this->_thumbImage == null )
					imagejpeg( $this->_image, $imageFile, $quality );
				else
					imagejpeg( $this->_thumbImage, $imageFile, $quality );
				return $directory . $fileName;
			break;
			
			case IMAGETYPE_GIF:
				if( $this->_thumbImage == null )
					imagegif( $this->_image, $imageFile, $quality );
				else
					imagegif( $this->_thumbImage, $imageFile, $quality );
				return $directory . $fileName;
			break;
			
			case IMAGETYPE_PNG:
				if( $this->_thumbImage == null )
					imagepng( $this->_image, $imageFile, $quality );
				else
					imagepng( $this->_thumbImage, $imageFile, $quality );
				return $directory . $fileName;
			break;
			
			default:
				throw new Exception( "The image is not a recognized type" );
			break;
		}
	}
	
	/**
	 * Method added in 0.5
	 * @param $width: Width of the center crop image
	 * @param $height: Height of the center crop image
	 * 
	 * Crop an image by the center point
	 */
	public function cropCenter( $width, $height )
	{
		$centerWidth = ( $this->_imageInformation['width'] - $width ) / 2;
		$centerHeight = ( $this->_imageInformation['height'] - $height ) / 2;
		
		$this->crop( $width, $height, $centerWidth, $centerHeight );
	}
	
	/**
	 * Method added in 0.5
	 * @param $width: the width of the thumb to crop
	 * @param $height: the height of the thumb to crop
	 * @param $positionX: The x value for the image position in the new image size
	 * @param $positionY: The x value for the image position in the new image size
	 * 
	 * Crop an image with fixed positions
	 */
	public function cropFixed( $width, $height, $positionX, $positionY )
	{
		$this->crop( $width, $height, $positionX, $positionY );
	}
	
	/**
	 * Method added in 0.5
	 * @param $width: the width of the thumb to crop
	 * @param $height: the height of the thumb to crop
	 * @param $corner: Value corner that was cropped
	 * 
	 * Crop an image from the corner
	 */
	public function cropCorner( $width, $height, $corner )
	{
		$x = 0;
		$y = 0;
		
		switch( $corner )
		{
			case self::$CROP_BOTTOM_LEFT:
				$x = 0;
				$y = $this->_imageInformation['height'] - $height;
			break;
			
			case self::$CROP_BOTTOM_RIGHT:
				$x = $this->_imageInformation['width'] - $width;
				$y = $this->_imageInformation['height'] - $height;
			break;
			
			case self::$CROP_TOP_LEFT:
				$x = 0;
				$y = 0;
			break;
			
			case self::$CROP_TOP_RIGHT:
				$x = $this->_imageInformation['width'] - $width;
				$y = 0;
			break;
		}
		
		$this->crop( $width, $height, $x, $y );
	}
}

/**

//USAGE INFORMATION

//Instance the class with the image filename.
$ia = new ImageAmatore( "./chrysanthemum.jpg" );

//Get the headers information if you want to show the image directly form the browser. If you want to save the image in another file, you must NO call this method.
$ia->getHeaders();

//If you want a fixed size for the image, call this method with your parameters.
$ia->resizeFixed(150, 160);

//If you want to resize the image maintaining the width and height ratio, call this method.
$ia->resizePerforming( 150, 160 );

//If you want to resize the image about the height ratio, call this method.
$ia->resizeToHeight( 180 );

//If you want to resize the image about the width ratio, call this method.
$ia->resizeToWidth( 200 );

//If you want to crop an image use this methods.
$ia->cropCenter( 100, 100 );
$ia->cropCorner( 100, 100, ImageAmatore::CROP_BOTTOM_RIGHT );
$ia->cropFixed( 100, 100, 30, 30 );

//If you want to show the image directly form the browser, call this method
$ia->output();

//If you want to save the image in another file, call this method. Passing for it, the directory of the new file and the name. If the diectory is null, use the actual directory (./). If the filename is null, use the file that you pass when instance the class.
$ia->save( $directory, $fileName );

**/

?>