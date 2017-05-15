<?php
// intialise slim
require(__DIR__.'/../../vendor/autoload.php');
use \Slim\App;
$app = new App();

// require db service
require_once(__DIR__.'/dbservice.php');
// require request manager
require_once(__DIR__.'/requestmanager.php');

// rate-limit middleware
$rateLimit = function($request, $response, $next) {
  // get request manager and client ip
  $manager = new apiRequestManager();
  $ip = $manager->getIp();

  // head of middleware - check count of todays requests
  $dailyLimitCount = $manager::DAILY_REQUEST_LIMIT;
  $dailyRequestCounter = $manager->countRequestsForDay($ip)[0];
 
  // continue with route logic if not exceeded daily limit
  if ($dailyRequestCounter <= $dailyLimitCount) {
    // middle of middleware - allow route logic to run 
    $response = $next($request, $response);
    // json decode the output as array
    $result = json_decode($response->getBody(), true);
    // increment api request
    $manager->logRequest($ip, time(), $request->getUri());
  } else {
    $response = $response->withStatus(429);
    $result = array();
    $result['warning'] = $manager::LIMIT_WARNING.$ip;
  }
  
  // tail of middleware
  // add request count for day
  $result['dailyRequestCounter'] = $dailyRequestCounter;

  // return response
  $response = $response->withJson($result);
    
  return $response;

};

// uniqueMorphemes 
$app->get('/uniqueMorphemes/{morphemeList}', function ($request, $response, $args) {
  try {   
    // api output
    $result = array();
    $status = 0;

    // return sorted morpheme list in response
    $sortedMorphemeList = explode(',', $args['morphemeList']);
    usort($sortedMorphemeList, function($a, $b) {
      return strlen($b) - strlen($a);
    });
    $result['sortedMorphemeList'] = $sortedMorphemeList;
    
    // get query parameters for response
    foreach($request->getQueryParams() as $key => $value) {
      $result['parameters'][] = $key.'='.$value;
    };

    // get database access layer
    $db = new dbservice();
    
    // query data
    $result['tokens'] = $db->queryAndMorphemizeData($request->getQueryParams(), [], $sortedMorphemeList, true);
 
    // http status = all good
    $status = 200;
    
  } catch (Exception $e) {
    // error handler
    $status = 500;
    $result['error'] = $e->getMessage();
    
  } finally {
    // return response
    $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    $response = $response->withStatus($status);
    $response = $response->write(json_encode($result));
    return $response;   
  }
})->add($rateLimit);

// morphemes
$app->get('/morphemes/{morphemeList}[/{columns}]', function ($request, $response, $args) {
  try {   
    // api output
    $result = array();
    $status = 0;

    // return sorted morpheme list in response
    $sortedMorphemeList = explode(',', $args['morphemeList']);
    usort($sortedMorphemeList, function($a, $b) {
      return strlen($b) - strlen($a);
    });
    $result['sortedMorphemeList'] = $sortedMorphemeList;
    
    // get query parameters for response
    foreach($request->getQueryParams() as $key => $value) {
      $result['parameters'][] = $key.'='.$value;
    };

    // get database access layer
    $db = new dbservice();
    
    // check optional column args against columns
    if(isset($args['columns'])) {
      $optionalColumns = $args['columns'];
      $selectedColumns = array_intersect(explode(',', $optionalColumns), array_keys($db->columnInfo));
      $result['selectedColumns'] = $selectedColumns;
    } else {
      $selectedColumns = array();
      $result['selectedColumns'] = 'item';
    }

    // query data
    $result['tokens'] = $db->queryAndMorphemizeData($request->getQueryParams(), $selectedColumns, $sortedMorphemeList, false);
 
    // http status = all good
    $status = 200;
    
  } catch (Exception $e) {
    // error handler
    $status = 500;
    $result['error'] = $e->getMessage();
    
  } finally {
    // return response
    $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    $response = $response->withStatus($status);
    $response = $response->write(json_encode($result));
    return $response;   
  }
})->add($rateLimit);

// unique tokens (no columns option available)
$app->get('/uniqueTokens', function ($request, $response, $args) {
  try {    
    // api output
    $result = array();
    $status = 0;
    
    // get query parameters for response
    foreach($request->getQueryParams() as $key => $value) {
      $result['parameters'][] = $key.'='.$value;
    };
          
    // get database access layer
    $db = new dbservice();
       
    // query data
    $result['tokens'] = $db->queryData($request->getQueryParams(), [], true);
    
    // http status = all good
    $status = 200;
    
  } catch (Exception $e) {
    // error handler
    $status = 500;
    $result['error'] = $e->getMessage();
    
  } finally {
    // return response
    $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    $response = $response->withStatus($status);
    $response = $response->write(json_encode($result));
    return $response;  
  }
})->add($rateLimit);

// tokens
$app->get('/tokens[/{columns}]', function ($request, $response, $args) {
  try {    
    // api output
    $result = array();
    $status = 0;
    
    // get query parameters for response
    foreach($request->getQueryParams() as $key => $value) {
      $result['parameters'][] = $key.'='.$value;
    };
          
    // get database access layer
    $db = new dbservice();
    
    // check optional column args against columns
    if(isset($args['columns'])) {
      $optionalColumns = $args['columns'];
      $selectedColumns = array_intersect(explode(',', $optionalColumns), array_keys($db->columnInfo));
      $result['selectedColumns'] = $selectedColumns;
    } else {
      $selectedColumns = array();
      $result['selectedColumns'] = 'item';
    }
    
    // query data
    $result['tokens'] = $db->queryData($request->getQueryParams(), $selectedColumns, false);
    
    // http status = all good
    $status = 200;
    
  } catch (Exception $e) {
    // error handler
    $status = 500;
    $result['error'] = $e->getMessage();
    
  } finally {
    // return response
    $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    $response = $response->withStatus($status);
    $response = $response->write(json_encode($result));
    return $response;  
  }
})->add($rateLimit);

// columns
$app->get('/columns', function ($request, $response, $args) {
  try {    
    // api output
    $result = array();
    $status = 0;
          
    // get database access layer
    $db = new dbservice();

    // add columns from db to response
    $result['values'] = $db->columnInfo;

    // http status = all good
    $status = 200;

  } catch (Exception $e) {
    // error handler
    $status = 500;
    $result['error'] = $e->getMessage();
    
  } finally {
    // return response
    $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    $response = $response->withStatus($status);
    $response = $response->write(json_encode($result));
    return $response;  
  }
})->add($rateLimit);

// column values
$app->get('/meta/{column}', function ($request, $response, $args) {
  try {    
    // api output
    $result = array();
    $status = 0;
          
    // get database access layer
    $db = new dbservice();

    // add columns from db to response
    $result['values'] = $db->queryMeta($args['column']);

    // http status = all good
    $status = 200;

  } catch (Exception $e) {
    // error handler
    $status = 500;
    $result['error'] = $e->getMessage();
    
  } finally {
    // return response
    $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    $response = $response->withStatus($status);
    $response = $response->write(json_encode($result));
    return $response;  
  }
})->add($rateLimit);

// run slim
$app->run();
