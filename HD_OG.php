<?php
/**
 * Class and Function List:
 * Function list:
 * - plgSystemHD_OG()
 * - onAfterRender()
 * Classes list:
 * - plgSystemHD_OG extends JPlugin
 */
# HD-OG   3
# Copyright (C) 2013 by Hyde-Design
# Homepage   : www.hyde-design.co.uk
# Author     : Hyde-Design
# Email      : sales@hyde-design.co.uk
# Version    : 3.7.1
# License    : http://www.gnu.org/copyleft/gpl.html GNU/GPL
# Patched by Uziel Almeida Oliveira    : https://github.com/uzielweb
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');
jimport('joomla.application.component.model');
class plgSystemHD_OG extends JPlugin
{
    function plgSystemHD_OG(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->_plugin = JPluginHelper::getPlugin('system', 'HD_OG');
    }
    function onAfterRender()
    {
        $element_url = '/administrator/';
        $current_url = $_SERVER['REQUEST_URI'];
        global $app;
        $buffer = JResponse::getBody();
        if (strstr($current_url, $element_url))
        {;
        }
        else
        {
            $document = JFactory::getDocument();
            $app = JFactory::getApplication();
            $hdog_title_tmp = $document->getTitle();
            $hdog_title = '<meta property="og:title" content="' . $hdog_title_tmp . '"/>';
            $hdog_type_tmp = $this->params->get('hdog_type');
            $hdog_type = '<meta property="og:type" content="' . $hdog_type_tmp . '"/>';
            $hdog_base = $document->getBase();
            $hdog_url = '<meta property="og:url" content="' . $hdog_base . '"/>';
            $hdog_fbappid_tmp = $this->params->get('hdog_fbappid');
            $hdog_fbappid = '<meta property="fb:app_id" content="' . $hdog_fbappid_tmp . '"/>';
            //get thumbnail image
            $hdog_image_tmp = $this->params->get('hdog_image');
            $hdog_image = '<meta property="og:image" content="' . JURI::base() . $hdog_image_tmp . '" />';
            //check article view
            $view = JRequest::getVar('view');
            $option = JRequest::getVar('option');
            $disable_in = $this->params->get('disable_in');
            $pieces = explode(",", $disable_in);
            foreach ($pieces as $value)
            {
                if ($option == $value)
                {
                    $hdog_image = "";
                    $hdog_fbappid = "";
                    $hdog_url = "";
                    $hdog_type = "";
                    $hdog_title = "";
                }
            }
            if ($view == "article")
            {
                $articlesModel = JModelLegacy::getInstance('ContentModelArticle');
                $articleId = JRequest::getInt('id', 0);
                $hdog_image_thumb = '<meta property="og:image" content="' . $articleId . '" />';
                $article = $articlesModel->getItem($articleId);
                preg_match('/(?<!_)src=([\'"])?(.*?)\\1/', $article->introtext, $matches);
                if (!empty($matches))
                {
                    $thumb_img = $matches[2];
                }
                if (empty($matches))
                {
                    $thumb_img = '';
                }
                if (!empty($thumb_img))
                {
                    $hdog_image_thumb = '<meta property="og:image" content="' . JURI::base() . $thumb_img . '" />';
                }
                else
                {
                    $hdog_image_thumb = '<meta property="og:image" content="' . JURI::base() . $hdog_image_tmp . '" />';
                }
            }

            //start of check if view is JEA property
            if ($view == "property")
            {
                $jeaitemsModel = JModelLegacy::getInstance('JeaModelProperty');
                $jeaitemId = JRequest::getInt('id', 0);
                JFactory::getLanguage()->load('com_jea', JPATH_BASE . '/components/com_jea');

                $jeaitem = $jeaitemsModel->getItem($jeaitemId);

                $jeaparams = JFactory::getApplication('site')->getParams('com_jea');
                $jeaprice = number_format($jeaitem->price, $jeaparams->get('decimals_number') , $jeaparams->get('decimals_separator') , $jeaparams->get('thousands_separator'));
                if ($jeaparams->get('symbol_position') == '0')
                {
                    $jeapricecurrency = $jeaparams->get('currency_symbol') . ' ' . $jeaprice;
                }
                if ($jeaparams->get('symbol_position') == '1')
                {
                    $jeapricecurrency = $jeaitem->price . ' ' . $jeaparams->get('currency_symbol');
                }

                $jeaitemdesc = JText::_('COM_JEA_FIELD_REF_LABEL') . ': ' . $jeaitem->ref . '. ' . JText::_('COM_JEA_FIELD_PRICE_LABEL') . ': ' . $jeapricecurrency . '. ' . strip_tags($jeaitem->description);

                $dbjea = JFactory::getDbo();
                $queryjea = $dbjea->getQuery(true);
                $queryjea->select('images');
                $queryjea->from('#__jea_properties');
                $queryjea->where('id=' . $jeaitem->id);
                $dbjea->setQuery($queryjea);
                $jeaimages = $dbjea->loadObjectList();
                foreach ($jeaimages as $jeaimage)
                {
                    $images = json_decode($jeaimage->images);
                    $first_image = $images[0];
                    $thumb_img = $first_image->name;
                }

            }
            //end of check if view is JEA property
            //check category view
            if ($view == "category")
            {
                $categoriesModel = JModelLegacy::getInstance('ContentModelCategories');
                $category = JRequest::getVar('id');
                $category = '9';
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('alias');
                $query->from('#__categories');
                $query->where('id=' . $category);
                $db->setQuery($query);
                $results = $db->loadObjectList();
                //       echo 'xx '.$results.' xx';

            };
            $hdog_image_thumb = '<meta property="og:image" content="' . JURI::base() . $hdog_image_tmp . '" />';
            if (!empty($thumb_img))
            {
                $hdog_image_thumb = '<meta property="og:image" content="' . JURI::base() . $thumb_img . '" />';
            }
            if ((!empty($hdog_image_tmp)) && ($view <> "article") && (empty($matches)))
            {
                $hdog_image_thumb = '<meta property="og:image" content="' . JURI::base() . $hdog_image_tmp . '" />';
            }
            if ((!empty($hdog_image_tmp)) && ($view == "article") && (empty($matches)))
            {
                $hdog_image_thumb = '<meta property="og:image" content="' . JURI::base() . $hdog_image_tmp . '" />';
            }
            if ((empty($thumb_img)) && ($view == "article") && (!empty($matches)))
            {
                $hdog_image_thumb = '<meta property="og:image" content="' . JURI::base() . $matches[2] . '" />';
            };
            if (($view == "property") and ($option == "com_jea"))
            {
                $hdog_image_thumb = '<meta property="og:image" content="' . JURI::base() . 'images/com_jea/images/'.$jeaitem->id.'/'.$thumb_img . '" />';
            };

            //get meta description
            $hdog_desc_tmp = $document->getDescription();
            $hdog_desc = '<meta property="og:description" content="' . $hdog_desc_tmp . '" />';
            if (($view == "property") and ($option == "com_jea"))
            {
                $hdog_desc = '<meta property="og:description" content="' . $jeaitemdesc . '" />';
            };
            //get title
            if (($view == "property") and ($option == "com_jea"))
            {
                $hdog_title = '<meta property="og:title" content="' . $jeaitem->title . '" />';
            };
            //get site name
            $hdog_name_tmp = $app->getCfg('sitename');
            $hdog_name = '<meta property="og:site_name" content="' . $hdog_name_tmp . '" />';
            //get locale
            $lang = JFactory::getLanguage();
            $sitelanguage = $lang->getTag();
            $oglanguage = str_replace('-', '_', $sitelanguage);
            $hdog_locale = '<meta property="og:locale" content="' . $oglanguage . '"/>';
            //get admin id
            $hdog_admins_tmp = $this->params->get('hdog_admins');
            $hdog_admins = '<meta property="fb:admins" content="' . $hdog_admins_tmp . '"/>';
            // render all to screen
            $hdog_all = $hdog_title . $hdog_type . $hdog_fbappid . $hdog_url . $hdog_image_thumb . $hdog_desc . $hdog_name . $hdog_locale;
            $use_admin = $this->params->get('use_admin');
            if ($use_admin == '1')
            {
                $hdog_all = $hdog_all . $hdog_admins;
            }
            $buffer = str_replace('<html xmlns="http://www.w3.org/1999/xhtml"', '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml" ', $buffer);
            $buffer = str_replace('</title>', '</title>' . $hdog_all, $buffer);
            JResponse::setBody($buffer);
            return true;
        }
    }
}
