<?php 
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
/* @var $this MLProductList */
/* @var $oObject MLProductListDependencyAction */
class_exists('MLProductList') or die();
?>
<form action="<?php echo $this->getUrl(false, false, false); ?>" method="post">
	<input type="hidden" name="selectionName" value="prepare">
	<input type="hidden" id="actionType" value="_">
	<table class="right">
		<tbody>
			<tr>
				<td class="textleft inputCell" rowspan="2" id="match_settings">
					<input type="radio" checked="checked" value="all" name="match" id="match_all_rb">
					<label for="match_all_rb"><?php echo ML_LABEL_ALL; ?></label><br>
					<input type="radio" value="notmatched" name="match" id="match_notmatched_rb">
					<label for="match_notmatched_rb"><?php echo ML_EBAY_LABEL_ONLY_NOT_PREPARED; ?></label>
				</td>
				<td class="texcenter inputCell">
					<table class="right">
						<tbody>
							<tr>
								<td>
									<input type="submit" name="prepare" id="prepare" value="<?php echo ML_EBAY_LABEL_PREPARE; ?>" class="fullWidth ml-button smallmargin mlbtn-action" />
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
</form>