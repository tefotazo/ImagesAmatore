<?php

if( isset( $_POST['send'] ) )
{
	if( isset( $_FILES['image'] ) )
	{
		$fileName = $_FILES['image']['name'];
		
		if( !file_exists( "./temp/" . $fileName ) )
		{
			if( copy( $_FILES['image']['tmp_name'], "./temp/" . $fileName ) )
			{
				require( "./images.class.php" );
				$images = new ImageAmatore( "./temp/" . $fileName );
				
				$images->resizePerforming( 600, 600 );
				$images->save( "./resizeadas/", $_FILES['image']['name'] );
				unlink( "./temp/" . $fileName );
				
				echo "
					<script type='text/javascript'>
						alert( 'Imagen subida. Preparando crop' );
						window.location = '?image={$_FILES['image']['name']}';
					</script>
				";
			}
			else
			{
				exit( "No se pudo subir la imagen" );
			}
		}
	}
	else
	{
		exit( "Falta la imagen" );
	}
}
elseif( isset( $_POST['send-crop'] ) )
{
	echo "croppeada";
	
	if( isset( $_POST['image'] ) )
	{
		require( "./images.class.php" );
		$images = new ImageAmatore( "./resizeadas/" . $_POST['image'] );
		
		$images->cropFixed( $_POST['crop-w'], $_POST['crop-h'], $_POST['crop-x'], $_POST['crop-y'] );
		$images->save( "./resizeadas/", date("YmdHis") . "croped" . $_POST['image'] );
		//unlink( "./temp/" . $fileName );
		
		echo "
			<script type='text/javascript'>
				alert( 'Crop guardado, redireccionando' );
				window.location = '?';
			</script>
		";
	}
}
else
{
	require( "./example.php" );
}

?>