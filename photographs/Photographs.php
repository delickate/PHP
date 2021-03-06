<?php
//SANI: Class to manipulate file
class Photographs
{
	//SANI: Note following points carefully
	//Public:    Can be accessed within the class, inherited class and from instance of class.
	//Private:   Can be accessed within the class only.
	//Protected: Can be accessed within the class and inherited classes
	
	
	public $file_allowed 		= "";  //SANI: Could be jpg|jpeg|png|gif
	public $file_path    		= "";
	public $uploading_path    	= "";
	
	public $resize_image        = false;
	public $resize_max_size     = 800;     //SANI: Axis
	public $keep_original       = true;
	
	public $create_watermark    = false;
	public $watermark_file      = "";
	
	private $max_image_size     =  5;  //SANI: MB
	private $custom_name        = "";
	
	
	//SANI: Do as object is created
	function __construct()
	{
		$this->file_allowed 	= "*";
		$this->uploading_path 	= "./";
		$this->max_image_size   = $this->file_bytes2mb($this->max_image_size);
		$this->custom_name 		= (time().rand(0,999).time());
	}
	
	/////////////////////////////////////////////////////////////
	//					FILE UPLOADING
	/////////////////////////////////////////////////////////////
	//SANI: Upload file
	function upload_file($fileName = '')
	{
		$error   = "";   //SANI: Default error message
		$success = array();
		
		$is_file_ready_to_upload = false;
		
		if(isset($fileName) && $fileName != "" && isset($_FILES[$fileName]))    //SANI: Check if file name parameter exist or not
		{   
			$fileObject = $_FILES[$fileName];
			if(isset($_FILES) && isset($fileObject))   //SANI: Check whether form has submitted with encrypt = multipart/form-data
			{  
				if(is_array($fileObject["name"]))             //SANI: Array files to upload
				{
					if(isset($_FILES[$fileName]["tmp_name"]) && !empty($_FILES[$fileName]["tmp_name"]))
					{
						$loopIndex  = 0;   
						foreach($_FILES[$fileName]["tmp_name"] as $fileIndex)        //SANI: Loop through all files
						{
							//echo $_FILES[$fileName]["name"][$loopIndex];
							
							if($fileIndex != "")
							{
								$image_size 	= filesize($fileIndex);        //SANI: Get image size in bytes
		
								if($image_size <= $this->max_image_size)   //SANI: Check wether file size is fine
								{
									$target_file = $this->uploading_path.basename($_FILES[$fileName]["name"][$loopIndex]);
								
									if(file_exists($target_file))   //SANI: Check wether file is already there
									{
										$error .= "File already exist. \n\r ".PHP_EOL;
									}else{
											$file_extenstion 			= strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
											$which_extenstion_allowed 	= explode("|", $this->file_allowed);
											
											if(in_array($file_extenstion, $which_extenstion_allowed))
											{
												$final_file_name = $this->custom_name.rand(0,9999).".".$file_extenstion;
												$uploaded_file   = $this->uploading_path.$final_file_name;
													
												if (move_uploaded_file($_FILES[$fileName]["tmp_name"][$loopIndex], $uploaded_file)) 
												{
													$success[] 		=  $final_file_name;
												} else {
															$error .= "Sorry, there was an error while uploading your file. \n\r ".PHP_EOL;
													   }
											}else{
													$error .= "File extension does not supported. \n\r ".PHP_EOL;
												 }
									     }
									
								}else{
										$error .= "File size ".$this->file_size_convert($image_size)." is too large. Allowed max ".$this->file_size_convert($this->max_image_size);
									 }
							}
							$loopIndex++;
						}
					}else{
							//$error .= "Invalid multi files to upload. \n\r ".PHP_EOL;
					     }
				}else{                                       //SANI: Single file to upload     
						if(isset($_FILES[$fileName]["tmp_name"]) && !empty($_FILES[$fileName]["tmp_name"]))
						{  
							$image_size 	= filesize($_FILES[$fileName]["tmp_name"]);        //SANI: Get image size in bytes
		
							if($image_size <= $this->max_image_size)   //SANI: Check wether file size is fine
							{
								$target_file = $this->uploading_path.basename($_FILES[$fileName]["name"]);
								
								if(file_exists($target_file))   //SANI: Check wether file is already there
								{
									$error .= "File already exist. \n\r ".PHP_EOL;
								}else{
										$file_extenstion 			= strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
										$which_extenstion_allowed 	= explode("|", $this->file_allowed);
										
										if(in_array($file_extenstion, $which_extenstion_allowed))
										{
											$uploaded_file 	= $this->uploading_path.$this->custom_name.".".$file_extenstion;
											if (move_uploaded_file($_FILES[$fileName]["tmp_name"], $uploaded_file)) 
											{
												$success[] 		= $this->custom_name.".".$file_extenstion;
											} else {
														$error .= "Sorry, there was an error while uploading your file. \n\r ".PHP_EOL;
												   }
										}else{
												$error .= "File extension does not supported. \n\r ".PHP_EOL;
										     }
								     }
							}else{
									$error .= "File size ".$this->file_size_convert($image_size)." is too large. Allowed max ".$this->file_size_convert($this->max_image_size);
								 }
						}else{
								//$error .= "Invalid single file to upload. \n\r ".PHP_EOL;
						     }
				     }
			}else{
					//$error .= "Make sure you have set form attribute encrypt. \n\r ".PHP_EOL;
			     }
		}
		
		
		if(!empty($success) && is_array($success))
		{
			return $success;
		}else{	 
				return $error;
			 }
	}
	
	/////////////////////////////////////////////////////////////
	//					IMAGE RESIZING
	/////////////////////////////////////////////////////////////
	//SANI: Uploading file actually
	function resizing_image($source_file, $target_file = "./")
	{
		$error = ""; 
		
		if($this->resize_image)
		{
			if(file_exists($source_file))
			{
				$process_only_on  	= array("jpg", "jpeg", "png", "gif");
				
				$fileInfo 			= pathinfo($source_file);                //SANI: Get file info, which file type it is.
				$extenstion 		= strtolower($fileInfo["extension"]);    //SANI: get extention of file.
				
				if(in_array($extenstion, $process_only_on))
				{
					switch($extenstion)
					{
						case "jpg" : $this->resize_jpg($source_file, $target_file); break;
						case "jpeg": $this->resize_jpg($source_file, $target_file); break;
						case "png" : $this->resize_png($source_file, $target_file); break;
						case "gif" : $this->resize_gif($source_file, $target_file); break;
					}
				}else{
						$error .= "File extension does not supported. \n\r ".PHP_EOL;
					 }
			}else{
					$error .= "Please provide source file. \n\r ".PHP_EOL;
				 }
		}else{
				$error .= "File resize configuration is not set. \n\r ".PHP_EOL;
		     }
		return $error;	 
	}
	
	//SANI: Play with JPEG File
	private function resize_jpg($source_file, $output_file = "./")
	{
		list($fileWidth, $fileHeight, $fileType) = getimagesize($source_file);   //SANI: Get with, height & file type
		$createImagePerExtension = imagecreatefromjpeg($source_file);            //SANI: Change image to true colors
		
		$photoX         = imagesx($createImagePerExtension);   //SANI: Original image X-axis dimension
		$photoY         = imagesy($createImagePerExtension);   //SANI: Original image Y-axis dimension
		
		//////////////////// IMAGE WIDTH & HEIGHT //////////////////////////////
					$newWidth  = $fileWidth; //$fileWidth;
					$newHeight = $fileHeight; //$fileHeight;
					
						if($photoX > $this->resize_max_size || $photoY > $this->resize_max_size)             //SANI: Check if image is greater
						{   
							$percentage = ($this->resize_max_size/$photoX)*100;         //SANI: Percentage = (obtain/total)*100  (Get how much width is going to decrease)
							$newWidth   = $this->resize_max_size;                       //SANI: Get new width & calculate height by ratio
							$newHeight  = ($percentage*$photoY)/100;       //SANI: Get new height with aspect ratio
						}else{   
										$newWidth  = $fileWidth; //$fileWidth;
										$newHeight = $fileHeight; //$fileHeight;
							 }
					
		///////////////////////////////// Keep transpatency if it has ///////////////////////////////////////
		//SANI: This part is not neccassry.			
					$createImageTrueColor     = imagecreatetruecolor($newWidth,$newHeight);
					
					imagesavealpha($createImageTrueColor,true);  //SANI: Maintain background transparency
					$transpatentColor   	  = imagecolorallocatealpha($createImageTrueColor,0,0,0,127);
					imagefill($createImageTrueColor,0,0,$transpatentColor);
					unset($transpatentColor);
		//////////////////////// Create new image //////////////////////
						imagecopyresampled ( $createImageTrueColor,            //SANI: Destination Image
												 $createImagePerExtension,         //SANI: Source Image
												 
												 0 ,                     //SANI: Destination X
												 0 ,                     //SANI: Destination Y
												 
												 0 ,                     //SANI: Source X
												 0 ,                     //SANI: Source Y
												 
												 $newWidth+1 ,                     //SANI: Destination Width
												 $newHeight+1 ,                    //SANI: Destination Height
												 
												 $photoX ,                     //SANI: Source Width
												 $photoY 					   //SANI: Source Height
											   );
						
						
					//////////////////////////////////////////////
					unset($photoX,$photoY,$fileWidth,$fileHeight);
					
					//SANI: If you do not use transparent then use following line
					//imagecreatefromjpeg($createImageTrueColor,$output_file);
					//SANI: If you use tranparency then use following line
					imagejpeg($createImageTrueColor,$output_file);
					unset($output_file);
					
					imagedestroy($createImagePerExtension);
					imagedestroy($createImageTrueColor);						
	}
	
	//SANI: Play with PNG File
	private function resize_png($source_file, $output_file = "./")
	{
		list($fileWidth, $fileHeight, $fileType) = getimagesize($source_file);   				//SANI: Get with, height & file type
		$createImagePerExtension 				 = imagecreatefrompng($source_file);            //SANI: Change image to true colors
		
		$photoX         = imagesx($createImagePerExtension);   //SANI: Original image X-axis dimension
		$photoY         = imagesy($createImagePerExtension);   //SANI: Original image Y-axis dimension
		
		//////////////////// IMAGE WIDTH & HEIGHT //////////////////////////////
					$newWidth  = $fileWidth; //$fileWidth;
					$newHeight = $fileHeight; //$fileHeight;
					
						if($photoX > $this->resize_max_size || $photoY > $this->resize_max_size)             //SANI: Check if image is greater
						{   
							$percentage = ($this->resize_max_size/$photoX)*100;         //SANI: Percentage = (obtain/total)*100  (Get how much width is going to decrease)
							$newWidth   = $this->resize_max_size;                       //SANI: Get new width & calculate height by ratio
							$newHeight  = ($percentage*$photoY)/100;       //SANI: Get new height with aspect ratio
						}else{   
										$newWidth  = $fileWidth; //$fileWidth;
										$newHeight = $fileHeight; //$fileHeight;
							 }
					
		///////////////////////////////// Keep transpatency if it has ///////////////////////////////////////
		//SANI: This part is not neccassry.			
					$createImageTrueColor     = imagecreatetruecolor($newWidth,$newHeight);
					
					imagesavealpha($createImageTrueColor,true);  //SANI: Maintain background transparency
					$transpatentColor   	  = imagecolorallocatealpha($createImageTrueColor,0,0,0,127);
					imagefill($createImageTrueColor,0,0,$transpatentColor);
					unset($transpatentColor);
		//////////////////////// Create new image //////////////////////
						imagecopyresampled ( $createImageTrueColor,            //SANI: Destination Image
												 $createImagePerExtension,         //SANI: Source Image
												 
												 0 ,                     //SANI: Destination X
												 0 ,                     //SANI: Destination Y
												 
												 0 ,                     //SANI: Source X
												 0 ,                     //SANI: Source Y
												 
												 $newWidth+1 ,                     //SANI: Destination Width
												 $newHeight+1 ,                    //SANI: Destination Height
												 
												 $photoX ,                     //SANI: Source Width
												 $photoY 					   //SANI: Source Height
											   );
						
						
					//////////////////////////////////////////////
					unset($photoX,$photoY,$fileWidth,$fileHeight);
					
					//SANI: If you do not use transparent then use following line
					//imagecreatefromjpeg($createImageTrueColor,$output_file);
					//SANI: If you use tranparency then use following line
					imagepng($createImageTrueColor,$output_file);
					unset($output_file);
					
					imagedestroy($createImagePerExtension);
					imagedestroy($createImageTrueColor);						
	}
	
	//SANI: Play with GIF File
	private function resize_gif($source_file, $output_file = "./")
	{
		list($fileWidth, $fileHeight, $fileType) = getimagesize($source_file);   				//SANI: Get with, height & file type
		$createImagePerExtension 				 = imagecreatefromgif($source_file);            //SANI: Change image to true colors
		
		$photoX         = imagesx($createImagePerExtension);   //SANI: Original image X-axis dimension
		$photoY         = imagesy($createImagePerExtension);   //SANI: Original image Y-axis dimension
		
		//////////////////// IMAGE WIDTH & HEIGHT //////////////////////////////
					$newWidth  = $fileWidth; //$fileWidth;
					$newHeight = $fileHeight; //$fileHeight;
					
						if($photoX > $this->resize_max_size || $photoY > $this->resize_max_size)             //SANI: Check if image is greater
						{   
							$percentage = ($this->resize_max_size/$photoX)*100;         //SANI: Percentage = (obtain/total)*100  (Get how much width is going to decrease)
							$newWidth   = $this->resize_max_size;                       //SANI: Get new width & calculate height by ratio
							$newHeight  = ($percentage*$photoY)/100;       //SANI: Get new height with aspect ratio
						}else{   
										$newWidth  = $fileWidth; //$fileWidth;
										$newHeight = $fileHeight; //$fileHeight;
							 }
					
		///////////////////////////////// Keep transpatency if it has ///////////////////////////////////////
		//SANI: This part is not neccassry.			
					$createImageTrueColor     = imagecreatetruecolor($newWidth,$newHeight);
					
					imagesavealpha($createImageTrueColor,true);  //SANI: Maintain background transparency
					$transpatentColor   	  = imagecolorallocatealpha($createImageTrueColor,0,0,0,127);
					imagefill($createImageTrueColor,0,0,$transpatentColor);
					unset($transpatentColor);
		//////////////////////// Create new image //////////////////////
						imagecopyresampled ( $createImageTrueColor,            //SANI: Destination Image
												 $createImagePerExtension,         //SANI: Source Image
												 
												 0 ,                     //SANI: Destination X
												 0 ,                     //SANI: Destination Y
												 
												 0 ,                     //SANI: Source X
												 0 ,                     //SANI: Source Y
												 
												 $newWidth+1 ,                     //SANI: Destination Width
												 $newHeight+1 ,                    //SANI: Destination Height
												 
												 $photoX ,                     //SANI: Source Width
												 $photoY 					   //SANI: Source Height
											   );
						
						
					//////////////////////////////////////////////
					unset($photoX,$photoY,$fileWidth,$fileHeight);
					
					//SANI: If you do not use transparent then use following line
					//imagecreatefromjpeg($createImageTrueColor,$output_file);
					//SANI: If you use tranparency then use following line
					imagegif($createImageTrueColor,$output_file);
					unset($output_file);
					
					imagedestroy($createImagePerExtension);
					imagedestroy($createImageTrueColor);						
	}	
	
	/////////////////////////////////////////////////////////////
	//					WATERMARK
	/////////////////////////////////////////////////////////////
	//SANI: Uploading file actually
	function watermark_image($source_file, $target_file = "./")
	{
		$error = ""; 
		
		if($this->create_watermark)
		{
			if(file_exists($source_file))
			{
				$process_only_on  	= array("jpg", "jpeg", "png", "gif");
				
				$fileInfo 			= pathinfo($source_file);                //SANI: Get file info, which file type it is.
				$extenstion 		= strtolower($fileInfo["extension"]);    //SANI: get extention of file.
				
				if(in_array($extenstion, $process_only_on))
				{
					switch($extenstion)
					{
						case "jpg" : $this->watermark_jpg($source_file, $target_file); break;
						case "jpeg": $this->watermark_jpg($source_file, $target_file); break;
						case "png" : $this->watermark_png($source_file, $target_file); break;
						case "gif" : $this->watermark_gif($source_file, $target_file); break;
					}
				}else{
						$error .= "File extension does not supported. \n\r ".PHP_EOL;
					 }
			}else{
					$error .= "Please provide source file. \n\r ".PHP_EOL;
				 }
		}else{
				$error .= "File watermark configuration is not set. \n\r ".PHP_EOL;
		     }
		return $error;	 
	}
	
	//SANI: Play with JPEG File
	private function watermark_jpg($source_file, $output_file = "./")
	{  
		//SANI: Base Image
		$originalImage 		  = $source_file;
		list($originalWidth, $originalHeight, $originalFileType) = getimagesize($originalImage);
		$originalImageCreate  = imagecreatefromjpeg($originalImage);
		imagealphablending( $originalImageCreate, true );
		imagesavealpha( $originalImageCreate, true );
		$originalX         = imagesx($originalImageCreate);   //SANI: Original image with
		$originalY         = imagesy($originalImageCreate);   //SANI: Original image height
		
		//SANI: Watermark Image
		$watermarkImage 		= $this->watermark_file;
		list($watermarkWidth, $watermarkHeight, $watermarkFileType) = getimagesize($watermarkImage);
		$watermarkImageCreate   = imagecreatefrompng($watermarkImage);
		imagealphablending( $watermarkImageCreate, true );
		imagesavealpha( $watermarkImageCreate, true );
		$watermarkX         = imagesx($watermarkImageCreate);   //SANI: Original image with
		$watermarkY         = imagesy($watermarkImageCreate); 
		
		//SANI: Create a transpatent background
		$createImageTrueColor     = imagecreatetruecolor(300,300);  //800 = width, 600 = height
		imagesavealpha($createImageTrueColor,true);  //SANI: Maintain background transparency
		$transpatentColor   	  = imagecolorallocatealpha($createImageTrueColor,0,0,0,127);
		imagefill($createImageTrueColor,0,0,$transpatentColor);
		unset($transpatentColor);
		
		//SANI: Center the watermark image which is going to overlap original image
		$marge_right  = abs(($originalX-$watermarkX)/2);
		$marge_bottom = abs(($originalY-$watermarkY)/2);

		imagecopy($originalImageCreate, $watermarkImageCreate, $marge_right, $marge_bottom, 0, 0, $watermarkWidth, $watermarkHeight);
		imagejpeg($originalImageCreate, $output_file);
		
		imagedestroy($originalImageCreate);
		imagedestroy($watermarkImageCreate);
		imagedestroy($createImageTrueColor);								
	}
	
	//SANI: Play with PNG File
	private function watermark_png($source_file, $output_file = "./")
	{  
		//SANI: Base Image
		$originalImage 		  = $source_file;
		list($originalWidth, $originalHeight, $originalFileType) = getimagesize($originalImage);
		$originalImageCreate  = imagecreatefrompng($originalImage);
		imagealphablending( $originalImageCreate, true );
		imagesavealpha( $originalImageCreate, true );
		$originalX         = imagesx($originalImageCreate);   //SANI: Original image with
		$originalY         = imagesy($originalImageCreate);   //SANI: Original image height
		
		//SANI: Watermark Image
		$watermarkImage 		= $this->watermark_file;
		list($watermarkWidth, $watermarkHeight, $watermarkFileType) = getimagesize($watermarkImage);
		$watermarkImageCreate   = imagecreatefrompng($watermarkImage);
		imagealphablending( $watermarkImageCreate, true );
		imagesavealpha( $watermarkImageCreate, true );
		$watermarkX         = imagesx($watermarkImageCreate);   //SANI: Original image with
		$watermarkY         = imagesy($watermarkImageCreate); 
		
		//SANI: Create a transpatent background
		$createImageTrueColor     = imagecreatetruecolor(300,300);  //800 = width, 600 = height
		imagesavealpha($createImageTrueColor,true);  //SANI: Maintain background transparency
		$transpatentColor   	  = imagecolorallocatealpha($createImageTrueColor,0,0,0,127);
		imagefill($createImageTrueColor,0,0,$transpatentColor);
		unset($transpatentColor);
		
		//SANI: Center the watermark image which is going to overlap original image
		$marge_right  = abs(($originalX-$watermarkX)/2);
		$marge_bottom = abs(($originalY-$watermarkY)/2);

		imagecopy($originalImageCreate, $watermarkImageCreate, $marge_right, $marge_bottom, 0, 0, $watermarkWidth, $watermarkHeight);
		imagepng($originalImageCreate, $output_file);
		
		imagedestroy($originalImageCreate);
		imagedestroy($watermarkImageCreate);
		imagedestroy($createImageTrueColor);								
	}
	
	//SANI: Play with GIF File
	private function watermark_gif($source_file, $output_file = "./")
	{  
		//SANI: Base Image
		$originalImage 		  = $source_file;
		list($originalWidth, $originalHeight, $originalFileType) = getimagesize($originalImage);
		$originalImageCreate  = imagecreatefromgif($originalImage);
		imagealphablending( $originalImageCreate, true );
		imagesavealpha( $originalImageCreate, true );
		$originalX         = imagesx($originalImageCreate);   //SANI: Original image with
		$originalY         = imagesy($originalImageCreate);   //SANI: Original image height
		
		//SANI: Watermark Image
		$watermarkImage 		= $this->watermark_file;
		list($watermarkWidth, $watermarkHeight, $watermarkFileType) = getimagesize($watermarkImage);
		$watermarkImageCreate   = imagecreatefrompng($watermarkImage);
		imagealphablending( $watermarkImageCreate, true );
		imagesavealpha( $watermarkImageCreate, true );
		$watermarkX         = imagesx($watermarkImageCreate);   //SANI: Original image with
		$watermarkY         = imagesy($watermarkImageCreate); 
		
		//SANI: Create a transpatent background
		$createImageTrueColor     = imagecreatetruecolor(300,300);  //800 = width, 600 = height
		imagesavealpha($createImageTrueColor,true);  //SANI: Maintain background transparency
		$transpatentColor   	  = imagecolorallocatealpha($createImageTrueColor,0,0,0,127);
		imagefill($createImageTrueColor,0,0,$transpatentColor);
		unset($transpatentColor);
		
		//SANI: Center the watermark image which is going to overlap original image
		$marge_right  = abs(($originalX-$watermarkX)/2);
		$marge_bottom = abs(($originalY-$watermarkY)/2);

		imagecopy($originalImageCreate, $watermarkImageCreate, $marge_right, $marge_bottom, 0, 0, $watermarkWidth, $watermarkHeight);
		imagegif($originalImageCreate, $output_file);
		
		imagedestroy($originalImageCreate);
		imagedestroy($watermarkImageCreate);
		imagedestroy($createImageTrueColor);								
	}
	
	/////////////////////////////////////////////////////////////
	//					GENERAL
	/////////////////////////////////////////////////////////////
	//SANI: Dump array
	function show($array)
	{
		echo "<pre>"; print_r($array); echo "</pre>";
	}
	
	function file_bytes2mb($size = 5)
	{
		return ((1024*1024)*$size); //SANI: 5 MB (1 KB = 1024 Byte  => (1024 KB * 1024) = 1 MB =>   1 MB * n = final MB); 
		
	}
	
	//SANI: Convert image size human readable
	function file_size_convert($bytes)
	{
		$bytes = floatval($bytes);
			$arBytes = array(
				0 => array(
					"UNIT" => "TB",
					"VALUE" => pow(1024, 4)
				),
				1 => array(
					"UNIT" => "GB",
					"VALUE" => pow(1024, 3)
				),
				2 => array(
					"UNIT" => "MB",
					"VALUE" => pow(1024, 2)
				),
				3 => array(
					"UNIT" => "KB",
					"VALUE" => 1024
				),
				4 => array(
					"UNIT" => "B",
					"VALUE" => 1
				),
			);
	
		foreach($arBytes as $arItem)
		{
			if($bytes >= $arItem["VALUE"])
			{
				$result = $bytes / $arItem["VALUE"];
				$result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
				break;
			}
		}
		return $result;
	}
	
	//SANI: Do as object is disposed
	function __destruct() 
	{
       
    }
}

?>
