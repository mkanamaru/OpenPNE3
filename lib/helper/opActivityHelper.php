<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opActivity provides helper function for Activity
 *
 * @package    OpenPNE
 * @subpackage helper
 * @author     Shogo Kawahara <kawahara@bucyou.net>
 * @author     Kimura Youichi <kim.upsilon@bucyou.net>
 */


/**
 * op_activity_body_filter
 *
 * @param Activity $activity
 * @param boolean  $is_auto_link
 * @return string
 */
function op_activity_body_filter($activity, $is_auto_link = true)
{
  $body = $activity->getBody();
  if ($activity->getTemplate())
  {
    $config = $activity->getTable()->getTemplateConfig();
    if (!isset($config[$activity->getTemplate()]))
    {
      return '';
    }

    $params = array();
    foreach ($activity->getTemplateParam() as $key => $value)
    {
      $params[$key] = $value;
    }
    $body = __($config[$activity->getTemplate()], $params);
    $event = sfContext::getInstance()->getEventDispatcher()->filter(new sfEvent(null, 'op_activity.template.filter_body'), $body);
    $body = $event->getReturnValue();
  }

  $event = sfContext::getInstance()->getEventDispatcher()->filter(new sfEvent(null, 'op_activity.filter_body'), $body);
  $body = $event->getReturnValue();

  if (false === strpos($body, '<a') && $activity->getUri())
  {
    return link_to($body, $activity->getUri());
  }

  if ($is_auto_link)
  {
    if ('mobile_frontend' === sfConfig::get('sf_app'))
    {
      return op_auto_link_text_for_mobile($body);
    }

    return op_auto_link_text($body);
  }

  return $body;
}

/**
 * Returns a url for the activity image
 *
 * @param   ActivityImage $activityImage
 * @param   mixed[]       $options
 * @param   bool          $absolute
 * @return  string
 */
function op_activity_image_uri($activityImage, $options = array(), $absolute = false)
{
  use_helper('sfImage');

  if ($activityImage->relatedExists('File'))
  {
    return sf_image_path($activityImage->File, $options, $absolute);
  }
  else
  {
    return $activityImage->uri;
  }
}

/**
 * Returns an <img> tag for the activity image
 *
 * @param   ActivityImage $activityImage
 * @param   mixed[]       $options
 * @return  string
 */
function op_activity_image_tag($activityImage, $options = array())
{
  use_helper('sfImage');

  if ($activityImage->relatedExists('File'))
  {
    return op_image_tag_sf_image($activityImage->File, $options);
  }
  else
  {
    return op_image_tag($activityImage->uri, $options);
  }
}
