COVERPHOTO
=============
Version 1.2

Main author: https://gitlab.com/randompenguin

System Requirements: Friendica 2024.12, Friendca 2025.07-rc 

_System Admins upgrading to Release Candidate make sure to read Known Issues below!_

## Description

Adds a user interface for setting and deleting a custom profile coverphoto (aka "header image" or "banner image").  Every other social media platform has these on user profiles except for Friendica. Because currently none of the themes support showing it none of them support setting/changing it either. The only way you could set/change/delete it was through third-party mobile apps.

Because all of the code already exists on the Friendica backend to support this feature!

This add-on goes beyond just showing it on your actual "Profile" page. It will show it at the top of ALL of the profile sub-pages as well, and it will do it no matter what theme you are using.

## How To Use

**Enabling the Add-On**

Server Administrators: 
1. Upload the "coverphoto" folder to your Friendica /addon folder and make sure it has the proper permissions.
2. Go to your **Account > Admin > Addons** and enable "_Coverphoto_" so your users can access it.

Admins, if you would like to change or disable the default platform image add the following to your /config/local_config.php file:

	'api' => [
		'mastodon_banner' => 'https://alternate.image.url.here.jpeg',
	],

This is the banner shown _to_ Mastodon by your server, not the banner _from_ Mastodon.

Friendica Users:
1. Go to **Settings > Addons > Coverphoto**
2. Tick the "Enable" checkbox. **You cannot set an image if the addon is not enabled**

**Setting A Coverphoto**

1. Click the "Browse" button to select an image from your photo library _or_ enter a valid image URL in the text box.
2. Click the "Save" button.

Coverphoto checks if the image is local or not. If it is local it also checks whether the permissions on that image are set to "Public" or not.  If the image is not public you cannot use it to create a coverphoto/banner image. The addon gives you error feedback telling you this.

Otherwise you _should_ see a preview of the image you chose in the add-on's "Preview" area.

If it does not already exist Friendica will create a "Banner Photos" album in your photo library.  It will then make a **copy** of the image you selected, place it in that folder, and flag it for use as your current `USER_BANNER` image.

Friendica does not check, in fact has no way to check, if there is already a copy of the image you selected in that folder. It will always just create a new copy.  It is possible, over time, you may have duplicate images in that album if you ever selected the same image more than once.

**Changing Coverphotos**

You may change your coverphoto at any time. To do so just select a different image from your photo library or enter a new image URL in the text box and save.

**Unsetting A Coverphoto**

There are two options for unsetting your coverphoto:

A. Leave the textbox in the addon blank and "Save" settings.

B. Untick the "Enabled" checkbox and "Save" settings.

Either of these actions will remove the `USER_BANNER` flag from the image in your photo library. The actual image is _not_ deleted!

**Deleting Coverphotos**

When you create a coverphoto a "Banner Photos" album will be automatically created in your photo library if it does not already exist. Every image you have set as a coverphoto will be in this folder.

You can delete images from the "Banner Photos" album just as you can from any other album. You can also delete the entire album. It will simply be recreated the next time you set a coverphoto.

If you delete the _current_ coverphoto from that album you will have disabled the ability of Friendica to show it on your profile.

**Where Is It Shown?**

Your coverphoto will be shown on your own profile pages to both yourself and any of your contacts.  If you visit the profile of any of your contacts who have set a coverphoto you will see their chosen image.

Specifically it is enabled for the following pages:
* /profile/username/
* /profile/username/profile
* /profile/username/conversations
* /profile/username/photos (but none of the photo sub-pages)
* /profile/username/media
* /profile/username/calendar
* /profile/username/notes
* /profile/username/schedule
* /profile/username/contacts (and all sub-pages)
* /contact/00 (where "00" is the contact ID number)
* /contact/00/conversations
* /contact/00/posts
* /contact/00/media
* /contact/00/contacts (and all sub-pages)

## Known Issues

* It is possible in some circumstances that the "default" image may be shown. This is either a "platform" image or a random image. Coverphoto uses the hook and gets the profile data the way it does to try and _prevent_ those default images from ever being shown, but it is possible they may still be shown if the coverphoto data (specifically the profile "header" key value) is being manipulated by something else - for example a third-party mobile app.  This would be the image that is shown at a URL in this format: 
`https://your.friendica.server/photo/banner/username.jpeg`

* The Photos browser relies on `ajaxupload.js` which is not normally loaded on the backend of the site. The Cover Photo add-on attempts to detect whether it was loaded or not and load it if not, but it does this clientside via JS so it's always possible the script doesn't get loaded, in which case selecting an image via the photo browser will fail.

* If the Bookface schemes for the Frio theme are in use and the profile uses the `[class=coverphoto]...[/class]` method in the Profile data, and there is _also_ a Cover Photo set through the add-on, the last image to load will "win" and be displayed. If both are set to use the same image, however, nobody using Bookface will notice. Users of other themes may see the image in your profile twice.

* If you used Coverphoto with Friendica 2024.12 and have upgraded to 2025.07-rc and your images are stored in the database make sure to go to _Admin > DB Updates > Check Database Structure_.  You and your users may also need to delete the images in the **Banner Photos** album or the album itself and have Friendica recreate the album or contents by (re)uploading the image.
## Technical Details

There are three functions of interest on the Friendica backend:

1. `Photo::uploadBanner()`
2. `User::getBannerUrl()`
3. `Contact::getDefaultHeader()`

Additionally we are specifically interested in the Contact/Profile data at key "header" where the value for the coverphoto/banner is stored.  However this is not the *actual* image URL! It will be a URL in this format:
`https://your.friendica.server/photo/banner/username.jpeg`

When someone accesses that URL Friendica will look for an image with the `USER_BANNER` flag and substitute it, otherwise it will return one of the default platform or random images.

You can find out what, if any, image is set as the `USER_BANNER` for any user in the database under the "_photo_" table it is stored as an integer value (11) in the "_photo-type_" column.

**src/Model/Contact.php**

In the function `updateSelfFromUserId()` it populates the "_header_" field with a call to `User:getBannerUrl($user)`

This is why you can't simply put an image URL into `$profile['header']` value because as soon as you update *anything* in your profile it will run "getBannerUrl()" and overwrite your value with whatever that returns.

There is also a `getDefaultHeader($contact)` which will return whatever is in `$contact['header']` if there is something, otherwise it returns either the platform image, currently only Friendika/Friendica and Disapora, or a random image:

For Friendika/Friendica it sets it to what's defined in **/src/Contact/Header.php** `getMastodonBannerPath()` which in turn runs **/src/Core/Capability/IManageConfigValues.php** `get('api', mastodon_banner')` [that's `$category` and `$key`]. This is defined in **/static/defaults.config.php** under:
```
'api' => [
	'mastodon_banner' => '/images/friendica-banner.jpg',
],
```
(You can, of course, redefine this in your **/config/local_config.php** file)

The Diaspora image is located at: **/images/diaspora-banner.jpg**

If the platform does not have a preset default image it gets a random image from:
`https://picsum.photos/seed/`

**/src/Model/User.php**

`User::getBannerUrl()` function searches your photos for one that is tagged with a "_photo-type_" of `Photo::USER_BANNER` (integer 11). There should only ever be ONE image at a time per user with that photo type.  If it doesn't find one it returns an empty string.

**/src/Model/Photo.php**

This is where we find `Photo::uploadBanner()` which does all the heavy-lifting of copying an image, scaling it down to 960 pixels wide if necessary, storing it with the `USER_BANNER` flag, in the "Banner Photos" album, remove the flag from any images already in your photo library, updates your profile, and publishes the update to the user directory (if you've opted into that).

**Banner vs Avatar**

While the banner functions are very similar to the avatar (profile photo) functions, they are not nearly as complete. The three functions of interest mentioned at the beginning of this section are the only ones. There are a half dozen additional functions for Avatars to retrieve the URL or cached files that do not exist for banner images. Avatar images also have a user interface for cropping and sizing the chosen image, but there is no such interface for banners.

## Changelog
1.2 (16 OCT 2025)
* Fixed markdown parse error in ReadMe file.
* Fixed hooked function so it appends to HTML rather than overwrites, in case other things also hook to page_content_top
* Added bottom margin to image container on profiles that are not your own and don't have edit button below.
* Removed unused variable
* Added id to "Change Coverphoto" button to make it easier to target for styling.
* Fixed typo in $margin variable name.

1.1 (01 SEP 2025)
* Improved client-side JS error handling
* Normalized JS script to jQuery (no longer a mix of vanilla and jQuery)
* Added handling for img url data from Frio theme's browser.js script
* Fixed regex extraction from embed data to handle captions
* Preview now shows image just selected from Photo Brower modal

1.0 (29 AUG 2025)
* Initial public release

## License
Same as Friendica I guess? GNU AGPL, see <https://www.gnu.org/licenses/>.


