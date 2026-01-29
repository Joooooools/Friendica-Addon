<?php
/**
 * Name: Authbanner
 * Description: Displays the profile banner for logged-in users (without the ability to upload your own banner). Based on the addon Coverphoto by Random Penguin and the modified addon by feb.
 * Version: 1.0
 * Author: Jools
 */

use Friendica\Core\Hook;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\User;

function authbanner_install() {
        Hook::register('page_content_top', __FILE__, 'authbanner_show_on_profile');
}

function authbanner_uninstall() {
        Hook::unregister('page_content_top', __FILE__, 'authbanner_show_on_profile');
}

function authbanner_show_on_profile(&$html) {
        $uid = DI::userSession()->getLocalUserId();
        if (!$uid) return;

        $pagename = !empty($_REQUEST['pagename']) ? explode('/', $_REQUEST['pagename'])[0] : '';
        $allowed_pages = ["profile", "calendar", "notes", "contact"];

        if (in_array($pagename, $allowed_pages)) {
                $owner = method_exists(DI::class, 'app') ? DI::app()->getProfileOwner() : DI::appHelper()->getProfileOwner();
                if ($owner == 0) $owner = $uid;

                if ($pagename == "contact") {
                        $parts = explode('/', $_REQUEST['pagename']);
                        $profile = (!empty($parts[1])) ? Contact::selectFirst([], ['id' => $parts[1]]) : ['header' => ''];
                } else {
                        $profile = User::getOwnerDataById($owner);
                }

                if (!empty($profile['header'])) {
                        $banner_html = '
                        <div id="authbanner-standard-wrapper">
                                <img src="' . $profile['header'] . '" alt="Profilbanner" />
                        </div>';

                        $html = $banner_html . $html;
                }
        }
}
