<?php
/**
 * @author Steinsplitter / https://de.wikipedia.org/wiki/Benutzer:Steinsplitter
 * @author Gorlingor / https://de.wikipedia.org/wiki/Benutzer:Gorlingor
 * @copyright 2015 GRStalker authors
 * @license http://unlicense.org/ Unlicense
 */

$getd = $_GET['wm'];

if (isset($getd)) {
        $tools_pw = posix_getpwuid(posix_getuid());
        $tools_mycnf = parse_ini_file($tools_pw['dir'] . "/replica.my.cnf");
        $db = new mysqli('metawiki.labsdb', $tools_mycnf['user'], $tools_mycnf['password'],
                'metawiki_p');
        if ($db->connect_errno)
                die("Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error);
        $r = $db->query( 'SELECT
 log_title,
 actor_name,
 log_timestamp,
 log_params,
 comment_text,
 DATE_FORMAT(log_timestamp, "%b %d %Y %h:%i %p") AS lts
FROM logging
JOIN comment
 ON comment_id = log_comment_id
INNER JOIN actor
 ON log_actor = actor_id
WHERE log_namespace = 2
AND log_title LIKE "%' . str_replace(" ", "_", $db->real_escape_string($getd)) . '"
AND log_title LIKE "%@%"
AND  log_type = "rights"
ORDER BY log_timestamp DESC
LIMIT 1000;');
        unset($tools_mycnf, $tools_pw);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title>GRStalker</title>
        <link rel="stylesheet" href="//tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/2.3.2/css/bootstrap.min.css">
        <script src="//tools-static.wmflabs.org/tooltranslate/tt.js"></script>
        <script src="//tools-static.wmflabs.org/cdnjs/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <style>
      body {
        padding-top: 65px;
      }
    </style>
    <script>
        $(document).on("click", "#sendreq1", function() {
        $('#spinner').show();
        });
    </script>
</head>
<body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">

          <a class="brand" href="index.php"><span tt="grstalker">GRStalker</span></a>
          <div class="nav-collapse collapse">
            <div class="navbar-form pull-right">
             <span id = "fasti18n"> <span id='tooltranslate_wrapper'></span></span>
            </div>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
        <div class="container">
<?php
require_once ( "/data/project/tooltranslate/public_html/tt.php") ;
$tt = new ToolTranslation ( array ( 'tool' => 'grstalker' , 'language' => 'en' , 'fallback' => 'en' , 'highlight_missing' => false ) ) ;
print $tt->getJS('#tooltranslate_wrapper') ;
print $tt->getJS() ;
?>
        <p><span tt="desc">Userrightchanges via meta on local wikis.</span></p>
        <form class="form-search">
                <input type="text" value="" name="wm" id="es" class="input-medium search-query" placeholder="user@wiki" />
                <button type="submit" id="sendreq1" class="btn sendreq1"><span tt="search">Search</span></button>
        <span id="spinner" style="display:none;"><img src="https://upload.wikimedia.org/wikipedia/commons/7/78/24px-spinner-0645ad.gif"/ ></span>
        </form>
<?php if (isset($getd)): ?>
        <p><b><span tt="results">Results for:</span></b> <?= htmlspecialchars($getd) ?></p>
        <br/>
        <table class="table table-bordered">
                <thead>
                        <tr>
                                <th><span tt="ts">Timestamp</span></th>
                                <th><span tt="user">User</span></th>
                                <th><span tt="actor">Actor</span></th>
                                <th><span tt="prevr">Previous rights</span></th>
                                <th><span tt="subs">Subsequent rights</span></th>
                                <th><span tt="reason">Reason</span></th>
                        </tr>
                </thead>
                <tbody>

                <?php while ($row = $r->fetch_row()):
                if (preg_match('/oldgroups/', $row[3]))
                {
                $rightChanges = unserialize($row[3]);
                $priv = implode(', ', $rightChanges['4::oldgroups']);
                $newone = implode(', ', $rightChanges['5::newgroups']);
                $push = "<td>". $priv ."</td><td>". $newone ."</td>";
                } else {
                $oldlog = preg_replace("/\n/", "</td><td>", htmlspecialchars($row[3]));
                $push = "<td>". $oldlog ."</td>";
                }
                ?>
                <tr>
                        <td><?= htmlspecialchars($row[5]) ?></td>
                        <td><?= str_replace("_", " ", htmlspecialchars( $row[0] )) ?></td>
                        <td><a href="https://meta.wikimedia.org/wiki/User:<?= str_replace(" ", "_", htmlspecialchars( $row[1] )) ?>"><?= htmlspecialchars( $row[1] ) ?></a></td>
                        <?= $push; ?>
                        <td><small><?= htmlspecialchars( $row[4] ) ?></small></td>
                </tr>
                <?php endwhile; ?>

                </tbody>
        </table>
        <?php
        $r->close();
        $db->close();
        ?>
<?php else: ?>
        <div class="alert alert-info">
                <div tt="howto"><strong>How to use this tool?</strong> You can search rightchanges by wiki (Example:  <strong>dewiki</strong>), by username (Example: <strong>Steinsplitter@test2wiki</strong>) or by a specific username on all wikis (Example: <strong>Base@%</strong>).</span>
        </div>
<?php endif; ?>
</div>
</div>
<center><small><span tt="maxresults">Max. 1000 results</span></small></center>
</body>
</html>
