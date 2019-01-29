<?php

namespace TG\ClearAddOnFile\XF\Admin\Controller;

use XF\Mvc\ParameterBag;

class AddOn extends XFCP_AddOn
{
    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
	public function actionDeleteFiles(ParameterBag $params)
	{
		$addOn = $this->assertAddOnAvailable($params['addon_id_url']);
		
		if (!$addOn->canInstall())
		{
			return $this->error(\XF::phrase('tgcaf_the_files_of_this_add_on_can_not_be_deleted'));
		}
		
		$warnings = [];

		if (!$addOn->hasHashes())
		{
			$warnings[] = \XF::phrase('tgcaf_hashes_file_for_this_add_on_missing');
		}
		
		if ($this->isPost())
		{
			if ($addOn->hasHashes())
			{
				foreach ($addOn->getHashes() as $path => $hash)
				{
					unlink($path);
				}
			}
			
			$addOnDirectoryIterator = new \RecursiveDirectoryIterator($addOn->getAddOnDirectory(), \FilesystemIterator::SKIP_DOTS);
			$addOnRecursiveIterator = new \RecursiveIteratorIterator($addOnDirectoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($addOnRecursiveIterator as $file) 
			{
				$file->isDir() ?  rmdir($file) : unlink($file);
			}
			
			if (file_exists($addOn->getAddOnDirectory()))
			{
				rmdir($addOn->getAddOnDirectory());
			}
			
			return $this->redirect($this->buildLink('add-ons'));
		}
		else
		{
			$viewParams = [
				'addOn' => $addOn,
				'warnings' => $warnings
			];
			return $this->view('SXFCAF:AddOn\DeleteFiles', 'tgcaf_addon_delete_files', $viewParams);
		}
	}
}