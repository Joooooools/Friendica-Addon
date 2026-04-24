<?php

/**
 * Name: EasyPhoto
 * Description: Adds a simple image description editor below the post textarea for easier accessibility.
 * Version: 1.0
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 * 
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Friendica\Core\Hook;

function easyphoto_install()
{
    Hook::register('page_header', 'addon/easyphoto/easyphoto.php', 'easyphoto_header');
}

function easyphoto_header(&$header)
{
    $header .= "\n" . '<link rel="stylesheet" type="text/css" href="/addon/easyphoto/easyphoto.css?v=1.0" media="all" />';
    $header .= "\n" . '<script type="text/javascript" src="/addon/easyphoto/easyphoto.js?v=1.0"></script>' . "\n";
}
