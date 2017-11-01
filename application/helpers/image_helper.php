<?php
/*
how to use:
1. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird'));
2. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird','wm' => 'assets/image/wm.png'));
3. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird','width'=>300,'height' => 300,'action' => 'resize'));
4. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird','width'=>300,'height' => 300,'action' => 'crop'));
5. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird','width'=>300,'height' => 300,'action' => 'resize','wm' => 'assets/image/wm.png'));
6. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird','width'=>300,'height' => 300,'action' => 'crop','wm' => 'assets/image/wm.png'));
*/
function createImage($data = false,$cache_folder = false){
    if(!$cache_folder){
        $cache_folder = 'cache/image';
    }
    if($data){
        $image_path = (!empty($data['path']))?$data['path']:'#';
        $image_name = (!empty($data['name']))?$data['name']:'#';
        $image_width = max(0,((!empty($data['width']))?(int)$data['width']:0));
        $image_height = max(0,((!empty($data['height']))?(int)$data['height']:0));
        $image_wm = (!empty($data['wm']))?$data['wm']:'#';
        $action = (!empty($data['action']))?strtolower($data['action']):'#';
        if (file_exists($image_path)) {
            // set new image
            $image_extension = pathinfo($image_path, PATHINFO_EXTENSION);
            $image_file_name = pathinfo($image_path, PATHINFO_FILENAME);
            if($image_wm != '#' && file_exists($image_wm)){
                $new_image = $cache_folder.'/'.str_replace('=','',base64_encode($image_file_name.'@'.$action.'@'.$image_width .'@'.$image_height.'@wm')).'.'.$image_extension;
            }
            else{
                $new_image = $cache_folder.'/'.str_replace('=','',base64_encode($image_file_name.'@'.$action.'@'.$image_width .'@'.$image_height)).'.'.$image_extension;
            }
            if($image_name == '#'){
                $image_name = $image_file_name;
            }
            if (!file_exists($new_image)) {
                if(!file_exists($cache_folder)){
                    mkdir($cache_folder,0755,true);
                }
                // load image library
                $CI =& get_instance();
                $CI->load->library('image_lib');
                // start create new image
                $vals = @getimagesize($image_path);
                $image_original_width = (int)$vals['0'];
                $image_original_height = (int)$vals['1'];
                if($image_width == 0){
                    $image_width = $image_original_width;
                }
                if($image_height == 0){
                    $image_height = $image_original_height;
                }
                $width_ratio = ($image_width/$image_original_width);
                $height_ratio = ($image_height/$image_original_height);
                if($width_ratio > $height_ratio){
                    $image_new_width = ceil($image_original_width*$height_ratio);
                    $image_new_height = $image_height;
                }
                else{
                    $image_new_width = $image_width;
                    $image_new_height = ceil($image_original_height*$width_ratio);
                }
                switch ($action){
                    case 'resize':
                        $CI->image_lib->initialize(array(
                            'image_library' => 'gd2',
                            'source_image' => $image_path,
                            'new_image' => $new_image,
                            'maintain_ratio' => true,
                            'width' => (($image_new_width > 0)?$image_new_width:false),
                            'height' => (($image_new_height > 0)?$image_new_height:false)
                        ));
                        $CI->image_lib->resize();
                        $CI->image_lib->clear();
                        break;
                    case 'crop':
                        // resize first
                        $scale_ratio = ceil(max(($image_width/$image_new_width),($image_height/$image_new_height)));
                        $CI->image_lib->initialize(array(
                            'image_library' => 'gd2',
                            'source_image' => $image_path,
                            'new_image' => $new_image,
                            'maintain_ratio' => true,
                            'width' => (($image_new_width*$scale_ratio > 0)?$image_new_width*$scale_ratio:false),
                            'height' => (($image_new_height*$scale_ratio > 0)?$image_new_height*$scale_ratio:false)
                        ));
                        $CI->image_lib->resize();
                        $CI->image_lib->clear();
                        // crop from center
                        $x_axis = (int)(($image_new_width*$scale_ratio-$image_width)/2);
                        $y_axis = (int)(($image_new_height*$scale_ratio-$image_height)/2);
                        $CI->image_lib->initialize(array(
                            'image_library' => 'gd2',
                            'source_image' => $new_image,
                            'new_image' => $new_image,
                            'maintain_ratio' => false,
                            'width' => (($image_width > 0)?$image_width:false),
                            'height' => (($image_height > 0)?$image_height:false),
                            'x_axis' => max(0,$x_axis),
                            'y_axis' => max(0,$y_axis)
                        ));
                        $CI->image_lib->crop();
                        $CI->image_lib->clear();
                        break;
                    default :
                        $CI->image_lib->initialize(array(
                            'image_library' => 'gd2',
                            'source_image' => $image_path,
                            'new_image' => $new_image,
                            'maintain_ratio' => true
                        ));
                        $CI->image_lib->resize();
                        $CI->image_lib->clear();
                }
                
                if($image_wm != '#' && file_exists($image_wm)){
                    $CI->image_lib->initialize(array(
                        'image_library' => 'gd2',
                        'source_image' => $new_image,
                        'new_image' => $new_image,
                        'maintain_ratio' => true,
                        'wm_type' => 'overlay',
                        'wm_overlay_path' => $image_wm,
                        'wm_vrt_alignment' => 'middle',
                        'wm_hor_alignment' => 'center'
                    ));
                    $CI->image_lib->watermark();
                    $CI->image_lib->clear();
                }
            }
            $imageData = base64_encode(file_get_contents($new_image));
            $src = 'data:'.mime_content_type($new_image).';base64,'.$imageData;
            return '<img src="'.$src.'" alt="'.$image_name.'"/>';
        }
    }
}