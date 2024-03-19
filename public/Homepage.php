<?php
require '../vendor/autoload.php';

use Google\Cloud\BigQuery\BigQueryClient;

$projectId = 's3273504-a1t2';
$top10 = 'SELECT time_ref,sum(value) as trade_value FROM `s3273504-a1t2.Task_2_Dataset.gsquarterlySeptember20`
group by time_ref
order by sum(value) desc
LIMIT 10';

$top40 = 'SELECT a.country_label,a.product_type,(b.Imports_value - a.Exports_value) as trade_deficit_value,a.status from (
    SELECT b.country_label,a.product_type,a.account,sum(a.value) as Exports_Value, a.status FROM `s3273504-a1t2.Task_2_Dataset.gsquarterlySeptember20` as a
    join `s3273504-a1t2.Task_2_Dataset.country_classification` as b on a.country_code = b.country_code
    where left(a.time_ref,4) between \'2013\' and \'2015\'
    and a.status = \'F\'
    and account = \'Exports\'
    group by b.country_label, a.product_type, a.account,a.status
    order by b.country_label, a.product_type ) a
    join (SELECT b.country_label,a.product_type,a.account,sum(a.value) as Imports_value, a.status FROM `s3273504-a1t2.Task_2_Dataset.gsquarterlySeptember20` as a
    join `s3273504-a1t2.Task_2_Dataset.country_classification` as b on a.country_code = b.country_code
    where left(a.time_ref,4) between \'2013\' and \'2015\'
    and a.status = \'F\'
    and account = \'Imports\'
    group by b.country_label, a.product_type, a.account,a.status
    order by b.country_label, a.product_type) b on a.country_label = b.country_label and a.product_type = b.product_type
    order by trade_deficit_value desc
    LIMIT 40';

function run_query(string $projectId, string $query)
{
    $bigQuery = new BigQueryClient([
        'projectId' => $projectId,
    ]);
    $jobConfig = $bigQuery->query($query);
    $queryResults = $bigQuery->runQuery($jobConfig);

    return $queryResults;
}



?>
<!DOCTYPE html>
<html>
<header>
    <link type="text/css" rel="stylesheet" href="/stylesheets/styles.css">
</header>

<body class="content">

    <?php $result1 = run_query($projectId, $top10);
    $rows1 = $result1->rows();
    ?>

    <h1>Show top 10 time slots (year and month) with the highest trade value</h1>

    <table class="center">
        <th>time_ref</th>
        <th>trade_value</th>
        <?php foreach ($rows1 as $row1): ?>
            <tr>
                <td>
                    <?= $row1['time_ref']; ?>
                </td>
                <td>
                    <?= $row1['trade_value']->get(); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>


    <h1>Show top 40 countries with the highest total trade deficit value</h1>
    
    <?php $result2 = run_query($projectId, $top40);
    $rows2 = $result2->rows();
    ?>

<table class="center">
        <th>country_label</th>
        <th>product_type</th>
        <th>trade_deficit_value</th>
        <th>status</th>
        <?php foreach ($rows2 as $row2): ?>
            <tr>
                <td>
                    <?= $row2['country_label']; ?>
                </td>
                <td>
                    <?= $row2['product_type']; ?>
                </td>
                <td>
                    <?= $row2['trade_deficit_value']; ?>
                </td>
                <td>
                    <?= $row2['status']; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>



</body>

</html>