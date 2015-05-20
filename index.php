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
//GET with authentification /:db/:collection/:admin/:pass/:username/:userpass -> list all articles with tokens
$app->get(
    '/:db/:collection/:admin/:pass/:username/:userpass',
    function ($db,$collection,$admin,$pass,$username,$userpass) use ($app) {
        //echo "tous les articles !";
        //header("Content-Type: application/json");
        //header("charset=utf-8");
          echo $app->request()->getResourceUri();
          echo("\n");
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
        if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
        {
            $collection = $m1->selectDB($db)->selectCollection($collection);    // pull a cursor query
            $cursor = $collection->find();
          // juste pour tester l'encodage des caractères ...
            echo json_encode("éèçàë%ùil''l");
            echo json_encode(iterator_to_array($cursor));
        }
    }
);

//GET with authentification /:db/:collection/:admin/:pass/:username/:userpass/id/:id -> list an article with :id and tokens
$app->get(
    '/:db/:collection/:admin/:pass/:username/:userpass/id/:id',
    function ($db,$collection,$admin,$pass,$username,$userpass,$id) use ($app) {
          //echo "Un seul article : $id";
          //header("Content-Type: application/json");
          echo $app->request()->getResourceUri();
          echo("\n");
          $dbhostML = 'ds045679.mongolab.com:45679';
          // Connect to test database
          // users must be read only !
          // connect with a given user
          $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
          {
              $collection = $m1->selectDB($db)->selectCollection($collection);    // pull a cursor query
              $myQuery = array("id" => $id);
              $cursor = $collection->find($myQuery);
              echo json_encode(iterator_to_array($cursor));
          }
    }
);
   
//GET with authentification /:db/:collection/:admin/:pass/:username/:userpass/category/:category -> list articles by one category with tokens
$app->get(
    '/:db/:collection/:admin/:pass/:username/:userpass/category/:category',
    function ($db,$collection,$admin,$pass,$username,$userpass,$category) use ($app) {
          //echo "Un seul article : $id";
          //header("Content-Type: application/json");
          echo $app->request()->getResourceUri();
          echo("\n");
          $dbhostML = 'ds045679.mongolab.com:45679';
          // Connect to test database
          // users must be read only !
          // connect with a given user
          $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
          {
              $collection = $m1->selectDB($db)->selectCollection($collection);    // pull a cursor query
              $myQuery = array("category" => $category);
              $cursor = $collection->find($myQuery);
              echo json_encode(iterator_to_array($cursor));
          }
    }
);

//GET with authentification /:db/:collection/:admin/:pass/:username/:userpass/articlesorderbyrate/:order -> list all articles order by rate with tokens, order values 1 : ASC, -1 : DESC
$app->get(
    '/:db/:collection/:admin/:pass/:username/:userpass/articlesorderbyrate/:order',
    function ($db,$collection,$admin,$pass,$username,$userpass,$order) use ($app) {
          //echo "Un seul article : $id";
          //header("Content-Type: application/json");
          echo $app->request()->getResourceUri();
          echo("\n");
          $dbhostML = 'ds045679.mongolab.com:45679';
          // Connect to test database
          // users must be read only !
          // connect with a given user
          $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
          {
              $collection = $m1->selectDB($db)->selectCollection($collection);    // pull a cursor query
              $mySort = array("rate" => intval($order));
              $cursor = $collection->find()->sort($mySort);
              echo json_encode(iterator_to_array($cursor));
          }
    }
);
  
// POST with authentification /:db/:collection/:admin/:pass/:username/:userpass -> insert an article in articles collection. Parameters accept blank as value.
$app->post(
    '/:db/:collection/:admin/:pass/:username/:userpass',
    function ($db,$collection,$admin,$pass,$username,$userpass) use ($app) {
           // le problème est que ici l'URL ressemble à ça :
           // http://techspeech.alwaysdata.net/apiartcom/artcom/articles/admin/9XTN#ztXmFnWH&/seb/seb?id=999&title=PROMO_CHEZ_KIKI&rate=100000
           // donc seb doit s'arrêter au ?, sinon le pass c'est seb?name...
           //$cheminComplet = $app->request()->getPath();
           // var_dump[$_SERVER]; pour debugger les paramètres envoyer
           //echo $_GET["name"]; pour récupérer la valeur de name
           
           // LE RATE DOIT ETRE A ZERO PAR DEFAUT
           
           // L'url complète est récupérée
           $cheminComplet = $app->request()->getPath();
           // On enlève tous les paramètres après le Query
           $url = strtok($cheminComplet, '?');
           // On découpe la chaîne suivant les /
           ///:db/:collection/:admin/:pass/:username/:userpass
           $arr = explode('/', $cheminComplet);
           // la case 8 du tableau est toujours le pass !
           $userpass = $arr[7];
           //echo ':'. $userpass .':';
           
           //$id = trim(strip_tags($app->request->params('id')));
           $title = trim(strip_tags($app->request->params('title')));
           $subtitle = trim(strip_tags($app->request->params('subtitle')));
           $category = trim(strip_tags($app->request->params('category')));
           // Le blancs sont gérés sans problème
           $text = trim(strip_tags($app->request->params('text')));
           $image = trim(strip_tags($app->request->params('image')));
           $rate = trim(strip_tags($app->request->params('rate')));
           
           $dbhostML = 'ds045679.mongolab.com:45679';
           $insertOptions = array(
                                  'safe'    => true,
                                  'fsync'   => true,
                                  'timeout' => 10000
                                  );
           // Connect to test database
           // users must be not read only !
           // connect with a given user
           $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
           
           if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
           {
            $collection = $m1->selectDB($db)->selectCollection($collection);
            $results = $collection->insert(array(//'id' => $id,
                                                 'title' => $title,
                                                 'subtitle' => $subtitle,
                                                 'category' => $category,
                                                 'text' => $text,
                                                 'image' => $image,
                                                 'rate' => new MongoInt32($rate),
                                                 'timestamp' => new MongoTimeStamp(time())),$insertOptions);
                                                // TIME STAMP ?
            //permet de voir le id interne généré par mongodb
            //print_r($results);
           }
    }
);

// PUT with authentification /:db/:collection/:admin/:pass/:username/:userpass/updatearticles/:id update an article with id set other values if define.
$app->put(
    '/:db/:collection/:admin/:pass/:username/:userpass/updatearticles/:id',
    function ($db,$collection,$admin,$pass,$username,$userpass,$id) use ($app) {
          // L'url complète est récupérée
          $cheminComplet = $app->request()->getPath();
          // On enlève tous les paramètres après le Query
          $url = strtok($cheminComplet, '?');
          // On découpe la chaîne suivant les /
          ///:db/:collection/:admin/:pass/:username/:userpass
          $arr = explode('/', $cheminComplet);
          // la case 8 du tableau est toujours le pass !
          $userpass = $arr[7];
          //echo ':'. $userpass .':';
          
          $title = trim(strip_tags($app->request->params('title')));
          $subtitle = trim(strip_tags($app->request->params('subtitle')));
          $category = trim(strip_tags($app->request->params('category')));
          // Le blancs sont gérés sans problème
          $text = trim(strip_tags($app->request->params('text')));
          $image = trim(strip_tags($app->request->params('image')));
          $rate = trim(strip_tags($app->request->params('rate')));
          
          $dbhostML = 'ds045679.mongolab.com:45679';
          $insertOptions = array(
                                 'safe'    => true,
                                 'fsync'   => true,
                                 'timeout' => 10000
                                 );
          // Connect to test database
          // users must be not read only !
          // connect with a given user
          $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          
          if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
          {
          echo $id;
          $identifiant = array('$id' => $id);
          $identifiant = json_encode($identifiant);
          $collection = $m1->selectDB($db)->selectCollection($collection);
          $results = $collection->update(array('_id' => new MongoId($id)), array('$set' => array('title' => $title,
                                                                                   'subtitle' => $subtitle,
                                                                                   'category' => $category,
                                                                                   'text' => $text,
                                                                                   'image' => $image,
                                                                                   'rate' => new MongoInt32($rate),
                                                                                   )));
          
          //permet de voir le id interne généré par mongodb
          print_r($results);
          }
    }
);

// PUT with authentification /:db/:collection/:admin/:pass/:username/:userpass/updateusers/:id update a user with id set other values if define.
$app->put(
          '/:db/:collection/:admin/:pass/:username/:userpass/updateusers/:id',
          function ($db,$collection,$admin,$pass,$username,$userpass,$id) use ($app) {
          // L'url complète est récupérée
          $cheminComplet = $app->request()->getPath();
          // On enlève tous les paramètres après le Query
          $url = strtok($cheminComplet, '?');
          // On découpe la chaîne suivant les /
          ///:db/:collection/:admin/:pass/:username/:userpass
          $arr = explode('/', $cheminComplet);
          // la case 8 du tableau est toujours le pass !
          $userpass = $arr[7];
          //echo ':'. $userpass .':';
          
          $title = trim(strip_tags($app->request->params('title')));
          $subtitle = trim(strip_tags($app->request->params('subtitle')));
          $category = trim(strip_tags($app->request->params('category')));
          // Le blancs sont gérés sans problème
          $text = trim(strip_tags($app->request->params('text')));
          $image = trim(strip_tags($app->request->params('image')));
          $rate = trim(strip_tags($app->request->params('rate')));
          
          $dbhostML = 'ds045679.mongolab.com:45679';
          $insertOptions = array(
                                 'safe'    => true,
                                 'fsync'   => true,
                                 'timeout' => 10000
                                 );
          // Connect to test database
          // users must be not read only !
          // connect with a given user
          $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          
          if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
          {
          $collection = $m1->selectDB($db)->selectCollection($collection);
          $results = $collection->update(array('id' => $id), array('$set' => array('title' => $title,
                                                                                   'subtitle' => $subtitle,
                                                                                   'category' => $category,
                                                                                   'text' => $text,
                                                                                   'image' => $image,
                                                                                   'rate' => new MongoInt32($rate),
                                                                                   )));
          
          //permet de voir le id interne généré par mongodb
          print_r($results);
          }
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
