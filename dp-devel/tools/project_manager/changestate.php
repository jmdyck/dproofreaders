<?
$relPath="./../../pinc/";
include($relPath.'dp_main.inc');

    $todaysdate = time();

    // Get Passed parameters to code
    $projectid = $_GET['project'];
    $newstate = $_GET['state'];
    $always = $_GET['always'];

    // Get more information about the project
    $sql = mysql_query("SELECT state, username FROM projects WHERE projectid = '$projectid'");
    $oldstate = mysql_result($sql, 0, "state");
    $username = mysql_result($sql, 0, "username");

    // Get more information about the project manager
    $sql = mysql_query("SELECT sitemanager FROM users WHERE username = '$pguser'");
    $sitemanager = mysql_result($sql, 0, "sitemanager");

    // If it was in "Unavailable No Round"
    if ($oldstate == 0) {
        // Check to see if there are pages. Project shouldn't be released if it has no pages in it.
        $result = mysql_query("SELECT fileid FROM $projectid");
        if ((mysql_num_rows($result) == 0) && ($newstate == 8)) {
            echo "<P>Project must have pages to be proofread in order to be taken out of unavailable no round. Back to <a href=\"projectmgr.php\">project manager</a> page.";
            die();
        }
    }

    // Checks the user's permissions to make changes
    if (($sitemanager != 'yes') && ($pguser != $username)) {
        echo "<P>You are not allowed to change the state on this project. If this message is an error, contact the <a href=\"charlz@lvcablemodem.com\">site manager</a>.";
        echo "<P>Back to <a href=\"projectmgr.php\">project manager</a> page.";
    } else if ($newstate == 42) {
        // Allows a user to delete a project as a last-case scenario
        if ($always == 'yes') {
            $sql = "DROP TABLE $projectid";
            $result = mysql_query($sql);

            //update projects table by removing dropped project info
            $sql = "DELETE FROM projects WHERE projectid = '$projectid'";
            $result = mysql_query($sql);

            $dir_name = "../../projects/".$projectid;
            exec("rm -rf $dir_name");
            echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0 ;URL=projectmgr.php\">";

        } else { // Gives them a warning before deleting a project, explaining why it should not be done.
            echo "<P><B>NOTE:</B> You no longer delete a project from the site, you move it to the posted to Project Gutenberg status. Deleting is only for a project that is beyond repair.";
            print "<P>Are you sure you want to change this state? If so, click <A HREF=\"changestate.php?project=$projectid&state=$newstate&always=yes\">here</a>, otherwise back to <a href=\"projectmgr.php\">project listings</a>.";
        }
    } else if ($newstate == 30) {
        // Changing a state to "Posted to Project Gutenberg"
        $sql = mysql_query("UPDATE projects SET state = $newstate WHERE projectid = '$projectid'");
	
	//Delete the topic from forum if there is one
	$result = mysql_query("SELECT topic_id FROM projects WHERE projectid='$projectid'");
	$topic_id = mysql_result($result, 0, "topic_id");
if ($topic_id == "") {
//Do Nothing
} else {
	$i = 0;
	$post_list = mysql_query("SELECT * FROM phpbb_posts WHERE topic_id=$topic_id");
	while($row = mysql_fetch_array($post_list) ) {
	$postid = $row['post_id'];
	$i++;
	$delete_post_text = mysql_query("DELETE FROM phpbb_posts_text WHERE post_id=$postid");
	}
	$delete_post = mysql_query("DELETE FROM phpbb_posts WHERE topic_id=$topic_id");
	$delete_topic = mysql_query("DELETE FROM phpbb_topics WHERE topic_id=$topic_id");
	$get_count = mysql_query("SELECT forum_posts, forum_topics FROM phpbb_forums WHERE forum_id=2");
	$forum_posts = mysql_result($get_count, 0, "forum_posts");
	$forum_topics = mysql_result($get_count, 0, "forum_topics");
	$forum_topics = $forum_topics-1;
	$forum_posts = $forum_posts-$i;
	$update_count = mysql_query("UPDATE phpbb_forums SET forum_posts=$forum_posts, forum_topics=$forum_topics WHERE forum_id=2");
}

        // Change Modified Date to New Date
        mysql_query("UPDATE projects SET modifieddate = '$todaysdate' WHERE projectid = '$projectid'");

        // TODO: Archive the project
        echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0 ;URL=editproject.php?project=$projectid\">";
    } else if (($newstate == 0) || ($newstate == 10) || ($newstate == 25) || ($always == 'yes') ||
        ($oldstate == 0) || ($oldstate == 1) || ($oldstate == 10) || ($oldstate == 25)) {

        // The above are valid changes that can be made to a project

        $sql = mysql_query("UPDATE projects SET state = $newstate WHERE projectid = '$projectid'");

        if ($newstate == 1) $sql = mysql_query("UPDATE projects SET modifieddate = '$todaysdate' WHERE projectid = '$projectid'");
        if ($newstate == 20) $sql = mysql_query("UPDATE projects SET checkedoutby = '' WHERE projectid = '$projectid'");
        if ($newstate == 25) $sql = mysql_query("UPDATE projects SET checkedoutby = '$pguser' WHERE projectid = '$projectid'");

        echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0 ;URL=projectmgr.php\">";

    } else if (($newstate == 8) || ($newstate == 18)) {

        // Allows a user to change a project to be checked, but should not be something they do.

        echo "<P><B>NOTE:</B> Changing a projects state to verify for the current round is not needed. Once the last available page is done, this will be changed automatically now. After it has been changed to verify for the current round, the auto-cleanup script will check the validity and will demote it back down to being available and will make the missing pages available again.";
        print "<P>Are you sure you want to change this state? If so, click <A HREF=\"changestate.php?project=$projectid&state=$newstate&always=yes\">here</a>, otherwise back to <a href=\"projectmgr.php\">project listings</a>.";
    } else {

        // This option should never appear if they follow the options on the page, only for those that know what they are doing...
        echo "<P><B>NOTE:</B> The choice you made is one that should not be chosen quickly. If you do not know what it will do, it is best to keep things the way they are.";
        print "<P>Are you sure you want to change this state? If so, click <A HREF=\"changestate.php?project=$projectid&state=$newstate&always=yes\">here</a>, otherwise back to <a href=\"projectmgr.php\">project listings</a>.";
    }
?>
