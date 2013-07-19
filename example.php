<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="content-type" content="text/html" />
	<meta name="author" content="Estefano" />

	<title>Upload an Image</title>
</head>

<body>

<?php if( !isset( $_GET['image'] ) ): ?>
<form action="" method="post" enctype="multipart/form-data">
	<input type="file" name="image" id="image" />
	
	<input type="submit" name="send" id="send" value="Subir Imagen" />
</form>
<?php else: ?>

<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery.Jcrop.js" type="text/javascript"></script>

<script type="text/javascript">

jQuery(function($){
	$('#crop-me').Jcrop({
		onChange:   showCoords,
		onSelect:   showCoords
	});	
});
	
	// Simple event handler, called from onChange and onSelect
	// event handlers, as per the Jcrop invocation above
	function showCoords(c)
	{
		$('#crop-x').val(c.x);
		$('#crop-y').val(c.y);
		//$('#x2').val(c.x2);
		//$('#y2').val(c.y2);
		$('#crop-w').val(c.w);
		$('#crop-h').val(c.h);
	};
	
	function sendCrop()
	{
		$("#crop-all").submit();
	}

</script>

<img src="./resizeadas/<?= $_GET['image']; ?>" id="crop-me" />
<br /><br />
<a href="#" onclick="sendCrop(); return false;">send crop</a>

<form action="" method="post" id="crop-all">
	<input type="hidden" name="send-crop" id="send-crop" />
	<input type="hidden" name="crop-x" id="crop-x" />
	<input type="hidden" name="crop-y" id="crop-y" />
	<input type="hidden" name="crop-w" id="crop-w" />
	<input type="hidden" name="crop-h" id="crop-h" />
	<input type="hidden" name="image" id="image" value="<?= $_GET['image']; ?>" />
</form>

<?php endif; ?>

</body>
</html>