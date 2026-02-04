<?php
/**
 * Name: Coverphoto
 * Description: Adds interface for setting and getting a user profile header image.
 * Version: 1.2
 * Author: randompenguin <https://friendica.world/profile/randompenguin>
 */
use Friendica\AppHelper;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\Photo;
use Friendica\Model\Profile;
use Friendica\Model\User;
/**
 * Installs the addon hook
 */
function coverphoto_install()
{
	Hook::register('addon_settings', __FILE__, 'coverphoto_addon_settings');
	Hook::register('addon_settings_post', __FILE__, 'coverphoto_addon_settings_post');
	Hook::register('page_content_top', __FILE__, 'coverphoto_show_on_profile');
}
function coverphoto_show_on_profile(&$html)
{
	// I think only the login page does not have "pagename" but this should prevent any "missing key" errors:
	$pagename = !empty($_REQUEST['pagename']) ? explode('/',$_REQUEST['pagename'])[0] : [];
	// We only want the coverphoto on profile pages...
	if ($pagename == "profile" || $pagename == "calendar" || $pagename == "notes" || $pagename == "contact"){
		// only do this if user enabled coverphoto addon
		$uid = DI::userSession()->getLocalUserId();
		if (!DI::pConfig()->get($uid, 'coverphoto', 'enabled')){
			return;
		}
			// figure out whose profile we are looking at
			if ( method_exists(DI::class, 'app') ){
				$owner = DI::app()->getProfileOwner();	// Friendica 2024
			} else {
				$owner = DI::appHelper()->getProfileOwner();	// Friendica 2025
			}
			// on your "notes" and "scheduled posts" pages the $owner becomes Friendica itself, so change that to your local user ID
			if ($owner == 0){ $owner = $uid; };
			if ($pagename == "contact"){
				$cid = explode('/',$_REQUEST['pagename']);
				if (empty($cid[1])){	// Your Contacts page does not have this
					$profile = ['header' => '']; // Your own Contacts page should not show a header image
				} else {
					// you are viewing profile info for one of your contacts
					$profile = Contact::selectFirst([], ['id' => $cid[1]]);
					$owner = $cid[1];
				}
			} else {
				// directly viewing profile info
				$profile = User::getOwnerDataById( $owner );
			}
			// check if there is a header image
			if (!empty($profile['header'])) {
				// all the profile pages have "profile" in the URL but we only want the "Change Coverphoto" button on the Profile page itself
				$regex = "/conversations|photos|media|calendar|notes|contact|schedule/i";
				if ($owner == $uid && !preg_match($regex,$_REQUEST['pagename']) ) { // person is looking at their own profile
					$margin = "0 auto";
					$edit_button = '<a id="change_coverphoto" href="'.DI::baseUrl().'/settings/addons/coverphoto" class="btn settings-submit btn-sm" style="margin-bottom: 5px;"><i class="fa fa-camera" aria-hidden="true"></i> '.DI::l10n()->t('Change Cover Photo').'</a>';
				} else {
					$margin = "0 auto 10px auto";
					$edit_button = '';
				}
				// build coverphoto code
				$html .= '<span class="coverphoto" style="display:block;width:100%;margin:'.$margin.';"><img src="'.$profile['header'].'" style="width:100%;max-width:100%;"/></span>' . $edit_button . $html;
			}	
	}
}

function coverphoto_addon_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()){
		return;
	}
	$uid = DI::userSession()->getLocalUserId();
	$profile = User::getOwnerDataById($uid);
	$enabled = DI::pConfig()->get($uid, 'coverphoto', 'enabled');
	$header_img = $profile['header'];	
	// get settings template
	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/coverphoto/');
	$html = Renderer::replaceMacros($t, [
		'$uncache'	=> time(),
		'$uid'		=> $uid,
		'$error'    => DI::pConfig()->get($uid, 'coverphoto', 'error'),
		'$instructions' => DI::l10n()->t('Either browse for an image in your Photo albums or enter the URL for an image. Leave empty and submit to clear banner image.'),
		'$browse'   => DI::l10n()->t('Browse'),
		'$prevhead' => DI::l10n()->t('Preview'),
		'$enabled' => [
			'enabled',
			DI::l10n()->t('Enable Cover Photo on Profile'),
			$enabled
		],
		'$header_img' => [
			'header_img',
			DI::l10n()->t('Cover Photo'),
			$header_img
		],
		'$preview' => $header_img,
	]);
	
	$data = [
		'addon' => 'coverphoto',
		'title' => DI::l10n()->t('Cover Photo Settings'),
		'html'  => $html,
		'submit' => [
			'coverphoto-save' => DI::l10n()->t('Save Setting'),
		],
	];
}

function coverphoto_addon_settings_post(array $post)
{
	if (!DI::userSession()->getLocalUserId() || empty($post['coverphoto-save'])){
		return;
	}

	if ($post['coverphoto-save']){
		$uid  = DI::userSession()->getLocalUserId();
		$self = DBA::selectFirst('contact', ['id'], ['uid' => $uid, 'self' => true]); // this gives us just what we are after
		if ($post['enabled']){
			DI::pConfig()->set($uid, 'coverphoto', 'enabled', intval($post['enabled']));
		} else {
			DI::pConfig()->delete($uid, 'coverphoto', 'enabled');
			$post['header_img'] = ""; // deletes $profile['header'] but not image
		}
		if (!empty($post['header_img'])){
			// check if this is a local image or not by getting resource ID
			$rid = Photo::ridFromUri($post['header_img']);
			if (!empty($rid)){
				// now check if this is a public image or not
				$permission = Photo::getPhoto($rid,0,0);
				if (is_array($permission) && !empty(trim($permission['allow_cid'])) ){
					// Image is not public and cannot be used
					DI::pConfig()->set($uid, 'coverphoto', 'error', DI::l10n()->t('Selected photo was not publicly accessible. Please select a different photo.'));
					return;
				} else {
					DI::pConfig()->delete($uid, 'coverphoto', 'error');
				}
			} else {
				// clear any permission error msg
				DI::pConfig()->delete($uid, 'coverphoto', 'error');
			}
			// hurdles cleared try to copy the image for coverphoto banner
			$new_resource_id = Photo::uploadBanner($uid, [], $post['header_img']);
			// NOTE: uploadBanner also updates profile and publishes update to global directory
			// save new banner image resource_id so we can refer to it when deleting
			DI::pConfig()->set($uid, 'coverphoto', 'current', $new_resource_id);
		} else {
			// wipe the contents of our "header" key...
			Contact::update(['header' => ''], ['id' => $self['id'], 'uid' => $uid], true);
			// pull the resource ID we saved when previous image was set
			$resource_id = DI::pConfig()->get($uid, 'coverphoto', 'current');
			// Attempt to remove USER_BANNER flag from that image
			$condition = ['photo-type' => Photo::USER_BANNER, 'resource-id' => $resource_id, 'uid' => $uid];
			Photo::update(['photo-type' => Photo::DEFAULT], $condition);
			// Clear any old image errors
			DI::pConfig()->delete($uid, 'coverphoto', 'error');
			// Clear the current image resource_id
			DI::pConfig()->delete($uid, 'coverphoto', 'current');
		}
		// Propagate Profile Changes
		Contact::updateSelfFromUserID($uid, true);
		// Update global directory in background
		Profile::publishUpdate($uid);
	}
}