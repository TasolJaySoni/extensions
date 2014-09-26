<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : EASYBLOG_1.5 (ccompatible with easybBlog 3.8.14427)
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die;
JHTML::_('behavior.tooltip');

require_once JPATH_COMPONENT_SITE.DS.'extensions'.DS.'easyblog'.DS.'helper.php';
$icms_helper = new easyblog_helper();
$categories = $icms_helper->getAllBlogCategory();

?>
<form action="<?php echo JRoute::_ ( $this->request_url )?>" method="post" name="adminForm" id="adminForm">
	<table width="100%">
		<tr>
			<td width="50%" valign="top">
			<?php
				$i=0;
				foreach ($this->groups as $group){
					if($i==3){
						echo '<td valign="top">';
					}
					?>
					<fieldset>
   					<legend><?php echo JText::_('COM_IJOOMERADV_EASYBLOG_'.strtoupper($group).'_CONFIG')?></legend>
   						<table style="text-align: left;" class="paramlist admintable">
							<?php
							 foreach($this->{$group.'Config'} as $key=>$value){?>
									<tr>
										<td class="paramlist_key" width="40%">
											<span class="hasTip" title="<?php echo JText::_($value->caption.'_LBL'); ?>::<?php echo JText::_($value->description); ?>">
												<?php echo JText::_($value->caption); ?>
											</span>
										</td>
										<td>
											<?php echo $value->html; ?>
										</td>
									</tr>
							<?php } ?>
						</table>
					</fieldset>
					<?php
					if($i==3){
						echo '<td>';
					}
					$i++;
				}?>
			</td>
		</tr>
	</table>

	<div class="clr"></div>
	<input type="hidden" name="option" value="com_ijoomeradv" />
	<input type="hidden" name="view" value="extensions" />
	<input type="hidden" name="extid" value="<?php echo $this->extension->id; ?>" />
	<input type="hidden" name="task" value="" />
</form>