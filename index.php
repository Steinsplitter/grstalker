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
        $r = $db->query('SELECT log_title, user_name, log_timestamp, log_params, log_comment, DATE_FORMAT(log_timestamp, "%b %d %Y %h:%i %p") AS lts FROM logging JOIN user ON log_user = user_id WHERE log_namespace = 2 AND log_title LIKE "%' . str_replace(" ", "_", $db->real_escape_string($getd)) . '" AND log_title LIKE "%@%" AND  log_type = "rights" ORDER BY log_timestamp DESC LIMIT 1000;');
        unset($tools_mycnf, $tools_pw);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title>GRStalker</title>
<link href="/steinsplitter/bootstrap.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px;
      }
    </style>
</head>
<body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">

          <a class="brand" href="#">GRStalker</a>
          <div class="nav-collapse collapse">
                <ul id="toolbar-right" class="nav pull-right">
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
        <div class="container">
        <p>Userrightchanges via meta on local wikis.</p>
        <form class="form-search">
                <input type="text" value="" name="wm" id="es" class="input-medium search-query" placeholder="user@wiki" />
                <button type="submit" class="btn">Search</button>
        </form>
<?php if (isset($getd)): ?>
        <p><b>Results for:</b> <?= htmlspecialchars($getd) ?></p>
        <br/>
        <table class="table table-bordered">
                <thead>
                        <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Actor</th>
                                <th>Previous rights</th>
                                <th>Subsequent rights</th>
                                <th>Reason</th>
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
                <strong>How to use this tool?</strong> You can search rightchanges by wiki (Example:  <strong>dewiki</strong>), by username (Example: <strong>Steinsplitter@test2wiki</strong>) or by a specific username on all wikis (Example: <strong>Base@%</strong>).
        </div>
<?php endif; ?>
</div>
</div>
<center>Powered by <a href="https://www.mediawiki.org/wiki/Wikimedia_Labs" title="Wikimedia Labs"><em>WMF Labs</em></a> | Max. 1000 results</center>
</body>
</html>
