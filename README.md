# Codeigniter Framework Note - Image Generator
1. Resize Image
2. Crop Image
3. Append Watermark To image


How to use:
-----------------------------
$this->load->helper(array('url','image'));

1. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird'));

2. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird','wm' => 'assets/image/wm.png'));

3. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird','width'=>300,'height' => 300,'action' => 'resize'));

4. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird','width'=>300,'height' => 300,'action' => 'crop'));

5. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird','width'=>300,'height' => 300,'action' => 'resize','wm' => 'assets/image/wm.png'));

6. createImage(array('path' => 'assets/image/test.jpg','name' => 'bird','width'=>300,'height' => 300,'action' => 'crop','wm' => 'assets/image/wm.png'));
