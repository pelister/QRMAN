<?php
 
 $width = $height = 200; 
 $url = urlencode("http://techlister.com"); 
 $error = 'L';
?>

<div>
<p>Formican with size specified</p>
<?php echo "<img src=\"http://formican.com/qr-api.php?size={$width}x{$height}&action=qr&ec=$error&data=$url\" />"; // my formican api
 ?>
</div>
 
 
<div>
<p>Formican without size specified</p>
<?php
$url = urlencode("http://anim.me"); 
echo "<img src=\"http://formican.com/qr-api.php?action=qr&ec=$error&data=$url\" />";  // without specifying size
?>
</div>

 
<div>
<p>Google with size specified</p>
<?php
echo "<img src=\"http://chart.googleapis.com/chart?chs={$width}x{$height}&cht=qr&chld=$error&chl=$url\" />";  // google api
?>
</div>


</div>
 