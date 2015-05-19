<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'Slim/Slim/Slim.php';
require 'functions.php';

\Slim\Slim::registerAutoloader();

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
    $app = new \Slim\Slim(array(
                                'debug' => true
                                ));
    
//au choix, mais ne règle pas le problème UTF-8
$app->response()->header("Content-Type", "application/json");
//$app->contentType('text/html; charset=utf-8');
    
/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, `Slim::patch`, and `Slim::delete`
 * is an anonymous function.
 */

// GET route
//GET with authentification /:db/:collection/:name/:pass -> list all articles with tokens
$app->get(
    '/:db/:collection/:admin/:pass/:username/:userpass',
    function ($db,$collection,$admin,$pass,$username,$userpass) use ($app) {
        //echo "tous les articles !";
        //header("Content-Type: application/json");
        //header("charset=utf-8");
          echo $app->request()->getResourceUri();
        
        $dbhostML = 'ds045679.mongolab.com:45679';
          // Connect to test database
          // users must be read only !
          // connect with a given user
        $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          // connect with a default user ?
          //$m1 = new Mongo("mongodb://$dbhostAD");
          //echo 'host: '.$m1;
          //echo "</br>";
          //echo 'base: '.$db1;
          //echo "</br>";
          // select the collection
        if (validAccess($db,$collection,$admin,$pass,$username,$userpass))
        {
            $collection = $m1->selectDB($db)->selectCollection($collection);    // pull a cursor query
            $cursor = $collection->find();
          // juste pour tester l'encodage des caractères ...
            echo json_encode("éèçàë%ùil''l");
            echo json_encode(iterator_to_array($cursor));
        }
    }
);

//GET without authentification /db/article/:id -> list an article with :id
//GET with authentification /db/article/:id/:name/:pass -> list an article with :id and tokens
$app->get(
    '/db/article/:id/:name/:pass',
    function ($id,$name,$pass) use ($app) {
          //echo "Un seul article : $id";
          //header("Content-Type: application/json");
          echo $app->request()->getResourceUri();
          echo "\n";
          var_dump($app->request()->params());
          
          
          $dbhostML = 'ds045679.mongolab.com:45679';
          $dbnameML = 'artcom';
          
          // Connect to test database
          $password = "QMBD35BEI";
          // users must be read only !
          $usernameAD = "techspeech_db";
          $usernameML = "root";
          // connect with a given user
          $m1 = new Mongo("mongodb://${usernameML}:${password}@${dbhostML}/artcom");
          $db1 = $m1->$dbnameML;
          if (validAccess($name,$pass))
          {
              $collection = $m1->selectDB("artcom")->selectCollection("articles");    // pull a cursor query
              $myQuery = array("id" => $id);
              $cursor = $collection->find($myQuery);
              echo json_encode(iterator_to_array($cursor));
          }
    }
);
   
//GET without authentification /db/articles/:category -> list articles by one category
//GET with authentification /db/articles/:category/:name/:pass -> list articles by one category with tokens
$app->get(
    '/db/articles/:category/:name/:pass',
    function ($category,$name,$pass) {
          //echo "Un seul article : $id";
          //header("Content-Type: application/json");
          
          $dbhostML = 'ds045679.mongolab.com:45679';
          $dbnameML = 'artcom';
          
          // Connect to test database
          $password = "QMBD35BEI";
          // users must be read only !
          $usernameAD = "techspeech_db";
          $usernameML = "root";
          // connect with a given user
          $m1 = new Mongo("mongodb://${usernameML}:${password}@${dbhostML}/artcom");
          $db1 = $m1->$dbnameML;
          if (validAccess($name,$pass))
          {
              $collection = $m1->selectDB("artcom")->selectCollection("articles");    // pull a cursor query
              $myQuery = array("category" => $category);
              $cursor = $collection->find($myQuery);
              echo json_encode(iterator_to_array($cursor));
          }
    }
);

//GET without authentification /db/articlesorderbyrateasc -> list all articles order by rate, 1 : ASC, -1 : DESC
//GET with authentification /db/articlesorderbyrateasc/:name/:pass -> list all articles order by rate with tokens
$app->get(
    '/db/articlesorderbyrateasc/:name/:pass',
    function ($name,$pass) {
          //echo "Un seul article : $id";
          //header("Content-Type: application/json");
          
          $dbhostML = 'ds045679.mongolab.com:45679';
          $dbnameML = 'artcom';
          
          // Connect to test database
          $password = "QMBD35BEI";
          // users must be read only !
          $usernameAD = "techspeech_db";
          $usernameML = "root";
          // connect with a given user
          $m1 = new Mongo("mongodb://${usernameML}:${password}@${dbhostML}/artcom");
          $db1 = $m1->$dbnameML;
          if (validAccess($name,$pass))
          {
              $collection = $m1->selectDB("artcom")->selectCollection("articles");    // pull a cursor query
              $mySort = array("rate" => 1);
              $cursor = $collection->find()->sort($mySort);
              echo json_encode(iterator_to_array($cursor));
          }
    }
);
  
//GET without authentification /db/articlesorderbyratedesc -> list all articles order by rate, 1 : ASC, -1 : DESC
//GET with authentification /db/articlesorderbyratedesc/:name/:pass -> list all articles order by rate with tokens
$app->get(
        '/db/articlesorderbyratedesc/:name/:pass',
        function ($name,$pass) {
          //echo "Un seul article : $id";
          //header("Content-Type: application/json");
          
          $dbhostAD = 'mongodb-techspeech.alwaysdata.net';
          $dbhostML = 'ds045679.mongolab.com:45679';
          $dbnameAD = 'techspeech_artcom';
          $dbnameML = 'artcom';
          
          // Connect to test database
          $password = "QMBD35BEI";
          // users must be read only !
          $usernameAD = "techspeech_db";
          $usernameML = "root";
          // connect with a given user
          $m1 = new Mongo("mongodb://${usernameML}:${password}@${dbhostML}/artcom");
          $db1 = $m1->$dbnameML;
          if (validAccess($name,$pass))
          {
              $collection = $m1->selectDB("artcom")->selectCollection("articles");    // pull a cursor query
              $mySort = array("rate" => -1);
              $cursor = $collection->find()->sort($mySort);
              echo json_encode(iterator_to_array($cursor));
          }
    }
);
    
    
// POST route
$app->post(
    '/db/article/:name/:pass',
    function ($user,$pass) use ($app) {
           // le problème est que ici l'URL ressemble à ça :
           // http://techspeech.alwaysdata.net/db/article/seb/seb?name=toto&test=titi
           // donc seb doit s'arrêter au ?, sinon le pass c'est seb?name...
           //$cheminComplet = $app->request()->getPath();
           // var_dump[$_SERVER]; pour debugger les paramètres envoyer
           //echo $_GET["name"]; pour récupérer la valeur de name
           
           // L'url complète est récupérée
           $cheminComplet = $app->request()->getPath();
           // On enlève tous les paramètres après le Query
           $url = strtok($cheminComplet, '?');
           // On découpe la chaîne suivant les /
           ///db/article/seb/seb
           $arr = explode('/', $cheminComplet);
           // la 4 case du tableau est toujours le pass !
           $pass = $arr[3];
           //echo ':'. $pass .':';
           $name = trim(strip_tags($app->request->params('name')));
           //echo ':' . $name . ':';
           $test = trim(strip_tags($app->request->params('test')));
           //echo ':' . $test . ':';
           $dbhostAD = 'mongodb-techspeech.alwaysdata.net';
           $dbhostML = 'ds045679.mongolab.com:45679';
           $dbnameAD = 'techspeech_artcom';
           $dbnameML = 'artcom';
           $insertOptions = array(
                                  'safe'    => true,
                                  'fsync'   => true,
                                  'timeout' => 10000
                                  );
           // Connect to test database
           $password = "9XTN#ztXmFnWH&";
           // users must be read only !
           $usernameAD = "techspeech_db";
           $usernameML = "admin";
           // connect with a given user
           $m1 = new Mongo("mongodb://${usernameML}:${password}@${dbhostML}/artcom");
           $db1 = $m1->$dbnameML;
           if (validAccess($user,$pass))
           {
            $collection = $m1->selectDB("artcom")->selectCollection("articles");
            $results = $collection->insert(array('name' => $name, 'test' => $test),$insertOptions);
            //print_r($results);
           }

    }
);

// PUT route
$app->put(
    '/db/article/:id/:name/:pass',
    function ($id,$name,$pass) {
        echo 'This is a PUT route';
    }
);

// PATCH route
$app->patch('/patch', function () {
    echo 'This is a PATCH route';
});

// DELETE route
$app->delete(
    '/db/article/:id/:name/:pass',
    function ($id,$name,$pass) {
        echo 'This is a DELETE route';
    }
);

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
?>
