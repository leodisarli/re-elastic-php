<?php

date_default_timezone_set('America/Sao_Paulo');

$elasticHost = getenv('ELASTIC_HOST') ?? null;
$elasticUser = getenv('ELASTIC_USER') ?? null;
$elasticPassword = getenv('ELASTIC_PASSWORD') ?? null;

$onlyOneForSafety = getenv('ONLY_ONE_FOR_SAFETY') ?? 0;
$onlyOneForSafety = (bool) $onlyOneForSafety;

$elastic = new Elastic([
    'host' => getenv('ELASTIC_HOST'),
    'user' => getenv('ELASTIC_USER') ?? null,
    'pass' => getenv('ELASTIC_PASSWORD') ?? null,
]);

require_once 'helpers.php';


try {
    showLog('Searching indices');
    $indices = $elastic->indices();

    if (empty($indices)) {
        showLog('No index found');
        showLog(':>>>');
        return;
    }
    $totalIndices = count($indices);

    showLog($totalIndices . ' indices found');

    $indicesErrors = [];
    $indicesOk = [];
    
    foreach ($indices as $indice) {
        $indexName = $indice['index'];
        $indexCount = $indice['docs.count'];
        $indexSize = $indice['pri.store.size'];

        showLog('==========================================================================');
        showLog('Cloning re-index: ' . $indexName . ' docs: ' . $indexCount . ' size: ' . $indexSize);
        $reindex = $elastic->reindex($indexName, $indexName.'-temp');
        showLog('Cloned in ' . $reindex['took'] . ' ms');

        showLog('Waiting 5s for check');
        sleep(5);

        showLog('Checking consistency');
        $oldStats = $elastic->stats($indexName);
        $oldTotal = $oldStats['_all']['primaries']['docs']['count'] ?? 0;

        $newStats = $elastic->stats($indexName.'-temp');
        $newTotal = $newStats['_all']['primaries']['docs']['count'] ?? 0;

        showLog('Origin count: ' . $oldTotal);
        showLog('Temp count: ' . $newTotal);

        if ($oldTotal !== $newTotal) {
            showLog('Inconsistency detected, removing temp and moving on');
            $indicesErrors[] = $indexName;
            $elastic->del($indexName.'-temp');
            continue;
        }

        showLog('Removing old');
        $elastic->del($indexName);

        showLog('Reindexing ' . $indexName);
        $reindex = $elastic->reindex($indexName.'-temp', $indexName);
        showLog('Reindexed in ' . $reindex['took'] . ' ms');

        showLog('Removing temp');
        $elastic->del($indexName.'-temp');
        $indicesOk[] = $indexName;
        
        if ($onlyOneForSafety) {
            showLog('Executing only one for safety');
            break;
        }
    }

    showLog('==========================================================================');
    showLog('Total indices: ' . $totalIndices);
    showLog('Total ok: ' . count($indicesOk));
    showLog('Total error: ' . count($indicesErrors));

    if (count($indicesErrors) > 0) {
        print_r($indicesErrors);
        echo PHP_EOL;
    }

    showLog('Done! :>>>');

} catch (Exception $e) {
    error_log($e->getMessage());
}
