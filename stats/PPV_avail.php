<?php
$relPath="../pinc/";
include_once($relPath.'base.inc');
include_once($relPath.'project_states.inc');
include_once($relPath.'theme.inc');

require_login();

$clausemap = array(
    'nameofwork'   => 'nameofwork ASC',
    'modifieddate' => 'modifieddate ASC',
    'PPer'         => 'postproofer ASC',
    'PM'           => 'username ASC'
);
$order = get_enumerated_param($_GET, 'order', 'nameofwork', array_keys($clausemap));
$orderclause = $clausemap[$order];

// ------------------

$title = _("Books Available for PPV");
output_header($title);

echo "<h1>$title</h1>\n";

// ------------------

// Header row

$colspecs = array(
    'bogus'        => _('#'),
    'nameofwork'   => _('Name of Work'),
    'PM'           => _('Project Manager'),
    'PPer'         => _('Post-Processed By'),
    'modifieddate' => _('Date Last Modified'),
);

echo "<table class='themed striped'>\n";
echo "<tr>";
foreach ( $colspecs as $col_order => $col_header )
{
    $s = $col_header;
    // Make each column-header a link that will sort on that column,
    // except for the header of the column that we're already sorting on.
    if ( $col_order != $order && $col_order != 'bogus' )
    {
        $s = "<a href='PPV_avail.php?order=$col_order'>$s</a>";
    }
    echo "<th>$s</th>";
}
echo "</tr>\n";

// ------------------

// Body

$result = mysql_query("
    SELECT
        nameofwork,
             username,
        postproofer,
        modifieddate
    FROM projects
    WHERE state = '".PROJ_POST_SECOND_AVAILABLE."'
    ORDER BY $orderclause
");

$rownum = 0;
while ( $project = mysql_fetch_object( $result ) )
{
    $rownum++;

    //calc last modified date for project
    $today = getdate($project->modifieddate);
    $month = $today['month'];
    $mday = $today['mday'];
    $year = $today['year'];
    $datestamp = "$month $mday, $year";

    echo "
        <tr>
        <td>$rownum</td>
        <td width='200'>$project->nameofwork</td>
        <td>$project->username</td>
        <td>$project->postproofer</td>
        <td>$datestamp</td>
        </tr>
    ";
}

echo "</table>";

// vim: sw=4 ts=4 expandtab
