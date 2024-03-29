
<?php
/**
*By
*Mukesh kumar mawaliya
*/
class cart {

	public $config     = array();
	private $items     = array();
	private $names     = array();
	private $prices    = array();
	private $qtys      = array();
	private $urls      = array();
	private $subtotal  = 0;
	private $itemCount = 0;

	function __construct() {

		// Get $config array
		include_once('config-loader.php');
		$this->config = $config;
	}

	/**
	* Get cart contents
	*
	* @return array
	*/
	public function get_contents() {
		$items = array();
		foreach($this->items as $tmpItem) {
			$item = null;
			$item['id']       = $tmpItem;
			$item['name']     = $this->names[$tmpItem];
			$item['price']    = $this->prices[$tmpItem];
			$item['qty']      = $this->qtys[$tmpItem];
			$item['url']      = $this->urls[$tmpItem];
			$item['subtotal'] = $item['price'] * $item['qty'];
			$items[]          = $item;
		}
		return $items;
	}

	/**
	* Add an item to the cart
	*
	* @param string $id
	* @param string $name
	* @param float $price
	* @param mixed $qty
	* @param string $url
	*
	* @return mixed
	*/
	private function add_item($id, $name, $price, $qty = 1, $url) {

		$validPrice = false;
		$validQty = false;

		// Verify the price is numeric
		if (is_numeric($price)) {
			$validPrice = true;
		}

		// If decimal quantities are enabled, verify the quantity is a positive float
		if ($this->config['decimalQtys'] === true && filter_var($qty, FILTER_VALIDATE_FLOAT) && $qty > 0) {
			$validQty = true;
		}
		// By default, verify the quantity is a positive integer
		elseif (filter_var($qty, FILTER_VALIDATE_INT) && $qty > 0) {
			$validQty = true;
		}

		// Add the item
		if ($validPrice !== false && $validQty !== false) {

			// If the item is already in the cart, increase its quantity
			if($this->qtys[$id] > 0) {
				$this->qtys[$id] += $qty;
				$this->update_subtotal();
			}
			// This is a new item
			else {
				$this->items[]     = $id;
				$this->names[$id]  = $name;
				$this->prices[$id] = $price;
				$this->qtys[$id]   = $qty;
				$this->urls[$id]   = $url;
			}
			$this->update_subtotal();
			return true;
		}
		elseif ($validPrice !== true) {
			$errorType = 'price';
			return $errorType;
		}
		elseif ($validQty !== true) {
			$errorType = 'qty';
			return $errorType;
		}
	}

	/**
	* Update an item in the cart
	*
	* @param string $id
	* @param mixed $qty
	*
	* @return boolean
	*/
	private function update_item($id, $qty) {

		// If the quantity is zero, no futher validation is required
		if ((int) $qty === 0) {
			$validQty = true;
		}
		// If decimal quantities are enabled, verify it's a float
		elseif ($this->config['decimalQtys'] === true && filter_var($qty, FILTER_VALIDATE_FLOAT)) {
			$validQty = true;
		}
		// By default, verify the quantity is an integer
		elseif (filter_var($qty, FILTER_VALIDATE_INT))	{
			$validQty = true;
		}

		// If it's a valid quantity, remove or update as necessary
		if ($validQty === true) {
			if($qty < 1) {
				$this->remove_item($id);
			}
			else {
				$this->qtys[$id] = $qty;
			}
			$this->update_subtotal();
			return true;
		}
	}


	

	/**
	* items from the shopping cart are removed by passing id of the item type,here it is the id of the hosting package
	* Reamove an item from the cart
	*
	* @param string $id	*
	*/
	private function remove_item($id) {
		$tmpItems = array();

		unset($this->names[$id]);
		unset($this->prices[$id]);
		unset($this->qtys[$id]);
		unset($this->urls[$id]);

		// Rebuild the items array, excluding the id we just removed
		foreach($this->items as $item) {
			if($item != $id) {
				$tmpItems[] = $item;
			}
		}
		$this->items = $tmpItems;
		$this->update_subtotal();
	}

	/**
	* Empty the cart
	*/
	public function empty_cart() {
		$this->items     = array();
		$this->names     = array();
		$this->prices    = array();
		$this->qtys      = array();
		$this->urls      = array();
		$this->subtotal  = 0;
		$this->itemCount = 0;
	}

	/**
	* Update the entire cart
	*/
	public function update_cart() {

		// Post value is an array of all item quantities in the cart
		// Treat array as a string for validation
		if (is_array($_POST['cartItemQty'])) {
			$qtys = implode($_POST['cartItemQty']);
		}

		// If no item ids, the cart is empty
		if ($_POST['cartItemId']) {

			$validQtys = false;

			// If decimal quantities are enabled, verify the combined string only contain digits and decimal points
			if ($this->config['decimalQtys'] === true && preg_match("/^[0-9.]+$/i", $qtys)) {
				$validQtys = true;
			}
			// By default, verify the string only contains integers
			elseif (filter_var($qtys, FILTER_VALIDATE_INT) || $qtys == '') {
				$validQtys = true;
			}

			if ($validQtys === true) {

				// The item index
				$count = 0;

				// For each item in the cart, remove or update as necessary
				foreach ($_POST['cartItemId'] as $id) {

					$qty = $_POST['cartItemQty'][$count];

					if($qty < 1) {
						$this->remove_item($id);
					}
					else {
						$this->update_item($id, $qty);
					}

					// Increment index for the next item
					$count++;
				}
				return true;
			}
		}
		// If no items in the cart, return true to prevent unnecssary error message
		elseif (!$_POST['cartItemId']) {
			return true;
		}
	}

	/**
	* Recalculate subtotal
	*/
	private function update_subtotal() {
		$this->itemCount = 0;
		$this->subtotal  = 0;

		if(sizeof($this->items > 0)) {
			foreach($this->items as $item) {
				$this->subtotal += ($this->qtys[$item] * $this->prices[$item]);

				// Total number of items
				$this->itemCount += $this->qtys[$item];
			}
		}
	}

	/**
	* Process and display cart
	*/
	public function display_cart() {

		$config = $this->config; 
		$errorMessage = null;

		// Simplify some config variables
		$checkout = $config['checkoutPath'];
		$priceFormat = $config['priceFormat'];

		$id    = $config['item']['id'];
		$name  = $config['item']['name'];
		$price = $config['item']['price'];
		$qty   = $config['item']['qty'];
		$url   = $config['item']['url'];
		$add   = $config['item']['add'];

		// Use config values as literal indices for incoming POST values
		// Values are the HTML name attributes set in config.json
		$id    = $_POST[$id];
		$name  = $_POST[$name];
		$price = $_POST[$price];
		$qty   = $_POST[$qty];
		$url   = $_POST[$url];

		$cartToken = $_POST['cartToken'];

		// Only generate unique token once per session
		if(!$_SESSION['cartToken']){
			$_SESSION['cartToken'] = md5(session_id() . time() . $_SERVER['HTTP_USER_AGENT']);
		}
		// If enabled, check submitted token against session token for POST requests
		if ($config['csrfToken'] === 'true' && $_POST && $cartToken != $_SESSION['cartToken']) {
			$errorMessage = 'Invalid token!' . $cartToken . ' / ' . $_SESSION['cartToken'];
		}

		// Sanitize values for output in the browser
		$id    = filter_var($id, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
		$name  = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
		$url   = filter_var($url, FILTER_SANITIZE_URL);

		// Round the quantity if necessary
		if($config['decimalPlaces'] === true) {
			$qty = round($qty, $config['decimalPlaces']);
		}

		// Add an item
		if ($_POST[$add]) {
			$itemAdded = $this->add_item($id, $name, $price, $qty, $url);
			// If not true the add item function returns the error type
			if ($itemAdded !== true) {
				$errorType = $itemAdded;
				switch($errorType) {
					case 'qty':
						$errorMessage = $config['text']['quantityError'];
						break;
					case 'price':
						$errorMessage = $config['text']['priceError'];
						break;
				}
			}
		}

		// Update a single item
		if ($_POST['cartUpdate']) {
			$itemUpdated = $this->update_item($_POST['itemId'], $_POST['itemQty']);
			if ($itemUpdated !== true)	{
				$errorMessage = $config['text']['quantityError'];
			}
		}

		// Update all items in the cart
		if($_POST['cartUpdateCart'] || $_POST['cartCheckout'])	{
			$cartUpdated = $this->update_cart();
			if ($cartUpdated !== true)	{
				$errorMessage = $config['text']['quantityError'];
			}
		}

		// Remove an item
		/* After an item is removed, its id stays set in the query string,
		preventing the same item from being added back to the cart in
		subsequent POST requests.  As result, it's not enough to check for
		GET before deleting the item, must also check that this isn't a POST
		request. */
		if($_GET['cartRemove'] && !$_POST) {
			$this->remove_item($_GET['cartRemove']);
		}

		// Empty the cart
		if($_POST['cartEmpty']) {
			$this->empty_cart();
		}

		// Determine which text to use for the number of items in the cart
		$itemsText = $config['text']['multipleItems'];
		if ($this->itemCount == 1) {
			$itemsText = $config['text']['singleItem'];
		}

		// Determine if this is the checkout page
		/* This can be used after payment gateway integration. In this project it is not used for now.
		First we check the request uri against the config checkout (set when
		the visitor first clicks checkout), then check for the hidden input
		sent with Ajax request (set when visitor has javascript enabled and
		updates an item quantity). */
		$isCheckout = strpos(request_uri(), $checkout);
		if ($isCheckout !== false || $_REQUEST['cartIsCheckout'] == 'true') {
			$isCheckout = true;
		}
		else {
			$isCheckout = false;
		}

		// Overwrite the form action to post to gateway.php instead of posting back to checkout page
		if ($isCheckout === true) {

		}

		// Default input type
		// Overridden if using button images in config.php
		$inputType = 'submit';

		// If this error is true the visitor updated the cart from the checkout page using an invalid price format
		// Passed as a session var since the checkout page uses a header redirect
		// If passed via GET the query string stays set even after subsequent POST requests
		if ($_SESSION['quantityError'] === true) {
			$errorMessage = $config['text']['quantityError'];
			unset($_SESSION['quantityError']);
		}

		// Set currency symbol based on config currency code
		$currencyCode = trim(strtoupper($config['currencyCode']));
		switch($currencyCode) {
			case 'EUR':
				$currencySymbol = '&#128;';
				break;
			case 'USD':
			default:
				$currencySymbol = 'Rs.';
				break;
		}

		////////////////////////////////////////////////////////////////////////
		// Output the cart

		// Return specified number of tabs to improve readability of HTML output
		function tab($n) {
			$tabs = null;
			while ($n > 0) {
				$tabs .= "\t";
				--$n;
			}
			return $tabs;
		}

		// If there's an error message wrap it in some HTML
		if ($errorMessage)	{
			$errorMessage = "<p id='cart-error'>$errorMessage</p>";
		}

		// Display the cart header
		echo tab(1) . "$errorMessage\n";
		echo tab(1) . "<form method='post' action='$checkout'>\n";
		echo tab(2) . "<fieldset>\n";
		echo tab(3) . "<input type='hidden' name='cartToken' value='{$_SESSION['cartToken']}' />\n";
		echo tab(3) . "<table border='1'>\n";
		echo tab(4) . "<thead>\n";
		echo tab(5) . "<tr>\n";
		echo tab(6) . "<th colspan='3'>\n";
		echo tab(7) . "<strong id='cart-title'>{$config['text']['cartTitle']}</strong> ($this->itemCount $itemsText)\n";
		echo tab(6) . "</th>\n";
		echo tab(5) . "</tr>". "\n";
		echo tab(4) . "</thead>\n";
		
		// Display the cart footer
		echo tab(4) . "<tfoot>\n";
		echo tab(5) . "<tr>\n";
		echo tab(6) . "<th colspan='3'>\n";

		// If this is the checkout hide the cart checkout button
		if ($isCheckout !== true) {
			if ($config['button']['checkout']) {
				$inputType = "image";
				$src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
			}
			echo tab(7) . "<input type='$inputType' $src id='cart-checkout' name='cartCheckout' class='cart-button' value='{$config['text']['checkout']}' />\n";
		}

		echo tab(7) . "<span id='cart-subtotal'>{$config['text']['subtotal']}: <strong>$currencySymbol" . number_format($this->subtotal, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></span>\n";
		echo tab(6) . "</th>\n";
		echo tab(5) . "</tr>\n";
		echo tab(4) . "</tfoot>\n";			
		
		echo tab(4) . "<tbody>\n";

		// If any items in the cart
		if($this->itemCount > 0) {

			// Display line items
			foreach($this->get_contents() as $item)	{
				echo tab(5) . "<tr>\n";
				echo tab(6) . "<td class='cart-item-qty'>\n";
				echo tab(7) . "<input name='cartItemId[]' type='hidden' value='{$item['id']}' />\n";
				echo tab(7) . "<input id='cartItemQty-{$item['id']}' name='cartItemQty[]' size='2' type='text' value='{$item['qty']}' />\n";
				echo tab(6) . "</td>\n";
				echo tab(6) . "<td class='cart-item-name'>\n";

				if ($item['url']) {
					echo tab(7) . "<a href='{$item['url']}'>{$item['name']}</a>\n";
				}
				else {
					echo tab(7) . $item['name'] . "\n";
				}
				echo tab(7) . "<input name='cartItemName[]' type='hidden' value='{$item['name']}' />\n";
				echo tab(6) . "</td>\n";
				echo tab(6) . "<td class='cart-item-price'>\n";
				echo tab(7) . "<span>$currencySymbol" . number_format($item['subtotal'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</span><input name='cartItemPrice[]' type='hidden' value='{$item['price']}' />\n";
				echo tab(7) . "<a class='cart-remove' href='?cartRemove={$item['id']}'>{$config['text']['removeLink']}</a>\n";
				echo tab(6) . "</td>\n";
				echo tab(5) . "</tr>\n";
			}
		}

		// The cart is empty
		else {
			echo tab(5) . "<tr><td id='cart-empty' colspan='3'>{$config['text']['emptyMessage']}</td></tr>\n";
		}
		echo tab(4) . "</tbody>\n";
		echo tab(3) . "</table>\n\n";

		echo tab(3) . "<div id='cart-buttons'>\n";

		if ($config['button']['update']) {
			$inputType = "image";
			$src = " src='{$config['button']['update']}' alt='{$config['text']['update']}' title='' ";
		}

		echo tab(4) . "<input type='$inputType' $src name='cartUpdateCart' value='{$config['text']['update']}' class='cart-button' />\n";

		if ($config['button']['empty']) {
			$inputType = "image";
			$src = " src='{$config['button']['empty']}' alt='{$config['text']['emptyButton']}' title='' ";
		}

		echo tab(4) . "<input type='$inputType' $src name='cartEmpty' value='{$config['text']['emptyButton']}' class='cart-button' />\n";
		echo tab(3) . "</div>\n";

		// If this is the checkout display the checkout button for now i have not implemented payment methods.
		if ($isCheckout === true) {
		
		}

		echo tab(2) . "</fieldset>\n";
		echo tab(1) . "</form>\n\n";
		
		echo tab(1) . "<div id='cart-tooltip'></div>\n";
	}
}

// Start a new session in case it hasn't already been started on the including page
@session_start();

// Initialize cart after session start
$cart = $_SESSION['cart'];
if(!is_object($cart)) {
	$cart = $_SESSION['cart'] = new cart();
}

// Enable request_uri for non-Apache environments
//http://api.drupal.org/api/function/request_uri/7
if (!function_exists('request_uri')) {
	function request_uri() {
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
		}
		else {
			if (isset($_SERVER['argv'])) {
				$uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['argv'][0];
			}
			elseif (isset($_SERVER['QUERY_STRING'])) {
				$uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
			}
			else {
				$uri = $_SERVER['SCRIPT_NAME'];
			}
		}
		$uri = '/' . ltrim($uri, '/');
		return $uri;
	}
}
?>