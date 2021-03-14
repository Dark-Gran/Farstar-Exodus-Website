<!DOCTYPE html>
<!--Copyright 2020 Filip Randák - DarkGran-->
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" charset="utf-8" />
<link rel="canonical" href="https://www.farstar.tech" />
<link rel="stylesheet" type="text/css" href="styling.css">
<title>FARSTAR: Exodus</title>
</head>

<body>
<div align="center"><img src="fslogo.png" alt="FARSTAR: Exodus"></div>

<?php
$err = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 	$data = $_POST;
}
if (isset($data) && isset($data['d']) && isset($data['r']) && isset($data['print']) && isset($data['pval'])) {
	$d = $data['d'];
	$r = $data['r'];
	$print = $data['print'];
	$pval = $data['pval'];
	switch ($pval) {
		case 0:
			$pstr = "400 Credits";
			break;
		case 1:
			$pstr = "1200 Credits";
			break;
		case 2:
			$pstr = "3000 Credits";
			break;
		case 3:
			$pstr = "20000 Credits";
			break;
	}
	if (!isset($pstr)) {
		$err = true;		
	}
} else {
	$err = true;
}
?>

<div class="container">

<br><br>

<?php if (!$err):?>

<script src="https://www.paypal.com/sdk/js?client-id=AaLhs21QgdzTXO8h6Awj3dXXBoCSKZ1xLuOPsp8_0BZwx0689EG2tZFkLSiyo81EuvnfPfbg4-1gt3S0"></script>
<div class="smallcontainer" id="paypal-button-container">
<br>
<div align="center" class="bigunderline">
<?php
echo 'Buying ' . $pstr  . '.';
?>
</div>
<br><br>
<script type="text/javascript">
var ppButts;
var pval = '<?php echo $pval ?>';
var pp = '0.00';
switch (pval) {
	case "0":
		pp = '2.00';
		break;
	case "1":
		pp = '5.00';
		break;
	case "2":
		pp = '10.00';
		break;
	case "3":
		pp = '60.00';
		break;
}
var userstr = '<?php echo $print ?>' + '&' + '<?php echo $r ?>';
ppButts = new paypal.Buttons({
	style: {
		layout: 'vertical',
		color: 'blue'
	},
	createOrder: function(data, actions) {
     	return actions.order.create({
        application_context: {
	  		shipping_preference:'NO_SHIPPING',
	  		brand_name:'FARSTAR: Exodus',
	  		user_action:'PAY_NOW'
		},
       	purchase_units: [{
			amount: {
				value: pp
			},
			custom_id: userstr
		}]
     	});
   	},
   	onApprove: function(data, actions) {
     	return actions.order.capture().then(function(details) {
        window.location.replace("/success.html");
     	});
   	}
});
ppButts.render("#paypal-button-container");
</script>
</div>
<br><br><br><br><br><br><br>

<?php else: 
$err = true;
?>
<br><br>
<div align="center" id="Void">	
<i>Right place at the wrong time...<br><br>
Did we get lost in the Void?<br><br>
Must have been my concentration...<br><br>
...<br><br>
Shall we try again?</i>
</div>
<br><br><br><br>

<?php endif; ?>
<?php if (!$err):?>
<div id="backbutton" align="center">
<a href="shop.php?d=<?php echo $d ?>&r=<?php echo $r ?>&print=<?php echo $print ?>"><img src="images/back.png" id="back"></a>
</div>
<?php endif; ?>
<?php if ($err):?>
<br><br><br><br><br><br>
<?php endif; ?>

<hr>
<div class="footer" align="left"><p> &#x00a9; 2020 Filip Randák - DarkGran (ICO/CRN: 01800485)</p></div>
<div align="center"><a href="index.html"><img alt="" src="fsgran.png"></a></div>

</div>
</body>
</html>