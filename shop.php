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

<div class="container">

<br><br>

<?php if (isset($_GET) && isset($_GET['d']) && isset($_GET['r'])  && $_GET['r'] != "0" && isset($_GET['print'])):?>

<div class="smallcontainer" id="creditspage">
<div class="chaptername" align="center">Choose a Package:</div>
<br>

<form class="unselectable" target="_self" action="/payment.php" method="POST">
<div class="custom-select" align="left">
<input type="radio" id="0" name="pval" value="0">
<label class="custom-label" for="0">400 Credits - 2 USD</label><br>
<input type="radio" id="1" name="pval" value="1" checked="checked">
<label class="custom-label"for="1">1200 Credits - 5 USD</label><br>
<input type="radio" id="2" name="pval" value="2">
<label class="custom-label"for="2">3000 Credits - 10 USD</label><br>
<input type="radio" id="3" name="pval" value="3">
<label class="custom-label"for="3">20000 Credits - 60 USD</label>
</div>

<br>
<?php
$data = $_GET;
$d = $data['d'];
$r = $data['r'];
$print = $data['print'];
$userstr = "(user:" . $print . ")";
echo htmlspecialchars($userstr, ENT_QUOTES, 'UTF-8');
?>
<br><br><br>
<div align="center">
<div align="center" class="buynow">
<input class="unselectable" type="hidden" name="d" value="<?php echo $d ?>" />
<input class="unselectable" type="hidden" name="r" value="<?php echo $r ?>" />
<input class="unselectable" type="hidden" name="print" value="<?php echo $print ?>" />
<input class="unselectable" type="image" src="images/buynow.png" border="0" alt="[buy now]" />
</form>
</div>
</div>
</div>

<?php else: ?>
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


<br><br><br><br><br><br>
<hr>
<div class="footer" align="left"><p> &#x00a9; 2020 Filip Randák - DarkGran (ICO/CRN: 01800485)</p></div>
<div align="center"><a href="index.html"><img alt="" src="fsgran.png"></a></div>

</div>
</body>
</html>