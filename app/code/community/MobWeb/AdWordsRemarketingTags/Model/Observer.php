<?php

class MobWeb_AdWordsRemarketingTags_Model_Observer
{
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