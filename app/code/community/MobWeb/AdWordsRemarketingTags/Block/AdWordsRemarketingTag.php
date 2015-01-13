<?php
class MobWeb_AdWordsRemarketingTags_Block_AdWordsRemarketingTag extends Mage_Core_Block_Abstract
{
	private $ecommPageType;

	protected function getEcommProdId()
	{
		$ecommPageType = $this->getEcommPageType();

		// Only pass a product ID for the "product" or "cart" page types
		if(!in_array($ecommPageType, array('product', 'cart'))) {
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommProdId: Pagetype is "%s", not passing a product ID!', $ecommPageType));

			return;
		}

		if($ecommPageType === 'product') {

			// On the product page, get the current product's ID
			$productId = Mage::registry('current_product')->getId();
		} else {

			// Loop through the items in the cart, collect their IDs
			foreach(Mage::helper('adwordsremarketingtags')->getProductsInCart() AS $product) {
				$productIds[] = $product->getId();
				Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommProdId: Product ID in cart collected: %s', $product->getId()));
			}

			// Implode the IDs of the items in the cart, make sure they are formatted in JavaScript array notation, e.g.: ["1","2","3"]
			$productId = '[\'' . implode("','", $productIds) . '\']';
		}

		Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommProdId: Product ID is "%s"', $productId));

		return $productId;
	}
	
	protected function getEcommPageType()
	{
		if(isset($this->ecommPageType)) {
			return $this->ecommPageType;
		}

		/*
		 *
		 * Get the type of page that we are currently on, by looking at the current
		 * module, controller and action. Possible page type values:
		 * home, searchresults, category, product, cart, purchase, other
		 *
		 * See: https://developers.google.com/adwords-remarketing-tag/parameters
		 *
		 */
		$moduleName = Mage::app()->getRequest()->getModuleName();
		$controllerName = Mage::app()->getRequest()->getControllerName();
		$actionName = Mage::app()->getRequest()->getActionName();

		switch($moduleName . ',' . $controllerName . ',' . $actionName) {

			case 'cms,index,index':
				$ecommPageType = 'home';
				break;

			case 'catalogsearch,result,index':
				$ecommPageType = 'searchresults';
				break;

			case 'catalog,category,view':
				$ecommPageType = 'category';
				break;

			case 'catalog,product,view':
				$ecommPageType = 'product';
				break;

			case 'checkout,cart,index':
				$ecommPageType = 'cart';
				break;

			case 'checkout,onepage,success':
				$ecommPageType = 'purchase';
				break;

			default:
				$ecommPageType = 'other';
				break;
		}

		Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommPageType: Pagetype is "%s", module is "%s", controller is "%s", action is "%s"', $ecommPageType, $moduleName, $controllerName, $actionName));

		return $this->ecommPageType = $ecommPageType;
	}
	
	protected function getEcommCategory()
	{
		$ecommPageType = $this->getEcommPageType();

		// Only pass the category for the "category" and "product" page types
		if(!in_array($ecommPageType, array('category', 'product'))) {
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommCategory: Pagetype is "%s", not passing a category', $ecommPageType));

			return;
		}

		if($ecommPageType === 'category') {

			// On the category page, extract the current category from the registry
			$currentCategory = Mage::registry('current_category');
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommCategory: Current category is "%s", category path: "%s"', $currentCategory->getEntityId(), $currentCategory->getPath()));

			$categoryPath = Mage::helper('adwordsremarketingtags')->getCategoryPathAsString($currentCategory);
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommCategory: Category path as string: %s', $categoryPath));

			return $categoryPath;
		} else {

			// On the product page, get the current product
			$product = Mage::registry('current_product');
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommCategory: Product loaded: %s', $product->getId()));

			// Get the product's categories
			$productCategories = $product->getCategoryCollection();

			// For each product category, get the category tree as a string
			$productCategoriesPaths = array();
			foreach($productCategories AS $productCategory) {
				$productCategoryPath = Mage::helper('adwordsremarketingtags')->getCategoryPathAsString($productCategory);
				$productCategoriesPaths[] = $productCategoryPath;
			}

			// Implode the category paths, separated by a comma
			$productCategoriesPaths = implode(',', $productCategoriesPaths);
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommCategory: Product category paths as string: %s', $productCategoriesPaths));

			return $productCategoriesPaths;
		}
	}
	
	protected function getEcommTotalValue()
	{
		$ecommPageType = $this->getEcommPageType();

		// Only pass the total value for the "product", "cart" and "purchase" page type
		if(!in_array($ecommPageType, array('product', 'cart', 'purchase'))) {
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommTotalValue: Pagetype is "%s", not passing a total value', $ecommPageType));

			return;
		}

		if($ecommPageType === 'product') {

			// On the product page, get the current product
			$product = Mage::registry('current_product');
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommTotalValue: Product loaded: %s', $product->getId()));

			// Get the product's final price from the helper function
			$totalValue = Mage::helper('adwordsremarketingtags')->getProductPrice($product);
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommTotalValue: Product price determined: %s', $totalValue));
		} else if($ecommPageType === 'cart') {

			// On the cart page, get the cart's grand total, with and without taxes
			$totalValueWithoutTaxes = $this->helper('checkout/cart')->getQuote()->getSubtotal();
			$totalValue = $this->helper('checkout/cart')->getQuote()->getGrandTotal();
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommTotalValue: Total value without taxes: %s, and with taxes: %s', $totalValueWithoutTaxes, $totalValue));

			// Check if the taxes should be included in the total value
			$includeTaxesInValues = Mage::helper('adwordsremarketingtags')->getIncludeTaxesInValues();
			$totalValue = $includeTaxesInValues ? $totalValue : $totalValueWithoutTaxes;
		} else if($ecommPageType === 'purchase') {

			// On the purchase page, get the order's grand total, with and without taxes
			$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
			$order = Mage::getModel('sales/order')->load($orderId);
			$totalValueWithoutTaxes = $order->getSubtotal();
			$totalValue = $order->getGrandTotal();
			Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommTotalValue: Total value without taxes: %s, and with taxes: %s', $totalValueWithoutTaxes, $totalValue));

			// Check if the taxes should be included in the total value
			$includeTaxesInValues = Mage::helper('adwordsremarketingtags')->getIncludeTaxesInValues();
			$totalValue = $includeTaxesInValues ? $totalValue : $totalValueWithoutTaxes;
		}

		// Format the total value and round it to the nearest two decimal
		$totalValue = number_format((float) $totalValue, 2, '.', '');
		Mage::helper('adwordsremarketingtags')->log(sprintf('getEcommTotalValue: Total value %s', $totalValue));

		return $totalValue;
	}
	
	protected function getIsSaleItem()
	{
		$ecommPageType = $this->getEcommPageType();

		// Only pass the total value for the "product" page type
		if(!in_array($ecommPageType, array('product'))) {
			Mage::helper('adwordsremarketingtags')->log(sprintf('getIsSaleItem: Pagetype is "%s", not passing this value', $ecommPageType));

			return;
		}

		// On the product page, get the current product
		$product = Mage::registry('current_product');
		Mage::helper('adwordsremarketingtags')->log(sprintf('getIsSaleItem: Product loaded: %s', $product->getId()));

		// Check whether the current product is on sale
		$isSaleItem = ($product->getSpecialPrice() > 0) ? 'true' : 'false';
		Mage::helper('adwordsremarketingtags')->log(sprintf('getIsSaleItem: Result: %s', $isSaleItem));
		return $isSaleItem;
	}
	
	protected function getReturnCustomer()
	{
		// Check if the customer is currently logged in
		$returnCustomer = Mage::getSingleton('customer/session')->isLoggedIn() ? 'true' : 'false';
		Mage::helper('adwordsremarketingtags')->log(sprintf('getReturnCustomer: Result: %s', $returnCustomer));

		return $returnCustomer;
	}
	
	protected function getGoogleConversionId()
	{
		// Get the value from the configuration
		$googleConversionId = Mage::helper('adwordsremarketingtags')->getSettings('google_conversion_id');
		Mage::helper('adwordsremarketingtags')->log(sprintf('getGoogleConversionId: Result: %s', $googleConversionId));

		return $googleConversionId;
	}

	protected function _toHtml()
	{
		$return = '';

		// Collect all the required information
		$data = array(
			'ecomm_prodid' => $this->getEcommProdId(),
			'ecomm_pagetype' => $this->getEcommPageType(),
			'ecomm_totalvalue' => $this->getEcommTotalValue(),
			'ecomm_category' => $this->getEcommCategory(),
			'isSaleItem' => $this->getIsSaleItem(),
			'returnCustomer' => $this->getReturnCustomer(),
			'google_conversion_id' => $this->getGoogleConversionId()
		);

		// Log it to the console, if debugging is enabled
		if(Mage::helper('adwordsremarketingtags')->getIsDebug()) {

			// Explode the data array into a string that can be pasted into JS
			$dataForJs = '';
			foreach($data AS $key => $value) {
				$dataForJs .= $key . ' => ' . $value;
				$dataForJs .= '\n';
			}

			// Log the data into the console
			$return .= sprintf('' .
			'<script>
				console.log("MobWeb_AdWordsRemarketingTags: Data for tag:");
				console.log("%s");
			</script>', $dataForJs);
		}

		// Output the JS for the tag
		$return .= sprintf('' .
		'<script type="text/javascript">
			var google_tag_params = {
			ecomm_prodid: "%s",
			ecomm_pagetype: "%s",
			ecomm_totalvalue: "%s",
			ecomm_category: "%s",
			isSaleItem: "%s",
			returnCustomer: "%s"
			};
		</script>
		<script type="text/javascript">
			/* <![CDATA[ */
			var google_conversion_id = %s;
			var google_custom_params = window.google_tag_params;
			var google_remarketing_only = true;
			/* ]]> */
		</script>
		<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
		<noscript>
			<div style="display:inline;">
				<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/%s/?value=0&amp;guid=ON&amp;script=0"/>
			</div>
		</noscript>' .
		'', $data['ecomm_prodid'], $data['ecomm_pagetype'], $data['ecomm_totalvalue'], $data['ecomm_category'], $data['isSaleItem'], $data['returnCustomer'], $data['google_conversion_id'], $data['google_conversion_id']);

		// Return the tag so that it can be printed
		return $return;
	}
}