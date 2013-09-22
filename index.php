<?php

include_once('cart/cart.php');

session_start();
?>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />

		<title>Select Web Hosting</title>

		<link rel="stylesheet" type="text/css" media="screen, projection" href="style.css" />

		<link rel="stylesheet" type="text/css" media="screen, projection" href="cart/css/cart.css" />

	</head>
	<body>
		<div id="wrapper">
			<h2>Select a Plan and a Domain Name</h2>

			<div id="sidebar">
				<div id="cart"><?php $cart->display_cart();?></div>
			</div>

			<div id="content">

				<form method="post" action="" class="cart">
					<fieldset>
						<input type="hidden" name="cartToken" value="<?php echo $_SESSION['cartToken'];?>" />
						<input type="hidden" name="my-item-id" value="ABC-123" />
						<input type="hidden" name="my-item-name" value="Student Hosting Plan" />
						<input type="hidden" name="my-item-price" value="399.00" />
						<input type="hidden" name="my-item-url" value="" />

						<ul>
							<li><strong>Student Hosting Plan</strong></li>
						
							<li>Price: Rs.399</li>
							<li>
								<label>Qty: <input type="text" name="my-item-qty" value="1" size="3" /></label>
							</li>
						</ul>

						<input type="submit" name="my-add-button" value="add to cart" class="button" />
					</fieldset>
				</form>

				<form method="post" action="" class="cart">
					<fieldset>
						<input type="hidden" name="cartToken" value="<?php echo $_SESSION['cartToken'];?>" />
						<input type="hidden" name="my-item-id" value="2" />
						<input type="hidden" name="my-item-name" value="Indian Domain Name" />
						<input type="hidden" name="my-item-price" value="299.00" />
						<input type="hidden" name="my-item-url" value="" />

						<ul>
							<li><strong>.IN Domain(.IN/.CO.IN)</strong></li>
							<li>Price: Rs.299.00</li>
							<li>
								<label>Qty: <input type="text" name="my-item-qty" value="1" size="3" /></label>
							</li>
						</ul>

						<input type="submit" name="my-add-button" value="add to cart" class="button" />
					</fieldset>
				</form>
<form method="post" action="" class="cart">
					<fieldset>
						<input type="hidden" name="cartToken" value="<?php echo $_SESSION['cartToken'];?>" />
						<input type="hidden" name="my-item-id" value="3" />
						<input type="hidden" name="my-item-name" value="International Domain Name" />
						<input type="hidden" name="my-item-price" value="499.00" />
						<input type="hidden" name="my-item-url" value="" />

						<ul>
							<li><strong>.COM/.NET/.ORG/.INFO DOMAIN</strong></li>
							<li>Price: Rs.499.00</li>
							<li>
								<label>Qty: <input type="text" name="my-item-qty" value="1" size="3" /></label>
							</li>
						</ul>

						<input type="submit" name="my-add-button" value="add to cart" class="button" />
					</fieldset>
				</form>

				<div class="clear"></div>

				
			</div>

			<div class="clear"></div>
		</div>

		<script type="text/javascript" src="cart/js/jquery-1.4.4.min.js"></script>
		<script type="text/javascript" src="cart/js/cart.min.js"></script>
	</body>
</html>