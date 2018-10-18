<?php

namespace ew_viabiona;

class Template extends \template
{
    /**
     * Checks if template cache is available - little changes by Jens Albert to check in plugin folder
     *
     * @param      $templateFile
     * @param bool $autoPluginPath
     * @return bool
     */
    public function isTemplateCache($templateFile, $autoPluginPath = true)
    {
        $this->content_smarty = new \Smarty();

        if (USE_CACHE == 'false') {
            $this->content_smarty->caching = 0;

            return false;
        } else {
            $this->content_smarty->caching = true;
            $this->content_smarty->cache_lifetime = CACHE_LIFETIME;
            $this->content_smarty->cache_modified_check = CACHE_CHECK;
        }

        $cacheID = $this->getTemplateCacheID($templateFile);
        $rootPath = $autoPluginPath ? $this->tpl_root_path : _SRV_WEBROOT . _SRV_WEB_TEMPLATES . $this->selected_template;

        return $this->content_smarty->is_cached($rootPath . $templateFile, $cacheID);
    }

    /**
     * Get the cached html- little (blind) changes by Jens Albert to use in plugin folder
     *
     * @param $templateFile
     * @return string
     */
    public function getCachedTemplate($templateFile)
    {
        return $this->getTemplate(null, $templateFile, array());
    }

//
//    original method
//
//    function isTemplateCache($template)
//    {
//        $this->content_smarty = new \Smarty();
//        if (USE_CACHE == 'false') {
//            $this->content_smarty->caching = 0;
//            return false;
//        } else {
//            $this->content_smarty->caching = true;
//            $this->content_smarty->cache_lifetime = CACHE_LIFETIME;
//            $this->content_smarty->cache_modified_check = CACHE_CHECK;
//        }
//
//        $cacheID = $this->getTemplateCacheID($template);
//
//        if ($this->content_smarty->is_cached(_SRV_WEBROOT . _SRV_WEB_TEMPLATES . $this->selected_template . $template, $cacheID)) {
//            return true;
//        } else {
//            return false;
//        }
//    }
}