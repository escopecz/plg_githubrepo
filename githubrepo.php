<?php
/**
 * @package	Joomla.Plugin
 * @subpackage	Content.githubrepo
 * @copyright	Copyright (C) Jan Linhart. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class plgContentGithubrepo extends JPlugin
{
    /**
     * Plugin that loads Github repo within content
     *
     * @param	string	The context of the content being passed to the plugin.
     * @param	object	The article object.  Note $article->text is also available
     * @param	object	The article params
     * @param	int	The 'page' number
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {

        // Don't run this plugin when the content is being indexed
        if ($context == 'com_finder.indexer') {
                return true;
        }

        // simple performance check to determine whether bot should process further
        if (strpos($article->text, 'githubrepo') === false) {
                return true;
        }

        // expression to search for (positions)
        $regex		= '/{githubrepo\s+(.*?)}/i';
        $jquery		= $this->params->get('jquery', true);

        // Find all instances of plugin and put in $matches for githubrepo
        // $matches[0] is full pattern match, $matches[1] is the repo declaration
        preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

        if ($matches) {

            foreach ($matches as $match) {

                $matcheslist = explode('|', str_replace(' ', '', $match[1]));


                if (!array_key_exists(0, $matcheslist)) {
                    JError::raiseNotice( 100, 'GithubRepo plugin can\'t find user at your github repo declaration. Check if the declaration is in the form of &#123;githubrepo user|repo&#125;' );
                    return ;
                }

                if (!array_key_exists(1, $matcheslist)) {
                    JError::raiseNotice( 100, 'GithubRepo plugin can\'t find repository name at your github repo declaration. Check if the declaration is in the form of &#123;githubrepo user|repo&#125;' );
                    return ;
                }

                $document = JFactory::getDocument();
                $document->addStyleDeclaration('.file.page.active{position:static}');

                $version = new JVersion;

                if (version_compare($version->getShortVersion(), '3.0', '<') == 1)
                {
                    if ($jquery)
                    {
                        $document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');
                    }
                }
                else
                {
                    JHtml::_('jquery.framework');
                }

                $document->addScript('plugins/content/githubrepo/repo.js');
                $document->addScriptDeclaration("jQuery(function(){jQuery('#".$matcheslist[0]."').repo({ user: '".$matcheslist[0]."', name: '".$matcheslist[1]."' });});");

                $repo = '<div id="'.$matcheslist[0].'"></div>';

                $article->text = str_replace($match[0], $repo, $article->text);
            }
        }
    }
}
