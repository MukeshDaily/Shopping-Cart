<?php

include_once('cart/cart.php');

session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />

		<title>Ordered</title>

		<link rel="stylesheet" type="text/css" media="screen, projection" href="style.css" />

		<link rel="stylesheet" type="text/css" media="screen, projection" href="cart/css/cart.css" />
	</head>
	<body>
	
	
	<?php
if($_POST[filled]==1)
{
$TotalPrice=0;
		foreach ($cart->get_contents() as $item) 
		{
     		$TotalPrice +=$item['qty']*$item['price'];
			$queryString .=' Name '. $item['name'];
			$queryString .=' Rs '. $item['price'];
			$queryString .=' Qt '. $item['qty'];
			
		}	
			$queryString .=' TotalPrice '.$TotalPrice;
			$bd=$_POST[month].':'.$_POST[day].':'.$_POST[year];
$con=mysql_connect("localhost","root","") or die("Could not connect to the Database Server");

mysql_select_db("hostpgbghd", $con);

$q="Insert into hostyuser (emaily, usernamey, orgpasswordy, mobile, address, security_ques, security_ans, country, gender, bd, invoice) values ('$_POST[emaily]', '$_POST[usernamey]', '$_POST[orgpasswordy]', '$_POST[mobile]', '$_POST[address]', '$_POST[security_ques]', '$_POST[security_ans]', '$_POST[country]', '$_POST[gender]', '$bd', '$queryString')";
mysql_query($q, $con);

mysql_close($con);


		

$ToEmail = $_POST[emaily];
$ToSubject = "01NATION Web Hosting invoice" ;

$EmailBody =   "UserName: $_POST[usernamey]\n 
pass: $_POST[orgpasswordy]\n Web Hosting invoice\n $queryString \n visit: www.01nation.com \n contact us: info@01nation.com\n mukeshmawaliya@gmail.com";

$Message = $EmailBody;


$headers .= "Content-type: text; charset=iso-8859-1\r\n";
$headers .= "From: 01NATION\r\n";

mail($ToEmail,$ToSubject,$Message, $headers);


 echo "successfully Registered for 01nation hosting. We shall contact you soon.Now follow the link given below and book your domain";
 
  
}
?>


	
		<div id="wrapper">
			<h2>Invoice</h2>

			<div id="sidebar">
			</div>
<?php   
if($_POST[filled]==1)
{ 
 echo $queryString;
}
?>
<h2>Book your Domain Now. If you have added the domain price,then price will not be added again.</h2>
<p><a href="http://01nationin.supersite.myorderbox.com">For Indian Domain Name Click here(.IN,.CO.IN and more....) </a></p>
<p><a href="http://01nation.supersite.myorderbox.com/">For International Domain Name Click here(.COM,.NET,.ORG,.INFO and more....) </a></p>
				
			<div class="clear"></div>
		</div>

		<script type="text/javascript" src="cart/js/jquery-1.4.4.min.js"></script>
		<script type="text/javascript" src="cart/js/cart.min.js"></script>
	</body>
</html>