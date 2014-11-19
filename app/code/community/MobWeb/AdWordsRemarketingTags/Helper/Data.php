<?php

class MobWeb_AdWordsRemarketingTags_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getSettings($field)
	{
		$value = Mage::getStoreConfig('google/adwordsremarketingtags/' . $field);
		return $value;
	}

	public function getIsDebug()
	{
		return (Mage::helper('adwordsremarketingtags')->getSettings('enable_debug') == '1');
	}

	public function log($msg)
	{
		if(Mage::helper('adwordsremarketingtags')->getIsDebug()) {
			Mage::log($msg, NULL, 'MobWeb_AdWordsRemarketingTags.log');
		}
	}

	public function getCategoryPathAsString(Mage_Catalog_Category $category)
	{
		// Extract the categry IDs from the category path
		$categoryPathIds = explode('/', $category->getPath());

		// Exclude the root category
		array_shift($categoryPathIds);

		// Get the name of each category in the category path
		$categoryNames = array();
		foreach($categoryPathIds AS $categoryId) {
			$categoryNames[] = Mage::getModel('catalog/category')->load($categoryId)->getName();
		}

		// Implode the categories, separated by a " - ", e.g. "Category X - Subcategory Y - Subcategory Z"
		$categoryPath = implode(' - ', $categoryNames);

		return $categoryPath;
	}

	public function getIncludeTaxesInValues()
	{
		$result = (Mage::helper('adwordsremarketingtags')->getSettings('include_taxes') == '1');
		Mage::helper('adwordsremarketingtags')->log(sprintf('getIncludeTaxesInValues: Result: %s', ($result) ? 'true' : 'false'));

		return $result;
	}

	public function getProductsInCart()
	{
		// First get a reference to the current quote, which contains all cart items
		$quote = Mage::getSingleton('checkout/session')->getQuote();

		Mage::helper('adwordsremarketingtags')->log(sprintf('getProductsInCart: Looping through products in cart...'));

		// Loop through the quote, collect the product IDs
		$productsInCart = array();
		foreach($quote->getAllVisibleItems() AS $item) {
			Mage::helper('adwordsremarketingtags')->log(sprintf('getProductsInCart: Inspecting quote item, ID: %s, Product ID: %s', $item->getId(), $item->getProductId()));

			// Load the product that belongs to the quote item
			$product = Mage::getModel('catalog/product')->load($item->getProductId());
			Mage::helper('adwordsremarketingtags')->log(sprintf('getProductsInCart: Loaded product for quote item, product ID: %s', $product->getEntityId()));

			// If the current item is a simple product with a parent configurable product, get that
			// configurable product instead
			if($product->getTypeInstance() === 'simple') {
				Mage::helper('adwordsremarketingtags')->log(sprintf('getProductsInCart: Current product is a simple product, trying to get its parent configurable product...'));
				$parentProductIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getEntityId());
				if(isset($parentProductIds[0])) {
					$parentProduct = Mage::getModel('catalog/product')->load($parentIds[0]);
					Mage::helper('adwordsremarketingtags')->log(sprintf('getProductsInCart: Parent configurable item loaded!'));

					// Save the parent product as the current product
					$product = $parentProduct;
				}
			}

			// Save the product in the array
			$productsInCart[] = $product;
		}

		return $productsInCart;
	}

	public function getProductPrice($product)
	{
		$productFinalPriceWithoutTaxes = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), false);
		$productFinalPrice = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);

		Mage::helper('adwordsremarketingtags')->log(sprintf('getProductPrice: Product price determined. Without taxes: %s, with taxes: %s', $productFinalPriceWithoutTaxes, $productFinalPrice));
		
		// Check if the taxes should be included in the product values, according to the configuration
		$includeTaxesInValues = Mage::helper('adwordsremarketingtags')->getIncludeTaxesInValues();

		return $includeTaxesInValues ? $productFinalPrice : $productFinalPriceWithoutTaxes;
	}
}