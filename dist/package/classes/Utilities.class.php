<?php
class Utilities{
	
	public static function upload($value,$target){
		if($value['error'] == 0){
			$infosfichier = pathinfo($value['name']);
			$extension_upload = $infosfichier['extension'];
			if(preg_match("#jpeg|jpg|png|bmp|gif#i",$extension_upload)){
				if($extension_upload=='jpeg' OR $extension_upload=='JPG' OR $extension_upload=='JPEG'){
					$extension_upload='jpg';
				}
				elseif($extension_upload=='PNG' OR $extension_upload=='png'){
					$extension_upload='jpg';
				}
				elseif($extension_upload=='BMP' OR $extension_upload=='bmp'){
					$extension_upload='jpg';
				}
				elseif($extension_upload=='GIF' OR $extension_upload=='gif'){
					$extension_upload='jpg';
				}
				$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
				$pass = array(); //remember to declare $pass as an array
				$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
				for ($i = 0; $i < 8; $i++) {
					$n = rand(0, $alphaLength);
					$pass[] = $alphabet[$n];
				}
				$name=time().implode($pass);
				$name1=$name.'.'.$extension_upload;
				move_uploaded_file($value['tmp_name'], $target.$name.'.'.$extension_upload);
				$img = new SimpleImage();
				$img->load($target.$name.'.'.$extension_upload)->fit_to_width(800)->save($target.'large_'.$name.'.'.$extension_upload);
				$img->load($target.$name.'.'.$extension_upload)->fit_to_width(300)->save($target.'small_'.$name.'.'.$extension_upload);				
				$img->load($target.$name.'.'.$extension_upload)->square_crop(400)->save($target.'cropped_'.$name.'.'.$extension_upload);
				$img->load($target.$name.'.'.$extension_upload)->fit_to_width(100)->save($target.'micro_'.$name.'.'.$extension_upload);		
				return $name1;
			}		
		}
	}

	public static function uploadFile($value,$target){
		if($value['error'] == 0){
			$infosfichier = pathinfo($value['name']);
			$extension_upload = $infosfichier['extension'];
			if(preg_match("#xlsx|xls|pdf#i",$extension_upload)){
				$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
				$pass = array(); //remember to declare $pass as an array
				$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
				for ($i = 0; $i < 8; $i++) {
					$n = rand(0, $alphaLength);
					$pass[] = $alphabet[$n];
				}
				$name=time().implode($pass);
				$name1=$name.'.'.$extension_upload;
				move_uploaded_file($value['tmp_name'], $target.$name.'.'.$extension_upload);
				return $name1;
			}
		}		
	}
	
}
?>