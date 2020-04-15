<?php

$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$url = explode("/",$url);
if(strpos($_SESSION['local_website'],"localhost") !== false){
	$_SESSION['version'] = $url[5];	
} else {
	$_SESSION['version'] = $url[3];	
}
$_SESSION['version'] = explode(".",$_SESSION['version']);

$img_src = "../logos/".$_SESSION['version'][0]."_white.png";

$_SESSION['version'] = implode(".",$_SESSION['version']);

?>
<image src="<?= $img_src ?>" style="margin:5px; width:30px; height:30px" title="This is the <?= $version ?> release of Collector" id='version_logo'>
<script>
version = "<?= $_SESSION['version'] ?>";
if(version == "antelope"){
	$("#version_logo").hover(function(){
		bootbox.alert("This version will be deprecated in favour of the kitten and cat releases. Please e-mail a.haffey@reading.ac.uk if you have any questions about this migration.");
	});
} else {
	var orig_filter = $("#version_logo").css("filter");
	$("#version_logo").on("load",function(){
		$("#version_logo").css("filter",'grayscale(100%)');
		setTimeout(function(){
			setTimeout(function(){
				setTimeout(function(){
					$("#version_logo").css("filter",orig_filter);
				}, 400);
				$("#version_logo").css("filter",'grayscale(100%)');
			}, 400);
			$("#version_logo").css("filter",orig_filter);
		}, 400);
	});	
}
</script>