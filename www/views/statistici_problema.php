<?php

require_once IA_ROOT_DIR.'www/format/format.php';
require_once IA_ROOT_DIR.'www/format/table.php';
require_once IA_ROOT_DIR.'www/php-ofc-library/open-flash-chart-object.php';

include 'header.php';

$username = '';
if (array_key_exists('username', $view)) {
    $username = $view['username'];
}
$task_id = $view['task_id'];
$round_id = $view['round_id'];
$round_name = $view['round_name'];

echo '<h1>Statisticile problemei '.format_link($view['task_url'], $view['task_id']).
     ' ('.htmlentities($round_name).')</h1>';

$data = $view['data'];

echo '<h2>Clasamente</h2>';
if (count($data['time']) === 0) {
    echo 'Nicio sursa corecta trimisa la aceasta problema :(';
} else {
    $column_infos = array(
        array(
            'title' => 'Loc',
            'key' => 'position',
            'css_class' => 'number',
        ),
        array(
            'title' => 'Utilizator',
            'key' => 'username',
            'rowform' => function($row) {
                return format_user_tiny($row['username'], $row['full_name'], $row['rating']);
            },
        ),
        array(
            'title' => '',
            'key' => 'special_score',
            'css_class' => 'number',
            'rowform' => function($row) {
                return format_link('job_detail/'.$row['job_id'], $row['special_score']);
            },
        ),
    );

    $options = array(
        'css_row_parity' => false,
    );

    $long_wording = array(
        'time' => 'timpul de executie',
        'memory' => 'memoria folosita',
        'size' => 'dimensiunea sursei',
    );
    $header_wording = array(
        'time' => 'Timp',
        'memory' => 'Memorie',
        'size' => 'Marime',
    );

    foreach ($data as $criteria => $ranking) {
        echo 'Clasament dupa '.$long_wording[$criteria];
        $column_infos[count($column_infos) - 1]['title'] = $header_wording[$criteria];
        echo format_table($data[$criteria], $column_infos, $options);
        echo '<br/>';
    }
}

echo '<h2>Alte statistici</h2>';
echo 'Numarul mediu de submisii gresite: '.$view['average_wrong_submissions'].'<br/>';
if (!identity_is_anonymous()) {
    echo 'Numarul tau de submisii gresite: '.$view['user_wrong_submissions'].'<br/>';
}
echo 'Procentajul de reusita: '.$view['solved_percentage'].'%<br/>';

$html = '<br /><div id="distribution-chart"></div><br />'
    .'<script src=\'static\\js\\swfobject.js\'></script>  <script type="text/javascript"> swfobject.embedSWF("'.html_escape(url_static('swf/open-flash-chart.swf'))
    .'", "distribution-chart", "560", "280", "9.0.0", null, {"data-file":"'.html_escape(url_home()."plot/points_distribution?args=$username,$task_id,$round_id").'"}); </script>';

echo $html;

include 'footer.php';
