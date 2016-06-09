<?php
/**
 * @package MobWeb_AdWordsRemarketingTags
 * @copyright Copyright (c) MobWeb GmbH
 */

/**
 * Default helper
 */
class MobWeb_AdWordsRemarketingTags_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get specific remarketing setting.
     *
     * @param $field
     * @return mixed
     */
    public function getSettings($field)
	{
		$value = Mage::getStoreConfig('google/adwordsremarketingtags/' . $field);
		return $value;
	}

    /**
     * Get debug state.
     *
     * @return bool
     */
    public function getIsDebug()
	{
		return (Mage::helper('adwordsremarketingtags')->getSettings('enable_debug') == '1');
	}

    /**
     * Log a message.
     *
     * @param $msg string
     */
    public function log($msg)
	{
		if(Mage::helper('adwordsremarketingtags')->getIsDebug()) {
			Mage::log($msg, NULL, 'MobWeb_AdWordsRemarketingTags.log');
		}
	}

    /**
     * Get category path as "-" separated string.
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryPathAsString(Mage_Catalog_Model_Category $category)
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

    /**
     * Get include tax state.
     *
     * @return bool
     */
    public function getIncludeTaxesInValues()
	{
		$result = (Mage::helper('adwordsremarketingtags')->getSettings('include_taxes') == '1');
		Mage::helper('adwordsremarketingtags')->log(sprintf('getIncludeTaxesInValues: Result: %s', ($result) ? 'true' : 'false'));

		return $result;
	}

    /**
     * Retrieves all products in cart.
     *
     * @return array
     */
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
					$parentProduct = Mage::getModel('catalog/product')->load($parentProductIds[0]);
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

    /**
     * Get product price with /without tax.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    public function getProductPrice($product)
	{
		if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
			/** @var Mage_Bundle_Model_Product_Price $priceModel */
			$priceModel = $product->getPriceModel();
			$finalPrice = $priceModel->getTotalPrices($product, 'min', false);
		} else {
			$finalPrice = $product->getFinalPrice();
		}

		$productFinalPriceWithoutTaxes = Mage::helper('tax')->getPrice($product, $finalPrice, false);
		$productFinalPrice = Mage::helper('tax')->getPrice($product, $finalPrice, true);

		Mage::helper('adwordsremarketingtags')->log(sprintf('getProductPrice: Product price determined. Without taxes: %s, with taxes: %s', $productFinalPriceWithoutTaxes, $productFinalPrice));
		
		// Check if the taxes should be included in the product values, according to the configuration
		$includeTaxesInValues = Mage::helper('adwordsremarketingtags')->getIncludeTaxesInValues();

		return $includeTaxesInValues ? $productFinalPrice : $productFinalPriceWithoutTaxes;
	}
}
