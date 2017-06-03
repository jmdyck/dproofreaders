<?php
$relPath="./../../pinc/";
include_once($relPath.'base.inc');
include_once($relPath.'Project.inc');
include_once($relPath.'user_is.inc');
include_once($relPath.'theme.inc');

require_login();

$title = _("Copyright Approval");
output_header($title);
echo "<h1>$title</h1>";

if (!$site_supports_metadata)
{
    die('$site_supports_metadata is false, so exiting.');
}

if (!user_is_a_sitemanager())
{
    die('You are not authorized to invoke this script.');
}

//----------------------------------------------------------------------------------

$projectid = validate_projectID('projectid', @$_GET['projectid'], true);
if (isset($projectid))
{
    //update project approval status
    if ($_GET['metadata'] =='approved')
    {
        $statuschange = 'project_new_app';
    }
    else
    {
        $statuschange = 'project_new_unapp';     
    }

    $result = mysql_query("
        UPDATE projects
        SET state = '$statuschange'
        WHERE projectid = '$projectid'
    ");
}

echo "<p>" . _("The following books need to be approved/disapproved for copyright clearance.") . "</p>";

echo "<table class='themed'>\n";
    // Header row
    echo "
        <tr>
            <th colspan='1'>" . _("Title") . "</td>
            <th colspan='1'>" . _("Author") . "</td>
            <th colspan='1'>" . _("Clearance Line") . "</td>
            <th colspan='1'>" . _("Approved/Disapproved") . "</td>
        </tr>
    ";

    $result = mysql_query("
        SELECT projectid, nameofwork, authorsname, clearance, state
        FROM projects
        WHERE state = 'project_new_waiting_app'
    ");

    while ($row = mysql_fetch_assoc($result)) {
        $projectid = $row["projectid"];
        $state = $row["state"];
        $name = $row["nameofwork"];
        $author = $row["authorsname"];
        $clearance = $row["clearance"];

        echo "
            <tr>
            <td align='right'><a href='$code_url/project.php?id=$projectid'>$name</a></td>
            <td align='right'>$author</td>
            <td><input type='text' size='67' name='clearance' value='$clearance'></td>
            <td>
                <form action='proj_approvals.php?projectid=$projectid'>
                " . _("Approved") . "<input type='radio' name='metadata' value='approved'>
                " . _("Disapproved") . "<input type='radio' name='metadata' value='disapproved'>
                <INPUT TYPE=SUBMIT VALUE='update'>
                </form>
            </td>
            </tr>
        ";
    }

echo "</table>";
echo "<br>";

// vim: sw=4 ts=4 expandtab
