<?php
require($_SERVER['DOCUMENT_ROOT'].'/config.php');

//params to control a failure in this context
$scriptDisposition='imagereader';

//code to begin on line 40 for consistency
$path=tree_id_to_path($Tree_ID);

if(!empty($src)){
	//not developed	
}else if($path && $Key){
	$Key=str_replace('_','',$Key);
	if(preg_match('#/'.$Key.'_#',$path) || $Key==md5($Tree_ID.$MASTER_PASSWORD)){
		if(!empty($thumbnail) && $thumbnail=='default'){
			//added 2011-02-23:
			$path=explode('/',$path);
			$file=array_pop($path);
			$path=implode('/',$path).'/.thumbs.dbr/'.$file;
		}else if(!empty($disposition)){
			//allow passage of _x300 or 300x_ where _ means "large"
			$d=preg_split('/x/i',$disposition);
			if(!$d[0])$d[0]=12000;
			if(!$d[1])$d[1]=12000;
			$boundingBoxWidth=$d[0];
			$boundingBoxHeight=$d[1];
			$disposition=implode('x',$d);
			
			//added 2011-11-06
			$source=$_SERVER['DOCUMENT_ROOT'].$path;
			$path=explode('/',$path);
			$file=array_pop($path);
			$path=implode('/',$path);
			if(!is_dir($_SERVER['DOCUMENT_ROOT'].$path.'/.thumbs.dbr') && !mkdir($_SERVER['DOCUMENT_ROOT'].$path.'/.thumbs.dbr')){
				mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='unable to create thumbs.dbr'),$fromHdrBugs);
				exit($err);
			}
			$target=$path.'/.thumbs.dbr/'.preg_replace('/(\.[^.]+)$/','{'.$disposition.($boxMethod==2?',2':'').'}$1',$file);
			if($g=@getimagesize($_SERVER['DOCUMENT_ROOT'].$target)){
				//we have it
			}else{
				$a=getimagesize($_SERVER['DOCUMENT_ROOT'].$path.'/'.$file);
				if(!$a){
					//what?
					mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='see coding'),$fromHdrBugs);
					exit;
				}
				if(($boundingBoxWidth >= $a[0]) && ($boundingBoxHeight >= $a[1])){
					//2012-04-18: don't do anything; prevents sizing up to a pixelated image
					$target=$path.'/'.$file;
				}else{
					if($boxMethod==2){
						//--------------------------- From FEX - handle boxing ---------------------------
						$imagewidth=$a[0];
						$imageheight=$a[1];

						//here what we should do is check if there is an original and use it instead				
						unset($crop);
						$widthOver = $imagewidth/$boundingBoxWidth;
						$heightOver = $imageheight/$boundingBoxHeight;
						switch(true){
							case $widthOver>1.00 && $heightOver>1.00:
								//image overlaps the box completely - shrink by smallest ratio
								$shrinkratio=($widthOver > $heightOver ? 1/$heightOver : 1/$widthOver);
								if($widthOver==$heightOver){
									//image is aspect ratio same as box, no cropping will be needed
				
								}else if($widthOver>$heightOver){
									//crop the width
									$wprime=round($boundingBoxWidth/$shrinkratio);
									$cropLeft=round(($imagewidth-$wprime)/2);
									$crop=array(
										$cropLeft, /* start x */
										0, /* start y */
										$cropLeft + $wprime, /* end x */
										$imageheight /* end y */
									);
								}else{
									//crop the height
									$hprime=round($boundingBoxHeight/$shrinkratio);
									$cropLeft=round(($imageheight-$hprime)/2);
									$crop=array(
										0, /* start x */
										$cropLeft, /* start y */
										$imagewidth, /* end x */
										$cropLeft + $hprime /* end y */
									);
								}
							break;
							case $widthOver>1.00:
								//center and snip the sides of the overflow width
								$crop=array(
									$left=round(($imagewidth - $boundingBoxWidth)/2), /* start x */
									0, /* start y */
									$left+$boundingBoxWidth, /* end x */
									$imageheight /* end y */
								);
							break;
							case $heightOver>1.00:
								//center and snip the sides of the overflow height
								$crop=array(
									0, /* start y */
									$left=round(($imageheight - $boundingBoxHeight)/2), /* start x */
									$imagewidth, /* end x */
									$left+$boundingBoxHeight /* end y */
								);
							break;
							default:
								//image fits in the box, no need for any boxing
						}

						//'creating resized image copy at '.$boundingBoxWidth.'x'.$boundingBoxHeight.', with method '.$boxMethod
						if(!function_exists('create_thumbnail'))require($FUNCTION_ROOT.'/function_create_thumbnail_v200.php');
						$b2w=create_thumbnail($_SERVER['DOCUMENT_ROOT'].$path.'/'.$file, 1, $crop, 'returnresource');
						$createdOK=create_thumbnail($b2w, $boundingBoxWidth.','.$boundingBoxHeight, '', $_SERVER['DOCUMENT_ROOT'].$target);
						//-------------------------------- end handle boxing -------------------------------------
					}else{
						//create it
						$str="convert -size $disposition \"$source\" -resize $disposition +profile '*' \"{$_SERVER[DOCUMENT_ROOT]}$target\"";
						$result=`$str`;
						if (!$result) $target=$path.'/'.$file;
					}
				}
			}
			$path=$target;
		}

		$ext=strtolower(@end(explode('.',$path)));
		if(!in_array($ext, array('jpg','gif','png','svg'))){
			//can't show a non-image
			exit;
		}
		header('Accept-Ranges: bytes');
		header('Content-Length: '.filesize($_SERVER['DOCUMENT_ROOT'].$path));
		header('Content-Type: image/'.$ext);
		readfile($_SERVER['DOCUMENT_ROOT'].$path);
		$assumeErrorState=false;
		$suppressNormalIframeShutdownJS=true;
		exit;
	}else{
		echo $path;
	}
}
