<?php
/**
 * @package MobWeb_AdWordsRemarketingTags
 * @copyright Copyright (c) MobWeb GmbH
 */

/**
 * Default observer model
 */
class MobWeb_AdWordsRemarketingTags_Model_Observer
{
    /**
     * Inject layout block needed.
     *
     * @see event controller_action_layout_generate_xml_before
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function controllerActionLayoutGenerateXmlBefore(Varien_Event_Observer $observer)
	{
		$layout = $observer->getEvent()->getLayout();
		$block = '' .
		'<reference name="before_body_end">
			<block type="adwordsremarketingtags/AdWordsRemarketingTag" name="adwordsremarketingtags_block"></block>
		</reference>';

		$layout->getUpdate()->addUpdate($block);
		return $this;
	}
}