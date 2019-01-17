<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: postify.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__."/../maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."forum/post.php";

add_to_title($locale['global_204']);

if (!isset($_GET['forum_id']) || !isnum($_GET['forum_id'])) {
    redirect(BASEDIR."forum/index.php");
}

if (!isset($_GET['error']) || !isnum($_GET['error']) || $_GET['error'] == 0 || $_GET['error'] > 6) {
    $_GET['error'] = 0;
    $errorb = "";
} else if ($_GET['error'] == 1) {
    $errorb = $locale['440a'];
} else if ($_GET['error'] == 2) {
    $errorb = $locale['440b'];
} else if ($_GET['error'] == 3) {
    $errorb = $locale['441'];
} else if ($_GET['error'] == 4) {
    $errorb = $locale['450'];
} else if ($_GET['error'] == 5) {
    $errorb = $locale['454'];
} else if ($_GET['error'] == 6) {
    $errorb = sprintf($locale['455'], $settings['forum_edit_timelimit']);
}

$valid_get = ["on", "off", "new", "reply", "edit"];

if (!iMEMBER || !in_array($_GET['post'], $valid_get)) {
    redirect(BASEDIR."forum/index.php");
}

if (($_GET['post'] == "on" || $_GET['post'] == "off") && $settings['thread_notify']) {
    $output = FALSE;
    if (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) {
        redirect(BASEDIR."forum/index.php");
    }
    $result = dbquery(
        "SELECT tt.*, tf.forum_access FROM ".DB_THREADS." tt
		INNER JOIN ".DB_FORUMS." tf ON tt.forum_id=tf.forum_id
		WHERE tt.thread_id='".$_GET['thread_id']."'"
    );
    if (dbrows($result)) {
        $data = dbarray($result);
        if (checkgroup($data['forum_access'])) {
            add_to_head("<meta http-equiv='refresh' content='2; url=".BASEDIR."forum/viewthread.php?forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id']."' />\n");
            $output = TRUE;
            opentable($locale['451']);
            echo "<div style='text-align:center'><br />\n";
            if ($_GET['post'] == "on" && !dbcount("(thread_id)", DB_THREAD_NOTIFY, "thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'")) {
                $result = dbquery("INSERT INTO ".DB_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$_GET['thread_id']."', '".time()."', '".$userdata['user_id']."', '1')");
                echo $locale['452']."<br /><br />\n";
            } else if (isset($_GET['post']) && $_GET['post'] == 'off') {
                $result = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id='".$_GET['thread_id']."' AND notify_user='".$userdata['user_id']."'");
                echo $locale['453']."<br /><br />\n";
            }
            echo "<a href='".BASEDIR."forum/viewthread.php?forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id']."'>".$locale['447']."</a> ::\n";
            echo "<a href='".BASEDIR."forum/viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['448']."</a> ::\n";
            echo "<a href='".BASEDIR."forum/index.php'>".$locale['449']."</a><br /><br />\n</div>\n";
            closetable();
        }
    }
    if (!$output)
        redirect(BASEDIR."forum/index.php");
} else if ($_GET['post'] == "new") {
    add_to_title($locale['global_201'].$locale['401']);
    opentable($locale['401']);
    echo "<div style='text-align:center'><br />\n";
    if ($errorb) {
        echo $errorb."<br /><br />\n";
    } else {
        echo $locale['442']."<br /><br />\n";
    }
    if ($_GET['error'] < 3) {
        if (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) {
            redirect(BASEDIR."forum/index.php");
        }
        echo "<a href='".BASEDIR."forum/viewthread.php?thread_id=".$_GET['thread_id']."'>".$locale['447']."</a> ::\n";
        add_to_head("<meta http-equiv='refresh' content='2; url=".BASEDIR."forum/viewthread.php?thread_id=".$_GET['thread_id']."' />\n");
    }
    echo "<a href='".BASEDIR."forum/viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['448']."</a> ::\n";
    echo "<a href='".BASEDIR."forum/index.php'>".$locale['449']."</a><br /><br /></div>\n";
    closetable();
} else if ($_GET['post'] == "reply") {
    if (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) {
        redirect(BASEDIR."forum/index.php");
    }
    add_to_title($locale['global_201'].$locale['403']);
    opentable($locale['403']);
    echo "<div style='text-align:center'><br />\n";
    if ($errorb) {
        echo $errorb."<br /><br />\n";
    } else {
        echo $locale['443']."<br /><br />\n";
    }
    if ($_GET['error'] < "2") {
        if (!isset($_GET['post_id']) || !isnum($_GET['post_id'])) {
            redirect(BASEDIR."forum/index.php");
        }
        add_to_head("<meta http-equiv='refresh' content='2; url=".BASEDIR."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$_GET['post_id']."#post_".$_GET['post_id']."' />\n");
        if ($settings['thread_notify']) {
            $result = dbquery(
                "SELECT tn.*, tu.user_id, tu.user_name, tu.user_email, tu.user_level, tu.user_groups
				FROM ".DB_THREAD_NOTIFY." tn
				LEFT JOIN ".DB_USERS." tu ON tn.notify_user=tu.user_id
				WHERE thread_id='".$_GET['thread_id']."' AND notify_user!='".$userdata['user_id']."' AND notify_status='1'
			");
            if (dbrows($result)) {
                require_once INCLUDES."sendmail_include.php";
                $data2 = dbarray(dbquery("SELECT tf.forum_access, tt.thread_subject
					FROM ".DB_THREADS." tt
					INNER JOIN ".DB_FORUMS." tf ON tf.forum_id=tt.forum_id
					WHERE thread_id='".$_GET['thread_id']."'"));
                $link = $settings['siteurl']."forum/viewthread.php?forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']."&pid=".$_GET['post_id']."#post_".$_GET['post_id'];
                $template_result = dbquery("SELECT template_key, template_active FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='POST' LIMIT 1");
                if (dbrows($template_result)) {
                    $template_data = dbarray($template_result);
                    if ($template_data['template_active'] == "1") {
                        while ($data = dbarray($result)) {
                            if ($data2['forum_access'] == 0 || in_array($data2['forum_access'], explode(".", $data['user_level'].".".$data['user_groups']))) {
                                sendemail_template("POST", $data2['thread_subject'], "", "", $data['user_name'], $link, $data['user_email']);
                            }
                        }
                    } else {
                        while ($data = dbarray($result)) {
                            if ($data2['forum_access'] == 0 || in_array($data2['forum_access'], explode(".", $data['user_level'].".".$data['user_groups']))) {
                                $message_el1 = ["{USERNAME}", "{THREAD_SUBJECT}", "{THREAD_URL}"];
                                $message_el2 = [$data['user_name'], $data2['thread_subject'], $link];
                                $message_subject = str_replace("{THREAD_SUBJECT}", $data2['thread_subject'], $locale['550']);
                                $message_content = str_replace($message_el1, $message_el2, $locale['551']);
                                sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $message_subject, $message_content);
                            }
                        }
                    }
                } else {
                    while ($data = dbarray($result)) {
                        if ($data2['forum_access'] == 0 || in_array($data2['forum_access'], explode(".", $data['user_level'].".".$data['user_groups']))) {
                            $message_el1 = ["{USERNAME}", "{THREAD_SUBJECT}", "{THREAD_URL}"];
                            $message_el2 = [$data['user_name'], $data2['thread_subject'], $link];
                            $message_subject = str_replace("{THREAD_SUBJECT}", $data2['thread_subject'], $locale['550']);
                            $message_content = str_replace($message_el1, $message_el2, $locale['551']);
                            sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $message_subject, $message_content);
                        }
                    }
                }
                $result = dbquery("UPDATE ".DB_THREAD_NOTIFY." SET notify_status='0' WHERE thread_id='".$_GET['thread_id']."' AND notify_user!='".$userdata['user_id']."'");
            }
        }
        echo "<a href='".BASEDIR."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$_GET['post_id']."#post_".$_GET['post_id']."'>".$locale['447']."</a> ::\n";
    } else {
        if (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) {
            redirect(BASEDIR."forum/index.php");
        }
        $data = dbarray(dbquery("SELECT post_id FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY post_id DESC"));
        add_to_head("<meta http-equiv='refresh' content='4; url=".BASEDIR."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$data['post_id']."#post_".$data['post_id']."' />\n");
        echo "<a href='".BASEDIR."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$data['post_id']."#post_".$data['post_id']."'>".$locale['447']."</a> ::\n";
    }
    echo "<a href='".BASEDIR."forum/viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['448']."</a> ::\n";
    echo "<a href='".BASEDIR."forum/index.php'>".$locale['449']."</a><br /><br />\n</div>\n";
    closetable();
} else if ($_GET['post'] == "edit") {
    if (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) {
        redirect(BASEDIR."forum/index.php");
    }
    add_to_title($locale['global_201'].$locale['409']);
    add_to_head("<meta http-equiv='refresh' content='2; url=".BASEDIR."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$_GET['post_id']."#post_".$_GET['post_id']."' />\n");
    opentable($locale['409']);
    echo "<div style='text-align:center'><br />\n";
    if ($errorb) {
        echo $errorb."<br /><br />\n";
    } else {
        echo $locale['446']."<br /><br />\n";
    }
    echo "<a href='".BASEDIR."forum/viewthread.php?thread_id=".$_GET['thread_id']."&amp;pid=".$_GET['post_id']."#post_".$_GET['post_id']."'>".$locale['447']."</a> ::\n";
    echo "<a href='".BASEDIR."forum/viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['448']."</a> ::\n";
    echo "<a href='".BASEDIR."forum/index.php'>".$locale['449']."</a><br /><br />\n</div>\n";
    closetable();
}

require_once THEMES."templates/footer.php";